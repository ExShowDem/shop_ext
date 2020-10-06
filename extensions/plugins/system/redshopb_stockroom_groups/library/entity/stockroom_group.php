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
 * Stockroom Group Entity.
 *
 * @since  2.0
 */
class RedshopbEntityStockroom_Group extends RedshopbEntity
{
	/**
	 * searchStockrooms() proxy to get this stockroom group stockrooms
	 *
	 * @return  RedshopbEntitiesCollection
	 *
	 * @since   2.0
	 */
	public function getStockrooms()
	{
		return $this->searchStockrooms();
	}

	/**
	 * Search on this stockroom group stockrooms
	 *
	 * @param   array  $modelState  State for the Stockroom model
	 *
	 * @return  RedshopbEntitiesCollection
	 *
	 * @since   2.0
	 */
	public function searchStockrooms($modelState = array())
	{
		$collection = new RedshopbEntitiesCollection;

		if (!$this->hasId())
		{
			return $collection;
		}

		// Default state
		$state = array(
			'list.ordering'  => 's.name',
			'list.direction' => 'ASC',
			'list.limit'     => 0,
			'list.start'     => 0
		);

		// Override any received state
		foreach ($modelState as $key => $value)
		{
			$state[$key] = $value;
		}

		// Force filter
		$state['filter.stockroomGroupId'] = $this->id;

		$model = RedshopbModel::getFrontInstance('Stockroomsgroup');
		$items = $model->search($state);

		foreach ($items as $item)
		{
			$entity = RedshopbEntityStockroom::getInstance($item->id)->bind($item);

			$collection->add($entity);
		}

		return $collection;
	}
}
