<?php
/**
 * Created by PhpStorm.
 * User: scvairy
 * Date: 2019-02-11
 * Time: 04:45
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("meeting"))
    return ShowError(GetMessage("ME_MODULE_NOT_INSTALLED"));

$arRequest = $_REQUEST;
$arResult['DATE_YEAR'] = intval($arRequest['DATE_YEAR']);
if($arResult['DATE_YEAR'] < 1900 || $arResult['DATE_YEAR'] > 9999) {
    $arResult['DATE_YEAR'] = date_format(date_create(), 'Y');
}

// Отчёт по количеству проведённых заседаний по коллегиальным органам
//$strSql = "SELECT m.COLLEGIATE, count(m.ID) as COUNT FROM sitemanager.b_meeting m
//  WHERE DATE_FINISH IS NOT NULL
//    AND YEAR(DATE_FINISH) = ".$arResult['DATE_YEAR']."
//  GROUP BY COLLEGIATE;";
////var_dump($strSql);
//$dbRes = $DB->Query($strSql, false, 'File: '.__FILE__.'<br>Line: '.__LINE__);
//
//while($result = $dbRes->GetNext()) {
//    $arCountByCollegiate[$result['COLLEGIATE']] = $result;
//};
////var_dump($arCountByCollegiate);
//$arResult['CountByCollegiate'] = $arCountByCollegiate;

// Отчёт по количеству проведённых ОЧНЫХ и ЗАОЧНЫХ заседаний по коллегиальным органам
//$strSql = "SELECT m.COLLEGIATE, count(m.ID) as COUNT, i.INTERNAL, ex.EXTERNAL
//FROM sitemanager.b_meeting m
//LEFT OUTER JOIN
//     (
//       SELECT m.COLLEGIATE,
//              count(PLACE) as EXTERNAL
//       FROM sitemanager.b_meeting m
//       WHERE DATE_FINISH IS NOT NULL
//         and (m.PLACE is null or m.PLACE = '')
//         AND YEAR(DATE_FINISH) = ".$arResult['DATE_YEAR']."
//       GROUP BY (m.COLLEGIATE)
//     ) as ex
//      ON m.COLLEGIATE = ex.COLLEGIATE
//left outer join
//     (
//       SELECT m.COLLEGIATE,
//             count(PLACE) as INTERNAL
//       FROM sitemanager.b_meeting m
//       WHERE DATE_FINISH IS NOT NULL
//         and m.PLACE is not null
//         and m.PLACE <> ''
//         AND YEAR(DATE_FINISH) = ".$arResult['DATE_YEAR']."
//     ) as i
//       on m.COLLEGIATE = i.COLLEGIATE
//WHERE DATE_FINISH IS NOT NULL
//  AND YEAR(DATE_FINISH) = ".$arResult['DATE_YEAR']."
//GROUP BY COLLEGIATE;";

$strSql = "SELECT
      M.COLLEGIATE,
      COUNT(M.ID) AS COUNT,
      I.INTERNAL,
      EX.EXTERNAL,
      USERS.COUNT AS USERS_COUNT,
      USERS_REJECTED.COUNT AS USERS_REJECTED_COUNT,
      1 - (USERS_REJECTED.COUNT / USERS.COUNT) AS VISITORS_PERCENTAGE
FROM
    b_meeting M
    LEFT OUTER JOIN
     (
       SELECT M.COLLEGIATE,
              count(PLACE) AS EXTERNAL
       FROM b_meeting M
       WHERE M.CURRENT_STATE = 'C'
         AND M.DATE_FINISH IS NOT NULL
         AND YEAR(M.DATE_FINISH) = ".$arResult['DATE_YEAR']."
         AND (M.PLACE IS NULL OR M.PLACE = '')
       GROUP BY (M.COLLEGIATE)
     ) AS EX ON M.COLLEGIATE = EX.COLLEGIATE

    LEFT OUTER JOIN
     (
       SELECT M.COLLEGIATE,
              count(M.PLACE) AS INTERNAL
       FROM b_meeting M
       WHERE M.CURRENT_STATE = 'C'
         AND M.DATE_FINISH IS NOT NULL
         AND YEAR(M.DATE_FINISH) = ".$arResult['DATE_YEAR']."
         AND M.PLACE IS NOT NULL
         AND M.PLACE <> ''
       GROUP BY (M.COLLEGIATE)
     ) AS I ON M.COLLEGIATE = I.COLLEGIATE

    LEFT OUTER JOIN
     (
       SELECT M.COLLEGIATE,
              COUNT(*) as COUNT
       FROM
            b_meeting_users AS U
            LEFT JOIN b_meeting M ON U.MEETING_ID = M.ID
       WHERE
           M.CURRENT_STATE = 'C'

       GROUP BY M.COLLEGIATE
     ) AS USERS ON M.COLLEGIATE = USERS.COLLEGIATE

    LEFT OUTER JOIN
     (
       SELECT M.COLLEGIATE,
              COUNT(*) as COUNT
#               CE.ID, CE.PARENT_ID, CE.MEETING_STATUS, CE.MEETING_HOST,
#               U.LOGIN, U.NAME, U.LAST_NAME, U.SECOND_NAME, U.EMAIL, U.PERSONAL_PHOTO, U.WORK_POSITION

       FROM
            b_meeting M
            LEFT JOIN b_calendar_event CE ON M.EVENT_ID = CE.PARENT_ID
#             LEFT JOIN b_user U ON (U.ID=CE.OWNER_ID)
       WHERE
           CE.ACTIVE = 'Y' AND
           CE.CAL_TYPE = 'user' AND
           CE.DELETED = 'N' AND
           CE.MEETING_STATUS = 'N' AND
           M.CURRENT_STATE = 'C'
       GROUP BY M.COLLEGIATE
     ) AS USERS_REJECTED ON M.COLLEGIATE = USERS_REJECTED.COLLEGIATE

WHERE M.CURRENT_STATE = 'C'
  AND M.DATE_FINISH IS NOT NULL
  AND YEAR(M.DATE_FINISH) = ".$arResult['DATE_YEAR']."
GROUP BY M.COLLEGIATE;";

//var_dump($strSql);
$dbRes = $DB->Query($strSql, false, 'File: '.__FILE__.'<br>Line: '.__LINE__);

while($result = $dbRes->GetNext()) {
    $arCountByCollegiateAndFrom[$result['COLLEGIATE']] = $result;
};

//var_dump($arCountByCollegiate);
$arResult['CountByCollegiateAndFrom'] = $arCountByCollegiateAndFrom;

$strSql = "SELECT
       M.COLLEGIATE,
#        M.*
# , INST.*
       count(M.ID) AS COUNT
FROM b_meeting_instance INST
       INNER JOIN
     (
       SELECT M.ID,
              M.COLLEGIATE,
              YEAR(M.DATE_FINISH) AS YEAR_FINISH
       FROM b_meeting M
       WHERE M.CURRENT_STATE = 'C'
         AND DATE_FINISH IS NOT NULL
         AND YEAR(DATE_FINISH) = ".$arResult['DATE_YEAR']."
       GROUP BY (M.COLLEGIATE)
     ) AS M ON M.ID = INST.MEETING_ID
WHERE YEAR_FINISH = ".$arResult['DATE_YEAR']."
  # AND INST.INSTANCE_TYPE = 'A'
GROUP BY COLLEGIATE;";

//var_dump($strSql);
$arCountItems = array();
//$dbRes = $DB->Query($strSql, false, 'File: '.__FILE__.'<br>Line: '.__LINE__);
//while($result = $dbRes->GetNext()) {
//    $arCountItems[$result['COLLEGIATE']] = $result;
//};
//var_dump($arCountByCollegiate);
//$arResult['CountItems'] = $arCountItems;

$strSql = "SELECT
#        M.COLLEGIATE,
        M.*,
        INST.*,
        I.*,
        C.TITLE AS CATEGORY_TITLE
#        ,
#         count(M.ID) AS COUNT
FROM b_meeting_instance INST
       INNER JOIN
     (
       SELECT M.ID,
              M.COLLEGIATE,
              YEAR(M.DATE_FINISH) AS YEAR_FINISH
       FROM b_meeting M
       WHERE M.CURRENT_STATE = 'C'
         AND DATE_FINISH IS NOT NULL
         AND YEAR(DATE_FINISH) = ".$arResult['DATE_YEAR']."
#        GROUP BY (M.COLLEGIATE)
     ) AS M ON M.ID = INST.MEETING_ID
LEFT OUTER JOIN (
  SELECT I.ID, I.CATEGORY_ID
  FROM b_meeting_item I
  ) AS I ON INST.ITEM_ID = I.ID
LEFT OUTER JOIN (
  SELECT ID, TITLE
  FROM b_meeting_item_category
  ) AS C ON C.ID = I.CATEGORY_ID
WHERE YEAR_FINISH = ".$arResult['DATE_YEAR'].";";

//var_dump($strSql);
$dbRes = $DB->Query($strSql, false, 'File: '.__FILE__.'<br>Line: '.__LINE__);
$arCountItemsByCategory = array();
while($result = $dbRes->GetNext()) {
    $arCountItemsByCategory[$result['COLLEGIATE']?'Комитет':'Совет директоров'][$result['CATEGORY_TITLE'] ? $result['CATEGORY_TITLE'] : 'Иное'] += 1;
    $arCountItems[$result['COLLEGIATE']] += 1;
};

$arResult['CountItems'] = $arCountItems;
$arResult['CountItemsByCategory'] = $arCountItemsByCategory;
//var_dump($arCountItemsByCategory);

$strSql = "SELECT ID, TITLE FROM b_meeting_item_category;";

//var_dump($strSql);
$dbRes = $DB->Query($strSql, false, 'File: '.__FILE__.'<br>Line: '.__LINE__);
$arCategories = array();
while($result = $dbRes->GetNext()) {
    $arCategories[] = $result['TITLE'];
};
//var_dump($arCategories);
$arResult['Categories'] = $arCategories;

$strSql = "SELECT
#   M.COLLEGIATE,
  M.*,
  INST.*,
#   I.*,
#   C.TITLE AS CATEGORY_TITLE,
  IT.*,
  count(M.ID) AS COUNT
FROM b_meeting_item_tasks IT
LEFT OUTER JOIN (
  SELECT * FROM
  b_meeting_instance INST
  ) AS INST ON INST.TASK_ID = IT.TASK_ID
                 OR INST.ITEM_ID = IT.ITEM_ID
INNER JOIN (
  SELECT M.ID,
         M.COLLEGIATE,
         YEAR(M.DATE_FINISH) AS YEAR_FINISH
  FROM b_meeting M
  WHERE M.CURRENT_STATE = 'C'
    AND DATE_FINISH IS NOT NULL
    AND YEAR(DATE_FINISH) = ".$arResult['DATE_YEAR']."
 ) AS M ON M.ID = INST.MEETING_ID
WHERE YEAR_FINISH = ".$arResult['DATE_YEAR']."
GROUP BY M.COLLEGIATE;";

//var_dump($strSql);
$dbRes = $DB->Query($strSql, false, 'File: '.__FILE__.'<br>Line: '.__LINE__);
$arTasks = array();
while($result = $dbRes->GetNext()) {
    $arTasks[$result['COLLEGIATE']] = $result['COUNT'];
};
//var_dump($arTasks);
$arResult['Tasks'] = $arTasks;
//var_dump($arResult);

$this->IncludeComponentTemplate('template');
