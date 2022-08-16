<?php

global $USER;

$showFooter = false;

$jsAuthVariable = substr(str_shuffle(str_repeat("abcdefghijklmnopqrstuvwxyz", 20)), 0, 20);

if ($_REQUEST['ajax_mode'] == 'Y') {
    require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";
    CJSCore::Init(["popup", "jquery"]);
    if ($USER->GetID()) {
        //$newsId = intval($_REQUEST['newsID']);
        $APPLICATION->IncludeComponent("bitrix:system.auth.form", "", array());
        echo '<br>Вы авторизовались, обновление страницы...';
        //echo $newsId;
        echo "<script>setTimeout(function(){BX.setCookie('newsID', newsID, {expires: 1000, path: '/'});window.location.href = window.location.href}, 2000);</script>";
    } else {
        $APPLICATION->AuthForm('', false, false);
    }
    die;
} elseif (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
    $APPLICATION->SetTitle("Авторизация");

    define("NEED_AUTH", true);

    if (is_string($_REQUEST["backurl"]) && strpos($_REQUEST["backurl"], "/") === 0) {
        LocalRedirect($_REQUEST["backurl"]);
    }
    ?>
    <p>Вы зарегистрированы и успешно авторизовались.</p>

    <p>
        Используйте административную панель в верхней части экрана для быстрого доступа к функциям управления структурой
        и информационным наполнением сайта. Набор кнопок верхней панели отличается для различных разделов сайта. Так
        отдельные наборы действий предусмотрены для управления статическим содержимым страниц, динамическими
        публикациями (новостями, каталогом, фотогалереей) и т.п.
    </p>
    <p>
        <a href="<?= SITE_DIR ?>">Вернуться на главную страницу</a>
    </p>
    <?php $showFooter = true;
}

CJSCore::Init(["popup", "jquery"]);

// https://habr.com/ru/sandbox/103916/ - Основа скрипта
// https://dev.1c-bitrix.ru/community/webdev/user/64008/blog/5942/ - BX.PopupWindow
// http://realty.lyrmin.ru/bitrix/js/main/core/core_popup.js - BX.PopupWindowManager, onAfterPopupShow
// https://dev.1c-bitrix.ru/api_help/js_lib/ajax/bx_ajax.php - BX.ajax
// http://realty.lyrmin.ru/bitrix/js/main/core/core_ajax.js - BX.ajax.prepareForm
// https://dev.1c-bitrix.ru/api_help/main/reference/cmain/authform.php - $APPLICATION->AuthForm

?>
<?php if ($USER->IsAuthorized()): ?>
    <a href="<?= $APPLICATION->GetCurPage() ?>?logout=yes" rel="nofollow" style="display:none;"><b>Выход</b></a>
<?php else: ?>
    <a href="#" onclick="<?= $jsAuthVariable ?>.showPopup('/auth/')" rel="nofollow" style="display:none;"><b>Авторизация</b></a>
<?php endif ?>

