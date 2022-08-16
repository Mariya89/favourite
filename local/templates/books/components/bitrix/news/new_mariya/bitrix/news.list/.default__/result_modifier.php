<?php
// подключаем пространство имен класса HighloadBlockTable и даём ему псевдоним HLBT для удобной работы
use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
// id highload-инфоблока
const MY_HL_BLOCK_ID = 1;
//подключаем модуль highloadblock
CModule::IncludeModule('highloadblock');
global $USER;
$userID=$USER->GetID();
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

const MY_HL_BLOCK_ID = 1;
CModule::IncludeModule('highloadblock');
$entity_data_class = GetEntityDataClass(MY_HL_BLOCK_ID);
$rsData = $entity_data_class::getList(array(
    'select' => array('*'),'filter' => array('UF_USER_ID' => $userID)
));

$items=&$arResult['ITEMS'];
while($elHighLoad = $rsData->fetch()){
	$keys = array_keys(array_column($items, 'ID'), $elHighLoad['UF_NEWS']);
	if(!empty($keys)){
		$key=$keys[0];
		$items[$key]['FAVORITE']=$elHighLoad['ID'];
		print_r($elHighLoad['ID'].";" );
	}
}



?>