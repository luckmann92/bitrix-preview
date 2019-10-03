<?php
/**
 * Created by PhpStorm.
 * User: scvairy
 * Date: 2019-02-11
 * Time: 04:47
 */


$APPLICATION->SetTitle("Список отчётов");

use \Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\PageNavigation;

$list_id = 'meetings_list';

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
//var_dump($filterData);
foreach ($filterData as $k => $v) {
    // Тут разбор массива $filterData из формата, в котором его формирует main.ui.filter в формат, который подойдет для вашей выборки.
    // Обратите внимание на поле "FIND", скорее всего его вы и захотите засунуть в фильтр по NAME и еще паре полей
    $filter[$k] = $v;
//    $filter['TITLE'] = "%".$filterData['FIND']."%";
//    $filter['CATEGORY'] = "%".$filterData['FIND']."%";
}

$res = CMeeting::getList($sort['sort'], $filter, false, $nav->getOffset(), ['*']);
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
<!--    <h2>Фильтр</h2>-->
    <div>
<!--        --><?//$APPLICATION->IncludeComponent('bitrix:main.ui.filter', '', [
//            'FILTER_ID' => $list_id,
//            'GRID_ID' => $list_id,
//            'FILTER' => $ui_filter,
//            'ENABLE_LIVE_SEARCH' => true,
//            'ENABLE_LABEL' => true
//        ]);?>
    </div>
    <div style="clear: both;"></div>

