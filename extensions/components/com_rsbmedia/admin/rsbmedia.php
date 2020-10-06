<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Rsbmedia
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Controller\BaseController;

$app    = Factory::getApplication();
$input  = $app->input;
$user   = Factory::getUser();
$asset  = $input->get('asset');
$author = $input->get('author');

/*
 // Access check.
/*
 @TODO What is this!
Why ?
	if (!$user->authorise('core.manage', 'com_rsbmedia')
		&&	(!$asset || (
				!$user->authorise('core.edit', $asset)
			&&	!$user->authorise('core.create', $asset)
			&& 	count($user->getAuthorisedCategories($asset, 'core.create')) == 0)
			&&	!($user->id == $author && $user->authorise('core.edit.own', $asset))))
	{
		return JError::raiseWarning(403, Text::_('JERROR_ALERTNOAUTHOR'));
	}
*/

$params = ComponentHelper::getParams('com_rsbmedia');

// Load the helper class
require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/rsbmedia.php';

// Set the path definitions
$popupUpload = $input->get('pop_up', null);
$path        = 'file_path';

$view = $input->get('view');

if (substr(strtolower($view), 0, 6) == 'images' || $popupUpload == 1)
{
	if (ComponentHelper::isEnabled('com_redshopb'))
	{
		$user = RedshopbApp::getUser();

		$company = $user->isLoaded() ? $user->getCompany() : RedshopbApp::getMainCompany();

		$base = $company->getImageFolder(true);
	}

	$path = 'image_path';
}

if (empty($base) || $base == '')
{
	$base = $params->get($path, 'images');
}
else
{
	$base = 'images/' . $base;
}

PluginHelper::importPlugin('rsbmedia');
$app->triggerEvent('onGetBasePath', array(&$base));

define('COM_RSBMEDIA_BASE_RELATIVEPATH', $base);
define('COM_RSBMEDIA_BASE',    JPATH_ROOT . '/' . COM_RSBMEDIA_BASE_RELATIVEPATH);
define('COM_RSBMEDIA_BASEURL', Uri::root() . COM_RSBMEDIA_BASE_RELATIVEPATH);

$controller = BaseController::getInstance('Rsbmedia', array('base_path' => JPATH_COMPONENT_ADMINISTRATOR));
$controller->execute($input->get('task'));
$controller->redirect();
