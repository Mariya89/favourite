<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
const MY_HL_BLOCK_ID = 1;
CJSCore::Init(array("jquery"));
global $USER;
$userID=$USER->GetID();

if($_REQUEST['del']){
		// подключаем пространство имен класса HighloadBlockTable и даём ему псевдоним HLBT для удобной работы
		// id highload-инфоблока
		//подключаем модуль highloadblock
		CModule::IncludeModule('highloadblock');
		//Напишем функцию получения экземпляра класса:
		$entity_data_class = GetEntityDataClass(MY_HL_BLOCK_ID);
		$result = $entity_data_class::add(array(
			  'UF_NEWS'         => $_REQUEST['del'],
			  'UF_USER_ID'         => $userID,
		   ));
}

?>
<div class="news-list">
<?if($arParams["DISPLAY_TOP_PAGER"]):?>
	<?=$arResult["NAV_STRING"]?><br/>
<?endif;
$i=0;
foreach($arResult["ITEMS"] as $arItem):
//print_r("Элемент массива №".$i);
//print_r($arItem);
	$i++;
	$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
	$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
	?>
	<p class="news-item" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
		<?if($arParams["DISPLAY_PICTURE"]!="N" && is_array($arItem["PREVIEW_PICTURE"])):?>
			<?if(!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])):?>
				<a href="<?=$arItem["DETAIL_PAGE_URL"]?>"><img
						class="preview_picture"
						border="0"
						src="<?=$arItem["PREVIEW_PICTURE"]["SRC"]?>"
						width="<?=$arItem["PREVIEW_PICTURE"]["WIDTH"]?>"
						height="<?=$arItem["PREVIEW_PICTURE"]["HEIGHT"]?>"
						alt="<?=$arItem["PREVIEW_PICTURE"]["ALT"]?>"
						title="<?=$arItem["PREVIEW_PICTURE"]["TITLE"]?>"
						style="float:left"
						/></a>
			<?else:?>
				<img
					class="preview_picture"
					border="0"
					src="<?=$arItem["PREVIEW_PICTURE"]["SRC"]?>"
					width="<?=$arItem["PREVIEW_PICTURE"]["WIDTH"]?>"
					height="<?=$arItem["PREVIEW_PICTURE"]["HEIGHT"]?>"
					alt="<?=$arItem["PREVIEW_PICTURE"]["ALT"]?>"
					title="<?=$arItem["PREVIEW_PICTURE"]["TITLE"]?>"
					style="float:left"
					/>
			<?endif;?>
		<?endif?>
		<?if($arParams["DISPLAY_DATE"]!="N" && $arItem["DISPLAY_ACTIVE_FROM"]):?>
			<span class="news-date-time"><?echo $arItem["DISPLAY_ACTIVE_FROM"]?></span>
		<?endif?>
		<?if($arParams["DISPLAY_NAME"]!="N" && $arItem["NAME"]):?>
			<?if(!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])):?>
				<a href="<?echo $arItem["DETAIL_PAGE_URL"]?>"><b><?echo $arItem["NAME"]?></b></a><br />
			<?else:?>
				<b><?echo $arItem["NAME"]?></b><br />
			<?endif;?>
		<?endif;?>
		<?if($arParams["DISPLAY_PREVIEW_TEXT"]!="N" && $arItem["PREVIEW_TEXT"]):?>
			<?echo $arItem["PREVIEW_TEXT"];?>
		<?endif;?>
		<?if($arParams["DISPLAY_PICTURE"]!="N" && is_array($arItem["PREVIEW_PICTURE"])):?>
			<div style="clear:both"></div>
		<?endif?>
		<?foreach($arItem["FIELDS"] as $code=>$value):?>
			<small>
			<?=GetMessage("IBLOCK_FIELD_".$code)?>:&nbsp;<?=$value;?>
			</small><br />
		<?endforeach;?>
		<?foreach($arItem["DISPLAY_PROPERTIES"] as $pid=>$arProperty):?>
			<small>
			<?=$arProperty["NAME"]?>:&nbsp;
			<?if(is_array($arProperty["DISPLAY_VALUE"])):?>
				<?=implode("&nbsp;/&nbsp;", $arProperty["DISPLAY_VALUE"]);?>
			<?else:?>
				<?=$arProperty["DISPLAY_VALUE"];?>
			<?endif?>
			</small><br />
		<?endforeach;?>
	<?if (!$arItem["FAVORITE"]){?>
	<a class="hl"  onclick="event.preventDefault()"  href="" data-id="<?=$arItem["ID"];?>">Добавить в избранное</a>
	<a href=""  onclick="event.preventDefault()" data-id="<?=$arItem["ID"];?>"  class="hl hldel" style="display:none;">Удалить из избранного</a>
	<?}
	else{?>
	<a class="hl"  onclick="event.preventDefault()"  href="" data-id="<?=$arItem["ID"];?>"  style="display:none;">Добавить в избранное</a>
	<a href=""  onclick="event.preventDefault()" data-id="<?=$arItem["ID"];?>"  class="hl hldel"    data-favid="<?=$arItem["FAVORITE"];?>">Удалить из избранного</a>
	<?}?>
	</p>
