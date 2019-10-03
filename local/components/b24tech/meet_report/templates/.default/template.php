<?php
/**
 * Created by PhpStorm.
 * User: scvairy
 * Date: 2019-02-11
 * Time: 04:47
 */


$APPLICATION->SetTitle("Список вопросов");

use \Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\PageNavigation;

$list_id = 'example_list';

$grid_options = new GridOptions($list_id);
$sort = $grid_options->GetSorting(['sort' => ['ID' => 'DESC'], 'vars' => ['by' => 'by', 'order' => 'order']]);
$nav_params = $grid_options->GetNavParams();

$nav = new PageNavigation('request_list');
$nav->allowAllRecords(true)
    ->setPageSize($nav_params['nPageSize'])
    ->initFromUri();

$filterOption = new Bitrix\Main\UI\Filter\Options($list_id);
$filterData = $filterOption->getFilter([]);
$filter = [];
var_dump($filterData);
foreach ($filterData as $k => $v) {
    // Тут разбор массива $filterData из формата, в котором его формирует main.ui.filter в формат, который подойдет для вашей выборки.
    // Обратите внимание на поле "FIND", скорее всего его вы и захотите засунуть в фильтр по NAME и еще паре полей
    $filter[$k] = $v;
//    $filter['TITLE'] = "%".$filterData['FIND']."%";
//    $filter['CATEGORY'] = "%".$filterData['FIND']."%";
}

$res = CMeetingItem::getList($sort['sort'], $filter, false, $nav->getOffset(), ['*']);
$res->NavStart($nav_params['nPageSize'], $nav_params['bShowAll'], $nav_params['iNumPage']);
//$res = CMeetingItem::getList([
//    'filter' => $filter,
//    'select' => [
//        "*",
//    ],
//    'offset'      => $nav->getOffset(),
//    'limit'       => $nav->getLimit(),
//    'order'       => $sort['sort']
//]);

$ui_filter = [
    ['id' => 'TITLE', 'name' => 'Название', 'type'=>'text', 'default' => true],
//    ['id' => 'CATEGORY', 'name' => 'Категория', 'type'=>'list', 'default' => true],
];
?>
    <h2>Фильтр</h2>
    <div>
        <?$APPLICATION->IncludeComponent('bitrix:main.ui.filter', '', [
            'FILTER_ID' => $list_id,
            'GRID_ID' => $list_id,
            'FILTER' => $ui_filter,
            'ENABLE_LIVE_SEARCH' => true,
            'ENABLE_LABEL' => true
        ]);?>
    </div>
    <div style="clear: both;"></div>

    <hr>

    <h2>Таблица</h2>
<?php
$columns = [];
$columns[] = ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => true];
$columns[] = ['id' => 'TITLE', 'name' => 'Название', 'sort' => 'TITLE', 'default' => true];
//$columns[] = ['id' => 'DATE_CREATE', 'name' => 'Создано', 'sort' => 'DATE_CREATE', 'default' => true];
$columns[] = ['id' => 'CATEGORY', 'name' => 'Категория', 'sort' => 'CATEGORY', 'default' => true];

for ($row = $res->GetNext(); !!($row); $row = $res->GetNext()) {
//for ($row = $res->fetch(); !is_null($row); $row = $res->fetch()) {
//    var_dump($row);
    $list[] = [
        'data' => [
            "ID" => $row['ID'],
            "TITLE" => $row['TITLE'],
//            "DATE_CREATE" => $row['DATE_CREATE'],
            "CATEGORY" => $row['CATEGORY'],
        ],
        'actions' => [
            [
                'text'    => 'Просмотр',
                'default' => true,
                'onclick' => 'document.location.href="?op=view&id='.$row['ID'].'"'
            ], [
                'text'    => 'Удалить',
                'default' => true,
                'onclick' => 'if(confirm("Точно?")){document.location.href="?op=delete&id='.$row['ID'].'"}'
            ]
        ]
    ];
}

$APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
    'GRID_ID' => $list_id,
    'COLUMNS' => $columns,
    'ROWS' => $list,
    'SHOW_ROW_CHECKBOXES' => false,
    'NAV_OBJECT' => $nav,
    'AJAX_MODE' => 'Y',
    'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
    'PAGE_SIZES' =>  [
        ['NAME' => '20', 'VALUE' => '20'],
        ['NAME' => '50', 'VALUE' => '50'],
        ['NAME' => '100', 'VALUE' => '100']
    ],
    'AJAX_OPTION_JUMP'          => 'N',
    'SHOW_CHECK_ALL_CHECKBOXES' => false,
    'SHOW_ROW_ACTIONS_MENU'     => true,
    'SHOW_GRID_SETTINGS_MENU'   => true,
    'SHOW_NAVIGATION_PANEL'     => true,
    'SHOW_PAGINATION'           => true,
    'SHOW_SELECTED_COUNTER'     => true,
    'SHOW_TOTAL_COUNTER'        => true,
    'SHOW_PAGESIZE'             => true,
    'SHOW_ACTION_PANEL'         => true,
    'ALLOW_COLUMNS_SORT'        => true,
    'ALLOW_COLUMNS_RESIZE'      => true,
    'ALLOW_HORIZONTAL_SCROLL'   => true,
    'ALLOW_SORT'                => true,
    'ALLOW_PIN_HEADER'          => true,
    'AJAX_OPTION_HISTORY'       => 'N'
]);
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>