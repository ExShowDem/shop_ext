<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Fees Model
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelFees extends RModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_fees';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'fees_limit';

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
				'currency_id',
				'fee_limit',
				'fee_amount',
				'product_id',
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
		$ordering  = is_null($ordering) ? 'f.id' : $ordering;
		$direction = is_null($direction) ? 'DESC' : $direction;

		parent::populateState($ordering, $direction);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db	= $this->getDbo();

		$query = $db->getQuery(true);

		$select = array(
			'f.*',
			$db->qn('p.name', 'productName'),
			$db->qn('c.name', 'currencyName'),
			$db->qn('c.alpha3')
		);

		$query->select(
			$this->getState('list.select', implode(',', $select))
		)
			->from($db->qn('#__redshopb_fee', 'f'))
			->leftJoin($db->qn('#__redshopb_product', 'p') . ' ON ' . $db->qn('p.id') . ' = ' . $db->qn('f.product_id'))
			->leftJoin($db->qn('#__redshopb_currency', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('f.currency_id'));

		// Filter search
		$search = $this->getState('filter.search_fees');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where($db->qn('p.name') . ' LIKE ' . $search);
		}

		// Filter by currency
		$currency = (int) $this->getState('filter.currency_id', 0);

		if ($currency)
		{
			$query->where($db->qn('f.currency_id') . ' = ' . (int) $currency);
		}

		// Filter by product
		$product = (int) $this->getState('filter.product_id', 0);

		if ($product)
		{
			$query->where($db->qn('f.product_id') . ' = ' . (int) $product);
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 'f.id';
		$direction = !empty($directionList) ? $directionList : 'DESC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}
}
