<?

use Bitrix\Disk\Internals\Error\ErrorCollection,
    Bitrix\Disk\Sharing,
    Bitrix\Main\Loader,
    Bitrix\Highloadblock\HighloadBlockTable as HLBT;

Loader::registerAutoLoadClasses(
    null,
    array(
        '\MA\Meeting\UsersTable' => '/local/classes/ma/modules/meeting/lib/users.php',
        '\MA\Meeting\MeetingTable' => '/local/classes/ma/modules/meeting/lib/meeting.php',
        '\MA\Meeting\InstanceTable' => '/local/classes/ma/modules/meeting/lib/instance.php',
        '\MA\Meeting\MeetingHandlers' => '/local/classes/ma/modules/meeting/handlers.php',
        '\MA\Tasks\TasksHandlers' => '/local/classes/ma/tasks/taskshandlers.php',
    )
);

AddEventHandler("meeting", "OnAfterMeetingAdd", array("\MA\Meeting\MeetingHandlers", "addIblockElementAfterMeetingAdd"));
AddEventHandler("tasks", "OnBeforeTaskAdd", array("\MA\Tasks\TasksHandlers", "onBeforeTaskAddHandler"));
AddEventHandler("meeting", "OnAfterMeetingItemAdd", array("\MA\Tasks\TasksHandlers", "onAfterMeetingItemAddHandler"));
AddEventHandler("meeting", "OnAfterMeetingInstanceAdd", array("\MA\Tasks\TasksHandlers", "onAfterMeetingInstanceAddHandler"));
AddEventHandler("meeting", "OnAfterMeetingItemUpdate", array("\MA\Tasks\TasksHandlers", "onAfterMeetingInstanceUpdateHandler"));

Loader::includeModule('highloadblock');
Loader::includeModule('disk');
Loader::includeModule('meeting');

AddEventHandler("meeting", "OnAfterMeetingAddComponent", array("MeetingHendlers", "OnAfterMeetingAddHandler"));
AddEventHandler("meeting", "OnAfterMeetingInstanceAdd", array("MeetingHendlers", "OnAfterMeetingInstanceAddHandler"));

function dump($var)
{
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
}

function clearVotingResult($meetingId, $arFilter = array())
{
    clearHLVotingResults($meetingId, $arFilter);
    updateVotingStatus($meetingId, $arFilter);
}

function clearHLVotingResults($meetingId, $arFilter = array())
{
    if (!($meetingId > 0)) return;

    \Bitrix\Main\Loader::includeModule('highloadblock');

    $arHLBlock = \Bitrix\Highloadblock\HighloadBlockTable::getById(2)->fetch();
    $obEntity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($arHLBlock);
    $strEntityDataClass = $obEntity->getDataClass();

    $filter = !empty($arFilter) ? $arFilter : array('UF_MEETING_ID' => $meetingId);

    $rsData = $strEntityDataClass::getList(array(
        'order' => array('ID' => 'ASC'),
        'filter' => $filter,
    ));

    while ($arItem = $rsData->Fetch()) {
        $strEntityDataClass::delete($arItem['ID']);
    }
}

function updateVotingStatus($meetingId, $arFilter = array())
{
    $filter = array(
        "ACTIVE" => "Y",
        "IBLOCK_ID" => 30,
        "UF_MEETING_ID" => $meetingId,
    );
    $arFilterValues = array_merge($filter, $arFilter);

    $db_list = CIBlockSection::GetList(Array("ID" => "DESC"),
        $arFilterValues,
        false,
        array("ID", "IBLOCK_ID", "NAME", "UF_*")
    );

    while ($ar_result = $db_list->Fetch()) {
        $bs = new CIBlockSection;
        $bs->Update($ar_result['ID'], array('UF_STATUS' => 'IN_PROCESS'));
    }

}

class MeetingHendlers
{
    const HLBLOCK = 1;

