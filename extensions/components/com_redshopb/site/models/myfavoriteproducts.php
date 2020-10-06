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

require_once 'shop.php';

/**
 * Shop Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelMyfavoriteproducts extends RedshopbModelShop
{
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$excludeId      = $this->getState('filter.favorite_list_id_exclude', true);
		$favoriteListId = $this->getState('filter.favorite_list_id', 0);
		$isTotal        = $this->getState('list.isTotal', false);
		$favTable       = $this->getTable('Myfavoritelist');

		if (!$favoriteListId || !$favTable->load((int) $favoriteListId))
		{
			// Dummy query to avoid returning null in case a getTotal is called
			$db    = $this->getDbo();
			$query = $db->getQuery(true)
				->select('1')
				->from($db->qn('#__redshopb_product', 'p'))
				->where('1 = 0');

			return $query;
		}

		$this->customerType  = 'employee';
		$this->customerId    = $favTable->get('user_id', 0);
		$this->customerCType = RedshopbHelperShop::getCustomerType($this->customerId, $this->customerType);

		$db    = Factory::getDbo();
		$query = parent::getListQuery()
			->clear('group')
			->group('p.id');

		if (!$isTotal)
		{
			$useCollection = RedshopbHelperShop::inCollectionMode(
				RedshopbEntityCompany::getInstance(
					RedshopbHelperCompany::getCompanyIdByCustomer($this->customerId, $this->customerType)
				)
			);

			if ($useCollection)
			{
				$subQuery = $db->getQuery(true)
					->select('GROUP_CONCAT(DISTINCT collection.collection_id SEPARATOR ' . $db->q(',') . ')')
					->from($db->qn('#__redshopb_collection_product_xref', 'collection'))
					->where('collection.product_id = p.id')
					->where('collection.state = 1');
				$query->select('(' . $subQuery . ') AS collection_ids');
			}
			else
			{
				$query->select($db->q('') . ' AS collection_ids');
			}

			$query->select($db->qn('um.alias', 'unit_measure_code'))
				->leftJoin($db->qn('#__redshopb_unit_measure', 'um') . ' ON ' . $db->qn('p.unit_measure_id') . ' = ' . $db->qn('um.id'));
		}

		if ($excludeId)
		{
			$subQuery = $db->getQuery(true)
				->select('favpx.product_id')
				->from($db->qn('#__redshopb_favoritelist_product_xref', 'favpx'))
				->where('favpx.favoritelist_id = ' . (int) $favoriteListId);

			$query->where('p.id NOT IN (' . $subQuery . ')');
		}
		else
		{
			$attrNameSubQuery = $db->getQuery(true)
				->select('GROUP_CONCAT(pav.sku ORDER BY pa.main_attribute desc, pa.ordering asc SEPARATOR ' . $db->q('-') . ') AS attr_name')
				->from($db->qn('#__redshopb_product_item_attribute_value_xref', 'pivx'))
				->leftJoin(
					$db->qn('#__redshopb_product_attribute_value', 'pav') . ' ON ' .
					$db->qn('pivx.product_attribute_value_id') . ' = ' . $db->qn('pav.id') . ' AND pav.state = 1'
				)
				->leftJoin(
					$db->qn('#__redshopb_product_attribute', 'pa') . ' ON ' .
					$db->qn('pa.id') . ' = ' . $db->qn('pav.product_attribute_id') . ' AND pa.state = 1'
				)
				->where($db->qn('pivx.product_item_id') . ' = ' . $db->qn('favpix.product_item_id'))
				->group('pivx.product_item_id');

			$query
				->select('COALESCE (favpx.quantity, favpix.quantity) AS quantity')
				->select('favpix.product_item_id')
				->select('(' . $attrNameSubQuery . ') as attr_name')
				->leftJoin($db->qn('#__redshopb_favoritelist_product_xref', 'favpx') . ' ON favpx.product_id = p.id')
				->leftJoin($db->qn('#__redshopb_favoritelist_product_item_xref', 'favpix') . ' ON favpix.product_item_id = pi.id')
				->where('favpx.favoritelist_id = ' . (int) $favoriteListId . ' OR favpix.favoritelist_id = ' . (int) $favoriteListId)
				->group('pi.id');
		}

		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function getItems()
	{
		$items = parent::getItems();

		if ($items)
		{
			usort(
				$items,
				function ($a, $b)
				{
					return strnatcasecmp($a->sku, $b->sku);
				}
			);
		}

		return $items;
	}

	/**
	 * Remove Not Available Products
	 *
	 * @return  boolean
	 *
	 * @since 1.13.0
	 */
	public function removeNotAvailableProducts()
	{
		$items      = $this->getItems();
		$productIds = array();

		if (!empty($items))
		{
			foreach ($items as $item)
			{
				$productIds[] = $item->id;
			}
		}

		$favoriteListId = $this->getState('filter.favorite_list_id', 0);
		$db             = Factory::getDbo();
		$subQuery       = $db->getQuery(true)
			->where('favoritelist_id = ' . (int) $favoriteListId);

		if (!empty($productIds))
		{
			$subQuery->where('product_id NOT IN (' . implode(',', $productIds) . ')');
		}

		$query = clone $subQuery;
		$query->select('product_id')
			->from($db->qn('#__redshopb_favoritelist_product_xref'));

		$result = $db->setQuery($query, 0, 1)->loadResult();

		if (empty($result))
		{
			return false;
		}

		$query = clone $subQuery;
		$query->delete($db->qn('#__redshopb_favoritelist_product_xref'));

		if (!$db->setQuery($query)->execute())
		{
			return false;
		}

		return true;
	}
}
