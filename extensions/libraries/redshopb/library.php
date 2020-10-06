<?php
/**
 * RedShopb Library file.
 * Including this file into your application will make redSHOPB available to use.
 *
 * @package    Aesir.E-Commerce
 * @copyright  Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;

$redcoreLoader = JPATH_LIBRARIES . '/redcore/bootstrap.php';

if (!file_exists($redcoreLoader))
{
	throw new Exception(Text::_('COM_REDSHOPB_REDCORE_INIT_FAILED'), 404);
}

include_once $redcoreLoader;

// Bootstraps redCORE
RBootstrap::bootstrap();

$composerAutoload = __DIR__ . '/vendor/autoload.php';

if (file_exists($composerAutoload))
{
	require_once $composerAutoload;
}

// Register library prefix and namespace
RLoader::registerPrefix('Redshopb', __DIR__);
JLoader::registerNamespace('Vanir', JPATH_LIBRARIES . '/redshopb/src');

// Make available the redSHOP fields
FormHelper::addFieldPath(__DIR__ . '/form/fields');

// Make available the redSHOP form rules
FormHelper::addRulePath(__DIR__ . '/form/rules');

// Add the include path for html
HTMLHelper::addIncludePath(__DIR__ . '/html');

// Load library language
$lang = Factory::getLanguage();
$lang->load('lib_redshopb', __DIR__);

// This shouldn't be loaded here but B/C ....
$lang->load('com_redshopb', JPATH_SITE . "/components/com_redshopb");

PluginHelper::importPlugin('vanir');
PluginHelper::importPlugin('vanir_search');
