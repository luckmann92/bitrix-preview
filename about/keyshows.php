<?
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/about/company/mission.php");

$APPLICATION->SetTitle('Ключевые показатели компании');

Bitrix\Main\Page\Asset::getInstance()->addJs('https://www.gstatic.com/charts/loader.js');

$isSecretary = false;
$arGroups = CUser::GetUserGroup(CUser::GetID());
foreach ($arGroups as $item) {
    if (intval($item) == 17) {
        $isSecretary = true;
        break;
    }
}

$HlBlockRepots = GetEntityDataClass(4);
$arReport = $HlBlockRepots::getList(array('filter' => array('UF_ACTIVE' => 'Y')))->fetch();

if ($_FILES && $isSecretary && $_REQUEST['file_load'] == 'Y')
{
    if (stristr($_FILES['excel_file']['type'], 'spreadsheetml') ||
        stristr($_FILES['excel_file']['type'], 'excel')) {
        $tmpdir = $_SERVER['DOCUMENT_ROOT'] . '/about/upload/';
        $tmpfile = $tmpdir . 'report_' . substr(md5($_FILES['excel_file']['name'] . date('H:i:s')), 0, 14) .'.xlsx';
        $content = file_get_contents($_FILES['excel_file']['tmp_name']);
        $reportFile = file_put_contents($tmpfile, $content);
        if ($reportFile) {
            $message = 'Файл успешно загружен, графики обновлены';
            unlink($_FILES['excel_file']['tmp_name']);
            if ($arReport['ID']) {
                $HlBlockRepots::update($arReport['ID'], array('UF_ACTIVE' => 'N'));
            }
            $arReport = array('UF_ACTIVE' => 'Y', 'UF_FILE_ID' => $tmpfile, 'UF_DATE_LOAD' => date('d-m-Y H:i:s'));
            $HlBlockRepots::add($arReport);
        } else {
            $message = 'Ошибка загрузки файла';
        }
    }
    unset($_FILES);
}

