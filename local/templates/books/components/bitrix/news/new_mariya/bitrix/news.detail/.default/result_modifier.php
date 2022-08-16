<?php
// подключаем пространство имен класса HighloadBlockTable и даём ему псевдоним HLBT для удобной работы
use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable as HL;
//подключаем модуль highloadblock
Loader::includeModule("highloadblock");
global $USER;
$userId=$USER->GetID();
//Напишем функцию получения экземпляра класса:
$entityHLBClass = HL::compileEntity(
        HL::getById(FAVOURITES_HLBLOCK_ID)->fetch()
    )->getDataClass();

$rsData = $entityHLBClass::getList([
    'select' => ['ID'],'filter' => ['UF_USER_ID' => $userId,'UF_NEWS'=>$arResult['ID']]
]);
while($elHighLoad = $rsData->fetch()){
	$arResult['FAVOURITE']=$elHighLoad['ID'];
}
?>