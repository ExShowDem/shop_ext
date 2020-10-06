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
 * Price Debtor Groups Model
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelPrice_Debtor_Groups extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_price_debtor_groups';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'price_debtor_groups_limit';

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
	protected $mainTablePrefix = 'cpg';

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
				'cpg.id', 'cpg.name', 'cpg.code',
				'cpg.state', 'price_debtor_groups_state', 'state',
				'default', 'cpg.default',
				'company', 'company_id',
				'group_company',
				'ignoreacl'
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
	protected function populateState($ordering = 'cpg.id', $direction = 'desc')
	{
		if ($ordering == '')
		{
			$ordering = 'cpg.id';
		}

		parent::populateState($ordering, $direction);
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
			$name = 'customer_price_group';
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
			->leftJoin($db->qn('#__redshopb_customer_price_group_xref', 'cpgx2') . ' ON c.id = cpgx2.customer_id')
			->where('cpgx2.price_group_id = cpg.id');
		$query    = $db->getQuery(true)
			->from($db->qn('#__redshopb_customer_price_group', 'cpg'))
			->leftJoin($db->qn('#__redshopb_customer_price_group_xref', 'cpgx') . ' ON cpgx.price_group_id = cpg.id');

		// Filter search (CRUD)
		$searchcrud = $this->getState('filter.search_price_debtor_groups');

		if (!empty($searchcrud))
		{
			$search = $db->quote('%' . $db->escape($searchcrud, true) . '%');
			$query->leftJoin($db->qn('#__redshopb_company', 'c2') . ' ON c2.id = cpgx.customer_id AND ' . $db->qn('c2.deleted') . ' = 0')
				->where('(cpg.name LIKE ' . $search . ' OR cpg.code LIKE ' . $search . ' OR c2.name LIKE ' . $search . ')');
		}

		// Filter search (ws)
		$searchws = $this->getState('filter.search');

		if (!empty($searchws))
		{
			$search = $db->quote('%' . $db->escape($searchws, true) . '%');
			$query->where('(cpg.name LIKE ' . $search . ' OR cpg.code LIKE ' . $search . ')');
		}

		// Filter by default
		$filter = $this->buildBooleanFilter('cpg.default', 'filter.price_debtor_groups_default', 'filter.default');

		if ($filter)
		{
			$query->where($filter);
		}

		// Filter by state
		$filter = $this->buildBooleanFilter('cpg.state', 'filter.price_debtor_groups_state', 'filter.state');

		if ($filter)
		{
			$query->where($filter);
		}

		$query->select(array('cpg.*', '(' . $subQuery . ') AS companies_names'));

		// Select and join the company
		$query->select('IFNULL(pc.name,' . $db->quote(Text::_('COM_REDSHOPB_MAIN_WAREHOUSE')) . ') AS company')
			->join('left', $db->qn('#__redshopb_company', 'pc') . ' ON pc.id = cpg.company_id AND ' . $db->qn('pc.deleted') . ' = 0');

		// Filter by company
		$filter = $this->buildIntegerFilter('cpg.company_id', 'filter.group_company');

		if ($filter)
		{
			$query->where($filter);
		}

		// ACL checks, if not ignored
		if (!(boolean) $this->getState('filter.ignoreacl', false))
		{
			$aclparts         = array();
			$allowedCompanies = RedshopbHelperACL::listAvailableCompanies($user->id);

			$aclparts[] = $db->qn('cpg.company_id') . ' IN (' . $allowedCompanies . ')';

			if (RedshopbHelperACL::getPermission('manage', 'mainwarehouse'))
			{
				$aclparts[] = $db->qn('cpg.company_id') . ' IS NULL';
			}

			$query->where('(' . implode(' OR ', $aclparts) . ')');
		}

		// Ordering
		$order     = $this->getState('list.ordering', 'cpg.id');
		$direction = $this->getState('list.direction', 'ASC');

		$query->order($db->escape($order) . ' ' . $db->escape($direction))
			->group('cpg.id');

		// Adds related web service data when requested
		$this->getListQueryWS($query);

		return $query;
	}
}