<script>
    let <?=$jsAuthVariable?>;
    let newsID;
    $(document).ready(function () {
        function actionFavourite(_) {
            let newsID, action, newsLink;

            if (BX.getCookie('newsID')) {
                newsID = BX.getCookie('newsID');
                action = "add";
                BX.setCookie('newsID', newsID, {expires: 0, path: '/'});
            } else {
                newsLink = $(_);
                newsID = newsLink.data("id");
                action = newsLink.hasClass("hldel") ? "delete" : "add";
            }

            if (!newsID || !<?=intval($USER->IsAuthorized())?>) {
                newsID = '';
                action = "get";
            }

            BX.ajax({
                url: '<?= SITE_TEMPLATE_PATH . '/include/favourite/ajax.php' ?>',
                data: {'newsID': newsID, 'action': action, 'AJAX_REQUEST': 'Y'},
                method: 'POST',
                dataType: 'json',
                timeout: 30,
                async: true,
                processData: true,
                scriptsRunFirst: true,
                emulateOnload: true,
                start: true,
                cache: false,
                onsuccess: function (data) {
                    if (data.RESULT === "OK") {
                        $('a.hl').show();
                        $('a.hldel').hide();
                        if (data.hasOwnProperty('ITEMS')) {
                            for (let id of data.ITEMS) {
                                $('a.hl[data-id=' + id + ']').toggle();
                            }
                        }
                    }
                    let path = window.location.pathname;
                    let page = path.split("/").pop();

                    if (page === "favorite.php") location.reload();

                },
                onfailure: function (error) {
                    console.log('ajax error', error);
                }
            });
        }

        actionFavourite();

        $(".hl").click(function () {
            <? if (!$USER->IsAuthorized()): ?>
                newsID = $(this).data("id");

                <?=$jsAuthVariable?> = {
                    id: "modal_auth",
                    popup: null,
                    /**
                     * 1. Обработка ссылок в форме модального окна для добавления в ссылку события onclick и выполнения
                     * перехода по ссылке через запрос новой формы через AJAX
                     * 2. Установка на форму обработчика onsubmit вместо стандартного перехода
                     */
                    convertLinks: function () {
                        let links = $("#" + this.id + " a");
                        links.each(function (i) {
                            $(this).attr('onclick', "<?=$jsAuthVariable?>.set('" + $(this).attr('href') + "')");
                        });
                        links.attr('href', '#');

                        let form = $("#" + this.id + " form");
                        form.attr('onsubmit', "try{<?=$jsAuthVariable?>.submit('" + form.attr('action') + "');}catch (e) {console.log(e);};return false;");
                    },
                    /**
                     * Вывод модального окна с формой на странице при клике по ссылке
                     * @param url - url с параметрами для определения какую форму показать
                     */
                    showPopup: function (url) {
                        let app = this;
                        this.popup = BX.PopupWindowManager.create(this.id, '', {
                            closeIcon: true,
                            autoHide: true,
                            draggable: {
                                restrict: true
                            },
                            closeByEsc: true,
                            content: this.getForm(url),
                            overlay: {
                                backgroundColor: 'black',
                                opacity: '20'
                            },
                            events: {
                                onPopupClose: function (PopupWindow) {
                                    PopupWindow.destroy(); //удаление из DOM-дерева после закрытия
                                },
                                onAfterPopupShow: function (PopupWindow) {
                                    app.convertLinks();
                                }
                            }
                        });

                        this.popup.show();
                    },
                    /**
                     * Получение формы при открытии модального окна или при переходе по ссылке
                     * @param url - url с параметрами для определения какую форму показать
                     * @returns string - html код формы
                     */
                    getForm: function (url) {
                        let content = null;
                        url += (url.includes("?") ? '&' : '?') + 'ajax_mode=Y';
                        BX.ajax({
                            url: url,
                            method: 'GET',
                            dataType: 'html',
                            async: false,
                            preparePost: false,
                            start: true,
                            processData: false, // Ошибка при переходе по ссылкам в форме
                            skipAuthCheck: true,
                            onsuccess: function (data) {
                                let html = BX.processHTML(data);
                                content = html.HTML;
                            },
                            onfailure: function (html, e) {
                                console.error('getForm onfailure html', html, e, this);
                            }
                        });

                        return content;
                    },
                    /**
                     * Получение формы при переходе по ссылке и вывод её в модальном окне
                     * @param url - url с параметрами ссылки
                     */
                    set: function (url) {
                        let form = this.getForm(url);
                        this.popup.setContent(form);
                        this.popup.adjustPosition();
                        this.convertLinks();
                    },
                    /**
                     * Отправка данных формы и получение новой формы в ответе
                     * @param url - url с параметрами ссылки
                     */
                    submit: function (url) {
                        let app = this;
                        let form = document.querySelector("#" + this.id + " form");
                        let data = BX.ajax.prepareForm(form).data;
                        data.ajax_mode = 'Y';

                        BX.ajax({
                            url: url,
                            data: data,
                            method: 'POST',
                            preparePost: true,
                            dataType: 'html',
                            async: false,
                            start: true,
                            processData: true,
                            skipAuthCheck: true,
                            onsuccess: function (data) {
                                let html = BX.processHTML(data);
                                app.popup.setContent(html.HTML);
                                app.convertLinks();
                            },
                            onfailure: function (html, e) {
                                console.error('getForm onfailure html', html, e, this);
                            }
                        });
                    }
                };
                <?=$jsAuthVariable?>.showPopup('/auth/');
            <? endif; ?>

            actionFavourite(this);
        });
    });
</script>
<?php if ($showFooter) require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");