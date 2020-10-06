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
 * Currency Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.6
 */
class RedshopbModelShipping_Routes extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_shipping_routes';

	/**
	 * Limit field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'shipping_routes_limit';

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
				'sr.name', 'name',
				'sr.id', 'id'
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
		$ordering  = is_null($ordering) ? 'sr.name' : $ordering;
		$direction = is_null($direction) ? 'ASC' : $direction;

		parent::populateState($ordering, $direction);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	public function getListQuery()
	{
		$user  = Factory::getUser();
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('sr.*')
			->select($db->qn('c.name', 'country_name'))
			->select($db->qn('co.name', 'company_name'))
			->select('GROUP_CONCAT(TRIM(CONCAT_WS(\' \', a.address, a.address2, a.zip, a.city)) SEPARATOR ' . $db->q(',') . ') AS address_names')
			->select('GROUP_CONCAT(a.id SEPARATOR ' . $db->q(',') . ') AS addresses')
			->from($db->qn('#__redshopb_shipping_route', 'sr'))
			->leftJoin($db->qn('#__redshopb_shipping_route_address_xref', 'sra') . ' ON sra.shipping_route_id = sr.id')
			->leftJoin($db->qn('#__redshopb_address', 'a') . ' ON a.id = sra.address_id')
			->leftJoin($db->qn('#__redshopb_country', 'c') . ' ON c.id = a.country_id')
			->leftJoin($db->qn('#__redshopb_company', 'co') . ' ON co.id = sr.company_id')
			->group($db->qn('sr.id'));

		// Filter search
		$search = $this->getState('filter.search_shipping_routes', $this->getState('filter.search'));

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');

			$searchFields = array(
				'sr.name LIKE ' . $search,
			);

			$query->where('(' . implode(' OR ', $searchFields) . ')');
		}

		// Filter by company
		if (!RedshopbHelperACL::isSuperAdmin())
		{
			$allowedCompanies = RedshopbHelperACL::listAvailableCompanies($user->id);
			$query->where($db->qn('sr.company_id') . ' IN (' . $allowedCompanies . ')');
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');
		$order         = !empty($orderList) ? $orderList : 'sr.name';
		$direction     = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}

	/**
	 * Method to get an array of data items. Overriden to add static cache support.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.7
	 */
	public function getItems()
	{
		$items = parent::getItems();

		foreach ($items as $key => $value)
		{
			$items[$key]->address_ids = explode(',', $value->address_ids);
		}

		return $items;
	}

	/**
	 * Method to get a form object.
	 *
	 * @param   string   $name     The name of the form.
	 * @param   string   $source   The form source. Can be XML string if file flag is set to false.
	 * @param   array    $options  Optional array of options for the form creation.
	 * @param   boolean  $clear    Optional argument to force load a new form.
	 * @param   mixed    $xpath    An optional xpath to search for the fields.
	 *
	 * @return  mixed  JForm object on success, False on error.
	 *
	 * @see     JForm
	 */
	protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
	{
		RedshopbForm::addFormPath(JPATH_PLUGINS . '/system/redshopb_self_shipping/extensions/components/com_redshopb/site/models/forms');

		return parent::loadForm($name, $source, $options, $clear, $xpath);
	}
}
