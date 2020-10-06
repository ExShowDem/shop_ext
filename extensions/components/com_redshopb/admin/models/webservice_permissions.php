<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Webservice Permissions Model
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelWebservice_Permissions extends RModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_webservice_permissions';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'webservice_permissions_limit';

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
				'manual',
				'permission_state',
				'search_webservice_permissions',
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

		$query->select('wp.*')
			->from($db->qn('#__redshopb_webservice_permission', 'wp'));

		// Filter search
		$search = $this->getState('filter.search_webservice_permissions');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where($db->qn('wp.name') . ' LIKE ' . $search);
		}

		// Filter by webservice permission manual option
		$manual = $this->getState('filter.manual', null);

		if (!is_null($manual))
		{
			$query->where($db->qn('wp.manual') . ' = ' . (int) $manual);
		}

		// Filter by webservice permission state
		$state = $this->getState('filter.permission_state', null);

		if (!is_null($state))
		{
			$query->where($db->qn('wp.state') . ' = ' . (int) $state);
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 'wp.name';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}
}
