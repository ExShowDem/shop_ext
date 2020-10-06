<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
/**
 * Product Item Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelProduct_Items extends RedshopbModelList
{
	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'product_items_limit';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitstartField = 'auto';

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select(
				$this->getState(
					'list.select',
					'pi.*,'
					. $db->qn('pp.price') . ','
					. $db->qn('pp.retail_price')
				)
			);

		$query->from($db->qn('#__redshopb_product_item', 'pi'))
			->leftJoin($db->qn('#__redshopb_product', 'p') . ' ON pi.product_id = p.id')
			->leftJoin(
				$db->qn('#__redshopb_product_price', 'pp') . ' ON ((' . $db->qn('pp.type_id') . ' = ' . $db->qn('pi.id')
				. ') AND (' . $db->qn('pp.type') . ' = ' . $db->quote('product_item') . '))'
			)
			->group('pi.id');

		// Filter by product ID
		$productId = (int) $this->getState('filter.product_id', 0);

		if ($productId)
		{
			$query->where($db->qn('pi.product_id') . ' = ' . $productId);
		}

		// Filter search
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');

			$searchFields = array(
				'pi.sku LIKE ' . $search
			);

			$query->where('(' . implode(' OR ', $searchFields) . ')');
		}

		// Filter by state
		$state = $this->getState('filter.state');

		if ($state == '0' || $state == 'false')
		{
			$query->where($db->qn('pi.state') . ' = 0');
		}
		elseif ($state == '1' || $state == 'true')
		{
			$query->where($db->qn('pi.state') . ' = 1');
		}

		// Filter by discontinued
		$discontinued = $this->getState('filter.discontinued');

		if ($discontinued == '0' || $discontinued == 'false')
		{
			$query->where($db->qn('pi.discontinued') . ' = 0');
		}
		elseif ($discontinued == '1' || $discontinued == 'true')
		{
			$query->where($db->qn('pi.discontinued') . ' = 1');
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 'pi.id';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   11.1
	 */
	public function getItems()
	{
		$productItems = parent::getItems();

		if (empty($productItems))
		{
			return false;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		foreach ($productItems as &$productItem)
		{
			$query->clear()
				->select($db->qn('product_attribute_value_id'))
				->from($db->qn('#__redshopb_product_item_attribute_value_xref'))
				->where($db->qn('product_item_id') . ' = ' . $productItem->id);
			$db->setQuery($query);
			$productItem->product_attribute_value_ids = $db->loadColumn();
		}

		return $productItems;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @param   string  $ordering   [description]
	 * @param   string  $direction  [description]
	 *
	 * @return  void
	 */
	protected function populateState($ordering = 'pi.id', $direction = 'ASC')
	{
		$app = Factory::getApplication();

		$filterProductId = $this->getUserStateFromRequest($this->context . '.product_id', 'product_id');
		$this->setState('filter.product_id', $filterProductId);

		$value = $app->getUserStateFromRequest('global.list.limit', $this->paginationPrefix . 'limit', $app->get('list_limit'), 'uint');
		$limit = $value;
		$this->setState('list.limit', $limit);

		$value      = $app->getUserStateFromRequest($this->context . '.limitstart', $this->paginationPrefix . 'limitstart', 0);
		$limitstart = ($limit != 0 ? (floor($value / $limit) * $limit) : 0);
		$this->setState('list.start', $limitstart);

		parent::populateState($ordering, $direction);
	}
}
