<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * Currency Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.6
 */
class RedshopbModelCountries extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_countries';

	/**
	 * Limit field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'country_limit';

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
				// Ordering
				'c.id',
				'c.name',
				'c.alpha2',
				'c.alpha3',
				'c.eu_zone',
				'code'
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
		parent::populateState('c.alpha2', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	public function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select(
				array(
					'c.*',
					$db->qn('c.alpha2', 'code')
				)
			)
			->from($db->qn('#__redshopb_country', 'c'));

		// Filter search
		$search = $this->getState('filter.search_countries', $this->getState('filter.search'));

		if (!empty($search))
		{
			$translated = RedshopbEntityCountry::getTranslatedList();
			$matches    = array();

			foreach ($translated as $id => $item)
			{
				if (stripos($item, $search) !== false)
				{
					$matches[] = $id;
				}
			}

			$search = $db->quote('%' . $db->escape($search, true) . '%');

			$searchFields = array(
				'c.name LIKE ' . $search,
				'c.alpha2 LIKE ' . $search,
				'c.alpha3 LIKE ' . $search
			);

			if (!empty($matches))
			{
				$searchFields[] = 'c.id IN (' . implode(',', $matches) . ')';
			}

			$query->where('(' . implode(' OR ', $searchFields) . ')');
		}

		$user = Factory::getUser();

		if (!RedshopbHelperACL::isSuperAdmin())
		{
			$availableCompanies = RedshopbHelperACL::listAvailableCompaniesAndParents($user->id);
			$or                 = array($db->qn('c.company_id') . ' IS NULL');

			if (!empty($availableCompanies))
			{
				$or[] = $db->qn('c.company_id') . ' IN (' . $availableCompanies . ')';
			}

			$query->where('(' . implode(' OR ', $or) . ')');
			$editAllowedCompanies = RedshopbHelperACL::listAvailableCompanies($user->id);

			if (!empty($editAllowedCompanies))
			{
				$query->select('CASE WHEN c.company_id IN (' . $editAllowedCompanies . ') THEN 1 ELSE 0 END AS editAllowed');
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
		$order         = !empty($orderList) ? $orderList : 'c.alpha2';
		$direction     = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.12.72
	 */
	public function getItems()
	{
		$items = parent::getItems();

		foreach ($items as $item)
		{
			$item->name = Text::_($item->name);
		}

		return $items;
	}
}
