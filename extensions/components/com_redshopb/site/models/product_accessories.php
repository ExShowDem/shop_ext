<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;
jimport('models.trait.productfilters', JPATH_ROOT . '/components/com_redshopb/');

use Joomla\CMS\Factory;
/**
 * Product Accessories Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelProduct_Accessories extends RedshopbModelList
{
	use RedshopbModelsTraitProductFilters;

	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_product_accessories';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'product_accessories_limit';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitstartField = 'auto';

	/**
	 * Main table query prefix
	 *
	 * @var  array
	 */
	protected $mainTablePrefix = 'pa';

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
				'id', 'pa.id',
				'product_id', 'pa.product_id',
				'accessory_product_id', 'pa.accessory_product_id',
				'collection_id', 'pa.collection_id',
				'selection', 'pa.selection',
				'state', 'pa.state',
				'p.name', 'product_name',
				'product_ids'
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
		$app = Factory::getApplication();

		$filterProductId = $this->getUserStateFromRequest($this->context . '.product_id', 'product_id');
		$this->setState('filter.product_id', $filterProductId);

		$filterAccessoryProductId = $this->getUserStateFromRequest($this->context . '.accessory_product_id', 'accessory_product_id');
		$this->setState('filter.accessory_product_id', $filterAccessoryProductId);

		$filterCollectionId = $this->getUserStateFromRequest($this->context . '.collection_id', 'collection_id');
		$this->setState('filter.collection_id', $filterCollectionId);

		parent::populateState('pa.id', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		// For get selected accessories products
		$getSelected = (boolean) $this->getState('list.getSelected', false);

		if ($getSelected)
		{
			return $this->getSelectedListQuery();
		}

		$db	= $this->getDbo();

		$query = $db->getQuery(true)
			->select('pa.*')
			->from($db->qn('#__redshopb_product_accessory', 'pa'));

		// Filter by product id
		$productId = $this->getState('filter.product_id');

		if (is_numeric($productId))
		{
			$query->where($db->qn('pa.product_id') . ' = ' . (int) $productId);
		}

		// Filter by accessory product id
		$accessoryProductId = $this->getState('filter.accessory_product_id');

		if (is_numeric($accessoryProductId))
		{
			$query->where($db->qn('pa.accessory_product_id') . ' = ' . (int) $accessoryProductId);
		}

		// Filter by collection id
		$collectionId = $this->getState('filter.collection_id');

		if (is_numeric($collectionId))
		{
			$query->where($db->qn('pa.collection_id') . ' = ' . (int) $collectionId);
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 'pa.id';
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
		$id .= ':' . $this->getState('filter.product_id');
		$id .= ':' . $this->getState('filter.accessory_product_id');
		$id .= ':' . $this->getState('filter.collection_id');
		$id .= ':' . $this->getState('filter.search_product_accessories');

		return parent::getStoreId($id);
	}

	/**
	 * Method for return query to get selected accessories product of an product.
	 *
	 * @return  JDatabaseQuery
	 */
	public function getSelectedListQuery()
	{
		$db = $this->getDbo();

		// Filter product Id
		$productId = (int) $this->getState('filter.product_id');

		if (!$productId)
		{
			return $db->getQuery(true);
		}

		// Filter search
		$filterSearch = $this->getState('filter.search_product_accessories');

		$productModel = RModelAdmin::getInstance('Product', 'RedshopbModel');
		$issetItems   = $productModel->getIssetItems($productId);
		$pItemsQuery  = null;

		if (!empty($issetItems))
		{
			$attributes    = $productModel->getAttributes($productId);
			$attributesIds = array();

			foreach ($attributes as $attribute)
			{
				if ($attribute['main_attribute'])
				{
					$attributesIds[] = $attribute['id'];
				}
			}

			$pItemsQuery = $db->getQuery(true)
				->select(
					array(
						$db->qn('pia.id', 'id'),
						$db->qn('pa.product_id', 'product_id'),
						$db->qn('pia.accessory_product_id', 'accessory_product_id'),
						$db->qn('pia.description', 'description'),
						$db->qn('pia.collection_id', 'collection_id'),
						$db->qn('pia.hide_on_collection', 'hide_on_collection'),
						$db->qn('pia.price', 'price'),
						$db->qn('pia.selection', 'selection'),
						$db->qn('pia.state', 'state'),
						$db->qn('pav.sku', 'product_item_sku'),
						$db->qn('p.name', 'product_name')
					)
				)
				->from($db->qn('#__redshopb_product_item_accessory', 'pia'))
				->leftJoin($db->qn('#__redshopb_product', 'p') . ' ON ' . $db->qn('p.id') . ' = ' . $db->qn('pia.accessory_product_id'))
				->innerJoin(
					$db->qn('#__redshopb_product_attribute_value', 'pav')
					. ' ON ' . $db->qn('pia.attribute_value_id') . ' = ' . $db->qn('pav.id')
				)
				->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pa.id = pav.product_attribute_id')
				->where($db->qn('pia.attribute_value_id') . ' IN (' . implode(',', $attributesIds) . ')');

			if (!empty($filterSearch))
			{
				$filterSearch = $db->quote('%' . $db->escape($filterSearch, true) . '%');
				$pItemsQuery->where($db->qn('p.name') . ' LIKE ' . $filterSearch);
			}

			$pItemsQuery->group($db->qn('pia.accessory_product_id'));
		}

		$pQuery = $db->getQuery(true)
			->select(
				array(
					$db->qn('pa.id', 'id'),
					$db->qn('pa.product_id', 'product_id'),
					$db->qn('pa.accessory_product_id', 'accessory_product_id'),
					$db->qn('pa.description', 'description'),
					$db->qn('pa.collection_id', 'collection_id'),
					$db->qn('pa.hide_on_collection', 'hide_on_collection'),
					$db->qn('pa.price', 'price'),
					$db->qn('pa.selection', 'selection'),
					$db->qn('pa.state', 'state'),
					'NULL as product_item_sku',
					$db->qn('p.name', 'product_name')
				)
			)
			->from($db->qn('#__redshopb_product_accessory', 'pa'))
			->leftJoin($db->qn('#__redshopb_product', 'p') . ' ON ' . $db->qn('p.id') . ' = ' . $db->qn('pa.accessory_product_id'))
			->where($db->qn('pa.product_id') . ' = ' . $productId);

		if (!empty($filterSearch))
		{
			$filterSearch = $db->quote('%' . $db->escape($filterSearch, true) . '%');
			$pQuery->where($db->qn('p.name') . ' LIKE ' . $filterSearch);
		}

		$pQuery = $this->applyCommonProductFilters($this, $db, $pQuery);

		$pQuery->group($db->qn('pa.accessory_product_id'));

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');
		$order         = !empty($orderList) ? $orderList : 'pa.id';
		$direction     = !empty($directionList) ? $directionList : 'ASC';

		if (strpos($order, '.') !== false)
		{
			$tmp   = explode('.', $order);
			$order = $tmp[1];
		}

		if (!is_null($pItemsQuery))
		{
			$query = $db->getQuery(true)->select('*')->from($pItemsQuery->unionDistinct($pQuery), 'res');
		}
		else
		{
			$query = $pQuery;
		}

		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}
}
