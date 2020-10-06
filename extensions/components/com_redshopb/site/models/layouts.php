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
 * Orders Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelLayouts extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_layouts';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'layout_limit';

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
				'id', 'l.id',
				'name', 'l.name',
				'created_date', 'l.created_date'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db   = $this->getDbo();
		$user = Factory::getUser();

		$query = $db->getQuery(true)
			->select(
				array(
					'l.*',
					'u1.name AS author',
					'u2.name AS editor',
					'c.id AS company_id',
					'c.name AS company_name',
					'c.name2 AS company_name2',
					'cl.id AS companyl_id',
					'cl.name AS companyl_name',
					'cl.name2 AS companyl_name2'
					)
			)
			->from($db->qn('#__redshopb_layout', 'l'))
			->leftJoin($db->qn('#__users', 'u1') . ' ON u1.id = l.created_by')
			->leftJoin($db->qn('#__users', 'u2') . ' ON u2.id = l.modified_by');

		// Filter search
		$search = $this->getState('filter.search_layouts');

		// Order by
		$orderBy = $this->getState('list.layouts_order');

		switch ($orderBy)
		{
			case 0:
				$orderBy        = "id";
				$orderDirection = "ASC";
				break;
			case 1:
				$orderBy        = "id";
				$orderDirection = "DESC";
				break;
			case 2:
				$orderBy        = "name";
				$orderDirection = "ASC";
				break;
			case 3:
				$orderBy        = "name";
				$orderDirection = "DESC";
				break;
			case 4:
				$orderBy        = "date_created";
				$orderDirection = "ASC";
				break;
			case 5:
				$orderBy        = "date_created";
				$orderDirection = "DESC";
				break;
		}

		// Check for available companies for this user if not a system admin of the app
		$availableCompanies = RedshopbHelperACL::listAvailableCompaniesbyPermission($user->id, 'redshopb.layout.view');
		$query->join('left', $db->qn('#__redshopb_company', 'cl') . ' ON cl.id = l.company_id AND ' . $db->qn('cl.deleted') . ' = 0')
			->join('left', $db->qn('#__redshopb_company', 'c') . ' ON c.layout_id = l.id AND ' . $db->qn('c.deleted') . ' = 0')
			->where(
				'(l.company_id IN (' . $availableCompanies . ')' .
				(RedshopbHelperACL::getPermission('manage', 'mainwarehouse') ? ' OR ' . $db->qn('l.company_id') . ' IS NULL' : '') .
				')'
			);

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('l.name LIKE ' . $search);
		}

		$order     = !empty($orderBy) ? $orderBy : 'id';
		$direction = !empty($orderDirection) ? $orderDirection : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}

	/**
	 * Delete layouts.
	 *
	 * @param   mixed   $pks            Id or array of ids of items to be deleted.
	 * @param   object  $defaultLayout  Layout object.
	 *
	 * @return  boolean True on success, false on failure.
	 */
	public function delete($pks = array(), $defaultLayout = null)
	{
		$companies = RedshopbHelperCompany::getCompaniesByLayoutsIds($pks);

		$table = RTable::getAdminInstance('Layout');

		if (!$table->delete($pks))
		{
			return false;
		}

		if ($defaultLayout != null)
		{
			$db        = $this->getDbo();
			$query     = $db->getQuery(true);
			$pksString = implode(',', $companies);

			$query->update($db->qn('#__redshopb_company'))
				->where('id IN (' . $pksString . ')')
				->set('layout_id = ' . (int) $defaultLayout->id)
				->where($db->qn('deleted') . ' = 0');

			$db->execute($query);
		}

		return true;
	}
}
