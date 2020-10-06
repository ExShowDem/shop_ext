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
class RedshopbViewReport_Products_Low_Stock extends RedshopbViewCsv
{
	/**
	 * Delimiter character for CSV columns
	 *
	 * @var string
	 */
	public $delimiter = ';';

	/**
	 * @var object
	 */
	public $loadedModel = null;

	/**
	 * Get the columns for the csv file.
	 *
	 * @return  array  An associative array of column names as key and the title as value.
	 */
	protected function getColumns()
	{
		$this->loadedModel = $this->getModel();
		$columns           = $this->loadedModel->getReportTableColumns($this->loadedModel->getState('filter.show_extended', 0));
		$csvColumns        = array();

		foreach ($columns as $key => $column)
		{
			$csvColumns[$key] = Text::_($column['title']);
		}

		return $csvColumns;
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
