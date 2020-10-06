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
 * Tax_Groups Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelTax_Groups extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_tax_groups';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'tax_group_limit';

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
				'id', 'tg.id',
				'name', 'tg.name'
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
		parent::populateState('tg.name', 'asc');
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
					'tg.*'
				)
			)
			->from($db->qn('#__redshopb_tax_group', 'tg'));

		// Filter search
		$search = $this->getState('filter.search_tax_groups');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');

			$searchFields = array(
				'tg.name LIKE ' . $search
			);

			$query->where('(' . implode(' OR ', $searchFields) . ')');
		}

		$user = Factory::getUser();

		if (!RedshopbHelperACL::isSuperAdmin())
		{
			$availableCompanies = RedshopbHelperACL::listAvailableCompaniesAndParents($user->id);
			$or                 = array($db->qn('tg.company_id') . ' IS NULL');

			if (!empty($availableCompanies))
			{
				$or[] = $db->qn('tg.company_id') . ' IN (' . $availableCompanies . ')';
			}

			$query->where('(' . implode(' OR ', $or) . ')');
			$editAllowedCompanies = RedshopbHelperACL::listAvailableCompanies($user->id);

			if (!empty($editAllowedCompanies))
			{
				$query->select('CASE WHEN tg.company_id IN (' . $editAllowedCompanies . ') THEN 1 ELSE 0 END AS editAllowed');
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

		$order     = !empty($orderList) ? $orderList : 'tg.name';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}
}
