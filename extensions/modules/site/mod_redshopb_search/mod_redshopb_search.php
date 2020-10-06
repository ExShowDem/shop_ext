<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  mod_redshopb_search
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

JLoader::import('redshopb.library');

$redshopbConfig = RedshopbApp::getConfig();
RHtmlMedia::setFramework($redshopbConfig->getString('default_frontend_framework', 'bootstrap3'));

$lang = Factory::getLanguage();
$lang->load('mod_redshopb_search', __DIR__);

RHelperAsset::load('mod_redshopb_search.css', 'mod_redshopb_search');

$lang = Factory::getLanguage();
$app  = Factory::getApplication();

$upperLimit     = $lang->getUpperLimitSearchWord();
$submitType     = $params->get('submit_type', 0);
$submitPosition = $params->get('submit_position', 0);
$submitClass    = $params->get('submit_button_class', '');
$clearClass     = $params->get('clear_button_class', '');
$inputClass     = $params->get('input_field_class', '');
$doAjax         = (int) $params->get('ajax', 1);
$buttonText     = htmlspecialchars($params->get('button_text', Text::_('MOD_REDSHOPB_SEARCH_SEARCHBUTTON_TEXT')));
$width          = (int) $params->get('width', 20);
$maxlength      = $upperLimit;
$setItemid      = (int) $params->get('set_itemid', 0);
$searchState    = Factory::getApplication()->input->getString('search', '');

if (!empty($searchState))
{
	$text = htmlspecialchars($searchState);
}
else
{
	$text = htmlspecialchars($params->get('text', Text::_('MOD_REDSHOPB_SEARCH_SEARCHBOX_TEXT')));
}

$label			= htmlspecialchars($params->get('label', Text::_('MOD_REDSHOPB_SEARCH_LABEL_TEXT')));
$moduleclassSfx = htmlspecialchars($params->get('moduleclass_sfx'));

$moduleLayout = RModuleHelper::getLayoutPath('mod_redshopb_search', $params->get('layout', 'default'));
require $moduleLayout;
