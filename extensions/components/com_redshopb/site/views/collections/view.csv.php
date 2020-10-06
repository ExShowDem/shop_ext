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
 * Collections View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewCollections extends RedshopbViewCsv
{
	/**
	 * Get the columns for the csv file.
	 *
	 * @return  array  An associative array of column names as key and the title as value.
	 */
	protected function getColumns()
	{
		return array(
			'name' => Text::_('COM_REDSHOPB_NAME'),
			'company' => Text::_('COM_REDSHOPB_COMPANY_LABEL'),
			'customer_company' => Text::_('COM_REDSHOPB_CUSTOMER_COMPANY_LABEL'),
			'customer_department' => Text::_('COM_REDSHOPB_CUSTOMER_DEPARTMENT_LABEL'),
		);
	}
}
