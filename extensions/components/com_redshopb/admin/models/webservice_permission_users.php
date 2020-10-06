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

/**
 * Webservice Permissions Model
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelWebservice_Permission_Users extends RModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_webservice_permission_users';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'webservice_permission_users_limit';

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
				'name',
				'webservice_permission_id',
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
		$db	= $this->getDbo();

		$query = $db->getQuery(true);

		$subQuery = $db->getQuery(true)
			->select('GROUP_CONCAT(wpu2.webservice_permission_id)')
			->from($db->qn('#__redshopb_webservice_permission_user_xref', 'wpu2'))
			->where($db->qn('wpu2.user_id') . ' = ' . $db->qn('wpu.user_id'));

		$query->select('u.*, wpu.user_id')
			->select('(' . $subQuery . ') AS webservice_permissions')
			->from($db->qn('#__redshopb_webservice_permission_user_xref', 'wpu'))
			->leftJoin($db->qn('#__users', 'u') . ' ON ' . $db->qn('u.id') . ' = ' . $db->qn('wpu.user_id'))
			->leftJoin(
				$db->qn('#__redshopb_webservice_permission', 'wp') . ' ON ' . $db->qn('wp.id') . ' = ' . $db->qn('wpu.webservice_permission_id')
			)
			->group('wpu.user_id');

		// Filter search
		$search = $this->getState('filter.search_webservice_permissions');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where($db->qn('wp.name') . ' LIKE ' . $search . ' OR ' . $db->qn('u.name') . ' LIKE ' . $search);
		}

		// Filter by webservice permission
		$permission = (int) $this->getState('filter.webservice_permission', 0);

		if ($permission)
		{
			$query->where($db->qn('wpu.webservice_permission_id') . ' = ' . (int) $permission);
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 'wp.name';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function getItems()
	{
		$items = parent::getItems();

		if ($items)
		{
			foreach ($items as $key => $item)
			{
				$item->webservice_permissions = $item->webservice_permissions ? explode(',', $item->webservice_permissions) : array();
			}
		}

		return $items;
	}

	/**
	 * Delete items
	 *
	 * @param   mixed  $pks  id or array of ids of items to be deleted
	 *
	 * @return  boolean
	 */
	public function delete($pks = null)
	{
		$pks = ArrayHelper::toInteger($pks);

		$db    = $this->_db;
		$query = $db->getQuery(true)
			->delete('#__redshopb_webservice_permission_user_xref')
			->where('user_id IN (' . implode(',', $pks) . ')');

		$db->setQuery($query);

		if (!$db->execute())
		{
			return false;
		}

		return true;
	}
}
