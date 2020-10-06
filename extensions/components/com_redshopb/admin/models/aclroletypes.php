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
 * ACL Role Types Model
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 * @since       1.6.107
 */
class RedshopbModelACLRoleTypes extends RModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = null;

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = null;

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
		$ordering  = is_null($ordering) ? 'rt.id' : $ordering;
		$direction = is_null($direction) ? 'ASC' : $direction;

		parent::populateState($ordering, $direction);
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

		$query->select('rt.*')
			->from($db->qn('#__redshopb_role_type', 'rt'))
			->where($db->qn('company_role') . ' = 0');

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 'rt.id';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}

	/**
	 * Method for get all acl access
	 *
	 * @return  array  List of all ACL Access
	 */
	public function getAllAccess()
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select($db->qn('a.id'))
			->select($db->qn('a.name'))
			->select($db->qn('a.title'))
			->select($db->qn('a.description'))
			->select($db->qn('s.name', 'section'))
			->from($db->qn('#__redshopb_acl_access', 'a'))
			->leftJoin($db->qn('#__redshopb_acl_section', 's') . ' ON ' . $db->qn('s.id') . ' = ' . $db->qn('a.section_id'))
			->order($db->qn('s.id') . ' ASC,' . $db->qn('a.name') . ' ASC');

		return $db->setQuery($query)->loadObjectList();
	}
}
