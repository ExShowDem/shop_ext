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
 * Product Compositions Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelProduct_Compositions extends RModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_product_compositions';

	/**
	 * Limit field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'product_compositions_limit';

	/**
	 * Limit start field used by the pagination
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
				'id', 'pc.id',
				'product_id', 'pc.product_id',
				'flat_attribute_value_id', 'pc.flat_attribute_value_id',
				'type', 'pc.type',
				'quality', 'pc.quality',
				'product_attribute_value_name',
				'product_name'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select(
			array(
				$db->qn('pc') . '.*',
				$db->qn('p.name', 'product_name'),
				$db->qn('pav.string_value', 'product_attribute_value_name')
			)
		)
			->from($db->qn('#__redshopb_product_composition', 'pc'))
			->innerJoin($db->qn('#__redshopb_product', 'p') . ' ON ' . $db->qn('p.id') . ' = ' . $db->qn('pc.product_id'))
			->leftJoin(
				$db->qn('#__redshopb_product_attribute_value', 'pav') . ' ON ' . $db->qn('pav.id') . ' = ' . $db->qn('pc.flat_attribute_value_id')
			);

		// Filter by search
		$search = $this->getState('filter.search_product_compositions');

		// Filter by product
		$productId = $this->getState('list.product', '');

		if (!is_numeric($productId))
		{
			$productId = $this->getState('filter.product_id', '');
		}

		if (is_numeric($productId))
		{
			$query->where('pc.product_id = ' . (int) $productId);
		}

		if (!empty($search))
		{
			$floatValue = (float) $search;
			$intValue   = (int) $search;
			$search     = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('((pav.string_value LIKE ' . $search . ') OR (pav.float_value = ' . $floatValue . ')' .
				' OR (pav.int_value = ' . $intValue . ') OR (p.name LIKE ' . $search . ')' .
				' OR (pc.type LIKE ' . $search . ') OR (pc.quality LIKE ' . $search . '))'
			);
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 'pc.id';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}
}
