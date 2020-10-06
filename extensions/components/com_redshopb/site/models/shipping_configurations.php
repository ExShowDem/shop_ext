<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Shipping configurations Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelShipping_Configurations extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_shipping_configurations';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'shipping_configurations_limit';

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
				'shipping_extension_name', 'sc.shipping_extension_name',
				'owner_name', 'sc.owner_name',
				'shipping_name', 'sc.shipping_name',
				'state', 'id'
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

		$queryDefaults = $db->getQuery(true)
			->select('p.params as plugin_params, p.name as plugin_name, p.element, p.enabled, p.extension_id')
			->select('CONCAT("plg_redshipping_", p.element) as plugin_path_name')
			->from($db->qn('#__extensions', 'p'))
			->where($db->qn('p.type') . '= "plugin"')
			->where($db->qn('p.folder') . ' IN (' . $db->q('redshipping') . ',' . $db->q('system') . ')')
			->select('sc.*')
			->leftJoin($db->qn('#__redshopb_shipping_configuration', 'sc') . ' ON 1 = 2');

		$query = $db->getQuery(true)
			->select('p.params as plugin_params, p.name as plugin_name, p.element, p.enabled, p.extension_id')
			->select('CONCAT("plg_redshipping_", p.element) as plugin_path_name')
			->from($db->qn('#__redshopb_shipping_configuration', 'sc'))
			->where($db->qn('p.type') . '= "plugin"')
			->where($db->qn('p.folder') . ' IN (' . $db->q('redshipping') . ',' . $db->q('system') . ')')
			->select('sc.*')
			->leftJoin($db->qn('#__extensions', 'p') . ' ON sc.shipping_name = p.element');

		// Filter search
		$search = $this->getState('filter.search_shipping_configurations');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('(sc.extension_name LIKE ' . $search . ' OR sc.shipping_name LIKE ' . $search . ' OR sc.owner_name LIKE ' . $search . ' )');
			$queryDefaults->where(
				'(sc.extension_name LIKE ' . $search . ' OR sc.shipping_name LIKE ' . $search . ' OR sc.owner_name LIKE ' . $search . ')'
			);
		}

		$shippingName = $this->getState('filter.shipping_name');

		if ($shippingName)
		{
			$shippingName = $db->quote($shippingName);
			$query->where('sc.shipping_name = ' . $shippingName);
			$queryDefaults->where('p.element = ' . $shippingName);
		}

		$extensionName = $this->getState('filter.shipping_extension_name');

		if ($extensionName)
		{
			$extensionName = $db->quote($extensionName);
			$query->where('sc.extension_name = ' . $extensionName);
			$queryDefaults->where('sc.extension_name = ' . $extensionName);
		}

		$ownerName = $this->getState('filter.owner_name');

		if ($ownerName)
		{
			$ownerName = $db->quote($ownerName);
			$query->where('sc.owner_name = ' . $ownerName);
			$queryDefaults->where('sc.owner_name = ' . $ownerName);
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 'element';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order('extension_name ASC, owner_name ASC, ' . $db->escape($order) . ' ' . $db->escape($direction));

		$query = $queryDefaults . ' UNION ' . $query;

		return $query;
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
	public function populateState($ordering = null, $direction = null)
	{
		$ordering  = is_null($ordering) ? 'element' : $ordering;
		$direction = is_null($direction) ? 'ASC' : $direction;

		parent::populateState($ordering, $direction);
	}
}
