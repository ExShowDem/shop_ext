<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\PluginHelper;
/**
 * Manufacturer Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.6.51
 */
class RedshopbModelManufacturer extends RedshopbModelAdmin
{
	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'product';

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
		/** @var RedshopbTableManufacturer $table */
		$table = $this->getTable();
		$key   = $table->getKeyName();
		$pk    = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		if ($table->load($pk))
		{
			$isNew = false;
		}

		// Set the new parent id if parent id not matched OR while New/Save as Copy .
		if ((isset($data['parent_id']) && $data['parent_id'] != '' && ($table->parent_id != $data['parent_id'])) || empty($data['id']))
		{
			$table->setLocation($data['parent_id'], 'last-child');
		}

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

		// Include the content plugins for the on save events.
		PluginHelper::importPlugin('content');
		$dispatcher = RFactory::getDispatcher();

		// Trigger the onContentBeforeSave event.
		$result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, &$table, $isNew));

		if (in_array(false, $result, true))
		{
			$this->setError($table->getError());

			return false;
		}

		// Store the data.
		if (!$table->store())
		{
			$this->setError($table->getError());

			return false;
		}

		// Trigger the onContentAfterSave event.
		$dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, &$table, $isNew));

		// Rebuild the paths
		if (!$table->rebuildPath($table->id)
			|| !$table->rebuild($table->id, $table->lft, $table->level, $table->path))
		{
			$this->setError($table->getError());

			return false;
		}

		$this->setState($this->getName() . '.id', $table->id);

		// Image loading and thumbnail creation from web service file
		if (!$this->saveImageFile($table, $data, 'manufacturers'))
		{
			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to get the row form.
	 *
	 * @param   int  $pk  Primary key
	 *
	 * @return	object
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		// Gets the image URL
		if (empty($item->id) || empty($item->image))
		{
			return $item;
		}

		$item->imageurl = $this->getImageUrl($item->image, 'manufacturers');

		return $item;
	}

	/**
	 * Validate incoming data from the update web service - maps non-incoming data to avoid problems with actual validation
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  array|false
	 */
	public function validateUpdateWS($data)
	{
		$data = parent::validateUpdateWS($data);

		if (!$data)
		{
			return false;
		}

		if ($data['parent_id'] == '')
		{
			unset($data['parent_id']);
		}

		return $data;
	}

	/**
	 *  feature/unfeature a manufacturer
	 *
	 * @param   integer  $id     The manufacturer id
	 * @param   integer  $value  value
	 *
	 * @return  boolean True on success. False otherwise.
	 */
	public function updateFeatured($id, $value)
	{
		// This method is only used in Webservice so we will set it like that
		$this->operationWS = true;

		$manufacturerTable = $this->getTable();

		if (!$manufacturerTable->load($id))
		{
			return false;
		}

		$data = array(
			'featured' => $value
		);

		if (!$manufacturerTable->save($data))
		{
			return false;
		}

		return $id;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  $pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   12.2
	 */
	public function delete(&$pks)
	{
		if (!is_array($pks))
		{
			$pks = array($pks);
		}

		// Remove child manufacturer if it exist in array. Because child manufacturer will be deleted follow it's parent.
		$exclude = array();

		foreach ($pks as $key => $manufacturerId)
		{
			$manufacturer = RedshopbEntityManufacturer::load($manufacturerId);

			if (!$manufacturer->isLoaded())
			{
				unset($pks[$key]);
				continue;
			}

			$exclude = array_merge($exclude, $manufacturer->getChildrenIds());
		}

		$exclude = array_unique($exclude);
		$exclude = array_values($exclude);

		$pks = array_diff($pks, $exclude);

		return parent::delete($pks);
	}

	/**
	 * Method rebuild the entire nested set tree.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 *
	 * @since   1.9.14
	 */
	public function rebuild()
	{
		if (!RedshopbHelperUser::isRoot())
		{
			return false;
		}

		// Get an instance of the table object.
		$table = $this->getTable();

		if (!$table->rebuild())
		{
			$this->setError($table->getError());

			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}
}
