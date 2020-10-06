<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Product Descriptions Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelProduct_Descriptions extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_product_descriptions';

	/**
	 * Limit field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'product_description_limit';

	/**
	 * Limit start field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitstartField = 'auto';

	/**
	 * Main table query prefix
	 *
	 * @var  array
	 */
	protected $mainTablePrefix = 'pd';

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
				'id', 'pd.id', 'not_id',
				'product_id', 'pd.product_id', 'product_id_array', 'product_ids',
				'main_attribute_value_id', 'pd.main_attribute_value_id'
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
		parent::populateState('pd.id', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	public function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$filterId 					= $this->getState('filter.id', null);
		$filterNotId 				= $this->getState('filter.not_id', null);
		$filterProductId 			= $this->getState('filter.product_id', null);
		$filterMainAttributeValueId = $this->getState('filter.main_attribute_value_id', null);

		$query->select(
			array(
					'pd.*'
				)
		)
			->from($db->qn('#__redshopb_product_descriptions', 'pd'));

		if (is_numeric($filterId) && $filterId > 0)
		{
			$query->where($db->qn('pd.id') . ' = ' . (int) $filterId);
		}

		if (is_numeric($filterNotId) && $filterNotId > 0)
		{
			$query->where($db->qn('pd.id') . ' <> ' . (int) $filterNotId);
		}

		if (is_numeric($filterProductId) && $filterProductId > 0)
		{
			$query->where($db->qn('pd.product_id') . ' = ' . (int) $filterProductId);
		}

		if (!is_null($filterMainAttributeValueId))
		{
			if ($filterMainAttributeValueId == 'null' || $filterMainAttributeValueId == '0')
			{
				$query->where($db->qn('pd.main_attribute_value_id') . ' IS NULL');
			}
			elseif (is_numeric($filterMainAttributeValueId) && $filterMainAttributeValueId > 0)
			{
				$query->where($db->qn('pd.main_attribute_value_id') . ' = ' . (int) $filterMainAttributeValueId);
			}
		}

		// Product id search by array
		$productIdArray = $this->getState('filter.product_id_array');

		if (!empty($productIdArray))
		{
			$productIdArray = json_decode($productIdArray, true);

			if (!empty($productIdArray))
			{
				$query->where(
					$db->qn('pd.product_id') . ' IN (' . implode(',', RHelperArray::quote($productIdArray)) . ')'
				);
			}
		}

		// Filter by multiple product ids
		$productIds = $this->getState('filter.product_ids', null);

		if (!is_null($productIds))
		{
			$query->where($db->qn('pd.product_id') . ' IN (' . implode(',', $db->q($productIds)) . ')');
		}

		// Filter search
		$search = $this->getState('filter.search_product_descriptions', $this->getState('filter.search'));

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');

			$searchFields = array(
				'pd.description_intro LIKE ' . $search,
				'pd.description LIKE ' . $search,
			);

			$query->where('(' . implode(' OR ', $searchFields) . ')');
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');
		$order         = !empty($orderList) ? $orderList : 'pd.id';
		$direction     = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		// Adds related web service data when requested
		$this->getListQueryWS($query);

		return $query;
	}
}
