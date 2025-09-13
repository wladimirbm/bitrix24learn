<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Intranet\Site\Sections\CollaborationSection;
use Bitrix\Main\Loader;
use Bitrix\Intranet\Settings\Tools\ToolsManager;

$GLOBALS['APPLICATION']->setPageProperty('topMenuSectionDir', SITE_DIR . 'collaboration/');

if (!Loader::includeModule('intranet'))
{
	return;
}

$aMenuLinks = CollaborationSection::getMenuData();
