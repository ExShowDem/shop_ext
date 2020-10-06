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
 * Report Products Low Stock View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewReport_Products_Low_Stock extends RedshopbViewJson
{
	/**
	 * @var object
	 */
	public $loadedModel = null;

	/**
	 * Get the columns for the JSON file.
	 *
	 * @return  array  An associative array of column names as key and the title as value.
	 */
	protected function getColumns()
	{
		$this->loadedModel = $this->getModel();
		$columns = $this->loadedModel->getReportTableColumns($this->loadedModel->getState('filter.show_extended', 0));
		$returnColumns = array();

		foreach ($columns as $key => $column)
		{
			$returnColumns[$key] = Text::_($column['title']);
		}

		return $returnColumns;
	}

	/**
	 * Preprocess the data before output
	 *
	 * @param   string  $name   Field key name
	 * @param   string  $value  Value
	 *
	 * @return  string
	 */
	protected function preprocess($name, $value)
	{
		return $this->loadedModel->getFormattedValue($name, $value);
	}
}
