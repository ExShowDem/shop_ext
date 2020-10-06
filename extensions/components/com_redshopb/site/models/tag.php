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
 * Tag Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelTag extends RedshopbModelAdmin
{
	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'tag';

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
		$table = $this->getTable();
		$key   = $table->getKeyName();
		$pk    = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');

		$isNew = true;

		if ($table->load($pk))
		{
			$isNew = false;
		}

		// Set the new parent id if parent id not matched OR while New/Save as Copy .
		$isNewParent = (!empty($data['parent_id']) && $data['parent_id'] != $table->parent_id);

		if ($isNewParent || empty($data['id']))
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

		// Trigger the onContentBeforeSave event.
		$dispatcher = RFactory::getDispatcher();
		PluginHelper::importPlugin('content');
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
			||!$table->rebuild($table->id, $table->lft, $table->level, $table->path))
		{
			$this->setError($table->getError());

			return false;
		}

		$this->setState($this->getName() . '.id', $table->id);

		// Image loading and thumbnail creation from web service file
		if (!$this->saveImageFile($table, $data, 'tags'))
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

		$item->imageurl = $this->getImageUrl($item->image, 'tags');

		return $item;
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
