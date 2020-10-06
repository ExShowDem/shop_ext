<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.helper');

/**
 * Companies View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.1
 */
class RedshopbViewOrders extends RedshopbViewCsv
{
	/**
	 * Delimiter character for CSV columns
	 *
	 * @var string
	 */
	public $delimiter = ',';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->delimiter = RedshopbApp::getConfig()->getString('csv_separator', $this->delimiter);
	}

	/**
	 * Get the columns for the csv file.
	 *
	 * @return  array  An associative array of column names as key and the title as value.
	 */
	protected function getColumns()
	{
		$model = $this->getModel();

		if (null === $model)
		{
			$model = RModel::getFrontInstance('Orders');
		}

		return $model->getCsvColumns();
	}

	/**
	 * Preprocesses fields to be added
	 *
	 * @param   string  $name   The name of the field value to be processed
	 * @param   string  $field  The field value to be processed
	 *
	 * @return  string  The processed field value
	 */
	public function preprocess($name, $field)
	{
		// Function is meant to be overridden in other views.
		$result = $field;

		$separator = RedshopbApp::getConfig()->getString('currency_separator', ',');

		// Checking to see if the field is a number with decimal places
		preg_match("/^\d+\.\d+$/", $field, $matches);

		if (!empty($separator) && !empty($matches) && $matches[0] == (string) $field)
		{
			$result = str_replace('.', $separator, $result);
		}

		return $result;
	}
}
