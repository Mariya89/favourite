<?php

use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable as HL;

if (isset($_POST["AJAX_REQUEST"]) && $_POST["AJAX_REQUEST"] === "Y") {
    define('NO_KEEP_STATISTIC', true);
    define('NO_AGENT_STATISTIC', true);
    define('NOT_CHECK_PERMISSIONS', true);
    define('DisableEventsCheck', true);

    require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

    global $APPLICATION;
    global $USER;

    $arResult = [];
    $arResult["RESULT"] = "ERROR";
    $userId = $USER->GetID();

    if (!check_bitrix_sessid()) {
        $arResult["ERROR"] = GetMessage("BCLMMD_ACCESS_DENIED");
    }

    if (!isset($arResult["ERROR"])) {
        $action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : "";
        $domain = isset($_REQUEST['domain']) ? trim($_REQUEST['domain']) : "";
        $strError = "";

        Loader::includeModule("highloadblock");

        $entityHLBClass = HL::compileEntity(
            HL::getById(FAVOURITES_HLBLOCK_ID)->fetch()
        )->getDataClass();

        switch ($action) {
            case 'add':
                $rsData = $entityHLBClass::add([
                    'UF_NEWS' => $_POST['newsID'],
                    'UF_USER_ID' => $userId,
                ]);
                if (!$rsData->isSuccess()) {
                    $strError = $rsData->getErrorMessages();
                }
                if (strlen($strError) > 0) {
                    $arResult["ERROR"] = $strError;
                }
                break;
            case 'delete':
                $rsData = $entityHLBClass::getList([
                    'select' => ['*'],
                    'filter' => [
                        'UF_USER_ID' => $userId,
                        'UF_NEWS' => $_POST['newsID']
                    ]
                ]);

                while ($elHighLoad = $rsData->fetch()) {
                    $rsDataDel = $entityHLBClass::delete($elHighLoad['ID']);
                    if (!$rsDataDel->isSuccess()) {
                        $strError = $rsDataDel->getErrorMessages();
                    }
                }
                if (strlen($strError) > 0) {
                    $arResult["ERROR"] = $strError;
                }
                break;

            default:
                break;
        }

        if ($userId) {
            $rsData = $entityHLBClass::getList([
                'select' => ['*'],
                'filter' => ['UF_USER_ID' => $userId]
            ]);
            while ($elHighLoad = $rsData->fetch()) {
                $arResult["ITEMS"][] = $elHighLoad["UF_NEWS"];
            }
        }

        if (isset($arResult["ERROR"])) {
            $arResult["RESULT"] = "ERROR";
        } else {
            $arResult["RESULT"] = "OK";
        }
    }

    $arResult = $APPLICATION->ConvertCharsetArray($arResult, SITE_CHARSET, 'utf-8');
    header('Content-Type: application/json; charset=utf-8');
    die(\Bitrix\Main\Web\Json::encode($arResult));
}
