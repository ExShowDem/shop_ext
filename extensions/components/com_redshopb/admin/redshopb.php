<?php
/**
 * @package    Redshopb.Admin
 *
 * @copyright  Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

JLoader::import('redshopb.library');

if (!PluginHelper::isEnabled('system', 'redcore'))
{
	throw new Exception(Text::_('COM_REDSHOPB_REDCORE_INIT_FAILED'), 404);
}

$lang = Factory::getLanguage();
$lang->load('com_redshopb', __DIR__);

JLoader::registerPrefix('Redshopb', __DIR__);

$app = Factory::getApplication();

// Instanciate and execute the front controller.
$controller = BaseController::getInstance('Redshopb');

// Check access.
if (!RedshopbHelperACL::getPermission('manage', '', Array(), false, 0, 'core'))
{
	$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');

	return false;
}

$controller->execute($app->input->get('task'));
$controller->redirect();