    public function OnAfterMeetingAddHandler($meetingId, $files)
    {
        global $USER;
        $userId = $USER->GetId();
        if (!($userId > 0)) {
            return;
        }

        $arFields = CMeeting::GetByID($meetingId)->Fetch();

        $folder = self::getDiskFolder($meetingId, 'meeting');

        if (!$folder) {
            $storage = self::getDiskStorageByUserId($userId);
            if ($storage) {
                $folder = self::createFolderOnDiskStorage($userId, $storage, $arFields['TITLE'] . ' (' . $arFields['ID'] . ')', $meetingId);
                if ($folder) {
                    $members = CMeeting::GetUsers($arFields['ID']);
                    MeetingHendlers::sharedFoldetOnMembers($userId, array_keys($members), $folder);

                    $arFields = array(
                        'UF_MEETING' => $arFields['ID'],
                        'UF_QUESTION' => 0,
                        'UF_TYPE' => 'meeting',
                        'UF_ID_STORAGE' => $storage->getId(),
                        'UF_ID_FOLDER' => $folder->getId(),
                        'UF_FOLDER_TITLE' => $folder->getName(),
                        'UF_DATE_CREATE' => new \Bitrix\Main\Type\DateTime
                    );
                    self::saveFolderToHL($arFields);

                    if (!empty($files)) {
                      //  MeetingHendlers::uploadFiles($folder, $files, $userId);
                    }
                }
            }
        }
        if (!empty($files) && $folder) {
            self::uploadFiles($folder, $files, $userId);
        }
    }
    protected function getQuestionsFromIblock($arFields)
    {
        $arMeetingInstance = self::getMeetingByInstanceID($arFields['INSTANCE_PARENT_ID']);
        $arSections = array();
        $db_list = CIBlockSection::GetList(Array("ID"=>"DESC"), array(
            "ACTIVE" => "Y",
            "IBLOCK_ID" => 30,
            "UF_MEETING_ID" => $arFields['MEETING_ID'],
            "UF_ID_INSTANCE" => $arFields['INSTANCE_PARENT_ID'],
            "UF_ITEM_ID" => $arMeetingInstance['ITEM_ID'],
        ),
            false,
            array("ID", "IBLOCK_ID", "NAME", "UF_*")
        );

        while ($ar_result = $db_list->Fetch()) {
            $arSections[] = $ar_result;
        }

        return !empty($arSections) ? $arSections : false;
    }
    public function getMeetingByInstanceID($instanceID) {
        return CMeetingInstance::GetList(
            array(),
            array('ID' => $instanceID),
            false,false,
            array())->Fetch();
    }

    public function OnAfterMeetingInstanceAddHandler($arFields)
    {
        $section = self::getSectionQuestion($arFields);
        if ($section['ID'] > 0) {
            self::addAnswerFromIblock($arFields, $section['ID']);
        }

        global $USER;
        $userId = $USER->GetId();
        $folder = null;
        if ($arFields['INSTANCE_PARENT_ID'] > 0) {
            $folder = MeetingHendlers::getDiskFolder($arFields['INSTANCE_PARENT_ID'], 'question');
        } else {
            $folder = MeetingHendlers::getDiskFolder($arFields['MEETING_ID'], 'meeting');
        }

        if ($folder) {
            $newFolder = $folder->addSubFolder(array(
                'NAME' => $arFields['TITLE'],
                'CREATED_BY' => $userId
            ));

            if ($newFolder) {
                $menbers = CMeeting::GetUsers($arFields['MEETING_ID']);
                if ($arFields['INSTANCE_PARENT_ID'] > 0) {
                    //MeetingHendlers::sharedFoldetOnMembers($userId, array_keys($menbers), $newFolder);
                }

                $arFields = array(
                    'UF_MEETING' => $arFields['MEETING_ID'],
                    'UF_QUESTION' => $arFields['ID'],
                    'UF_TYPE' => 'question',
                    'UF_ID_STORAGE' => $newFolder->getStorage()->getId(),
                    'UF_ID_FOLDER' => $newFolder->getId(),
                    'UF_FOLDER_TITLE' => $newFolder->getName(),
                    'UF_DATE_CREATE' => new \Bitrix\Main\Type\DateTime
                );
                MeetingHendlers::saveFolderToHL($arFields);
            }

        }
    }

    public function uploadFiles($folder, $files, $userId)
    {
        if (0 < $userId) {
            $arUser = CUser::GetById($userId)->fetch();
            $fullName = '';
            if ($arUser['NAME']) {
                $fullName .= $arUser['NAME'];
            }
            if ($arUser['LAST_NAME']) {
                $fullName .= ' ' . $arUser['LAST_NAME'];
            }
            if (0 >= strlen($fullName)) {
                $fullName = $arUser['LOGIN'];
            }

            foreach ($files as $file) {
                if ($file['name']) {
                    $folder->uploadFile($file, array(
                        'CREATED_BY' => $userId,
                        'NAME' => $file['name'],
                    ));
                }
            }

        }
    }

    public function addAnswerFromIblock($arFields, $sectionID)
    {
        $el = new CIBlockElement();
        return $el->Add(
            array(
                'IBLOCK_ID' => 30,
                'NAME' => $arFields['TITLE'],
                'IBLOCK_SECTION_ID' => $sectionID,
                'PROPERTY_VALUES' => array(
                    'ID_INSTANCE' => $arFields['INSTANCE_ID'],
                    'INSTANCE_PARENT_ID' => $arFields['INSTANCE_PARENT_ID'],
                    'MEETING_ID' => $arFields['MEETING_ID']
                )
            )
        );
    }

