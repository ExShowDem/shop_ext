<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
/**
 * Discount Product Groups Model
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelProduct_Discount_Groups extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_product_discount_groups';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'product_discount_groups_limit';

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
	protected $mainTablePrefix = 'pdg';

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
				'pdg.id', 'pdg.name', 'pdg.code',
				'pdg.state', 'product_discount_groups_state', 'state',
				'company',
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
		parent::populateState('pdg.id', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$user  = Factory::getUser();
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->from($db->qn('#__redshopb_product_discount_group', 'pdg'))
			->select('pdg.*')
			->leftJoin($db->qn('#__redshopb_product_discount_group_xref', 'pdgx') . ' ON pdgx.discount_group_id = pdg.id');

		if ($this->getState('select.product_list', true))
		{
			$subQuery  = $db->getQuery(true)
				->select('GROUP_CONCAT(p.name SEPARATOR ' . $db->q(', ') . ')')
				->from($db->qn('#__redshopb_product', 'p'))
				->leftJoin($db->qn('#__redshopb_product_discount_group_xref', 'pdgx2') . ' ON p.id = pdgx2.product_id')
				->where('pdgx2.discount_group_id = pdg.id');
			$subQuery2 = $db->getQuery(true)
				->select('GROUP_CONCAT(pi.sku SEPARATOR ' . $db->q(', ') . ')')
				->from($db->qn('#__redshopb_product_item', 'pi'))
				->leftJoin($db->qn('#__redshopb_product_item_discount_group_xref', 'pdigx') . ' ON pi.id = pdigx.product_item_id')
				->where('pdigx.discount_group_id = pdg.id');
			$query->select('CONCAT_WS(' . $db->q(',') . ', (' . $subQuery . '), (' . $subQuery2 . ')) AS products_names');
		}

		// Select and join the company
		$query->select('IFNULL(pc.name,' . $db->quote(Text::_('COM_REDSHOPB_MAIN_WAREHOUSE')) . ') AS company')
			->join('left', $db->qn('#__redshopb_company', 'pc') . ' ON pc.id = pdg.company_id AND ' . $db->qn('pc.deleted') . ' = 0');

		// Filter by company
		$company = $this->getState('filter.group_company', $this->getState('filter.company_id', ''));

		if (is_numeric($company) && $company > 0)
		{
			$query->where('pdg.company_id = ' . (int) $company);
		}
		elseif ($company == 'null')
		{
			$query->where('pdg.company_id IS NULL');
		}

		// ACL checks
		$aclparts         = array();
		$allowedCompanies = RedshopbHelperACL::listAvailableCompanies($user->id);

		$aclparts[] = $db->qn('pdg.company_id') . ' IN (' . $allowedCompanies . ')';

		if (RedshopbHelperACL::getPermission('manage', 'mainwarehouse'))
		{
			$aclparts[] = $db->qn('pdg.company_id') . ' IS NULL';
		}

		$query->where('(' . implode(' OR ', $aclparts) . ')');

		// Filter search (CRUD)
		$searchcrud = $this->getState('filter.search_product_discount_groups');

		if (!empty($searchcrud))
		{
			$search = $db->quote('%' . $db->escape($searchcrud, true) . '%');
			$query->leftJoin($db->qn('#__redshopb_product', 'p') . ' ON p.id = pdgx.product_id')
				->where('(pdg.name LIKE ' . $search . ' OR pdg.code LIKE ' . $search . ' OR p.name LIKE ' . $search . ')');
		}

		// Filter search (ws)
		$searchws = $this->getState('filter.search');

		if (!empty($searchws))
		{
			$search = $db->quote('%' . $db->escape($searchws, true) . '%');
			$query->where('(pdg.name LIKE ' . $search . ' OR pdg.code LIKE ' . $search . ')');
		}

		// Filter by state
		$state = $this->getState('filter.product_discount_groups_state', $this->getState('filter.state'));

		if ($state == '0' || $state == 'false')
		{
			$query->where($db->qn('pdg.state') . ' = 0');
		}
		elseif ($state == '1' || $state == 'true')
		{
			$query->where($db->qn('pdg.state') . ' = 1');
		}

		// Filter by product
		$product = $this->getState('filter.product_id');

		if (is_array($product) && !empty($product))
		{
			$product = ArrayHelper::toInteger($product);

			$query->where('pdgx.product_id IN (' . $product . ')');
		}
		elseif (is_numeric($product))
		{
			$query->where('pdgx.product_id = ' . (int) $product);
		}

		// Ordering
		$order     = $this->getState('list.ordering', 'pdg.id');
		$direction = $this->getState('list.direction', 'ASC');

		$query->order($db->escape($order) . ' ' . $db->escape($direction))
			->group('pdg.id');

		// Adds related web service data when requested
		$this->getListQueryWS($query);

		return $query;
	}
}
