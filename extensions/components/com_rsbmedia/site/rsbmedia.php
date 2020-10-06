<?php
/**
 * @package     Aesir.E-Commerce.Site
 * @subpackage  Rsmedia
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

// Load the com_media language files, default to the admin file and fall back to site if one isn't found
$lang = Factory::getLanguage();
$lang->load('com_rsbmedia', JPATH_ADMINISTRATOR, null, false, true)
||	$lang->load('com_rsbmedia', JPATH_SITE, null, false, true);

// Hand processing over to the admin base file
require_once JPATH_COMPONENT_ADMINISTRATOR . '/rsbmedia.php';
