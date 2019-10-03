<?
$APPLICATION->RestartBuffer();

use \Bitrix\Main\Loader,
	\Bitrix\Highloadblock;

global $USER;

if (is_array($arResult['MEETING']['AGENDA']) && count($arResult['MEETING']['AGENDA']) > 0) {
	Loader::includeModule('highloadblock');

	$voitingHlBlock = Highloadblock\HighloadBlockTable::getById(2)->fetch();
	$voitingEntity = Highloadblock\HighloadBlockTable::compileEntity($voitingHlBlock);
	$voitingEntityClass = $voitingEntity->getDataClass();

	$voitingFilter = array(
		'UF_MEETING_ID' => $arResult['MEETING']['ID'],
	);

	$arAgendaIds = array_keys($arResult['MEETING']['AGENDA']);
	$voitingFilter['UF_ID_INSTANCE'] = $arAgendaIds;

	$res = $voitingEntityClass::getList(array(
		'filter' => $voitingFilter,
		'select' => array('*')
	));

	while ($arVoid = $res->fetch()) {
		if (!isset($arResult['MEETING']['AGENDA'][$arVoid['UF_ID_INSTANCE']]['VOID_'.$arVoid['UF_VALUE']])) {
			$arResult['MEETING']['AGENDA'][$arVoid['UF_ID_INSTANCE']]['VOID_'.$arVoid['UF_VALUE']] = 0;
		}
		$arResult['MEETING']['AGENDA'][$arVoid['UF_ID_INSTANCE']]['VOID_'.$arVoid['UF_VALUE']]++;
	}
}

if ($_REQUEST['format'] == 'pdf') {
	$FORMAT = 'PDF';
} else {
	$FORMAT = 'DOCX';
}

$COMPANY_NAME = COption::GetOptionString('bitrix24', 'site_title', '');

require_once 'PhpOffice/Autoloader.php';
PhpOffice\Common\Autoloader::autoload('PhpOffice\Common\Text');
PhpOffice\Common\Autoloader::autoload('PhpOffice\Common\XMLWriter');
PhpOffice\Common\Autoloader::autoload('PhpOffice\Common\XMLReader');
PhpOffice\Common\Autoloader::autoload('PhpOffice\Common\Microsoft\PasswordEncoder');

require_once 'PhpOffice/PhpWord/WordAutoloader.php';
require_once 'ZendEscaper/Escaper.php';

$srcBaseDirectory = dirname(__FILE__).'/PhpOffice/PhpWord';

$loader = new PhpOffice\PhpWord\Autoloader();
$loader->register();
$loader->addNamespace('PhpOffice\PhpWord', $srcBaseDirectory);

$phpWord = new \PhpOffice\PhpWord\PhpWord();

if ($FORMAT == 'DOCX') {
	$phpWord->setDefaultFontName('Times New Roman');
	$phpWord->setDefaultFontSize(11);
} elseif ($FORMAT == 'PDF') {
	$phpWord->setDefaultFontName('dejavusans');
	$phpWord->setDefaultFontSize(10);
}

$properties = $phpWord->getDocInfo();

$properties->setCreator('My name');
$properties->setCompany($COMPANY_NAME);
$properties->setTitle('Письменное мнение');
$properties->setDescription('My description');
$properties->setCategory('My category');
$properties->setLastModifiedBy('My name');
$properties->setCreated(mktime());
$properties->setModified(mktime());
$properties->setSubject('My subject');
$properties->setKeywords('my, key, word');

$sectionStyle = array(
	'orientation' => 'portrait', //portrait,landscape
	'marginTop' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2),
	'marginLeft' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(3),
	'marginRight' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(1.5),
	'marginBottom' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2),
);

$section = $phpWord->addSection($sectionStyle);

$section->addText(
	'В Совет директоров '.$COMPANY_NAME,
	array('size' => 12),
	array('align' => 'right')
);

$section->addText(
	'от члена Совета директоров '.$USER->GetFullName(),
	array('size' => 12),
	array('align' => 'right')
);

$section->addTextBreak();

$section->addText(
	'Письменное мнение',
	array(
		'allCaps' => true,
		'bold' => true,
	),
	array('align' => 'center')
);

$section->addTextBreak();

$section->addText(
	'Заседание '.($arResult['MEETING']['COLLEGIATE'] ? 'Комитета' : 'Совета директоров'),
	array(
		'allCaps' => true,
		'bold' => true,
	),
	array('align' => 'center')
);

if ($arResult["MEETING"]["DATE_FINISH"]) {
	$date = strtolower(FormatDate('d F Y', MakeTimeStamp($arResult["MEETING"]["DATE_FINISH"])));
	$time = FormatDate('H:i', MakeTimeStamp($arResult["MEETING"]["DATE_FINISH"]));
} else {
	$date = '_____________';
	$time = '_____________';
}

$section->addText(
	'Дата проведения заседания: ' . $date
);

$meetingRoom = '';
if ($arResult['MEETING']['PLACE']) {
	$meetingRoom = $arResult['MEETING']['PLACE'];
} else {
	$meetingRoom = '______________';
}

$section->addText(
	'Место проведения заседания: ' . $meetingRoom
);

$section->addText(
	'Время проведения заседания: ' . $time
);

$section->addTextBreak();

$dateStart = '';
if ($arResult["MEETING"]["DATE_START"]) {
	$dateStart = strtolower(FormatDate('d F Y', MakeTimeStamp($arResult["MEETING"]["DATE_START"])));
} else {
	$dateStart = '«___» _______ 20___';
}

