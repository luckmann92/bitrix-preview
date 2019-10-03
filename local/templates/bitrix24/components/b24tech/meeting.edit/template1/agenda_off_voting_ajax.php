<?php
define("STOP_STATISTICS", true);
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$voting_element_id = $_GET['id'];
$block_id = 30;
//Определяем ИБ по коду элемента, если ИБ не будет меняться, не подключать
//Если ИБ может быть другим, раскомментировать
//$block_id = CIBlockElement::GetIBlockByID($voting_element_id); // отдает id инфоблока '30'

//Устанавливаем значение свойству AGENDA_OFF_VOTING - по этому свойству будем определять, активирована кнопка или нет
$PROPERTY_CODE = "AGENDA_OFF_VOTING";// код свойства
$PROPERTY_VALUE = "Y";  // значение свойства "Y" (text) - активирована, " " или "N" - не активирована.
$SetValue = CIBlockElement::SetPropertyValues($voting_element_id, $block_id, $PROPERTY_VALUE, $PROPERTY_CODE);

//Массив с данными свойства для проверки
/*
$IsProp = CIBlockElement::GetProperty(30($block_id), 761($voting_element_id), "sort", "asc", array("NAME" => "AGENDA_OFF_VOTING"));
$propValue = $IsProp->Fetch();
echo '<pre>';
print_r($propValue);//выведет весь массив
print_r($propValue['VALUE']);//выведет значение
echo '</pre>';
*/