<?endforeach;?>
<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
	<br /><?=$arResult["NAV_STRING"]?>
<?endif;?>
</div>
<script>
<?
	$jsAuthVariable = substr(str_shuffle(str_repeat("abcdefghijklmnopqrstuvwxyz", 20)), 0, 20);
?>
var <?=$jsAuthVariable?>;
var newsID;
$( ".hl" ).click(function() {
	let newsLink=$(this);
	newsID=newsLink.data("id");
	console.log("Пытаемся залогиниться");
	<? if (!$USER->IsAuthorized()): ?>
		<?/*$jsAuthVariable = \Bitrix\Main\Security\Random::getString(20)*/?>

	<?=$jsAuthVariable?> = {
            id: "modal_auth",
            popup: null,
            /**
             * 1. Обработка ссылок в форме модального окна для добавления в ссылку события onclick и выполнения
             * перехода по ссылке через запрос новой формы через AJAX
             * 2. Установка на форму обработчика onsubmit вместо стандартного перехода
             */
            convertLinks: function() {
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
            showPopup: function(url) {
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
                        onPopupClose: function(PopupWindow) {
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
            getForm: function(url) {
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
                    onsuccess: function(data) {
                        let html = BX.processHTML(data);
                        content = html.HTML;
                    },
                    onfailure: function(html, e) {
                        console.error('getForm onfailure html', html, e, this);
                    }
                });

                return content;
            },
            /**
             * Получение формы при переходе по ссылке и вывод её в модальном окне
             * @param url - url с параметрами ссылки
             */
            set: function(url) {
                let form = this.getForm(url);
                this.popup.setContent(form);
                this.popup.adjustPosition();
                this.convertLinks();
            },
            /**
             * Отправка данных формы и получение новой формы в ответе
             * @param url - url с параметрами ссылки
             */
            submit: function(url) {
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
                    onsuccess: function(data) {
                        let html = BX.processHTML(data);
                        app.popup.setContent(html.HTML);
                        app.convertLinks();
                    },
                    onfailure: function(html, e) {
                        console.error('getForm onfailure html', html, e, this);
                    }
                });
            }
        };
		<?=$jsAuthVariable?>.showPopup('/auth/');
	<? endif ?>
		<?if($userID){?>

	let favoriteID=newsLink.data("favid");
	newsLink.toggle();
	newsLink.siblings(".hl").toggle();
	let newsAction="add";
	if(!newsLink.hasClass( "hldel" )){
		favoriteID="";
	}else{
		newsAction="delete";
	}

	BX.ajax({
           url: '<?=$templateFolder.'/ajax.php';?>',
           data: {'newsID':newsID, 'action':newsAction, 'userID':<?=$userID;?>,'delid':favoriteID,'AJAX_REQUEST':'Y'},
           method: 'POST',
           dataType: 'json',
           timeout: 30,
           async: true,
           processData: true,
           scriptsRunFirst: true,
           emulateOnload: true,
           start: true,
           cache: false,
           onsuccess: function(data){
            console.log(data);

           },
           onfailure: function(){

           }
          }); 
	<?}?>
});

</script>