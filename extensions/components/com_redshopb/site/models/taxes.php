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
 * Taxes Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelTaxes extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_taxes';

	/**
	 * Limit field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'tax_limit';

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
				'tx.id', 'tx.name',
				'tx.tax_rate', 'tax_rate',
				'tx.state', 'country_name',
				'state_name', 'is_eu_country',
				'tx.is_eu_country'
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
		parent::populateState('tx.name', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	public function getListQuery()
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select('tx.*')
			->select('c.name AS country_name')
			->select('s.name AS state_name')
			->from($db->qn('#__redshopb_tax', 'tx'))
			->leftJoin($db->qn('#__redshopb_country', 'c') . ' ON tx.country_id = c.id')
			->leftJoin($db->qn('#__redshopb_state', 's') . ' ON tx.state_id = s.id');

		// Filter by state
		$state = $this->getState('filter.tax_state', $this->getState('filter.state'));

		if ($state == '0' || $state == 'false')
		{
			$query->where($db->qn('tx.state') . ' = 0');
		}
		elseif ($state == '1' || $state == 'true')
		{
			$query->where($db->qn('tx.state') . ' = 1');
		}

		// Filter search
		$search = $this->getState('filter.search_tax_configurations');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');

			$searchFields = array(
				'tx.name LIKE ' . $search
			);

			$query->where('(' . implode(' OR ', $searchFields) . ')');
		}

		$taxGroupFilter = $this->getState('filter.tax_group');

		if ($taxGroupFilter)
		{
			$query->leftJoin($db->qn('#__redshopb_tax_group_xref', 'tgx') . ' ON tgx.tax_id = tx.id')
				->where('tgx.tax_group_id = ' . (int) $taxGroupFilter);
		}

		$countryIdFilter = $this->getState('filter.country_id');

		if ($countryIdFilter)
		{
			$query->where('tx.country_id = ' . (int) $countryIdFilter);
		}

		$stateIdFilter = $this->getState('filter.state_id');

		if ($stateIdFilter)
		{
			$query->where('(tx.state_id = ' . (int) $stateIdFilter . ' OR tx.state_id IS NULL)');
		}

		$user = Factory::getUser();

		if (!RedshopbHelperACL::isSuperAdmin())
		{
			$availableCompanies = RedshopbHelperACL::listAvailableCompaniesAndParents($user->id);
			$or                 = array($db->qn('tx.company_id') . ' IS NULL');

			if (!empty($availableCompanies))
			{
				$or[] = $db->qn('tx.company_id') . ' IN (' . $availableCompanies . ')';
			}

			$query->where('(' . implode(' OR ', $or) . ')');
			$editAllowedCompanies = RedshopbHelperACL::listAvailableCompanies($user->id);

			if (!empty($editAllowedCompanies))
			{
				$query->select('CASE WHEN tx.company_id IN (' . $editAllowedCompanies . ') THEN 1 ELSE 0 END AS editAllowed');
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
		$order         = !empty($orderList) ? $orderList : 'tx.name';
		$direction     = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}
}
