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
use Joomla\Registry\Registry;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Date\Date;

/**
 * Sync Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelSync extends RModelList
{
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('node.*')
			->from($db->qn('#__redshopb_cron', 'node'))
			->innerjoin($db->qn('#__redshopb_cron', 'parent') . ' ON node.parent_id = parent.id')
			->where('node.level > 0')
			->order('node.lft ASC');

		$app = Factory::getApplication();

		if (!$app->getUserState('list.change_sync', 0))
		{
			$query->where('node.state = 1');
		}

		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getItems()
	{
		// Get a storage key.
		$store = $this->getStoreId();

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Load the list items.
		$query = $this->_getListQuery();

		try
		{
			$items = $this->_getList($query);
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
	 * Triggers correct plugin call
	 *
	 * @param   int    $cronId     ID of the cron process to sync
	 * @param   bool   $fullSync   Force a full sync
	 * @param   bool   $startSync  True if it's starting to sync (false if it's just continuing an existing process)
	 * @param   array  $tableSet   Optional table set to sync (it won't set a "last executed" time if an specific array is sent)
	 *
	 * @return  array
	 *
	 * @throws Exception
	 */
	public function syncSelectedItem($cronId, $fullSync = false, $startSync = false, $tableSet = array())
	{
		/** @var RedshopbTableSyncEdit $table */
		$table = $this->getTable('SyncEdit', 'RedshopbTable');
		$table->reset();
		$return            = array();
		$return['success'] = false;
		$continueSync      = false;
		$isCheckIn         = true;
		$setTime           = true;
		$tmpXML            = '';
		$parts             = null;
		$session           = Factory::getSession();
		$app               = Factory::getApplication();

		if ($table->load($cronId))
		{
			try
			{
				// Load up params
				$params = !is_object($table->get('params')) ? new Registry($table->get('params')) : $table->get('params');

				// Temp file
				$tmpXML = $session->get('sync_' . $table->get('name'), '', 'com_redshopb');

				// If no cache file or it's the first sync of this object, it creates the cache file
				if ($tmpXML == '' || $startSync)
				{
					$tmpPath = $app->get('tmp_path', '');

					if (!file_exists($tmpPath . '/com_redshopb'))
					{
						mkdir($tmpPath . '/com_redshopb');
					}

					$tmpXML = $tmpPath . '/com_redshopb/sync_' . $table->get('name') . '_' . date('YmdHis') . '.xml';
					$session->set('sync_' . $table->get('name'), $tmpXML, 'com_redshopb');
				}
				else
				{
					$continueSync = true;
				}

				if (!$continueSync && $table->get('checked_out') != 0)
				{
					$app->enqueueMessage(Text::_('COM_REDSHOPB_SYNC_OTHER_USER_SYNCED_AT_THIS_TIME'), 'error');

					return $return;
				}

				if ($fullSync || $table->get('start_time') == '0000-00-00 00:00:00')
				{
					$table->set('fullSync', 1);
				}
				else
				{
					$table->set('fullSync', 0);
				}

				if (count($tableSet) > 0)
				{
					$filterFields = array('productId');

					foreach ($tableSet as $key => $oneSet)
					{
						if (in_array($key, $filterFields))
						{
							$table->set($key, $oneSet);
						}
					}
				}

				$isCheckIn = false;
				$userId    = Factory::getUser()->id;

				if (!$userId)
				{
					$userId = -1;
				}

				$table->checkOut($userId, $cronId);
				$startTime         = $params->get('executionStartTime', null);
				$startTime         = $startTime ? $startTime : Date::getInstance()->toSql();
				$table->start_time = $startTime;

				PluginHelper::importPlugin('rb_sync');
				$dispatcher = RFactory::getDispatcher();

				// Disable translation for sync plugins
				$db            = Factory::getDbo();
				$oldTranslate  = $db->translate;
				$db->translate = false;

				// We will reset Types and Fields so we unload translated values if needed
				RedshopbHelperField::$fields      = null;
				RedshopbHelperField::$types       = null;
				RedshopbHelperField::$fieldValues = array();

				$pluginReturns = $dispatcher->trigger(
					'onFuncRead', array('RedshopbSync', ucfirst($table->get('plugin')) . $table->get('name'), &$table)
				);

				$db->translate = $oldTranslate;
				$table         = $this->getTable('SyncEdit', 'RedshopbTable');
				$table->load($cronId);

				// Load up params
				if (!is_object($table->get('params')))
				{
					$table->set('params', new Registry($table->get('params')));
				}

				foreach ($pluginReturns as $pluginReturn)
				{
					if (!empty($pluginReturn))
					{
						$return['success'] = $pluginReturn;
					}
				}

				if (empty($return['success']))
				{
					RedshopbHelperSync::addMessage(Text::_('COM_REDSHOPB_SYNC_METHOD_NOT_FOUND'), 'error');

					// In the end, it deletes the temp file if it exists
					if (file_exists($tmpXML))
					{
						unlink($tmpXML);
						$session->set('sync_' . ucfirst($table->get('plugin')) . $table->get('name'), '', 'com_redshopb');
					}

					if (!$table->checkIn())
					{
						throw new Exception(Text::_('COM_REDSHOPB_CLI_ERROR_UPDATE_TABLE'));
					}
				}
				elseif (!is_array($return['success']))
				{
					$table->params->set('executionStartTime', null);

					// In the end, it deletes the temp file if it exists
					if (file_exists($tmpXML))
					{
						unlink($tmpXML);
						$session->set('sync_' . ucfirst($table->get('plugin')) . $table->get('name'), '', 'com_redshopb');
					}

					if ($setTime)
					{
						// Require string, if delete - after finish all transactions in current webservice can be not right data stored
						$finishTime = Date::getInstance()->toSql();
						$table->set('start_time', $startTime);
						$table->set('finish_time', $finishTime);
					}

					if ($isCheckIn == false)
					{
						if (!$table->checkIn())
						{
							throw new Exception(Text::_('COM_REDSHOPB_CLI_ERROR_UPDATE_TABLE'));
						}
					}

					$parts = 0;
				}
				else
				{
					if (!$table->checkIn())
					{
						throw new Exception(Text::_('COM_REDSHOPB_CLI_ERROR_UPDATE_TABLE'));
					}

					$return['continue'] = true;
					$table->params->set('executionStartTime', $startTime);

					$parts = $return['success']['total'] - $return['success']['parts'];
				}
			}
			catch (Exception $e)
			{
				$return['success'] = false;
				RedshopbHelperSync::addMessage($e->getMessage(), 'error');

				// In the end, it deletes the temp file if it exists
				if (file_exists($tmpXML))
				{
					unlink($tmpXML);
					$session->set('sync_' . ucfirst($table->get('plugin')) . $table->get('name'), '', 'com_redshopb');
				}

				if ($isCheckIn == false)
				{
					if (!$table->checkIn())
					{
						throw new Exception(Text::_('COM_REDSHOPB_CLI_ERROR_UPDATE_TABLE'));
					}
				}
			}

			// Sets number of executed items in Cron
			RedshopbHelperSync::setProgressCounters($table, $parts, null, true);
		}
		else
		{
			$app->enqueueMessage(Text::_('COM_REDSHOPB_SYNC_METHOD_NOT_FOUND'), 'error');
		}

		return $return;
	}

	/**
	 * Clear hashed keys
	 *
	 * @param   mixed  $pks  Id or array of ids of items to be cleared
	 *
	 * @return  boolean
	 */
	public function clearHashedKeys($pks = null)
	{
		if (empty($pks))
		{
			return true;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('node.*')
			->from($db->qn('#__redshopb_cron', 'node'))
			->where('node.level > 0')
			->where('node.id IN (' . implode(',', $pks) . ')');

		$cronItems = $db->setQuery($query)
			->loadObjectList();

		if ($cronItems)
		{
			PluginHelper::importPlugin('rb_sync');
			$dispatcher = RFactory::getDispatcher();

			foreach ($cronItems as $cronItem)
			{
				$result = $dispatcher->trigger('onFuncCronClearHashedKeys', array('RedshopbSync', $cronItem));

				foreach ($result as $pluginReturn)
				{
					if (!empty($pluginReturn))
					{
						continue 2;
					}
				}

				return false;
			}
		}

		return true;
	}

	/**
	 * Clear all hashed keys
	 *
	 * @return  boolean
	 */
	public function clearAllHashedKeys()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->update($db->qn('#__redshopb_sync'))
			->set($db->qn('hash_key') . ' = ' . $db->q(''));

		if (!$db->setQuery($query)->execute())
		{
			return false;
		}

		return true;
	}
}
