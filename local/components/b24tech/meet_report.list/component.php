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

$this->IncludeComponentTemplate('list');
