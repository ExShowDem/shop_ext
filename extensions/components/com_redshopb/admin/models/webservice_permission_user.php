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
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Plugin\PluginHelper;
/**
 * Webservice Permission User Model
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelWebservice_Permission_User extends RedshopbModelAdmin
{
	/**
	 * Get the associated Table
	 *
	 * @param   string  $name    Table name
	 * @param   string  $prefix  Table prefix
	 * @param   array   $config  Configuration array
	 *
	 * @return  Table
	 */
	public function getTable($name = null, $prefix = '', $config = array())
	{
		$name   = is_null($name) ? 'Webservice_Permission_User_Xref' : $name;
		$prefix = is_null($prefix) ? 'RedshopbTable' : $prefix;

		return parent::getTable($name, $prefix, $config);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   12.2
	 */
	public function save($data)
	{
		$app   = Factory::getApplication();
		$table = RTable::getAdminInstance('Webservice_Permission_User_Xref', array(), 'com_redshopb');

		// Include the content plugins for the on save events.
		PluginHelper::importPlugin('content');

		// Allow an exception to be thrown.
		try
		{
			// Bind the data.
			if (!$table->bind($data))
			{
				$this->setError($table->getError());

				return false;
			}

			// Prepare the row for saving
			$this->prepareTable($table);

			// Check the data.
			if (!$table->check())
			{
				$this->setError($table->getError());

				return false;
			}

			// Trigger the onContentBeforeSave event.
			$result = $app->triggerEvent($this->event_before_save, array($this->option . '.' . $this->name, $table, true));

			if (in_array(false, $result, true))
			{
				$this->setError($table->getError());

				return false;
			}

			// Store the data.
			if (!$table->storeXref())
			{
				$this->setError($table->getError());

				return false;
			}

			// Clean the cache.
			$this->cleanCache();

			// Trigger the onContentAfterSave event.
			$app->triggerEvent($this->event_after_save, array($this->option . '.' . $this->name, $table, true));
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		$pkName = $table->getKeyName();

		if (isset($table->$pkName))
		{
			$this->setState($this->getName() . '.id', $table->user_id);
			$this->setState($this->getName() . '.user_id', $table->user_id);
		}

		$this->setState($this->getName() . '.new', true);

		return true;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function getItem($pk = null)
	{
		$db = Factory::getDbo();

		$item                                = new stdClass;
		$item->user_id                       = $this->getState($this->getName() . '.id', Factory::getApplication()->input->getInt('id'));
		$item->webservice_permission_user_id = $item->user_id;

		$query = $db->getQuery(true)
			->select('wpu.webservice_permission_id')
			->from($db->qn('#__redshopb_webservice_permission_user_xref', 'wpu'))
			->where('wpu.user_id = ' . (int) $item->user_id);

		$db->setQuery($query);

		$item->webservice_permissions = $db->loadColumn(0);

		return $item;
	}

	/**
	 * Problems with multiple primary keys so we will disable this feature
	 *
	 * @param   integer  $pk  The ID of the primary key.
	 *
	 * @return  boolean  True.
	 */
	public function checkout($pk = null)
	{
		return true;
	}

	/**
	 * Delete items
	 *
	 * @param   mixed  $pks  id or array of ids of items to be deleted
	 *
	 * @return  boolean
	 */
	public function delete(&$pks = null)
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