<!--    <hr>-->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <div id="CountByCollegiate"></div>
    <script>
        google.charts.load('current', {packages: ['corechart', 'bar']});
        google.charts.setOnLoadCallback(drawBasic);

        function drawBasic() {

            var data = new google.visualization.arrayToDataTable([
                ['Element', 'Количество заседаний', {role: 'annotation'}],
                <?foreach ($arResult['CountByCollegiateAndFrom'] as $item => $value):?>
                ['<?=$item ? 'Комитет' : 'Совет директоров'?>', <?=$value['COUNT']?>, <?=$value['COUNT']?>],
                <?endforeach;?>
            ]);

            var chart = new google.visualization.ColumnChart(
                document.getElementById('CountByCollegiate'));

            var options = {
                title: "Количество заседаний в <?=$arResult['DATE_YEAR']?> году",
                width: 600,
                height: 400,
                // legend: { position: 'top', maxLines: 3 },
                bar: { groupWidth: '75%' },
                annotations: {
                    alwaysOutside: true,
                    textStyle: {
                        fontSize: 14,
                        color: '#000',
                        auraColor: 'none'
                    }
                },
                // isStacked: true,
            };

            chart.draw(data, options);
        }
    </script>
    <div id="CountByCollegiateAndForm"></div>
    <script>
        google.charts.load('current', {packages: ['corechart', 'bar']});
        google.charts.setOnLoadCallback(drawBasic2);

        function drawBasic2() {

            var data = new google.visualization.arrayToDataTable([
                ['Element', 'Количество очных', {role: 'annotation'},  'Количество заочных', {role: 'annotation'}],

                <?foreach ($arResult['CountByCollegiateAndFrom'] as $item => $value):?>
                ['<?=$item ? 'Комитет' : 'Совет директоров'?>', <?=$value['INTERNAL'] ?? 0?>, <?=$value['INTERNAL'] ?? 0?>, <?=$value['EXTERNAL'] ?? 0?>, <?=$value['EXTERNAL'] ?? 0?>],
                <?endforeach;?>
<?//foreach ($arResult['CountByCollegiateAndForm'] as $item => $value):?>
                //['<?//=$item ? 'Комитет' : 'Совет директоров'?>//', <?//=$value['INTERNAL']?>//, <?//=$value['EXTERNAL']?>//],
<?//endforeach;?>
            ]);

            var chart = new google.visualization.ColumnChart(
                document.getElementById('CountByCollegiateAndForm'));

            var options = {
                title: "Форма проведения заседаний",
                width: 600,
                height: 400,
                legend: { position: 'top', maxLines: 3 },
                bar: { groupWidth: '75%' },
                isStacked: true,
            };

            chart.draw(data, options);
        }
    </script>
    <div id="Visitors"></div>
    <script>
        google.charts.load('current', {packages: ['corechart', 'bar']});
        google.charts.setOnLoadCallback(drawBasic3);

        function drawBasic3() {

            var data = new google.visualization.arrayToDataTable([
                ['Element', 'Процент', {role: 'annotation'}],

                <?/*foreach ($arResult['CountByCollegiateAndFrom'] as $item => $value):?>
                ['<?=$item ? 'Комитет' : 'Совет директоров'?>', <?=$value['VISITORS_PERCENTAGE']?>, '<?=$value['VISITORS_PERCENTAGE'] * 100?>%'],
                <?endforeach;*/?>


                <?$value = $arResult['CountByCollegiateAndFrom'][0];?>
                ['<?='Совет директоров'?>', <?=$value['VISITORS_PERCENTAGE']?>, '<?=$value['VISITORS_PERCENTAGE'] * 100?>%'],
                <?$value2 = $arResult['CountByCollegiateAndFrom'][1];
                $value2['VISITORS_PERCENTAGE'] = 1-$value['VISITORS_PERCENTAGE'];
                ?>
                ['<?='Комитет'?>', <?=$value2['VISITORS_PERCENTAGE']?>, '<?=$value2['VISITORS_PERCENTAGE'] * 100?>%'],


                <?/*foreach ($arResult['CountByCollegiateAndForm'] as $item => $value):?>
                                ['<?=$item ? 'Комитет' : 'Совет директоров'?>', <?=$value['INTERNAL']?>, <?=$value['EXTERNAL']?>],
                <?endforeach;*/?>
            ]);

            var chart = new google.visualization.ColumnChart(
                document.getElementById('Visitors'));

            var options = {
                title: "Посещаемость",
                width: 600,
                height: 400,
                legend: { position: 'none' },
                bar: { groupWidth: '75%' },
                vAxis: {
                    format: 'percent',
                },
            };

            chart.draw(data, options);
        }
    </script>
    <div id="Items"></div>
    <script>
        google.charts.load('current', {packages: ['corechart', 'bar']});
        google.charts.setOnLoadCallback(drawBasic4);

        function drawBasic4() {

            var data = new google.visualization.arrayToDataTable([
                ['Element', 'Количество рассмотренных вопросов', {role: 'annotation'}],

                <?foreach ($arResult['CountItems'] as $item => $value):?>
                ['<?=$item ? 'Комитет' : 'Совет директоров'?>', <?=$value?>, <?=$value?>],
                <?endforeach;?>
<?//foreach ($arResult['CountByCollegiateAndForm'] as $item => $value):?>
                //['<?//=$item ? 'Комитет' : 'Совет директоров'?>//', <?//=$value['INTERNAL']?>//, <?//=$value['EXTERNAL']?>//],
<?//endforeach;?>
            ]);

            var chart = new google.visualization.ColumnChart(
                document.getElementById('Items'));

            var options = {
                title: "Количество рассмотренных вопросов",
                width: 600,
                height: 400,
                legend: { position: 'none' },
                bar: { groupWidth: '75%' },
                annotations: {
                    alwaysOutside: true,
                    textStyle: {
                        fontSize: 14,
                        color: '#000',
                        auraColor: 'none'
                    }
                },
                // vAxis: {
                //     format: 'percent',
                // },
            };

            chart.draw(data, options);
        }
    </script>
    <div id="ItemsByCategory"></div>
    <script>
        google.charts.load('current', {packages: ['corechart', 'bar']});
        google.charts.setOnLoadCallback(drawBasic5);

        function drawBasic5() {

            var data = new google.visualization.arrayToDataTable([
                ['Element'
                    <?foreach ($arResult['Categories'] as $item):?>
                    ,'<?=$item?>', {role: 'annotation'}
                    <?endforeach;?>
                ],

                <?foreach ($arResult['CountItemsByCategory'] as $collegiate => $value):?>
                ['<?=$collegiate?>',
                    <?foreach ($arResult['Categories'] as $category):?>
                        <?=$value[$category] ?? 0?>, <?=$value[$category] ?? 0?>,
                    <?endforeach;?>
                ],
                <?endforeach;?>
<?//foreach ($arResult['CountByCollegiateAndForm'] as $item => $value):?>
                //['<?//=$item ? 'Комитет' : 'Совет директоров'?>//', <?//=$value['INTERNAL']?>//, <?//=$value['EXTERNAL']?>//],
<?//endforeach;?>
            ]);

            var chart = new google.visualization.BarChart(
                document.getElementById('ItemsByCategory'));

            var options = {
                title: "Структура рассмотренных вопросов",
                width: 800,
                height: 400,
                legend: { position: 'right' },//, maxLines: 4 },
                bar: { groupWidth: '75%' },
                chartArea: {width: '45%'},
                isStacked: 'percent',
                annotations: {
                    // alwaysOutside: true,
                    textStyle: {
                        fontSize: 12,
                        auraColor: 'none',
                        color: '#555'
                    },
                },
                // vAxis: {
                //     format: 'percent',
                // },
            };

            chart.draw(data, options);
        }
    </script>
    <div id="Tasks"></div>
    <script>
        google.charts.load('current', {packages: ['corechart', 'bar']});
        google.charts.setOnLoadCallback(drawBasic6);

        function drawBasic6() {

            var data = new google.visualization.arrayToDataTable([
                ['Element', 'Количество задач', {role: 'annotation'}],
                <?foreach ($arResult['Tasks'] as $collegiate => $value):?>
                ['<?=$collegiate ? 'Комитет' : 'Совет директоров'?>', <?=$value ?? 0?>, <?=$value ?? 0?>],
                <?endforeach;?>
<?//foreach ($arResult['CountByCollegiateAndForm'] as $item => $value):?>
                //['<?//=$item ? 'Комитет' : 'Совет директоров'?>//', <?//=$value['INTERNAL']?>//, <?//=$value['EXTERNAL']?>//],
<?//endforeach;?>
            ]);

            var chart = new google.visualization.ColumnChart(
                document.getElementById('Tasks'));

            var options = {
                title: "Количество поручений",
                width: 600,
                height: 400,
                // legend: { position: 'top', maxLines: 4 },
                bar: { groupWidth: '75%' },
                annotations: {
                    alwaysOutside: true,
                    textStyle: {
                        fontSize: 14,
                        color: '#000',
                        auraColor: 'none'
                    }
                },
                // isStacked: 'percent',
                // vAxis: {
                //     format: 'percent',
                // },
            };

            chart.draw(data, options);
        }
    </script>
