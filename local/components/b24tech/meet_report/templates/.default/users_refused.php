<?php
/**
 * Created by PhpStorm.
 * User: scvairy
 * Date: 2019-02-11
 * Time: 04:47
 */


$APPLICATION->SetTitle("Процент участников");

$arResult['USERS'] = array();
$dbUsers = CUser::GetList($by='ID', $order='ASC', array('ID' => implode('|', array_keys($arResult['MEETING']['USERS']))));
while ($arUser = $dbUsers->GetNext())
{
    $arResult['USERS'][$arUser['ID']] = $arUser;
}

$arUsers = array('O' => array(), 'K' => array(), 'M' => array(), 'R' => array());

foreach ($arResult['MEETING']['USERS'] as $USER_ID => $USER_ROLE):
    if($arResult['MEETING']['USERS_EVENT'][$USER_ID] == 'N')
        $USER_ROLE = 'R';

    $arUsers[$USER_ROLE][] = $USER_ID;
endforeach;

?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>