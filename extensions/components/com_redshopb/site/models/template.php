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
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\PluginHelper;
/**
 * Template Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.6.36
 */
class RedshopbModelTemplate extends RedshopbModelAdmin
{
	/**
	 * Stock method to auto-populate the model state.
	 *
	 * @return  void
	 */
	protected function populateState()
	{
		parent::populateState();
		$templateName = Factory::getApplication()->input->getString('templateName', '');
		$this->setState('templateName', $templateName);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$item         = parent::getItem($pk);
		$templateName = $this->getState('templateName');

		if (property_exists($item, 'params'))
		{
			$params = $item->params;

			if (array_key_exists(0, $params))
			{
				$item->params = $params[0];
			}

			if (!empty($templateName) && array_key_exists($templateName, $params))
			{
				$item->params = $params[$templateName];
			}
		}

		if ($templateName != '')
		{
			if ($templateName == 'none')
			{
				$otherCustomizations = RedshopbHelperTemplate::getListExtraCustomizations($item);

				if (!empty($otherCustomizations))
				{
					$firstPath    = reset($otherCustomizations);
					$templateName = $firstPath->folder;
				}
			}

			$filePath = RedshopbHelperTemplate::getFilePath($item, $templateName);

			if (JFile::exists($filePath))
			{
				$item->content = file_get_contents($filePath);
			}
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
		$app = Factory::getApplication();

		if (!$this->canSave($data))
		{
			if (!empty($data['id']))
			{
				$app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_EDIT_ITEM_NOT_PERMITTED'), 'error');
			}
			else
			{
				$app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'), 'error');
			}

			return false;
		}

		$table   = $this->getTable();
		$context = $this->option . '.' . $this->name;

		if ((!empty($data['tags']) && $data['tags'][0] != ''))
		{
			$table->newTags = $data['tags'];
		}

		$key   = $table->getKeyName();
		$pk    = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		// Include the plugins for the save events.
		PluginHelper::importPlugin($this->events_map['save']);

		// Allow an exception to be thrown.
		try
		{
			// Load the row if saving an existing record.
			if ($pk > 0)
			{
				$table->load($pk);
				$isNew = false;
			}

			$dataContext  = $data['content'];
			$templateName = $data['templateName'];

			if (isset($data['params']))
			{
				if ($templateName)
				{
					$paramsKey = $templateName;
				}
				else
				{
					$paramsKey = 0;
				}

				$registry = new Registry;
				$registry->loadString($table->get('params'));
				$oldParams = $registry->toArray();
				$params    = array($paramsKey => $data['params']);
				unset($data['params']);
				$data['params'] = array_replace($oldParams, $params);
			}

			// If it`s customization, then store just params
			if ($templateName != '')
			{
				$data = array(
					'params' => $data['params'],
					'id' => $data['id']
				);
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

			// Trigger the before save event.
			$result = $app->triggerEvent($this->event_before_save, array($context, $table, $isNew));

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

			// Clean the cache.
			$this->cleanCache();

			// Trigger the after save event.
			$app->triggerEvent($this->event_after_save, array($context, $table, $isNew));

			if ($templateName != '')
			{
				$app->input->set('templateName', $templateName);
				$filePath = RedshopbHelperTemplate::getFilePath($table, $templateName);
				JFile::write($filePath, $dataContext);

				if (strcmp(RedshopbApp::getConfig()->getString('default_frontend_framework'), 'bootstrap2') === 0)
				{
					$filePathBs2 = str_replace('.php', '.bs2.php', $filePath);
					JFile::write($filePathBs2, $dataContext);
				}
			}
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		if (isset($table->$key))
		{
			$this->setState($this->getName() . '.id', $table->$key);
		}

		$this->setState($this->getName() . '.new', $isNew);

		return true;
	}

	/**
	 * Method to validate the form data.
	 * Each field error is stored in session and can be retrieved with getFieldError().
	 * Once getFieldError() is called, the error is deleted from the session.
	 *
	 * @param   Form    $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 */
	public function validate($form, $data, $group = null)
	{
		$templateName = $this->getState('templateName');

		if ($templateName == '')
		{
			$form->setFieldAttribute('templateName', 'required', 'false');
		}

		if (!isset($data['template_group']) || $data['template_group'] != 'email' || $data['scope'] == 'send-to-friend')
		{
			$form->setFieldAttribute('mail_subject', 'required', 'false', 'params');
		}

		return parent::validate($form, $data, $group);
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  $pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 */
	public function delete(&$pks)
	{
		$templateName = $this->getState('templateName');

		// Delete template
		if ($templateName == '')
		{
			if (!parent::delete($pks))
			{
				return false;
			}
		}

		// Delete customization(s)
		else
		{
			$pks   = (array) $pks;
			$table = $this->getTable();

			foreach ($pks as $pk)
			{
				if ($table->load($pk))
				{
					$file = RedshopbHelperTemplate::getFilePath($table, $templateName);

					if (JFile::exists($file))
					{
						JFile::delete($file);
					}

					$registry = new Registry;
					$registry->loadString($table->get('params'));
					$params = $registry->toArray();

					if (array_key_exists($templateName, $params))
					{
						unset($params[$templateName]);

						$table->save(
							array(
								'id' => $table->get('id'),
								'params' => $params
							)
						);
					}
				}
			}
		}

		return true;
	}

	/**
	 * Method to change the default state of one or more items.
	 *
	 * @param   array    $pks    A list of the primary keys to change.
	 * @param   integer  $value  The value of the home state.
	 *
	 * @return  boolean  True on success.
	 */
	public function setDefault(&$pks, $value = 1)
	{
		$table = $this->getTable();
		$pks   = (array) $pks;

		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				if ($table->default != $value)
				{
					$table->default = $value;

					if ($value == 1)
					{
						$table->state = 1;
					}

					if (!$table->check())
					{
						$this->setError($table->getError());

						return false;
					}

					if (!$table->store())
					{
						$this->setError($table->getError());

						continue;
					}
				}
			}
		}

		return true;
	}
}
