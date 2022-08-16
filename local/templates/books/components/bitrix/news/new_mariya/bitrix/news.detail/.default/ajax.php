<?php
use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable as HL;
/*
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
    // подключаем пространство имен класса HighloadBlockTable и даём ему псевдоним HLBT для удобной работы

    use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
    // id highload-инфоблока
    const FAVOURITES_HLBLOCK_ID = 1;
    //подключаем модуль highloadblock
    Loader::includeModule("highloadblock");
    //Напишем функцию получения экземпляра класса:
    function GetEntityDataClass($HlBlockId)
    {
        if (empty($HlBlockId) || $HlBlockId < 1)
        {
            return false;
        }
        $hlblock = HLBT::getById($HlBlockId)->fetch();
        $entity = HLBT::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();
        return $entity_data_class;
    }

    Loader::includeModule("highloadblock");
    $entity_data_class = GetEntityDataClass(FAVOURITES_HLBLOCK_ID);

    if ( $_POST['delid']){
            $result = $entity_data_class::delete($_POST['delid']);
    } else {
        $result = $entity_data_class::add(array(
              'UF_NEWS'         => $_POST['newsID'],
              'UF_USER_ID'         => $_POST['userID'],
           ));
    }
    print_r($result);
*/
/////////////////////////////////////////////////////////////////////////

if (isset($_POST["AJAX_REQUEST"]) && $_POST["AJAX_REQUEST"] === "Y") {

//    print_r("23423423423424");
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

    Loader::includeModule("highloadblock");

    $entityHLBClass = HL::compileEntity(
        HL::getById(FAVOURITES_HLBLOCK_ID)->fetch()
    )->getDataClass();

    if (!check_bitrix_sessid()) {
        $arResult["ERROR"] = GetMessage("BCLMMD_ACCESS_DENIED");
    }

    if (!isset($arResult["ERROR"])) {
        $action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : "";
        $domain = isset($_REQUEST['domain']) ? trim($_REQUEST['domain']) : "";
        //$monitoring = \CBitrixCloudMonitoring::getInstance();
        $strError ="";// $monitoring->stopMonitoring($domain);

        switch ($action) {
            case 'add':
                $rsData = $entityHLBClass::add([
                     'UF_NEWS'         => $_POST['newsID'],
                     'UF_USER_ID'         => $_POST['userID'],
                ]);
                print_r($rsData);
                break;
            case 'delete':
                $rsData = $entityHLBClass::delete($_POST['delid']);
                if (strlen($strError) > 0)
                    $arResult["ERROR"] = $strError;
                break;
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