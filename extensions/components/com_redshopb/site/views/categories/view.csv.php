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
 * Categories View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewCategories extends RedshopbViewCsv
{
	/**
	 * Get the columns for the csv file.
	 *
	 * @return  array  An associative array of column names as key and the title as value.
	 */
	protected function getColumns()
	{
		return array(
			'id' => 'ID',
			'parent_id' => Text::_('COM_REDSHOPB_CATEGORY_PARENT_LABEL'),
			'name' => Text::_('JGLOBAL_TITLE'),
			'state' => Text::_('JSTATUS'),
			'created_date' => Text::_('JGLOBAL_CREATED_DATE'),
			'modified_date' => Text::_('JGLOBAL_FIELD_MODIFIED_LABEL'),
			'created_by' => Text::_('JAUTHOR'),
			'modified_by' => Text::_('JEDITOR'),
		);
	}
}
