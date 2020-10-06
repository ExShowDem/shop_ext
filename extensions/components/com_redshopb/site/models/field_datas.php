<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\CMS\Table\Table;
/**
 * Field Datas Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelField_Datas extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_field_datas';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'field_data_limit';

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
	protected $mainTablePrefix = 'fd';

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
				'id',
				'item_id',
				'field_id', 'field_ids',
				'product_id', 'product_id_array', 'product_ids',
				'previous_id',
				'field_scope',
				'value',
				'display_params'
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
		parent::populateState('fd.id', 'asc');
	}

	/**
	 * Get the associated Table
	 *
	 * @param   string  $name    Table name
	 * @param   string  $prefix  Table prefix
	 * @param   array   $config  Configuration array
	 *
	 * @return  Table
	 */
	public function getTable($name = 'Field_Data', $prefix = 'RedshopbTable', $config = array())
	{
		if ($name == '')
		{
			$name = 'Field_Data';
		}

		return parent::getTable($name, $prefix, $config);
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   12.2
	 */
	public function getListQuery()
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select('fd.*')
			->select($db->qn('p.id', 'product_id'))
			->select(
				'CASE t.value_type WHEN ' . $db->q('string_value') . ' THEN fd.string_value '
				. 'WHEN ' . $db->q('float_value') . ' THEN fd.float_value '
				. 'WHEN ' . $db->q('int_value') . ' THEN fd.int_value '
				. 'WHEN ' . $db->q('text_value') . ' THEN fd.text_value '
				. 'WHEN ' . $db->q('field_value') . ' THEN fv.value '
				. 'ELSE ' . $db->q('') . ' END AS value'
			)
			->select(
				array(
						$db->qn('f.multiple_values'),
						$db->qn('f.scope'),
						$db->qn('f.name'),
						$db->qn('f.title'),
						$db->qn('f.alias', 'field_alias'),
						$db->qn('f.description')
					)
			)
			->select(
				array(
						$db->qn('t.name', 'type_name'),
						$db->qn('t.alias', 'type_alias'),
						$db->qn('t.value_type')
					)
			)
			->select($db->qn('fv.id', 'field_value_id'))
			->from($db->qn('#__redshopb_field_data', 'fd'))
			->join('inner', $db->qn('#__redshopb_field', 'f') . ' ON ' . $db->qn('f.id') . ' = ' . $db->qn('fd.field_id'))
			->join('inner', $db->qn('#__redshopb_type', 't') . ' ON ' . $db->qn('t.id') . ' = ' . $db->qn('f.type_id'))
			->join(
				'left',
				$db->qn('#__redshopb_product', 'p') .
				' ON ' . $db->qn('p.id') . ' = ' . $db->qn('fd.item_id') .
				' AND ' . $db->qn('f.scope') . ' = ' . $db->q('product')
			)
			->join('left', $db->qn('#__redshopb_field_value', 'fv') . ' ON ' . $db->qn('fv.id') . ' = ' . $db->qn('fd.field_value'));

		if (!$this->getState('list.ws', false))
		{
			$query->select(
				'CASE t.value_type WHEN ' . $db->q('string_value') . ' THEN fd.string_value '
				. 'WHEN ' . $db->q('float_value') . ' THEN fd.float_value '
				. 'WHEN ' . $db->q('int_value') . ' THEN fd.int_value '
				. 'WHEN ' . $db->q('text_value') . ' THEN fd.text_value '
				. 'WHEN ' . $db->q('field_value') . ' THEN fd.field_value '
				. 'ELSE ' . $db->q('') . ' END AS actual_value'
			);
		}

		// Filter by product id
		$filterProductId = $this->getState('filter.product_id');

		if (!is_null($filterProductId) && is_numeric($filterProductId) && $filterProductId > 0)
		{
			$this->setState('filter.item_id', $filterProductId);
			$this->setState('filter.field_scope', 'product');
		}

		// Filter by multiple product ids
		$productIds = $this->getState('filter.product_ids', null);

		if (!is_null($productIds))
		{
			$query->where($db->qn('fd.item_id') . ' IN (' . implode(',', $db->q($productIds)) . ')');
			$this->setState('filter.field_scope', 'product');
		}

		// Product id search by array
		$productIdArray = $this->getState('filter.product_id_array');

		if (!empty($productIdArray))
		{
			if (is_string($productIdArray))
			{
				$productIdArray = json_decode($productIdArray, true);
			}

			$productIdArray = (array) $productIdArray;

			if ($productIdArray[0] != '')
			{
				$productIdArray = ArrayHelper::toInteger($productIdArray);

				$query->where(
					$db->qn('fd.item_id') . ' IN (' . implode(',', $productIdArray) . ')'
				);
				$this->setState('filter.field_scope', 'product');
			}
		}

		// Filter by item id
		$filterItemId = $this->getState('filter.item_id', $this->getState('item_id'));

		if (!is_null($filterItemId) && is_numeric($filterItemId) && $filterItemId > 0)
		{
			$query->where($db->qn('fd.item_id') . ' = ' . $db->q($filterItemId));
		}

		// Filter by subitem id
		$filterSubitemId = $this->getState('filter.subitem_id', $this->getState('subitem_id'));

		if (!is_null($filterSubitemId) && is_numeric($filterSubitemId) && $filterSubitemId > 0)
		{
			$query->where($db->qn('fd.subitem_id') . ' = ' . $db->q($filterSubitemId));
		}

		// Filter by field id
		$filterFieldId = $this->getState('filter.field_id', $this->getState('field_id'));

		if (!is_null($filterFieldId) && is_numeric($filterFieldId) && $filterFieldId > 0)
		{
			$query->where($db->qn('fd.field_id') . ' = ' . $db->q($filterFieldId));
		}

		// Filter by field value
		$filterField 	  = $this->getState('filter.field');
		$filterFieldValue = $this->getState('filter.value', $this->getState('value'));

		if (!is_null($filterFieldValue))
		{
			$query->select('f.importable');
			$query->having($db->qn('value') . ' = ' . $db->q($filterFieldValue));
			$query->where($db->qn('f.name') . ' = ' . $db->q($filterField));
		}

		// Filter by field scope
		$filterFieldScope = $this->getState('filter.field_scope', 'product');

		if (!is_null($filterFieldScope) && $filterFieldScope != '')
		{
			$query->where($db->qn('f.scope') . ' = ' . $db->q($filterFieldScope));
		}

		// Filter by state
		$state = $this->getState('filter.field_data_state', $this->getState('filter.state'));

		if ($state == '0' || $state == 'false')
		{
			$query->where($db->qn('fd.state') . ' = 0');
		}
		elseif ($state == '1' || $state == 'true')
		{
			$query->where($db->qn('fd.state') . ' = 1');
		}

		// Filter above some field id
		$previousId = $this->getState('filter.previous_id', null);

		if (!is_null($previousId))
		{
			$query->where($db->qn('fd.id') . ' > ' . (int) $previousId);
		}

		// Filter by multiple field ids
		$fieldIds = $this->getState('filter.field_ids', null);

		if (!is_null($fieldIds))
		{
			$query->where($db->qn('fd.field_id') . ' IN (' . implode(',', $db->q($fieldIds)) . ')');
		}

		// Filter search
		$search = $this->getState('filter.search_field_datas', $this->getState('filter.search'));

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');

			$searchFields = array(
				'value LIKE ' . $search,
			);

			$query->where('(' . implode(' OR ', $searchFields) . ')');
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');
		$order         = !empty($orderList) ? $orderList : 'fd.id';
		$direction     = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		// Adds related web service data when requested
		$this->getListQueryWS($query);

		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();

		// If no data or no web services processing, it skips media transformation
		if (empty($items) || !$this->getState('list.ws', false))
		{
			return $items;
		}

		foreach ($items as $item)
		{
			if ($item->params == '')
			{
				continue;
			}

			// Processes parameters
			$item->params = new Registry($item->params);

			$internalUrl = $item->params->get('internal_url', null);

			if (!empty($internalUrl))
			{
				$internalUrl = Uri::root() . RedshopbHelperMedia::getFullMediaPath(
					$internalUrl, RInflector::pluralize($item->scope), $item->type_alias
				);
				$item->params->set('internal_url', $internalUrl);
			}

			$item->media_description  = $item->params->get('description');
			$item->media_internal_url = $internalUrl;
			$item->media_external_url = $item->params->get('external_url');

			// Filter display params
			$displayParams = $this->getState('filter.display_params', false);
			$includeParams = $this->getState('filter.include_params', false);

			// If not displaying extra parameters, it hides them and also the field value since it's split in multiple fields
			if (!$displayParams && !$includeParams)
			{
				unset($item->params);
				unset($item->value);
			}
		}

		return $items;
	}

	/**
	 * Method to get an array of data items prepared for the web service - including the external keys from sync
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getItemsWS()
	{
		$this->getState();

		// Field data web service is restricted to products (because of API scope)
		$this->setState('filter.field_scope', 'product');

		$items = parent::getItemsWS();

		foreach ($items AS $item)
		{
			if ($item->value_type == 'field_value')
			{
				$item->product_field_value_id = $item->field_value_id;

				continue;
			}

			$item->value = $item->{$item->value_type};
		}

		return $items;
	}
}