    public function getSectionQuestion($arFields) {
        $arFilter = array(
            'UF_ID_INSTANCE' => $arFields['INSTANCE_PARENT_ID'],
            'UF_TYPE' => 'offline',
            'UF_STATUS' => 'IN_PROCESS',
            'UF_MEETING_ID' => $arFields['MEETING_ID'],
            //'UF_ITEM_ID' => $arFields['ITEM_ID'],
            'IBLOCK_ID' => 30
        );
        return CIBlockSection::GetList(array(), $arFilter, false, array('ID', 'IBLOCK_ID'), false)->Fetch();
    }

    public function getAnswerFromIblock($arFields, $sectionID = 0)
    {
        $arFilter = array(
            'IBLOCK_ID' => 30,
            'PROPERTY_ID_INSTANCE' => $arFields['INSTANCE_ID'],
            'PROPERTY_MEETING_ID' => $arFields['MEETING_ID'],
            'PROPERTY_INSTANCE_PARENT_ID' => $arFields['INSTANCE_PARENT_ID']
        );
        if ($sectionID > 0) {
            $arFilter['IBLOCK_SECTION_ID'] = $sectionID;
        }

        return CIBlockElement::GetList(array(), $arFilter, false, array(), array('ID', 'IBLOCK_ID'))->Fetch();
    }


    public function getDiskStorageByUserId($userId)
    {
        $driver = \Bitrix\Disk\Driver::getInstance();
        return $driver->getStorageByUserId($userId);
    }

    public function createFolderOnDiskStorage($userId, $storage, $folderTitle)
    {
        $newFolder = $storage->addFolder(
            array(
                'NAME' => $folderTitle,
                'CREATED_BY' => $userId
            )
        );

        return $newFolder;
    }

    public function sharedFoldetOnMembers($userId, $users, $folder)
    {
        $erros = new ErrorCollection;
        $needToAdd = array();
        foreach ($users as $id) {
            $needToAdd['U' . $id] = 'disk_access_full';
        }
        $sharings = Sharing::addToManyEntities(array(
            'FROM_ENTITY' => Sharing::CODE_USER . $userId,
            'REAL_OBJECT' => $folder,
            'CREATED_BY' => $userId,
            'CAN_FORWARD' => false,
        ), $needToAdd, $erros);
    }

    public function saveFolderToHL($arFields)
    {
        $arHLBlock = \Bitrix\Highloadblock\HighloadBlockTable::getById(MeetingHendlers::HLBLOCK)->fetch();
        $obEntity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($arHLBlock);
        $strEntityDataClass = $obEntity->getDataClass();

        $obResult = $strEntityDataClass::add($arFields);

        return $bSuccess = $obResult->isSuccess();

    }

    public function getDiskFolder($id, $type)
    {
        $arHLBlock = \Bitrix\Highloadblock\HighloadBlockTable::getById(MeetingHendlers::HLBLOCK)->fetch();
        $obEntity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($arHLBlock);
        $strEntityDataClass = $obEntity->getDataClass();

        if ($type == 'meeting') {
            $filter = array('UF_MEETING' => $id, 'UF_TYPE' => 'meeting');
        }
        if ($type == 'question') {
            $filter = array('UF_QUESTION' => $id, 'UF_TYPE' => 'question');
        }

        $rsData = $strEntityDataClass::getList(array(
            'order' => array('ID' => 'ASC'),
            'filter' => $filter,
        ));

        if ($arItem = $rsData->Fetch()) {
            return \Bitrix\Disk\Folder::loadById($arItem['UF_ID_FOLDER']);
        }

        return null;
    }


}

function writeToLog($data, $title = '', $type = 'simple') {
    $log = "[".date("Y.m.d G:i:s")."] [". (strlen($title) > 0 ? $title : 'DEBUG') . "]\n";
    $log .= print_r($data, 1);
    $log .= "\n------------------------\n";
    file_put_contents(getcwd() . '/log_'.$type.'.log', $log, FILE_APPEND);
    return true;
}

function GetEntityDataClass($HlBlockId)
{
    Loader::IncludeModule('highloadblock');
    if (empty($HlBlockId) || $HlBlockId < 1) {
        return false;
    }
    $hlblock = HLBT::getById($HlBlockId)->fetch();
    $entity = HLBT::compileEntity($hlblock);
    $entity_data_class = $entity->getDataClass();
    return $entity_data_class;
}

function restCommand($method, $params)
{
    if ($method == null)
    {
        return null;
    }

    $queryUrl = 'https://bitrix-preview.tk/rest/1/3gb60guirn4oxa5m/' . $method;
    $queryData = http_build_query(array_merge($params));
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $queryUrl,
        CURLOPT_POSTFIELDS => $queryData,
    ));
    $result = curl_exec($curl);
    curl_close($curl);
    $result = json_decode($result, 1);
    return $result;
}