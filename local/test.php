<?php
/**
 * @author Lukmanov Mikhail <lukmanof92@gmail.com>
 */
define("NOT_CHECK_PERMISSIONS", true);
define('STOP_STATISTICS', true);
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC', 'Y');
define('DisableEventsCheck', true);
define('BX_SECURITY_SHOW_MESSAGE', true);
define('XHR_REQUEST', true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");


die();
$arResult = restCommand('lists.element.add', array(
    'IBLOCK_ID' => 31,
    'IBLOCK_TYPE_ID' => 'bitrix_processes',
    'ELEMENT_CODE' => 'test2',
    'FIELDS' => array(
        'NAME' => 'test2'
    )
));

var_dump($arResult);
die();
$id = 7043;
dump(
    restCommand('bizproc.workflow.start', array(
        'TEMPLATE_ID' => 9,
        'DOCUMENT_ID' => array('lists', 'BizprocDocument', $id),
        'PARAMETERS' => array(
            'chairman' => '',
            'participants' => '',
            'URLmeeting' => '',
        )
    ))
);
die();
dump(
    restCommand('bizproc.workflow.template.list', array(
        'select' => array(
            'MODULE_ID', 'ENTITY'
        ),
        'filter' => array(
            'ID' => 9
        )
    ))
);