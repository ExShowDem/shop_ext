<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
jimport('models.trait.productfilters', JPATH_ROOT . '/components/com_redshopb/');

/**
 * Product Complimentary_Products Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelProduct_Complimentary_Products extends RedshopbModelList
{
	use RedshopbModelsTraitProductFilters;

	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_product_complimentary_products';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'product_complimentary_products_limit';

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
				'complimentary_product_id', 'pa.complimentary_product_id',
				'state', 'pa.state',
				'p.name', 'product_name',
				'product_ids'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  Table  A Table object
	 */
	public function getTable($name = '', $prefix = 'RedshopbTable', $options = array())
	{
		$name = (empty($name)) ? 'Product_Complimentary' : $name;

		return parent::getTable($name, $prefix, $options);
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
		$filterProductId = $this->getUserStateFromRequest($this->context . '.product_id', 'product_id');
		$this->setState('filter.product_id', $filterProductId);

		$filterComplimentaryProductId = $this->getUserStateFromRequest($this->context . '.complimentary_product_id', 'complimentary_product_id');
		$this->setState('filter.complimentary_product_id', $filterComplimentaryProductId);

		parent::populateState('pa.id', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		// For get selected complimentary_products
		$getSelected = (boolean) $this->getState('list.getSelected', false);

		if ($getSelected)
		{
			return $this->getSelectedListQuery();
		}

		$db	= $this->getDbo();

		$query = $db->getQuery(true)
			->select('pa.*')
			->from($db->qn('#__redshopb_product_complimentary', 'pa'));

		// Filter by product id
		$productId = $this->getState('filter.product_id');

		if (is_numeric($productId))
		{
			$query->where($db->qn('pa.product_id') . ' = ' . (int) $productId);
		}

		// Filter by complimentary product id
		$complimentaryProductId = $this->getState('filter.complimentary_product_id');

		if (is_numeric($complimentaryProductId))
		{
			$query->where($db->qn('pa.complimentary_product_id') . ' = ' . (int) $complimentaryProductId);
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
		$id .= ':' . $this->getState('filter.complimentary_product_id');
		$id .= ':' . $this->getState('filter.collection_id');
		$id .= ':' . $this->getState('filter.search_product_complimentary_products');

		return parent::getStoreId($id);
	}

	/**
	 * Method for return query to get selected complimentary_products of an product.
	 *
	 * @return  JQuery
	 */
	public function getSelectedListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Filter product Id
		$productId = (int) $this->getState('filter.product_id');

		if (!$productId)
		{
			return $query;
		}

		// Filter search
		$filterSearch = $this->getState('filter.search_product_complimentary_products');
		$query        = $db->getQuery(true)
			->select('pa.*')
			->select($db->qn('p.name', 'product_name'))
			->from($db->qn('#__redshopb_product_complimentary', 'pa'))
			->leftJoin($db->qn('#__redshopb_product', 'p') . ' ON ' . $db->qn('p.id') . ' = ' . $db->qn('pa.complimentary_product_id'))
			->where($db->qn('pa.product_id') . ' = ' . $productId);

		if (!empty($filterSearch))
		{
			$filterSearch = $db->quote('%' . $db->escape($filterSearch, true) . '%');

			$query->where($db->qn('p.name') . ' LIKE ' . $filterSearch);
		}

		$query = $this->applyCommonProductFilters($this, $db, $query);

		$query->group($db->qn('pa.complimentary_product_id'));

		// Filter by multiple product ids
		$productIds = $this->getState('filter.product_ids', null);

		if (!is_null($productIds))
		{
			$query->where($db->qn('pa.product_id') . ' IN (' . implode(',', $db->q($productIds)) . ')');
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 'pa.id';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}
}
