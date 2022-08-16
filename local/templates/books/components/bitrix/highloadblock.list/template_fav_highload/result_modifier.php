<?php
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
/*
// подключаем пространство имен класса HighloadBlockTable и даём ему псевдоним HLBT для удобной работы
use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
// id highload-инфоблока

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
$rsData = $entity_data_class::getList(array(
    'select' => array('*')
));

$items=&$arResult['ITEMS'];
while($elHighLoad = $rsData->fetch()){
	$keys = array_keys(array_column($items, 'ID'), $elHighLoad['UF_NEWS']);
    	//print_r($elHighLoad );

	if(!empty($keys)){
		$key=$keys[0];
		$items[$key]['FAVOURITE']=true;
	}
}*/
// PSR 12

global $USER;

if ($userId = $USER->GetID()) {
    $rowsFavorite = &$arResult['rows'];
    $rowsFavorite = array_filter($rowsFavorite, function($element)  use ($userId) {
        return $element['UF_USER_ID'] == $userId;
    });
}
?>
<pre>
<?php
print_r($arResult);