<!--    <h2>Таблица</h2>-->
<?php
//$columns = [];
//$columns[] = ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => true];
//$columns[] = ['id' => 'TITLE', 'name' => 'Название', 'sort' => 'TITLE', 'default' => true];
//$columns[] = ['id' => 'DATE_FINISH', 'name' => 'Дата завершения', 'sort' => 'DATE_CREATE', 'default' => true];
//$columns[] = ['id' => 'PLACE', 'name' => 'Место проведения', 'sort' => 'CATEGORY', 'default' => true];
//$columns[] = ['id' => 'COLLEGIATE', 'name' => 'Форма', 'sort' => 'CATEGORY', 'default' => true];
//
//for ($row = $res->GetNext(); !!($row); $row = $res->GetNext()) {
////for ($row = $res->fetch(); !is_null($row); $row = $res->fetch()) {
////    var_dump($row);
//    $list[] = [
//        'data' => [
//            "ID" => $row['ID'],
//            "TITLE" => $row['TITLE'],
//            "DATE_FINISH" => $row['DATE_FINISH'],
//            "CATEGORY" => $row['CATEGORY'],
//            "PLACE" => $row['PLACE'],
//            "COLLEGIATE" => $row['COLLEGIATE'] ? 'Комитет' : 'Совет директоров',
//        ],
//        'actions' => [
//            [
//                'text'    => 'Просмотр',
//                'default' => true,
//                'onclick' => 'document.location.href="?op=view&id='.$row['ID'].'"'
//            ], [
//                'text'    => 'Удалить',
//                'default' => true,
//                'onclick' => 'if(confirm("Точно?")){document.location.href="?op=delete&id='.$row['ID'].'"}'
//            ]
//        ]
//    ];
//}
//
//$APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
//    'GRID_ID' => $list_id,
//    'COLUMNS' => $columns,
//    'ROWS' => $list,
//    'SHOW_ROW_CHECKBOXES' => false,
//    'NAV_OBJECT' => $nav,
//    'AJAX_MODE' => 'Y',
//    'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
//    'PAGE_SIZES' =>  [
//        ['NAME' => '20', 'VALUE' => '20'],
//        ['NAME' => '50', 'VALUE' => '50'],
//        ['NAME' => '100', 'VALUE' => '100']
//    ],
//    'AJAX_OPTION_JUMP'          => 'N',
//    'SHOW_CHECK_ALL_CHECKBOXES' => false,
//    'SHOW_ROW_ACTIONS_MENU'     => true,
//    'SHOW_GRID_SETTINGS_MENU'   => true,
//    'SHOW_NAVIGATION_PANEL'     => true,
//    'SHOW_PAGINATION'           => true,
//    'SHOW_SELECTED_COUNTER'     => true,
//    'SHOW_TOTAL_COUNTER'        => true,
//    'SHOW_PAGESIZE'             => true,
//    'SHOW_ACTION_PANEL'         => true,
//    'ALLOW_COLUMNS_SORT'        => true,
//    'ALLOW_COLUMNS_RESIZE'      => true,
//    'ALLOW_HORIZONTAL_SCROLL'   => true,
//    'ALLOW_SORT'                => true,
//    'ALLOW_PIN_HEADER'          => true,
//    'AJAX_OPTION_HISTORY'       => 'N'
//]);
//?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>