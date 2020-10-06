<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
/**
 * Products View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewProducts extends RedshopbViewCsv
{
	/**
	 * Get the columns for the csv file.
	 *
	 * @return  array  An associative array of column names as key and the title as value.
	 */
	protected function getColumns()
	{
		return array(
			'sku' => Text::_('COM_REDSHOPB_SKU'),
			'name' => Text::_('COM_REDSHOPB_NAME'),
			'state' => Text::_('JSTATUS'),
			'discontinued' => Text::_('COM_REDSHOPB_PRODUCT_DISCONTINUED'),
			'company' => Text::_('COM_REDSHOPB_COMPANY_LABEL'),
		);
	}
}
