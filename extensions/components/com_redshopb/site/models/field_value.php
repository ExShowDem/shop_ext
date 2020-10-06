<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Plugin\PluginHelper;
/**
 * Field Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelField_Value extends RedshopbModelAdmin
{
	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   Table  $table  A Table object.
	 *
	 * @return  array  An array of conditions to add to ordering queries.
	 *
	 * @since   12.2
	 */
	protected function getReorderConditions($table)
	{
		$db        = $this->getDbo();
		$condition = $db->qn('field_id') . '=' . $db->q($table->field_id);

		return array($condition);
	}

	/**
	 * Method to get a single record using possible related data from the web service
	 *
	 * @param   string  $pk              The pk to be retrieved
	 * @param   bool    $addRelatedData  Add the other related data fields from web service sync
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function getItemWS($pk, $addRelatedData = true)
	{
		$item = parent::getItemWS($pk, $addRelatedData);

		if (!$item)
		{
			return false;
		}

		// Checks if the field can be loaded from Webservices, otherwise it rejects the record read
		$fieldModel = RedshopbModelAdmin::getAdminInstance('Field');

		if (!$fieldModel->getItemFromWSData($item->field_id))
		{
			return false;
		}

		return $item;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 */
	public function save($data)
	{
		// Fixes bug in joomla with updating and not setting item id in the model state
		$this->getState();

		$app = Factory::getApplication();

		if ($this->canSave($data))
		{
			$table = $this->getTable();

			if ((!empty($data['tags']) && $data['tags'][0] != ''))
			{
				$table->newTags = $data['tags'];
			}

			$key   = $table->getKeyName();
			$pk    = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
			$isNew = true;

			// Include the content plugins for the on save events.
			PluginHelper::importPlugin('content');

			// Allow an exception to be thrown.
			try
			{
				// Load the row if saving an existing record.
				if ($pk > 0)
				{
					$table->load($pk);
					$isNew = false;
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
				$result = $app->triggerEvent($this->event_before_save, array($this->option . '.' . $this->name, $table, $isNew));

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

				if (!$this->saveImageFile($table, $data, 'field_values'))
				{
					return false;
				}

				// Clean the cache.
				$this->cleanCache();

				// Trigger the onContentAfterSave event.
				$app->triggerEvent($this->event_after_save, array($this->option . '.' . $this->name, $table, $isNew));
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());

				return false;
			}

			$pkName = $table->getKeyName();

			if (isset($table->$pkName))
			{
				$this->setState($this->getName() . '.id', $table->$pkName);
			}

			$this->setState($this->getName() . '.new', $isNew);

			return true;
		}

		$msg = 'JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED';

		if (!empty($data['id']))
		{
			$msg = 'JLIB_APPLICATION_ERROR_EDIT_ITEM_NOT_PERMITTED';
		}

		$app->enqueueMessage(Text::_($msg), 'error');

		return false;
	}

	/**
	 * Method to save an new image file
	 *
	 * @param   Table   $table    the category table
	 * @param   array   $data     the data being saved
	 * @param   string  $section  the section to save the file in.
	 *
	 * @return boolean
	 */
	protected function saveImageFile(Table $table, $data, $section = 'field_values')
	{
		// Image loading and thumbnail creation from web service file
		$imageFile = $data['image_file']['params'];
		$hasImage  = !empty($imageFile['imageFileUpload']['name']);

		if ($hasImage)
		{
			$data['params']['deleteImage'] = 1;
		}

		$params   = $table->get('params');
		$registry = new Registry;
		$registry->loadString($params);
		$params = $registry->toArray();

		if ($data['params']['deleteImage'] && isset($params['image']) && $params['image'])
		{
			RedshopbHelperThumbnail::deleteImage($params['image'], 1, $section);
			unset($params['image'], $params['deleteImage']);

			if (!$hasImage)
			{
				$registry = new Registry;
				$registry->loadArray($params);
				$table->set('params', $registry->toString());

				if (!$table->store())
				{
					$this->setError($table->getError());

					return false;
				}
			}
		}

		if (!$hasImage)
		{
			return true;
		}

		$file = $imageFile['imageFileUpload'];

		RedshopbHelperThumbnail::$displayError = false;

		if (!RedshopbHelperThumbnail::checkFileError($file['name'], $file['error'])
			|| !RedshopbHelperMedia::checkExtension($file['name'])
			|| !RedshopbHelperMedia::checkIsImage($file['tmp_name']))
		{
			$this->setError(RedshopbHelperThumbnail::getError());

			return false;
		}

		$pk = $table->getKeyName();

		// Saving image
		$imageFileName = RedshopbHelperThumbnail::savingImage((string) $file['tmp_name'], (string) $file['name'], $table->{$pk}, false, $section);

		if ($imageFileName === false)
		{
			$this->setError(RedshopbHelperThumbnail::getError());

			return false;
		}

		$params['image'] = $imageFileName;
		$registry        = new Registry;
		$registry->loadArray($params);
		$table->set('params', $registry->toString());

		if (!$table->store())
		{
			$this->setError($table->getError());

			return false;
		}

		return true;
	}
}
