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
 * Wash and care specs Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelWash_Care_Specs extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_wash_care_specs';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'wash_care_specs_limit';

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
				'id', 'wcs.id',
				'code', 'wcs.code',
				'type_code', 'wcs.type_code',
				'description', 'wcs.description',
				'pwcsx.product_id', 'product_id'
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
		$db	= $this->getDbo();

		$query = $db->getQuery(true)
			->select($db->qn('wcs') . '.*')
			->from($db->qn('#__redshopb_wash_care_spec', 'wcs'));

		// Filter search
		$search = $this->getState('filter.search_wash_care_specs');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('(wcs.description LIKE ' . $search . ' OR wcs.type_code LIKE ' . $search . ' OR wcs.code LIKE ' . $search . ')');
		}

		// Filter by product
		$productId = $this->getState('list.product', '');

		if (!is_numeric($productId))
		{
			$productId = $this->getState('filter.product_id', '');
		}

		if (is_numeric($productId))
		{
			$query->innerJoin($db->qn('#__redshopb_product_wash_care_spec_xref', 'pwcsx') . ' ON pwcsx.wash_care_spec_id = wcs.id');
			$query->where('pwcsx.product_id = ' . (int) $productId);
		}

		// Filter by state
		$state = $this->getState('filter.wash_care_spec_state', '');

		if (is_numeric($state))
		{
			$query->where('wcs.state = ' . (int) $state);
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 'wcs.id';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}
}
