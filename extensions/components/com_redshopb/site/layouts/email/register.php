<?php
/**
 * @package     Vanir.Plugin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2005 - 2017 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$data       = (object) $displayData;
$defaultMsg = nl2br(Text::_('PLG_USER_JOOMLA_NEW_USER_EMAIL_BODY'));

echo sprintf(
	$defaultMsg,
	$data->name,
	$data->siteName,
	$data->url,
	$data->userName,
	$data->password
);
