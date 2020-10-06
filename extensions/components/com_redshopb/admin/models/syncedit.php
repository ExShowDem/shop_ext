<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Object\CMSObject;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * Sync Edit Model
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelSyncEdit extends RModelAdmin
{
	/**
	 * Method to get a form object.
	 *
	 * @param   string   $name     The name of the form.
	 * @param   string   $source   The form source. Can be XML string if file flag is set to false.
	 * @param   array    $options  Optional array of options for the form creation.
	 * @param   boolean  $clear    Optional argument to force load a new form.
	 * @param   mixed    $xpath    An optional xpath to search for the fields.
	 *
	 * @return  mixed  Form object on success, False on error.
	 */
	protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
	{
		// Handle the optional arguments.
		$options['control'] = ArrayHelper::getValue($options, 'control', false);

		// Create a signature hash.
		$hash = md5($source . serialize($options));

		// Check if we can use a previously loaded form.
		if (isset($this->_forms[$hash]) && !$clear)
		{
			return $this->_forms[$hash];
		}

		// Get the form.
		RForm::addFormPath(JPATH_COMPONENT . '/models/forms');
		RForm::addFieldPath(JPATH_COMPONENT . '/models/fields');

		// Add form path from MVC override plugin
		if (method_exists('RModelAdminBase', 'addComponentFormPath'))
		{
			RForm::addFormPath(self::addComponentFormPath());
		}

		// Add field path from MVC override plugin
		if (method_exists('RModelAdminBase', 'addComponentFieldPath'))
		{
			RForm::addFieldPath(self::addComponentFieldPath());
		}

		try
		{
			$form = RForm::getInstance($name, $source, $options, false, $xpath);

			if (isset($options['load_data']) && $options['load_data'])
			{
				// Get the data for the form.
				$data = $this->loadFormData();
			}
			elseif (isset($options['form_data']) && $options['form_data']
				&& isset($options['form_data']['plugin']) && $options['form_data']['plugin'])
			{
				$data = array('plugin' => (string) $options['form_data']['plugin']);
			}
			else
			{
				$data = array();
			}

			// Convert the input to an array.
			if (is_object($data))
			{
				if ($data instanceof Registry)
				{
					// Handle a Registry.
					$cronData = $data->toArray();
				}
				elseif ($data instanceof CMSObject)
				{
					// Handle a CMSObject.
					$cronData = $data->getProperties();
				}
				else
				{
					// Handle other types of objects.
					$cronData = (array) $data;
				}
			}
			else
			{
				$cronData = $data;
			}

			if (isset($cronData['plugin']) && $cronData['plugin'])
			{
				RForm::addFormPath(JPATH_SITE . '/plugins/rb_sync/' . $cronData['plugin']);
				$lang      = Factory::getLanguage();
				$extension = 'plg_rb_sync_' . $cronData['plugin'];
				$lang->load(strtolower($extension), JPATH_ADMINISTRATOR, null, false, true)
				|| $lang->load(strtolower($extension), JPATH_PLUGINS . '/rb_sync/' . $cronData['plugin'], null, false, true);
				$pluginForm   = RForm::getInstance($this->context . '.' . $cronData['plugin'], $cronData['plugin'], $options, false, 'config/fields');
				$fieldsetData = $form->getFieldset('plugin');

				if (empty($fieldsetData))
				{
					$xml = new SimpleXMLElement('<fields name="' . $cronData['plugin'] . '"></fields>');
					$form->setField($xml);
				}

				foreach ($pluginForm->getXml() as $fields)
				{
					if (!isset($fields['name']) || $fields['name'] != 'params')
					{
						continue;
					}

					foreach ($fields->fieldset as $fieldset)
					{
						if (!isset($fieldset['name']) || $fieldset['name'] != 'basic')
						{
							continue;
						}

						$fieldset['name'] = 'sync_plugin';
						$xml              = '<field name="extends_plugin_config"
								type="radio"
								label="COM_REDSHOPB_SYNC_WEBSERVICE_EXTENDS_PLUGIN_CONFIG_LABEL"
								description="COM_REDSHOPB_SYNC_WEBSERVICE_EXTENDS_PLUGIN_CONFIG_DESC"
								default="0"
								class="btn-group"
								>
								<option value="0">JNO</option>
								<option value="1">JYES</option>
							</field>
							<field name="reset_plugin_config"
								type="checkbox"
								label="COM_REDSHOPB_SYNC_WEBSERVICE_RESET_PLUGIN_CONFIG_LABEL"
								description="COM_REDSHOPB_SYNC_WEBSERVICE_RESET_PLUGIN_CONFIG_DESC">
								<option value="1">JYES</option>
							</field>
							<field type="spacer" />';
						$node             = dom_import_simplexml($fieldset->field);
						$fragment         = $node->ownerDocument->createDocumentFragment();
						$fragment->appendXML($xml);
						$node->parentNode->insertBefore($fragment, $node);
						$form->setField($fieldset, $cronData['plugin']);
					}
				}
			}

			// Allow for additional modification of the form, and events to be triggered.
			// We pass the data because plugins may require it.
			$this->preprocessForm($form, $data);

			// Load the data into the form after the plugins have operated.
			$form->bind($data);
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Store the form for later.
		$this->_forms[$hash] = $form;

		return $form;
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
		$item = parent::getItem($pk);

		if (!$item)
		{
			return false;
		}

		if ($item->plugin && isset($item->params[$item->plugin]) && $item->params[$item->plugin])
		{
			$item->{$item->plugin} = $item->params[$item->plugin];
			unset($item->params[$item->plugin]);
		}

		return $item;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array  The default data is an empty array.
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState(
			$this->context . '.data',
			array()
		);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		if ($data)
		{
			if (is_array($data))
			{
				$plugin = $data['plugin'];

				if ($plugin && isset($data[$plugin]) && $data[$plugin])
				{
					return $data;
				}
			}
			else
			{
				$plugin = $data->plugin;

				if ($plugin && isset($data->{$plugin}) && $data->{$plugin})
				{
					return $data;
				}
			}

			if ($plugin)
			{
				$params = $this->getPluginParams($plugin);

				if (is_array($data))
				{
					$data[$plugin] = $params;
				}
				else
				{
					$data->{$plugin} = $params;
				}
			}
		}

		return $data;
	}

	/**
	 * Method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A Form object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			$this->context . '.' . $this->formName, $this->formName,
			array(
				'control' => 'jform',
				'load_data' => $loadData,
				'form_data' => $data
			)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Get plugin params
	 *
	 * @param   string  $plugin  Plugin name
	 *
	 * @return  array
	 */
	protected function getPluginParams($plugin)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('params')
			->from($db->qn('#__extensions'))
			->where('type = ' . $db->q('plugin'))
			->where('folder = ' . $db->q('rb_sync'))
			->where('element = ' . $db->q($plugin));

		$params = $db->setQuery($query)
			->loadResult();

		$registry = new Registry;
		$registry->loadString($params);

		return $registry->toArray();
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
		$data = parent::validate($form, $data, $group);

		if (!$data)
		{
			return false;
		}

		if (isset($data['plugin']) && $data['plugin'])
		{
			$plugin = $data['plugin'];

			if (isset($data[$plugin]) && $data[$plugin])
			{
				// Reset clones plugin configuration
				if (isset($data[$plugin]['reset_plugin_config'])
					&& $data[$plugin]['reset_plugin_config'])
				{
					unset($data[$plugin]['reset_plugin_config']);
					$data['params'][$plugin] = $this->getPluginParams($plugin);

					if (isset($data[$plugin]['extends_plugin_config']))
					{
						$data['params'][$plugin]['extends_plugin_config'] = $data[$plugin]['extends_plugin_config'];
					}
				}
				else
				{
					$data['params'][$plugin] = $data[$plugin];
				}

				unset($data[$plugin]);
			}
		}

		return $data;
	}

	/**
	 * Method to save the reordered nested set tree.
	 * First we save the new order values in the lft values of the changed ids.
	 * Then we invoke the table rebuild to implement the new ordering.
	 *
	 * @param   array    $idArray    An array of primary key ids.
	 * @param   integer  $lftArray   The lft value
	 *
	 * @return  boolean  False on failure or error, True otherwise
	 */
	public function saveorder($idArray = null, $lftArray = null)
	{
		// Get an instance of the table object.
		$table = $this->getTable();

		if (!$table->saveorder($idArray, $lftArray))
		{
			$this->setError($table->getError());

			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
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

			// Set the new parent id if parent id not matched OR while New/Save as Copy .
			if ($table->parent_id != $data['parent_id'] || $data['id'] == 0)
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

			// Clean the cache.
			$this->cleanCache();

			// Trigger the onContentAfterSave event.
			$app->triggerEvent($this->event_after_save, array($this->option . '.' . $this->name, $table, $isNew));

			// Rebuild the path
			if (!$table->rebuildPath($table->id))
			{
				$this->setError($table->getError());

				return false;
			}

			// Rebuild the paths of a childrens
			if (!$table->rebuild($table->id, $table->lft, $table->level, $table->path))
			{
				$this->setError($table->getError());

				return false;
			}
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
}
