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
 * User multi company Xrefs Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelUser_Multi_Companies extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_user_multi_companies';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'user_multi_companies_limit';

	/**
	 * Main table query prefix
	 *
	 * @var  array
	 */
	protected $mainTablePrefix = 'umc';

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
				'id',
				'user_id',
				'company_id',
				'role_id',
				'state',
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
		parent::populateState('umc.user_id', 'asc');
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
			->select('umc.*, c.name AS company_name, u.name1 AS user_name, r.name AS role_name')
			->from($db->qn('#__redshopb_user_multi_company', 'umc'))
			->leftJoin($db->qn('#__redshopb_company', 'c') . ' ON c.id = umc.company_id')
			->leftJoin($db->qn('#__redshopb_user', 'u') . ' ON u.id = umc.user_id')
			->leftJoin($db->qn('#__redshopb_role_type', 'r') . ' ON r.id = umc.role_id');

		$userId = $this->getState('filter.user_id');

		if (is_numeric($userId))
		{
			$query->where('umc.user_id = ' . (int) $userId);
		}

		$companyId = $this->getState('filter.company_id');

		if (is_numeric($companyId))
		{
			$query->where('umc.company_id = ' . (int) $companyId);
		}

		$roleId = $this->getState('filter.role_id');

		if (is_numeric($roleId))
		{
			$query->where('umc.role_id = ' . (int) $roleId);
		}

		$state = $this->getState('filter.state');

		if (is_numeric($state))
		{
			$query->where('umc.state = ' . (int) $state);
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 'umc.user_id';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		// Adds related web service data when requested
		$this->getListQueryWS($query);

		return $query;
	}
}
