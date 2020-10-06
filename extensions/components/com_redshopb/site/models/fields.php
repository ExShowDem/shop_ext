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
 * Currency Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.6
 */
class RedshopbModelFields extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_fields';

	/**
	 * Limit field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'fields_limit';

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
				'type_code',
				'filter_type_code',
				'field_type_name',
				'values_field_id',
				'previous_id',
				'scope',
				'id',
				// Ordering
				'f.id',
				'f.name',
				'f.alias',
				'f.ordering',
				'fg.name'
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
		parent::populateState('f.ordering', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	public function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select(
			array(
					'f.*',
					$db->qn('t.name', 'field_type_name'),
					$db->qn('t.alias', 'type_code'),
					$db->qn('ft.alias', 'filter_type_code'),
					$db->qn('vf.id', 'values_field_id'),
					$db->qn('fg.name', 'field_group_name')
				)
		)
			->from($db->qn('#__redshopb_field', 'f'))
			->join('inner', $db->qn('#__redshopb_type', 't') . ' ON ' . $db->qn('f.type_id') . ' = ' . $db->qn('t.id'))
			->join('left', $db->qn('#__redshopb_type', 'ft') . ' ON ' . $db->qn('f.filter_type_id') . ' = ' . $db->qn('ft.id'))
			->join('left', $db->qn('#__redshopb_field', 'vf') . ' ON ' . $db->qn('f.field_value_xref_id') . ' = ' . $db->qn('vf.id'))
			->join('left', $db->qn('#__redshopb_field_group', 'fg') . ' ON ' . $db->qn('f.field_group_id') . ' = ' . $db->qn('fg.id'));

		$filterScope = $this->getState('filter.scope', null);

		if (!is_null($filterScope) && $filterScope != '')
		{
			$query->where($db->qn('f.scope') . ' = ' . $db->q($filterScope));
		}

		$filterFieldGroup = $this->getState('filter.field_group_id', null);

		if (!is_null($filterFieldGroup) && $filterFieldGroup != '')
		{
			$query->where($db->qn('f.field_group_id') . ' = ' . $db->q($filterFieldGroup));
		}

		$filterFieldTypeName = $this->getState('filter.field_type_name', null);

		if (!is_null($filterFieldTypeName) && $filterFieldTypeName != '')
		{
			$query->where($db->qn('t.name') . ' = ' . $db->q($filterFieldTypeName));
		}

		$filterTypeCode = $this->getState('filter.type_code', null);

		if (!is_null($filterTypeCode) && $filterTypeCode != '')
		{
			$query->where($db->qn('t.alias') . ' = ' . $db->q($filterTypeCode));
		}

		$filterFilterTypeCode = $this->getState('filter.filter_type_code', null);

		if (!is_null($filterFilterTypeCode) && $filterFilterTypeCode != '')
		{
			$query->where($db->qn('ft.alias') . ' = ' . $db->q($filterFilterTypeCode));
		}

		$filterValuesFieldId = $this->getState('filter.values_field_id', null);

		if (!is_null($filterValuesFieldId) && is_numeric($filterValuesFieldId) && $filterValuesFieldId > 0)
		{
			$query->where($db->qn('vf.id') . ' = ' . $db->q($filterValuesFieldId));
		}

		// Filter by multiple values
		$filterMultipleValues = $this->getState('filter.multiple_values');

		if ($filterMultipleValues == '0' || $filterMultipleValues == 'false')
		{
			$query->where($db->qn('f.multiple_values') . ' = 0');
		}
		elseif ($filterMultipleValues == '1' || $filterMultipleValues == 'true')
		{
			$query->where($db->qn('f.multiple_values') . ' = 1');
		}

		// Filter by searchable frontend
		$filterSearchableFrontend = $this->getState('filter.searchable_frontend');

		if ($filterSearchableFrontend == '0' || $filterSearchableFrontend == 'false')
		{
			$query->where($db->qn('f.searchable_frontend') . ' = 0');
		}
		elseif ($filterSearchableFrontend == '1' || $filterSearchableFrontend == 'true')
		{
			$query->where($db->qn('f.searchable_frontend') . ' = 1');
		}

		// Filter by searchable backend
		$filterSearchableBackend = $this->getState('filter.searchable_backend');

		if ($filterSearchableBackend == '0' || $filterSearchableBackend == 'false')
		{
			$query->where($db->qn('f.searchable_backend') . ' = 0');
		}
		elseif ($filterSearchableBackend == '1' || $filterSearchableBackend == 'true')
		{
			$query->where($db->qn('f.searchable_backend') . ' = 1');
		}

		// Filter by global
		$filterGlobal = $this->getState('filter.global');

		if ($filterGlobal == '0' || $filterGlobal == 'false')
		{
			$query->where($db->qn('f.global') . ' = 0');
		}
		elseif ($filterGlobal == '1' || $filterGlobal == 'true')
		{
			$query->where($db->qn('f.global') . ' = 1');
		}

		// Filter by state
		$state = $this->getState('filter.field_state', $this->getState('filter.state'));

		if ($state == '0' || $state == 'false')
		{
			$query->where($db->qn('f.state') . ' = 0');
		}
		elseif ($state == '1' || $state == 'true')
		{
			$query->where($db->qn('f.state') . ' = 1');
		}

		// Filter above some field id
		$previousId = $this->getState('filter.previous_id', null);

		if (!is_null($previousId))
		{
			$query->where($db->qn('f.id') . ' > ' . (int) $previousId);
		}

		// Filter search
		$search = $this->getState('filter.search_fields', $this->getState('filter.search'));

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');

			$searchFields = array(
				'f.name LIKE ' . $search,
				'f.title LIKE ' . $search,
				'f.description LIKE ' . $search,
			);

			$query->where('(' . implode(' OR ', $searchFields) . ')');
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');
		$order         = !empty($orderList) ? $orderList : 'f.ordering';
		$direction     = !empty($directionList) ? $directionList : 'ASC';

		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		// Adds related web service data when requested
		$this->getListQueryWS($query);

		return $query;
	}

	/**
	 * Method to get an array of data items prepared for the web service - including the external keys from sync
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getItemsWS()
	{
		$this->getState();

		// Field web service is restricted to products (because of API scope)
		$this->setState('filter.scope', 'product');

		return parent::getItemsWS();
	}
}
