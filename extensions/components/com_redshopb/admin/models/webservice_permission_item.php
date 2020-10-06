<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * Webservice Permission Item Model
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelWebservice_Permission_Item extends RedshopbModelAdmin
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
		$table = RTable::getAdminInstance('Webservice_Permission_Item_Xref', array(), 'com_redshopb');

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
			$this->setState($this->getName() . '.id', $table->item_id);
		}

		$this->setState($this->getName() . '.new', true);

		return true;
	}
}
