<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  mod_redshopb_topnav
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

JLoader::import('redshopb.library');

$redshopbConfig = RedshopbApp::getConfig();
RHtmlMedia::setFramework($redshopbConfig->getString('default_frontend_framework', 'bootstrap3'));

$lang = Factory::getLanguage();
$lang->load('mod_redshopb_layoutparts', __DIR__);

HTMLHelper::stylesheet(
	'mod_redshopb_layoutparts/mod_redshopb_layoutparts.css',
	array('relative' => true)
);

// Checks current layout set
$layout       = null;
$layoutParams = null;

$currentLayout = RedshopbHelperLayout::getCurrentLayout();

if ($currentLayout)
{
	$layout = RedshopbHelperLayout::getLayout($currentLayout);

	if ($layout)
	{
		$layoutParams = json_decode($layout->params);
	}
}

$part    = $params->get('part', '');
$wrapper = $params->get('wrapper', '');
$valPart = '';

if ($part != '')
{
	switch ($part)
	{
		case 'topimage':
			$moduleLayout = RModuleHelper::getLayoutPath('mod_redshopb_layoutparts', $part);
			require $moduleLayout;
			break;

		default:
			if ($layoutParams)
			{
				if (property_exists($layoutParams, $part))
				{
					eval("\$valPart = \$layoutParams->" . $part . ";");
					eval("\$wrapper = '" . $wrapper . "';");
				}
			}

			$moduleLayout = RModuleHelper::getLayoutPath('mod_redshopb_layoutparts');
			require $moduleLayout;
	}
}
