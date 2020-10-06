<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Tax_Group Entity.
 *
 * @since  2.0
 */
class RedshopbEntityTax_Group extends RedshopbEntity
{
	/**
	 * Get the associated table
	 *
	 * @param   string  $name  Main name of the Table. Example: Article for ContentTableArticle
	 *
	 * @return  RTable
	 */
	public function getTable($name = null)
	{
		$name = is_null($name) ? 'Tax_Group' : $name;

		return parent::getTable($name);
	}

	/**
	 * searchTax() proxy to get this price group taxes
	 *
	 * @param   array  $modelState  State for the Taxes model
	 *
	 * @return  RedshopbEntitiesCollection
	 *
	 * @since   2.0
	 */
	public function getTaxes($modelState = array())
	{
		return $this->searchTaxes($modelState);
	}

	/**
	 * Search inside this price group taxes
	 *
	 * @param   array  $modelState  State for the Taxes model
	 *
	 * @return  RedshopbEntitiesCollection    List of taxes
	 *
	 * @since   2.0
	 */
	public function searchTaxes($modelState = array())
	{
		$collection = new RedshopbEntitiesCollection;

		if (!$this->hasId())
		{
			return $collection;
		}

		$state = array(
			'list.ordering'  => 'tx.tax_rate',
			'list.direction' => 'ASC',
			'list.limit'     => 0,
			'list.start'     => 0
		);

		foreach ($modelState as $key => $value)
		{
			$state[$key] = $value;
		}

		// Force tax group filter
		$state['filter.tax_group'] = $this->id;
		$state['filter.tax_state'] = 1;

		$taxes = RedshopbModel::getFrontInstance('Taxes')
			->search($state);

		foreach ($taxes as $tax)
		{
			$entity = RedshopbEntityTax::getInstance($tax->id)
				->bind($tax);

			$collection->add($entity);
		}

		return $collection;
	}
}
