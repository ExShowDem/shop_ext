<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Companies View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewAddresses extends RedshopbViewCsv
{
	/**
	 * Delimiter character for CSV columns
	 *
	 * @var string
	 */
	public $delimiter = ';';

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
			$model = RModel::getFrontInstance('Addresses');
		}

		return $model->getCsvColumns();
	}
}
