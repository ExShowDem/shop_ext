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
 * Currencies View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewCurrencies extends RedshopbViewCsv
{
	/**
	 * Get the columns for the csv file.
	 *
	 * @return  array  An associative array of column names as key and the title as value.
	 */
	protected function getColumns()
	{
		return array(
			'state' => Text::_('JSTATUS'),
			'name' => Text::_('COM_REDSHOPB_NAME'),
			'alpha3' => Text::_('COM_REDSHOPB_CURRENCY_ALPHA3_LBL'),
			'numeric' => Text::_('COM_REDSHOPB_CURRENCY_NUMERIC_LBL'),
			'symbol' => Text::_('COM_REDSHOPB_CURRENCY_SYMBOL_LBL'),
			'created_by' => Text::_('JAUTHOR'),
			'created_date' => Text::_('JGLOBAL_CREATED_DATE'),
		);
	}
}
