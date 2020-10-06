<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Base
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;

/**
 * Redshopb list Model
 *
 * @package     Aesir.E-Commerce
 * @subpackage  List
 * @since       1.0
 */
class RedshopbModelList extends RModelList
{
	/**
	 * Internal memory based cache array of data.
	 *
	 * @var    array
	 */
	protected $cache = array();

	/**
	 * Main table query prefix
	 *
	 * @var  array
	 */
	protected $mainTablePrefix = '';

	/**
	 * Static cache for items
	 *
	 * @var    array
	 * @since  1.7
	 */
	protected static $staticCache = array();

	/**
	 * Flag ignore request
	 *
	 * @var boolean
	 */
	protected $ignoreRequest = false;

	/**
	 * Gets an array of objects from the results of database query.
	 *
	 * @param   string   $query       The query.
	 * @param   integer  $limitstart  Offset.
	 * @param   integer  $limit       The number of records.
	 *
	 * @return  array  An array of results.
	 *
	 * @since   12.2
	 * @throws  RuntimeException
	 */
	protected function _getList($query, $limitstart = 0, $limit = 0)
	{
		if (is_array($this->getState('productsearch.hash'))
			&& count($this->getState('productsearch.hash')) > 0)
		{
			$db            = Factory::getDbo();
			$query         = $db->replacePrefix((string) $query);
			$query         = str_replace(
				array_keys($this->getState('productsearch.hash')),
				array_values($this->getState('productsearch.hash')),
				$query
			);
			$oldTranslate  = $db->translate;
			$db->translate = false;

			// Disable limit for CSV export
			if ($this->getState('streamOutput', '') == 'csv')
			{
				$this->_db->setQuery($query);
			}
			else
			{
				$this->_db->setQuery($query, $limitstart, $limit);
			}

			$result        = $this->_db->loadObjectList();
			$db->translate = $oldTranslate;
		}
		else
		{
			$this->_db->setQuery($query, $limitstart, $limit);
			$result = $this->_db->loadObjectList();
		}

		return $result;
	}

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     BaseDatabaseModel
	 */
	public function __construct($config = array())
	{
		if (array_key_exists('paginationPrefix', $config) && $config['paginationPrefix'] != '' && empty($this->paginationPrefix))
		{
			$this->paginationPrefix = strtolower($config['paginationPrefix']);
		}

		if (array_key_exists('context', $config) && $config['context'] != '' && empty($this->context))
		{
			$this->context = strtolower($config['context']);

			if (empty($this->paginationPrefix))
			{
				$this->paginationPrefix = strtolower(str_replace('.', '_', $config['context'])) . '_';
			}
		}

		// Add any additional configuration to the model list constructor
		RFactory::getDispatcher()->trigger('onRedshopbListConstruct', array($this, &$config));

		parent::__construct($config);

		$configuration    = RedshopbEntityConfig::getInstance();
		$listLimitDefault = $configuration->getInt('list_limit', -1);

		// If Limit Field value is -1 then we are using Joomla default Limit
		if ($listLimitDefault != -1 && Factory::getApplication()->getUserState('global.list.' . $this->limitField) == null)
		{
			Factory::getApplication()->set('list_limit', $listLimitDefault);
		}

		// Guesses the main table prefix
		if ($this->mainTablePrefix == '')
		{
			$this->mainTablePrefix = strtolower(substr($this->getName(), 0, 1));
		}

		if (!empty($config['ignore_request']))
		{
			$this->ignoreRequest = true;
		}
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
			$name = RInflector::singularize($this->getName());
		}

		return parent::getTable($name, $prefix, $options);
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
		$staticCache = &$this->getStaticCache();

		$hash = $this->getStateHash();

		if (isset($staticCache[$hash]))
		{
			return $staticCache[$hash];
		}

		$items = parent::getItems();

		RFactory::getDispatcher()->trigger('onRedshopbListAfterGetItems', array($this, &$items));

		$staticCache[$hash] = $items ? $items : false;

