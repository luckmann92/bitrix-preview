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
$properties->setTitle('Протокол');
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

$listCounter = 0;

$section->addText(
	'Протокол №',
	array('allCaps' => true),
	array('align' => 'center')
);

$section->addText(
	'на заседании '.($arResult['MEETING']['COLLEGIATE'] ? 'Комитета' : 'Совета директоров').(strlen($COMPANY_NAME) > 0 ? ' '.$COMPANY_NAME : ''),
	array(),
	array('align' => 'center')
);

$section->addTextBreak();

if ($arResult['MEETING']['INTERNAL']) {
	$form = 'заочная';
} else {
	$form = 'очная';
}
$section->addText(
	'Форма проведения заседания: ' . $form
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

$section->addText(
	'Дата и время составления протокола: ______________'
);

$section->addText(
	'Лица, принявшие участие в заседании:'
);

$tableStyle = array(
	'borderColor' => '000000',
	'borderSize'  => 1,
	'cellMargin'  => 0,
	'cellMarginLeft' => 30,
	'cellMarginRight' => 30,
);
$cellStyle = array(
	'valign' => 'center',
);
$cellTextStyle = array(
	// 'align' => 'center',
);

$table = $section->addTable($tableStyle);

$columnWidth = 4650;

/**
USER_ROLE
O - председатель?
K - секретарь
M - член
H - ???

USERS_EVENT
R - отказался от участия
**/

$arUsersDocx = array(
	'O' => array(),
	'M' => array(),
	'K' => array(),
	'R' => array(),
);

foreach ($arResult['MEETING']['USERS'] as $USER_ID => $USER_ROLE) {
	if($arResult['MEETING']['USERS_EVENT'][$USER_ID] == 'N') {
		$USER_ROLE = 'R';
	}

	$arUsersDocx[$USER_ROLE][] = $USER_ID;
}

$membersCounter = 0;
$membersTotalCounter = 0;
foreach ($arUsersDocx as $role => $arRoleUsers) {
	if ($role == 'R')
		continue;

	foreach ($arRoleUsers as $key => $userId) {
		$text = '';
		if ($role == 'O') {
			// $text = 'Председатель Совета директоров';
			$text = 'Председатель';
		} elseif ($role == 'M') {
			// $text = 'Член Совета директоров';
			$text = 'Участник';
		} elseif ($role == 'K') {
			// $text = 'Корпоративный секретарь';
			$text = 'Секретарь';
		}
		
		$userName = '';

		if ($arResult['USERS'][$userId]['LAST_NAME']) {
			$userName .= $arResult['USERS'][$userId]['LAST_NAME'];
		}
		if ($arResult['USERS'][$userId]['NAME']) {
			$userName .= ' ' . $arResult['USERS'][$userId]['NAME'];
		}
		if ($arResult['USERS'][$userId]['SECOND_NAME']) {
			$userName .= ' ' . $arResult['USERS'][$userId]['SECOND_NAME'];
		}

		if (!$userName) {
			$userName = $arResult['USERS'][$userId]['LOGIN'];
		}

		$table->addRow(300, array('exactHeight' => true));
		$table->addCell($columnWidth, $cellStyle)->addText($text, array(), $cellTextStyle);
		$table->addCell($columnWidth, $cellStyle)->addText($userName, array(), $cellTextStyle);
		if ($role != 'K') {
            $membersCounter++;
            $membersTotalCounter++;
        }
	}
}

$section->addTextBreak();

if (count($arUsersDocx['R']) > 0) {
	$section->addText('Лица, не принявшие участие в заседании:');
	$table = $section->addTable($tableStyle);
	foreach ($arUsersDocx['R'] as $key => $userId) {
		$text = 'Участник';

		$userName = '';

		if ($arResult['USERS'][$userId]['LAST_NAME']) {
			$userName .= $arResult['USERS'][$userId]['LAST_NAME'];
		}
		if ($arResult['USERS'][$userId]['NAME']) {
			$userName .= ' ' . $arResult['USERS'][$userId]['NAME'];
		}
		if ($arResult['USERS'][$userId]['SECOND_NAME']) {
			$userName .= ' ' . $arResult['USERS'][$userId]['SECOND_NAME'];
		}

		if (!$userName) {
			$userName = $arResult['USERS'][$userId]['LOGIN'];
		}

		$table->addRow(300, array('exactHeight' => true));
		$table->addCell($columnWidth, $cellStyle)->addText($text, array(), $cellTextStyle);
		$table->addCell($columnWidth, $cellStyle)->addText($userName, array(), $cellTextStyle);

		//if ($key != 'K') {
            $membersTotalCounter++;
       // }
	}

	$section->addTextBreak();
}

$arMembersText = array(
	'член',
	'члена',
	'членов'
);

$membersText = $arMembersText[($membersCounter % 10 === 1 && $membersCounter % 100 !== 11) ? 0 : ($membersCounter % 10 >= 2 && $membersCounter % 10 <= 4 && ($membersCounter % 100 < 10 || $membersCounter % 100 >= 20) ? 1 : 2)];

$section->addText(
	'В заседании приняли участие '.$membersCounter.' '.$membersText.' '.($arResult['MEETING']['COLLEGIATE'] ? 'Комитета' : 'Совета директоров').(strlen($COMPANY_NAME) > 0 ? ' '.$COMPANY_NAME : '').' из '.$membersTotalCounter.' избранных.'
);

$bCvorum = $membersCounter > $membersTotalCounter/2;
$section->addText(
	'Кворум для проведения заседания и принятия решений по вопросам повестки дня '.($bCvorum ? 'имелся' : 'не имелся').'.'
);

$section->addText(
	'Повестка дня заседания '.($arResult['MEETING']['COLLEGIATE'] ? 'Комитета' : 'Совета директоров').(strlen($COMPANY_NAME) > 0 ? ' '.$COMPANY_NAME : '')
);

if (count($arResult['MEETING']['AGENDA']) > 0) {
	$phpWord->addNumberingStyle(
		'multilevel-'.$listCounter,
		array(
			'type' => 'multilevel',
			'levels' => array(
				array('format' => 'decimal', 'text' => '%1.', 'left' => 360, 'handing' => 360, 'tabPos' => 360),
				array('format' => 'decimal', 'text' => '%1.%2.', 'left' => 792, 'handing' => 792, 'tabPos' => 432),
			),
		)
	);
	$i = 1;
	foreach ($arResult['MEETING']['AGENDA'] as $key => $arAgenda) {
		if ($arAgenda['INSTANCE_PARENT_ID'] > 0)
			continue;
		if ($FORMAT == 'PDF') {
			$section->addText($i.'. '.$arAgenda['TITLE']);
			$i++;
		} else {
			$section->addListItem($arAgenda['TITLE'], 0, null, 'multilevel-'.$listCounter);
		}
	}
	$listCounter++;
}

$section->addText(
	'Других предложений не было.'
);

if (count($arResult['MEETING']['AGENDA']) > 0) {
	$cellVoitingTextStyle = array(
		'align' => 'center',
	);

	$phpWord->addNumberingStyle(
		'multilevel-'.$listCounter,
		array(
			'type' => 'multilevel',
			'levels' => array(
				array('format' => 'decimal', 'text' => '%1.', 'left' => 360, 'handing' => 360, 'tabPos' => 360),
				array('format' => 'decimal', 'text' => '%1.%2.', 'left' => 792, 'handing' => 792, 'tabPos' => 432),
			),
		)
	);
	$i = 1;
	foreach ($arResult['MEETING']['AGENDA'] as $key => $arAgenda) {
		if ($arAgenda['INSTANCE_PARENT_ID'] > 0)
			continue;
		if ($FORMAT == 'PDF') {
			$section->addText($i.'. '.$arAgenda['TITLE']);
			$i++;
		} else {
			$section->addListItem($arAgenda['TITLE'], 0, null, 'multilevel-'.$listCounter);
		}
		// $section->addText(
		// 	'Вопрос, поставленный на голосование: '.$arAgenda['TITLE']
		// );
		// $section->addText(
		// 	'Результаты голосования:'
		// );
		// $table = $section->addTable($tableStyle);
		// $table->addRow(300, array('exactHeight' => true));
		// $table->addCell(3100, $cellStyle)->addText('«За»', array(), $cellVoitingTextStyle);
		// $table->addCell(3100, $cellStyle)->addText('«Против»', array(), $cellVoitingTextStyle);
		// $table->addCell(3100, $cellStyle)->addText('«Воздержался»', array(), $cellVoitingTextStyle);
		// $table->addRow(300, array('exactHeight' => true));
		// $table->addCell(3100, $cellStyle)->addText(($arAgenda['VOID_Y'] ? $arAgenda['VOID_Y'] : 0), array(), $cellVoitingTextStyle);
		// $table->addCell(3100, $cellStyle)->addText(($arAgenda['VOID_N'] ? $arAgenda['VOID_N'] : 0), array(), $cellVoitingTextStyle);
		// $table->addCell(3100, $cellStyle)->addText(($arAgenda['VOID_A'] ? $arAgenda['VOID_A'] : 0), array(), $cellVoitingTextStyle);
		// $section->addText(
		// 	'Решение принято / не принято.'
		// );
		// $section->addText(
		// 	'Принятое решение: ________________'
		// );
		foreach ($arResult['MEETING']['AGENDA'] as $keyCh => $arAgendaCh) {
			if ($arAgendaCh['INSTANCE_PARENT_ID'] == $arAgenda['ID']) {
				// $section->addListItem($arAgendaCh['TITLE'], 0, null, 'multilevel-'.$listCounter);
				$section->addText(
					'Вопрос, поставленный на голосование: '.$arAgendaCh['TITLE']
				);
				$section->addText(
					'Результаты голосования:'
				);
				$table = $section->addTable($tableStyle);
				$table->addRow(300, array('exactHeight' => true));
				$table->addCell(3100, $cellStyle)->addText('«За»', array(), $cellVoitingTextStyle);
				$table->addCell(3100, $cellStyle)->addText('«Против»', array(), $cellVoitingTextStyle);
				$table->addCell(3100, $cellStyle)->addText('«Воздержался»', array(), $cellVoitingTextStyle);
				$table->addRow(300, array('exactHeight' => true));
				$table->addCell(3100, $cellStyle)->addText(($arAgendaCh['VOID_Y'] ? $arAgendaCh['VOID_Y'] : 0), array(), $cellVoitingTextStyle);
				$table->addCell(3100, $cellStyle)->addText(($arAgendaCh['VOID_N'] ? $arAgendaCh['VOID_N'] : 0), array(), $cellVoitingTextStyle);
				$table->addCell(3100, $cellStyle)->addText(($arAgendaCh['VOID_A'] ? $arAgendaCh['VOID_A'] : 0), array(), $cellVoitingTextStyle);
				if ($arAgendaCh['VOID_Y'] >= $membersTotalCounter / 2) {
					$section->addText(
						'Решение принято.'
					);
					$section->addText(
						'Принятое решение: '.$arAgendaCh['TITLE']
					);
				} else {
					$section->addText(
						'Решение не принято.'
					);
					// $section->addText(
					// 	'Принятое решение: ________________'
					// );
				}
				$section->addTextBreak();
			}
		}
	}
}

$section->addTextBreak();

$textrun = $section->addTextRun(array('align' => 'right'));
$textrun->addText(
	'Председатель '.($arResult['MEETING']['COLLEGIATE'] ? 'Комитета' : 'Совета директоров').' '.$COMPANY_NAME.' '
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
$textrun->addText(
	'Корпоративный секретарь '.' '.$COMPANY_NAME.' '
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

if ($FORMAT == 'DOCX') {
	header("Content-Description: File Transfer");
	header('Content-Disposition: attachment; filename="Protocol-of-a-meeting.docx"');
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
	header('Content-Disposition: inline; filename="Protocol-of-a-meeting.pdf"');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Expires: 0');

	$xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'PDF');
	$xmlWriter->save("php://output");
}