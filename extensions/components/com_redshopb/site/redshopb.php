<?php
/**
 * @package    Redshopb.Site
 *
 * @copyright  Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\Controller\BaseController;

JLoader::import('redshopb.library');

if (!PluginHelper::isEnabled('system', 'redcore'))
{
	throw new Exception(Text::_('COM_REDSHOPB_REDCORE_INIT_FAILED'), 404);
}

// Override language default language which will force it to always search for translation
$currentSiteLanguage              = RTranslationHelper::$siteLanguage;
RTranslationHelper::$siteLanguage = '-';

$lang = Factory::getLanguage();
$lang->load('com_redshopb', __DIR__);

JLoader::registerPrefix('Redshopb', __DIR__);
PluginHelper::importPlugin('vanir');

RedshopbHelperUser::checkStateCompanyForUser();
RHelperAsset::load('cart.css', 'com_redshopb');
$app   = Factory::getApplication();
$input = $app->input;

// Instantiate and execute the front controller.
$controller = BaseController::getInstance('Redshopb');

$app->triggerEvent('onRedshopbSiteController', array($controller));

$view = $input->getCmd('view');

if (!$view)
{
	$view = $controller->get('view_list');
	$app->setUserState('user.view', $controller->get('view_list'));
}

$specification = RedshopbApp::getAccessSpecification();
$user          = RedshopbEntityUser::loadFromJoomlaUser(Factory::getUser()->id);

if (!RedshopbHelperAjax::isAjaxRequest() && !$specification->grant($view, $user, $input))
{
	$specification->redirect();
}

$redshopbConfig = RedshopbApp::getConfig();
RHtmlMedia::setFramework($redshopbConfig->getString('default_frontend_framework', 'bootstrap3'));

$controller->execute($input->get('task'));

// We are setting back default site language
RTranslationHelper::$siteLanguage = $currentSiteLanguage;

$controller->redirect();
