<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
/**
 * Discount Debtor Groups Model
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelDiscount_Debtor_Groups extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_discount_debtor_groups';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'discount_debtor_groups_limit';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitstartField = 'auto';

	/**
	 * Main table query prefix
	 *
	 * @var  array
	 */
	protected $mainTablePrefix = 'cdg';

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
				'cdg.id', 'cdg.name', 'cdg.code',
				'cdg.state', 'discount_debtor_groups_state', 'state',
				'company', 'company_id',
				'group_company'
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
		parent::populateState('cdg.id', 'asc');
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  Table  A Table object
	 *
	 * @since   12.2
	 * @throws  Exception
	 */
	public function getTable($name = '', $prefix = 'RedshopbTable', $options = array())
	{
		if (empty($name))
		{
			$name = 'customer_discount_group';
		}

		return parent::getTable($name, $prefix, $options);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$user = Factory::getUser();
		$db   = Factory::getDbo();

		$subQuery = $db->getQuery(true)
			->select('GROUP_CONCAT(c.name SEPARATOR ' . $db->q(', ') . ')')
			->from($db->qn('#__redshopb_company', 'c'))
			->where($db->qn('c.deleted') . ' = 0')
			->leftJoin($db->qn('#__redshopb_customer_discount_group_xref', 'cdgx2') . ' ON c.id = cdgx2.customer_id')
			->where('cdgx2.discount_group_id = cdg.id');
		$query    = $db->getQuery(true)
			->from($db->qn('#__redshopb_customer_discount_group', 'cdg'))
			->leftJoin($db->qn('#__redshopb_customer_discount_group_xref', 'cdgx') . ' ON cdgx.discount_group_id = cdg.id');

		// Filter search (CRUD)
		$searchcrud = $this->getState('filter.search_discount_debtor_groups');

		if (!empty($searchcrud))
		{
			$search = $db->quote('%' . $db->escape($searchcrud, true) . '%');
			$query->leftJoin($db->qn('#__redshopb_company', 'c2') . ' ON c2.id = cdgx.customer_id AND ' . $db->qn('c2.deleted') . ' = 0')
				->where('(cdg.name LIKE ' . $search . ' OR cdg.code LIKE ' . $search . ' OR c2.name LIKE ' . $search . ')');
		}

		// Filter search (ws)
		$searchws = $this->getState('filter.search');

		if (!empty($searchws))
		{
			$search = $db->quote('%' . $db->escape($searchws, true) . '%');
			$query->where('(cdg.name LIKE ' . $search . ' OR cdg.code LIKE ' . $search . ')');
		}

		// Filter by state
		$state = $this->getState('filter.discount_debtor_groups_state', $this->getState('filter.state'));

		if ($state == '0' || $state == 'false')
		{
			$query->where($db->qn('cdg.state') . ' = 0');
		}
		elseif ($state == '1' || $state == 'true')
		{
			$query->where($db->qn('cdg.state') . ' = 1');
		}

		$query->select(array('cdg.*', '(' . $subQuery . ') AS companies_names'));

		// Select and join the company
		$query->select('IFNULL(pc.name,' . $db->quote(Text::_('COM_REDSHOPB_MAIN_WAREHOUSE')) . ') AS company')
			->join('left', $db->qn('#__redshopb_company', 'pc') . ' ON pc.id = cdg.company_id AND ' . $db->qn('pc.deleted') . ' = 0');

		// Filter by company
		$company = $this->getState('filter.group_company', $this->getState('filter.company_id', ''));

		if (is_numeric($company) && $company > 0)
		{
			$query->where('cdg.company_id = ' . (int) $company);
		}
		elseif ($company == 'null')
		{
			$query->where('cdg.company_id IS NULL');
		}

		// ACL checks
		$aclparts         = array();
		$allowedCompanies = RedshopbHelperACL::listAvailableCompanies($user->id);

		$aclparts[] = $db->qn('cdg.company_id') . ' IN (' . $allowedCompanies . ')';

		if (RedshopbHelperACL::getPermission('manage', 'mainwarehouse'))
		{
			$aclparts[] = $db->qn('cdg.company_id') . ' IS NULL';
		}

		$query->where('(' . implode(' OR ', $aclparts) . ')');

		// Ordering
		$order     = $this->getState('list.ordering', 'cdg.id');
		$direction = $this->getState('list.direction', 'ASC');

		$query->order($db->escape($order) . ' ' . $db->escape($direction))
			->group('cdg.id');

		// Adds related web service data when requested
		$this->getListQueryWS($query);

		return $query;
	}
}
