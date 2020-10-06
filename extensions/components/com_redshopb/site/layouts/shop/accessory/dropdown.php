<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Extracted
 *
 * @var $displayData
 * @var $id
 * @var $cartPrefix
 * @avr $options
 */

extract($displayData);

if (empty($options))
{
	return;
}

array_unshift($options, HTMLHelper::_('select.optgroup', Text::_('COM_REDSHOPB_SHOP_SELECT_ACCESSORY')));
$options[] = HTMLHelper::_('select.optgroup', Text::_('COM_REDSHOPB_SHOP_SELECT_ACCESSORY'));

echo HTMLHelper::_(
	'select.genericlist', $options, 'accessory[' . $id . '][]',
	' class="dropDownAccessory" multiple="multiple" ', 'value', 'text', $selected,
	'dropDownAccessory_' . $id . $cartPrefix
);
