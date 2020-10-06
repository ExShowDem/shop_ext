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
 * Offers Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelOffers extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_offers';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'offer_limit';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitstartField = 'auto';

	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'product';

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
				'id', 'off.id',
				'status', 'off.status',
				'state', 'off.state',
				'customer_type', 'off.customer_type',
				'customer_name', 'collection_name'
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
	protected function populateState($ordering = 'off.id', $direction = 'DESC')
	{
		parent::populateState($ordering, $direction);

		$this->setState('filter.user_id', Factory::getUser()->id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select(
				array(
					'off.*',
					'CASE off.customer_type WHEN ' . $db->q('company') . ' THEN comp.name '
					. 'WHEN ' . $db->q('department') . ' THEN dep.name '
					. 'WHEN ' . $db->q('employee') . ' THEN usr.name1 '
					. 'ELSE NULL END AS customer_name',
					'CASE off.customer_type WHEN ' . $db->q('company') . ' THEN off.company_id '
					. 'WHEN ' . $db->q('department') . ' THEN off.department_id '
					. 'WHEN ' . $db->q('employee') . ' THEN off.user_id '
					. 'ELSE NULL END AS customer_id',
					$db->qn('col.name', 'collection_name')
				)
			)
			->from($db->qn('#__redshopb_offer', 'off'))
			->leftJoin(
				$db->qn('#__redshopb_company', 'comp') .
				' ON comp.id = off.company_id'
			)
			->leftJoin(
				$db->qn('#__redshopb_department', 'dep') .
				' ON dep.id = off.department_id'
			)
			->leftJoin(
				$db->qn('#__redshopb_user', 'usr') .
				' ON usr.id = off.user_id'
			)
			->leftJoin($db->qn('#__redshopb_collection', 'col') . ' ON col.id = off.collection_id');

		// Check for available companies for this user if not a system admin of the app
		if (!RedshopbHelperACL::isSuperAdmin())
		{
			$joomlaUserId   = (int) $this->getState('filter.user_id', 0);
			$userCompanyId  = RedshopbHelperUser::getUserCompanyId($joomlaUserId, 'joomla');
			$companiesCount = RedshopbHelperACL::listAvailableCompanies(
				$joomlaUserId, 'comma', $userCompanyId, '', 'redshopb.company.view', '', true, false, false
			);
			$companiesCount = $companiesCount ? $companiesCount : 0;
			$query->where('off.vendor_id IN (' . $companiesCount . ')');
		}

		// Filter by status
		$status = $this->getState('filter.status', 0);

		if ($status)
		{
			$query->where($db->qn('off.status') . ' = ' . $db->q($status));
		}

		// Filter by status
		$customerType = $this->getState('filter.customer_type', '');

		if ($customerType)
		{
			$query->where('off.customer_type = ' . $db->q($customerType));
		}

		// Filter search
		$search = $this->getState('filter.search_offers');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where(
				'(off.name LIKE ' . $search . ' OR col.name LIKE ' . $search
				. ' OR CASE off.customer_type WHEN ' . $db->q('company') . ' THEN comp.name '
				. 'WHEN ' . $db->q('department') . ' THEN dep.name '
				. 'WHEN ' . $db->q('employee') . ' THEN usr.name1 ELSE FALSE END LIKE ' . $search . ')'
			);
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');
		$order         = !empty($orderList) ? $orderList : 'off.id';
		$direction     = !empty($directionList) ? $directionList : 'DESC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}
}
