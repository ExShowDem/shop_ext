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
 * States Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelStates extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_states';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'state_limit';

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
				'id', 's.id',
				'name', 's.name',
				'alpha3', 's.alpha3',
				'alpha2', 's.alpha2',
				'country_name'
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
		parent::populateState('s.alpha3', 'asc');
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
					's.*',
					'c.name as country_name'
				)
			)
			->from($db->qn('#__redshopb_state', 's'))
			->leftJoin($db->qn('#__redshopb_country', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('s.country_id'));

		// Filter search
		$search = $this->getState('filter.search_states');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');

			$searchFields = array(
				's.name LIKE ' . $search,
				's.alpha3 LIKE ' . $search,
				's.alpha2 LIKE ' . $search,
				'c.name LIKE ' . $search
			);

			$query->where('(' . implode(' OR ', $searchFields) . ')');
		}

		$countryFilter = $this->getState('filter.country_id', null);

		if (is_numeric($countryFilter))
		{
			$query->where('c.id = ' . $db->q($countryFilter));
		}

		$user = Factory::getUser();

		if (!RedshopbHelperACL::isSuperAdmin())
		{
			$availableCompanies = RedshopbHelperACL::listAvailableCompaniesAndParents($user->id);
			$or                 = array($db->qn('s.company_id') . ' IS NULL');

			if (!empty($availableCompanies))
			{
				$or[] = $db->qn('s.company_id') . ' IN (' . $availableCompanies . ')';
			}

			$query->where('(' . implode(' OR ', $or) . ')');
			$editAllowedCompanies = RedshopbHelperACL::listAvailableCompanies($user->id);

			if (!empty($editAllowedCompanies))
			{
				$query->select('CASE WHEN s.company_id IN (' . $editAllowedCompanies . ') THEN 1 ELSE 0 END AS editAllowed');
			}
			else
			{
				$query->select('0 AS editAllowed');
			}
		}
		else
		{
			$query->select('1 AS editAllowed');
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 's.alpha3';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}
}