if (file_exists($arReport['UF_FILE_ID'])) {
    require_once dirname(__FILE__) . '/lib/PHPExcel/Classes_overload2/PHPExcel.php';
    $objPHPExcel = new PHPExcel();
    $oExcel = PHPExcel_IOFactory::load($arReport['UF_FILE_ID']);

    // С какой линии начинаются данные
    $iStart = 6;
    $aRes = array();
    for ($i = $iStart; $i <= 30; $i++) {
        for ($k = ord('A'); $k <= ord('Z'); $k++) {
            $value = $oExcel->getActiveSheet()->getCell(chr($k) . $i)->getValue();

            if (strlen($value) > 0) {
                if ($i == 6) {
                    $aRes['PERIODS'][$k] = $value;
                } else {
                    switch (chr($k)) {
                        case 'C':
                            $aRes['ITEMS'][$i]['NAME'] = $value;

                            break;
                        case 'D':
                            $aRes['ITEMS'][$i]['MEASURE'] = $value;
                            break;
                        case 'B':
                        case 'A':
                            break;
                        default:
                            $aRes['ITEMS'][$i]['VALUES'][$k] = tofloat($value);
                    }
                }
            }
        }
    }
    $arResult = array();

    $index = 0;
    foreach ($aRes['ITEMS'] as $key => $item) {
        $arResult[$index][] = array('Период', $item['NAME'] . ' (' . $item['MEASURE'] . ')');
        foreach ($aRes['PERIODS'] as $k => $period) {
            if (strlen($item['VALUES'][$k]) > 0) {
                $arResult[$index][] = array($period, $item['VALUES'][$k]);
            }
        }
        $index++;
    }
}
if ($isSecretary) {
?>
    <?if ($arReport['UF_DATE_LOAD']) {?>
        <div class="file-block">
            <div class="file-block_title">
                Дата последнего загруженного отчета:
            </div>
            <div class="file-block_item"><?=$arReport['UF_DATE_LOAD']?></div>
        </div>
    <?}?>
    <style>
        .ui-btn-file-load, .ui-btn-file-send {
            display: inline-block;
            padding: 0 20px;
            text-transform: uppercase;
            line-height: 37px;
            height:39px;
            font-size: 12px;
            font-family: OpenSans-Bold, "Helvetica Neue", Helvetica, Arial, sans-serif;
            border:none;
            cursor: pointer;
        }
        .ui-btn-file-load {
            display:none;
            color:#fff;
            background: #3bc8f5;
        }
        .ui-btn-file-send {
            color:#535c69;
            background: #bbed21;
            margin-right: 10px;
        }
        .input_file {
            opacity:0;
            position: absolute;
            z-index: -1;
        }
        .file-block {
            margin-bottom: 20px;
        }
        .file-block_item {
            color:#51a530;
        }
        .form-field {
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
        }
    </style>
    <div class="file-block_form">
        <form enctype="multipart/form-data" action="" method="POST">
            <input type="hidden" name="MAX_FILE_SIZE" value="30000" />
            <div class="form-field">
                <input type="hidden" name="file_load" value="Y">
                <input class="input_file" data-multiple-caption="{count} files selected" id="file" name="excel_file" type="file" />
                <label for="file" class="ui-btn-file-send">
                    <span>Выбрать файл...</span>
                </label>
                <input type="submit" class="ui-btn-file-load" value="Загрузить" />
            </div>
        </form>
    </div>
    <script>
        var inputs = document.querySelectorAll('.input_file');
        Array.prototype.forEach.call(inputs, function(input){
            var label	 = input.nextElementSibling,
                btnSend = document.querySelector('.ui-btn-file-load'),
                labelVal = label.innerHTML;
            input.addEventListener('change', function(e){
                var fileName = '';
                if( this.files && this.files.length > 1 ) {
                    fileName = ( this.getAttribute( 'data-multiple-caption' ) || '' ).replace( '{count}', this.files.length );
                } else {
                    fileName = e.target.value.split( '\\' ).pop();
                }

                if( fileName ) {
                    label.querySelector( 'span' ).innerHTML = fileName;
                    btnSend.style.display = 'inline-block';
                } else {
                    label.innerHTML = labelVal;
                    btnSend.style.display = 'none';
                }
            });
        });
    </script>
<?
}

if ($arResult) {
    foreach ($arResult as $k => $arRow) {
        ?>
        <div id="curve_chart_<?= $k ?>" style="width: 900px; height: 500px"></div>
        <script type="text/javascript">
            google.charts.load('current', {'packages': ['corechart']});
            google.charts.setOnLoadCallback(drawChart);

            function drawChart() {
                var data = google.visualization.arrayToDataTable(<?=arrayToJS($arRow)?>);
                var options = {
                    title: '<?=$arRow[0][1]?>',
                    curveType: 'function',
                    legend: {position: 'left'}
                };
                var chart = new google.visualization.LineChart(document.getElementById('curve_chart_<?=$k?>'));
                chart.draw(data, options);
            }
        </script>
    <?
    }
}

function arrayToJS($array) {
    $res = '[';
    $ind = 0;
    foreach ($array as $k => $arr) {
        $res .= '[';
        foreach ($arr as $i => $ar) {
            $res .= is_string($ar) || $i == 0 ? '"' .$ar. '"' : $ar;
            //$res .= !is_int($ar) || $i == 0 ? '"' .$ar. '"' : $ar;
            if ($i < count($arr) - 1) {
                $res .= ',';
            }
        }
        $res .= ']';
        if ($ind < count($array) - 1) {
            $res .= ',';
        }
        $ind++;
    }
    $res .= ']';
    return $res;
}

function tofloat($num) {
    $dotPos = strrpos($num, '.');
    $commaPos = strrpos($num, ',');
    $sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
        ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);

    if (!$sep) {
        return floatval(preg_replace("/[^0-9]/", "", $num));
    }

    return floatval(
        preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
        preg_replace("/[^0-9]/", "", substr($num, $sep+1, strlen($num)))
    );
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>