		return $staticCache[$hash];
	}

	/**
	 * Method to get an array of data items prepared for the web service - including the external keys from sync
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getItemsWS()
	{
		$table       = $this->getTable();
		$nestedTable = (stripos(get_parent_class($table), 'nested') === false ? false : true);
		$pk          = $table->getKeyName();

		$this->getState();
		$this->setState('list.ws', true);

		$items = $this->getItems();

		$wsSyncMapPK         = $table->get('wsSyncMapPK');
		$wsMapFields         = $table->get('wsSyncMapFields');
		$wsDateFix           = $table->get('wsSyncMapDate');
		$wsMapFieldsMultiple = $table->get('wsSyncMapFieldsMultiple', array());

		// Adds any other related fk field data with sync ref data to the query
		if (count($wsMapFields) || count($wsDateFix) || count($wsSyncMapPK))
		{
			foreach ($items as $i => $item)
			{
				// Adds item data from the related PK sync fields
				if (count($wsSyncMapPK))
				{
					RedshopbHelperWebservices::addWSItemData($item, $pk, $wsSyncMapPK);
				}

				if (count($wsMapFields))
				{
					foreach ($wsMapFields as $mapField => $mapFieldData)
					{
						$fieldModelName = $mapFieldData['model'];

						// When on a nested table, if the object is in level 1, it omits the ROOT object, because it won't be displayed in WS data
						if ($nestedTable && $mapField == 'parent_id' && isset($item->level) && $item->level == 1)
						{
							$item->$mapField = null;
						}
						else
						{
							$fieldModel = RedshopbModel::getAdminInstance($fieldModelName);
							$fieldTable = $fieldModel->getTable();
							RedshopbHelperWebservices::addWSItemData($item, $mapField, $fieldTable->get('wsSyncMapPK'));
						}

						$items[$i]->{$mapField} = $item->$mapField;
					}
				}

				if (count($wsDateFix))
				{
					foreach ($wsDateFix as $field)
					{
						if ($item->{$field} == '0000-00-00 00:00:00' || $item->{$field} == '0000-00-00')
						{
							$items[$i]->{$field} = null;
						}
					}
				}

				if (count($wsMapFieldsMultiple))
				{
					foreach ($wsMapFieldsMultiple as $mapField => $fieldModelName)
					{
						$item->{$mapField . '_syncref'} = array();

						if (count($item->{$mapField}) == 0)
						{
							continue;
						}

						$fieldModel        = RedshopbModel::getAdminInstance(RInflector::singularize($fieldModelName));
						$fieldTable        = $fieldModel->getTable();
						$multiplesMapArray = $item->$mapField;

						if (!is_array($multiplesMapArray))
						{
							$multiplesMapArray = (array) $item->$mapField;
						}

						foreach ($multiplesMapArray as $fieldValue)
						{
							// Discards invalid "_errors" properties added by array to object conversion
							if (is_array($fieldValue))
							{
								continue;
							}

							$tempItem            = new stdClass;
							$tempItem->$mapField = $fieldValue;

							/** @var   RedshopbModelAdmin  $fieldModel */
							$fieldModel->addWSItemData(
								$tempItem, $mapField, $fieldTable->get('_tbl'), $fieldTable->getKeyName(), $fieldTable->get('wsSyncMapPK')
							);

							if (is_array($tempItem->{$mapField . '_syncref'}) && count($tempItem->{$mapField . '_syncref'}))
							{
								$item->{$mapField . '_syncref'}[] = implode(',', $tempItem->{$mapField . '_syncref'});
							}
						}
					}
				}
			}
		}

		return $items;
	}

	/**
	 * Adds WS data to the query - when requested
	 *
	 * @param   JDatabaseQuery  $query           The input query from getListQuery function
	 * @param   string          $order           Order by field
	 * @param   string          $orderDirection  Order by direction
	 *
	 * @return  void
	 */
	protected function getListQueryWS(&$query, $order = '', $orderDirection = '')
	{
		if ($this->getState('list.ws', false))
		{
			// Add additional query parameters if needed from plugin
			RFactory::getDispatcher()->trigger('onRedshopbListQueryWebservice', array($this, &$query));

			// Adds webservice permission restrictions for the current user if needed
			RedshopbHelperWebservice_Permission::addWSPermissionRestrictionQuery($query, $this);
			$isTotal = $this->getState('list.isTotal', false);

			if (!$isTotal)
			{
				$table = $this->getTable();
				$pk    = $table->getKeyName();

				$fields       = array($pk);
				$fieldMapping = array($table->get('wsSyncMapPK'));
				$wsMapFields  = $table->get('wsSyncMapFields');

				// Adds any other related fk field with sync ref data to the query
				if (count($wsMapFields))
				{
					foreach ($wsMapFields as $mapField => $mapFieldData)
					{
						$fieldModelName = $mapFieldData['model'];
						$fieldModel     = RedshopbModel::getAdminInstance($fieldModelName);
						$fieldTable     = $fieldModel->getTable();
						$fields[]       = (isset($mapFieldData['alias']) ? array($mapField => $mapFieldData['alias']) : $mapField);
						$fieldMapping[] = $fieldTable->get('wsSyncMapPK');
					}
				}

				// Adds field mapping for all the fields to the db query
				RedshopbHelperWebservices::addWSDataQuery(
					$query,
					$fields,
					$fieldMapping,
					$this->mainTablePrefix,
					$this->getStart(),
					$this->getState('list.limit', 0),
					$order,
					$orderDirection
				);

				// Changes limit start to 0 since the filter was sent to the internal query
				$store               = $this->getStoreId('getstart');
				$this->cache[$store] = 0;
			}
		}
	}

	/**
	 * Adds enriched data to the query - when requested
	 *
	 * @param   JDatabaseQuery  $query  The input query from getListQuery function
	 *
	 * @return  void
	 */
	protected function getSyncEnrichedQuery(&$query)
	{
		$syncReference = RedshopbHelperSync::getEnrichmentBase($this);

		if ($syncReference == '')
		{
			return;
		}

		$db = Factory::getDbo();
		$query->select($db->qn('sync.remote_key', 'sync_related_id'))
			->join(
				'left',
				$db->qn('#__redshopb_sync', 'sync') .
					' ON ' . $db->qn('sync.local_id') . ' = ' . $db->qn($this->mainTablePrefix . '.id') .
					' AND ' . $db->qn('sync.reference') . ' = ' . $db->q($syncReference)
			);

		// Filter by related id
		$filterRelatedId = $this->getState('filter.related_id', null);

		if (!empty($filterRelatedId))
		{
			$query->where($db->qn('sync.remote_key') . ' = ' . $db->q($filterRelatedId));
		}
	}

	/**
	 * Gets a unique hash based on a prefix + model state
	 *
	 * @param   string  $prefix  Prefix for the cache
	 *
	 * @return  string
	 *
	 * @since   1.7
	 */
	protected function getStateHash($prefix = null)
	{
		$prefix = $prefix ? $prefix : get_class($this);

		$state = $this->getState()->getProperties();

		ksort($state);

		return md5($this->context . ':' . $prefix . ':' . json_encode($state));
	}

	/**
	 * Gets static cache for this class
	 *
	 * @return  array
	 *
	 * @since   1.7
	 */
	protected function &getStaticCache()
	{
		$className = get_class($this);

		if (!isset(static::$staticCache[$className]))
		{
			static::$staticCache[$className] = array();
		}

		return static::$staticCache[$className];
	}

	/**
	 * Override because core method doesn't use filters to generate the id
	 *
	 * @param   string  $id  An identifier string to generate the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.7
	 */
	protected function getStoreId($id = '')
	{
		$id .= ':' . (int) $this->getState('list.isTotal', false);

		return $this->getStateHash($id);
	}

	/**
	 * Method to clear static cache for the list
	 *
	 * @param   boolean  $clearAll  Clear All Static Cache or just for the current state
	 *
	 * @return  void
	 *
	 * @since   12.2
	 * @throws  Exception
	 */
	public function clearListStaticCache($clearAll = false)
	{
		$staticCache = &$this->getStaticCache();

		if ($clearAll)
		{
			$staticCache = array();

			return;
		}

		$hash = $this->getStateHash();

		if (isset($staticCache[$hash]))
		{
			unset($staticCache[$hash]);
		}
	}

	/**
	 * Method to search items based on a state.
	 *
	 * Note: This method clears the model state.
	 *
	 * @param   array  $state  Array with filters + list options
	 *
	 * @return  array
	 *
	 * @since   1.7
	 */
	public function search($state = array())
	{
		// Clear current state and avoid populateState
		$this->state       = new CMSObject;
		$this->__state_set = true;

		foreach ($state as $key => $value)
		{
			$this->setState($key, $value);
		}

		$items = $this->getItems();

		return $items ? $items : array();
	}

	/**
	 * Method to get the total number of items for the data set.
	 *
	 * @return  integer  The total number of items available in the data set.
	 */
	public function getTotal()
	{
		$this->setState('list.isTotal', true);
		$result = parent::getTotal();
		$this->setState('list.isTotal', false);

		return $result;
	}

	/**
	 * Method to get the starting number of items for the data set.
	 *
	 * @return  integer  The starting number of items available in the data set.
	 */
	public function getStart()
	{
		if ($this->ignoreRequest)
		{
			return 0;
		}

		if (!$this->getState('list.ws', false))
		{
			return parent::getStart();
		}

		$store = $this->getStoreId('getstart');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		$start = $this->getState('list.start');
		$total = $this->getTotal();

		// Add the total to the internal cache.
		$this->cache[$store] = $start > $total ? $total : $start;

		return $this->cache[$store];
	}

	/**
	 * Get data for CSV export
	 *
	 * @param   string   $tableAlias   Aliased table name (usually the first letter)
	 * @param   string   $data         Array data in string format (from e.g. implode())
	 *
	 * @return   array|false
	 */
	public function getItemsCsv($tableAlias = null, $data = null)
	{
		$db = $this->getDbo();

		// Get a storage key.
		$store = $this->getStoreId();

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Load the list items.
		$query = $this->getListQuery();

		if (null !== $data)
		{
			$data = implode(',', $db->q($data));
			$query->where("{$db->qn("{$tableAlias}.id")} IN ({$data})");
		}

		try
		{
			$items = $this->_getList($query, $this->getStart(), $this->getState('list.limit'));
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Add the items to the internal cache.
		$this->cache[$store] = $items;

		return $this->cache[$store];
	}

	/**
	 * Return a query line using a given boolean-like filter
	 *
	 * @param   string  $dbColumn  Column name of the DB (or alias in the query)
	 * @param   string  $filter1   Name of the filter (1st option)
	 * @param   string  $filter2   Name of the filter (2nd option - can be empty)
	 *
	 * @return  string|false
	 * @since   1.13.2
	 */
	protected function buildBooleanFilter($dbColumn, $filter1, $filter2 = '')
	{
		$db     = Factory::getDbo();
		$filter = $this->getState($filter1, (!empty($filter2)) ? $this->getState($filter2) : '');

		if ($filter == '0' || $filter == 'false')
		{
			return $db->qn($dbColumn) . ' = 0';
		}
		elseif ($filter == '1' || $filter == 'true')
		{
			return $db->qn($dbColumn) . ' = 1';
		}

		return false;
	}

	/**
	 * Return a query line using an integer-like filter, with null option
	 *
	 * @param   string   $dbColumn    Column name of the DB (or alias in the query)
	 * @param   string   $filter1     Name of the filter (1st option)
	 * @param   string   $filter2     Name of the filter (2nd option - can be empty)
	 * @param   boolean  $nullOption  Include null option for emptying the filter when needed
	 *
	 * @return  string|false
	 * @since   1.13.2
	 */
	protected function buildIntegerFilter($dbColumn, $filter1, $filter2 = '', $nullOption = true)
	{
		$db     = Factory::getDbo();
		$filter = $this->getState($filter1, (!empty($filter2)) ? $this->getState($filter2) : '');

		if (is_numeric($filter) && $filter > 0)
		{
			return $db->qn($dbColumn) . ' = ' . (int) $filter;
		}
		elseif ($nullOption && $filter == 'null')
		{
			return $db->qn($dbColumn) . ' IS NULL';
		}

		return false;
	}
}
