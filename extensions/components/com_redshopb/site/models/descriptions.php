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
class RedshopbModelDescriptions extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_descriptions';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'description_limit';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitstartField = 'auto';

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
				'pd.id', 'value', 'product_id', 'main_attribute_value_id'
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
	protected function getListQuery()
	{
		$db	= $this->getDbo();

		$query = $db->getQuery(true)
			->select(
				array(
					'pd.*',
					'p.sku',
					'(CASE WHEN pa.type_id = ' . $db->q('1') . ' THEN pav.string_value WHEN pa.type_id = ' . $db->q('2')
						. ' THEN pav.float_value WHEN pa.type_id = ' . $db->q('3') . ' THEN pav.int_value END) AS value'
				)
			)
			->from($db->qn('#__redshopb_product_descriptions', 'pd'))
			->innerJoin($db->qn('#__redshopb_product', 'p') . ' ON pd.product_id = p.id')
			->leftJoin($db->qn('#__redshopb_product_attribute_value', 'pav') . ' ON pav.id = pd.main_attribute_value_id')
			->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pa.id = pav.product_attribute_id');

		// Filter by product
		$productId = $this->getState('list.product', $this->getState('filter.product_id'));

		if (is_numeric($productId))
		{
			$query->where('pd.product_id = ' . (int) $productId);
		}

		$mainAttrId = $this->getState('filter.main_attribute_value_id');

		if (is_numeric($mainAttrId))
		{
			$query->where('pd.main_attribute_value_id = ' . (int) $mainAttrId);
		}

		// Filter search
		$search = $this->getState('filter.search_product_descriptions', $this->getState('filter.search'));

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('(pd.description LIKE ' . $search . ')');
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 'pd.id';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}
}
