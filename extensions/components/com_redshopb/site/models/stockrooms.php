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
 * Stockrooms Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelStockrooms extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_stockrooms';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'stockrooms_limit';

	/**
	 * Constructor
	 *
	 * @param   array  $config  Configuration array
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'name', 's.name',
				'ordering', 's.ordering',
				'id', 's.id',
				'min_delivery_time', 's.min_delivery_time',
				'max_delivery_time', 's.max_delivery_time',
				'company_name',
				'company_id', 'zip', 'city', 'country_code',
				'state', 's.state'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState('s.ordering', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db	= $this->getDbo();

		$query = $db->getQuery(true)
			->select('s.*')
			->from($db->qn('#__redshopb_stockroom', 's'))

			// Select address infos
			->select(
				array(
					$db->qn('a.address'),
					$db->qn('a.zip'),
					$db->qn('a.city'),
					$db->qn('a.name', 'address_name1'),
					$db->qn('a.name2', 'address_name2'),
					$db->qn('a.address', 'address_line1'),
					$db->qn('a.address2', 'address_line2')
				)
			)
			->leftJoin($db->qn('#__redshopb_address', 'a') . ' ON ' . $db->qn('s.address_id') . ' = ' . $db->qn('a.id'))

			// Select the country name and code
			->select(
				array(
					$db->qn('con.name', 'country'),
					$db->qn('con.alpha2', 'country_code')
				)
			)
			->leftJoin($db->qn('#__redshopb_country', 'con') . ' ON ' . $db->qn('con.id') . ' = ' . $db->qn('a.country_id'))

			// Select the company info
			->select($db->qn('c.name', 'company_name'))
			->leftJoin($db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('s.company_id'));

		$id = $this->getState('filter.id');

		if (is_numeric($id) && $id > 0)
		{
			$query->where('s.id = ' . (int) $id);
		}

		$companyId = $this->getState('filter.company_id');

		if (is_numeric($companyId))
		{
			$query->where('s.company_id = ' . (int) $companyId);
		}

		$zip = $this->getState('filter.zip');

		if (!empty($zip))
		{
			$query->where($db->qn('a.zip') . ' = ' . $db->q($zip));
		}

		$city = $this->getState('filter.city');

		if (!empty($city))
		{
			$query->where($db->qn('a.city') . ' = ' . $db->q($city));
		}

		$countryCode = $this->getState('filter.country_code');

		if (!empty($countryCode))
		{
			$query->where($db->qn('con.alpha2') . ' = ' . $db->q($countryCode));
		}

		// Filter by state.
		$state = $this->getState('filter.company_state', $this->getState('filter.state'));

		if ($state == '0' || $state == 'false')
		{
			$query->where($db->qn('s.state') . ' = 0');
		}
		elseif ($state == '1' || $state == 'true')
		{
			$query->where($db->qn('s.state') . ' = 1');
		}

		// Filter search
		$search = $this->getState('filter.search_stockrooms', $this->getState('filter.search'));

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');

			$searchFields = array(
				$db->qn('s.name') . ' LIKE ' . $search,
				$db->qn('a.name') . ' LIKE ' . $search,
				$db->qn('a.name2') . ' LIKE ' . $search,
				$db->qn('a.address') . ' LIKE ' . $search,
				$db->qn('a.address2') . ' LIKE ' . $search,
				$db->qn('a.zip') . ' LIKE ' . $search,
				$db->qn('a.city') . ' LIKE ' . $search,
				$db->qn('con.alpha2') . ' LIKE ' . $search,
				$db->qn('con.name') . ' LIKE ' . $search
			);

			$query->where('(' . implode(' OR ', $searchFields) . ')');
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 's.ordering';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		// Adds related web service data when requested
		$this->getListQueryWS($query);

		return $query;
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return	string  A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':' . $this->getState('filter.id');
		$id	.= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.company_id');

		return parent::getStoreId($id);
	}

	/**
	 * Method for get stock id with have minimum delivery time.
	 *
	 * @param   array|int  $productIds  ID of product.
	 *
	 * @return  array ID of stockrooms.
	 */
	public function getMinDeliveryStocks($productIds)
	{
		$productIds        = (array) $productIds;
		$productIds        = Joomla\Utilities\ArrayHelper::toInteger($productIds);
		static $stockrooms = array();

		if (empty($productIds))
		{
			return array();
		}

		$foundProducts     = array();
		$productsForSearch = array();

		foreach ($productIds as $productId)
		{
			if (array_key_exists($productId, $stockrooms))
			{
				$foundProducts[$productId] = $stockrooms[$productId];
			}
			else
			{
				$productsForSearch[$productId] = $productId;
			}
		}

		if (!empty($productsForSearch))
		{
			$db = Factory::getDbo();

			// Getting min delivery time for each product
			$subQuery = $db->getQuery(true)
				->select('MIN(s1.min_delivery_time) AS min_delivery_time, ref2.product_id')
				->from($db->qn('#__redshopb_stockroom_product_xref', 'ref2'))
				->leftJoin($db->qn('#__redshopb_stockroom', 's1') . ' ON ' . $db->qn('s1.id') . ' = ' . $db->qn('ref2.stockroom_id'))
				->where($db->qn('ref2.product_id') . ' IN (' . implode(',', $productsForSearch) . ')')
				->where('(' . $db->qn('ref2.unlimited') . ' = 1 OR (' . $db->qn('ref2.unlimited') . ' = 0 AND ' . $db->qn('ref2.amount') . ' > 0))')
				->group('ref2.product_id');

			// Getting stockroom id for each product depends with min delivery time
			$query = $db->getQuery(true)
				->select('ref.product_id, ref.stockroom_id')
				->from($db->qn('#__redshopb_stockroom_product_xref', 'ref'))
				->leftJoin($db->qn('#__redshopb_stockroom', 's') . ' ON ' . $db->qn('s.id') . ' = ' . $db->qn('ref.stockroom_id'))
				->where($db->qn('ref.product_id') . ' IN (' . implode(',', $productsForSearch) . ')')
				->innerJoin(
					'(' . $subQuery . ') AS subQuery ON subQuery.product_id = ref.product_id AND subQuery.min_delivery_time = s.min_delivery_time'
				)
				->group('ref.product_id');

			$results = $db->setQuery($query)
				->loadAssocList('product_id', 'stockroom_id');

			if (!empty($results))
			{
				$stockrooms    = array_replace($stockrooms, $results);
				$foundProducts = array_replace($foundProducts, $results);
			}

			foreach ($productsForSearch as $item)
			{
				if (!array_key_exists($item, $stockrooms))
				{
					$stockrooms[$item] = null;
				}
			}
		}

		return $foundProducts;
	}
}