$section->addText(
	'В связи с тем, что не имею возможности присутствовать на заседании '.($arResult['MEETING']['COLLEGIATE'] ? 'Комитета' : 'Совета директоров').', запланированного на  ' . $dateStart . ' года по причине ____________________________________________________________________________ выражаю письменное мнение по вопросам Повестки дня:'
);

$tableStyle = array(
	'borderColor' => '000000',
	'borderSize'  => 1,
	'cellMargin'  => 0,
);
$tableInnerStyle = array(
	'borderColor' => '000000',
	'borderSize' => 1,
	'cellMargin' => 0,
);
$cellStyle = array(
	'valign' => 'center',
);
$cellTextStyle = array(
	'align' => 'center',
);

if (count($arResult['MEETING']['AGENDA']) > 0) {
	$i = 1;
	foreach ($arResult['MEETING']['AGENDA'] as $key => $arAgenda) {
		if ($arAgenda['INSTANCE_PARENT_ID'] > 0)
			continue;
		if ($FORMAT == 'PDF') {
			$section->addText($i.'. '.$arAgenda['TITLE']);
			$i++;
		} else {
			// $section->addListItem($arAgenda['TITLE'], 0, null, 'multilevel-'.$listCounter);
			$section->addListItem($arAgenda['TITLE'], 0, array(), array('listType' => \PhpOffice\PhpWord\Style\ListItem::TYPE_NUMBER_NESTED));
		}
		// $section->addText(
		// 	'Проект решения: '.$arAgenda['TITLE']
		// );
		// $table = $section->addTable($tableStyle);
		// $table->addRow(300, array('exactHeight' => true));
		// $table->addCell(3100, $cellStyle)->addText('«ЗА»', array(), $cellTextStyle);
		// $table->addCell(3100, $cellStyle)->addText('«ПРОТИВ»', array(), $cellTextStyle);
		// $table->addCell(3100, $cellStyle)->addText('«ВОЗДЕРЖАЛСЯ»', array(), $cellTextStyle);
		// $table->addRow(300, array('exactHeight' => true));
		// $table->addCell(3100, $cellStyle)->addText('', array(), $cellTextStyle);
		// $table->addCell(3100, $cellStyle)->addText('', array(), $cellTextStyle);
		// $table->addCell(3100, $cellStyle)->addText('', array(), $cellTextStyle);
		// $section->addTextBreak();
		// $section->addText(
		// 	'Особое мнение: ___________________________________'
		// );
		$counter = 1;
		foreach ($arResult['MEETING']['AGENDA'] as $keyCh => $arAgendaCh) {
			if ($arAgendaCh['INSTANCE_PARENT_ID'] == $arAgenda['ID']) {
				// $section->addListItem($arAgendaCh['TITLE'], 0, array(), array('listType' => \PhpOffice\PhpWord\Style\ListItem::TYPE_NUMBER_NESTED));
				$section->addText(
					'Проект решения '.$counter.': '.$arAgendaCh['TITLE']
				);
				$table = $section->addTable($tableStyle);
				$table->addRow(300, array('exactHeight' => true));
				$table->addCell(3100, $cellStyle)->addText('«ЗА»', array(), $cellTextStyle);
				$table->addCell(3100, $cellStyle)->addText('«ПРОТИВ»', array(), $cellTextStyle);
				$table->addCell(3100, $cellStyle)->addText('«ВОЗДЕРЖАЛСЯ»', array(), $cellTextStyle);
				$table->addRow(300, array('exactHeight' => true));
				$table->addCell(3100, $cellStyle)->addText('', array(), $cellTextStyle);
				$table->addCell(3100, $cellStyle)->addText('', array(), $cellTextStyle);
				$table->addCell(3100, $cellStyle)->addText('', array(), $cellTextStyle);
				$section->addText(
					'Особое мнение: ___________________________________'
				);
				$section->addTextBreak();
				$counter++;
			}
		}
	}
}

$section->addTextBreak(2);

$textrun = $section->addTextRun(array('align' => 'right'));
$textrun->addText(
	'Член '.($arResult['MEETING']['COLLEGIATE'] ? 'Комитета' : 'Совета директоров').' '.$COMPANY_NAME.' '
);
if ($FORMAT == 'DOCX') {
	$textrun->addText(
		'/               /_________________',
		array(
			'underline' => 'single'
		)
	);
} elseif ($FORMAT == 'PDF') {
	$textrun->addText(
		'/_____________/______________________'
	);
}

$textrun = $section->addTextRun(array('align' => 'right'));
$textrun->addText('Дата ');
$textrun->addText(
	$date,
	array(
		'underline' => 'single'
	)
);

if ($FORMAT == 'DOCX') {
	header("Content-Description: File Transfer");
	header('Content-Disposition: attachment; filename="Written-opinion.docx"');
	header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
	header('Content-Transfer-Encoding: binary');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Expires: 0');

	$xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
	$xmlWriter->save("php://output");
} elseif ($FORMAT == 'PDF') {
	$rand = rand();

	$xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
	$xmlWriter->save('template-'.$rand.'.docx');

	$phpWord = \PhpOffice\PhpWord\IOFactory::load('template-'.$rand.'.docx');

	unlink('template-'.$rand.'.docx');

	\PhpOffice\PhpWord\Settings::setPdfRendererPath(__DIR__.'/tcpdf');
	\PhpOffice\PhpWord\Settings::setPdfRendererName('TCPDF');

	header('Content-Type: application/pdf');
	header('Pragma: public');
	header('Content-Disposition: inline; filename="Written-opinion.pdf"');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Expires: 0');

	$xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'PDF');
	$xmlWriter->save("php://output");
}