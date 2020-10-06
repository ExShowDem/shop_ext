<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\PluginHelper;

jimport('models.trait.customfields', JPATH_ROOT . '/components/com_redshopb/');

/**
 * Category Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelCategory extends RedshopbModelAdmin
{
	use RedshopbModelsTraitCustomFields;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  Configuration array
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->setScope('category');
	}

	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'category';

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   12.2
	 * @throws Exception
	 */
	public function save($data)
	{
		/** @var RedshopbTableCategory $table */
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

		$categoryId = 0;

		if (isset($data['id']))
		{
			$categoryId = (int) $data['id'];
		}

		// Store the data.
		if (!$table->store())
		{
			$this->setError($table->getError());

			return false;
		}

		if (!$categoryId)
		{
			$categoryId = $this->getState($this->getName() . '.id');
		}

		// Trigger the onContentAfterSave event.
		$dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, &$table, $isNew));

		// Rebuild the path for the category and children
		if (!$table->rebuildPath($table->id)
			|| !$table->rebuild($table->id, $table->lft, $table->level, $table->path))
		{
			$this->setError($table->getError());

			return false;
		}

		$this->setState($this->getName() . '.id', $table->id);

		// Image loading and thumbnail creation from web service file
		if (!$this->saveImageFile($table, $data, 'categories'))
		{
			return false;
		}

		// Saves the optional sync related id
		if (!$this->saveSyncRelatedId($data))
		{
			return false;
		}

		// Store extra fields data if available.
		if (!is_null($data['extrafields']) && is_array($data['extrafields']))
		{
			if (!RedshopbHelperField::storeScopeFieldData(
				'category', $categoryId, 0, $data['extrafields'], true, $table->getOption('lockingMethod', 'User')
			))
			{
				Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_FIELDS_SAVE_FAILURE'), 'error');
			}
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

		$localFields = $this->getFields($pk, true);

		if ($localFields)
		{
			$item->local_fields = $localFields;
		}

		$this->attachExtraFields($item, $this);

		// No item or no image
		if (empty($item->id) || empty($item->image))
		{
			return $item;
		}

		$item->imageurl = $this->getImageUrl($item->image, 'categories');

		return $item;
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
		$form = parent::getForm($data, $loadData);

		if (!$form)
		{
			return false;
		}

		$this->addExtraFields($form);

		return $form;
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

		// Used the isset here because it might be set if addRelatedData is false
		if (isset($item->parent_id_syncref)
			&& empty($item->parent_id_syncref))
		{
			$item->parent_id_syncref = array();
		}

		return $item;
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
		if (!$this->operationWS)
		{
			$this->addCustomFieldsValidation($form);
		}

		return parent::validate($form, $data, $group);
	}

	/**
	 * Validate incoming data from the web service
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  array | false
	 */
	public function validateWS($data)
	{
		$data = parent::validateWS($data);

		if (!$data)
		{
			return false;
		}

		if (!$this->areValidTemplateCodes($data))
		{
			Factory::getApplication()->enqueueMessage(Text::_($this->getError()), 'error');

			return false;
		}

		// Creates an temp image so it can be saved by the model
		$data = RedshopbHelperWebservices::getTempImageURL($data);

		if (!$data)
		{
			return false;
		}

		// Optional enrichment
		$syncReference = RedshopbHelperSync::getEnrichmentBase($this);

		if ($syncReference != '')
		{
			if (isset($data['related_id']))
			{
				// Leaves related id all the same if it's not sent
				if (isset($data['id']) && $data['id'] > 0 && $data['related_id'] == '')
				{
					$sync                    = new RedshopbHelperSync;
					$data['sync_related_id'] = $sync->findSyncedLocalId($syncReference, $data['id']);
				}
				else
				{
					$data['sync_related_id'] = $data['related_id'];
					unset($data['related_id']);
				}

				if ($data['sync_related_id'] == 'null')
				{
					$data['sync_related_id'] = '';
				}
			}
			else
			{
				$sync                    = new RedshopbHelperSync;
				$data['sync_related_id'] = $sync->findSyncedLocalId($syncReference, $data['id']);
			}
		}

		return $data;
	}

	/**
	 * Validate incoming data from the update web service - maps non-incoming data to avoid problems with actual validation
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  array
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
	 * Method to check if given template codes are valid product templates for each field
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return boolean
	 */
	private function areValidTemplateCodes($data)
	{
		if (!empty($data['template_code']) && !$this->isValidTemplateCode($data['template_code'], 'shop', 'category'))
		{
			return false;
		}

		if (!empty($data['product_list_template_code']) && !$this->isValidTemplateCode($data['product_list_template_code'], 'shop', 'list-product'))
		{
			return false;
		}

		if (!empty($data['product_grid_template_code']) && !$this->isValidTemplateCode($data['product_grid_template_code'], 'shop', 'grid-product'))
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to check if an specific template code is valid
	 *
	 * @param   string  $templateCode  Given template code
	 * @param   string  $validGroup    Valid template group
	 * @param   string  $validScope    Valid template scope
	 *
	 * @return  boolean
	 *
	 * @since   1.13.0
	 *
	 */
	private function isValidTemplateCode($templateCode, $validGroup, $validScope)
	{
		if (empty($templateCode))
		{
			return true;
		}

		$templateEntity = RedshopbEntityTemplate::getInstance()->loadItem('alias', $templateCode);

		if ($templateEntity->get('template_group') != $validGroup || $templateEntity->get('scope') != $validScope)
		{
			$this->setError(Text::sprintf('COM_REDSHOPB_CATEGORY_WS_ERROR_INVALID_TEMPLATE_CODE', $templateCode));

			return false;
		}

		return true;
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

	/**
	 * Method to add a field assocation to the category
	 *
	 * @param   integer  $categoryId  Id of the category
	 * @param   integer  $fieldId     Id of the field
	 *
	 * @return  boolean
	 */
	public function addFieldAssociation($categoryId, $fieldId)
	{
		$table = RedshopbTable::getAdminInstance('Category_Field_Xref');

		$row         = array();
		$keys        = array('category_id' => $categoryId, 'field_id' => $fieldId);
		$wsMapFields = $table->get('wsSyncMapFields', array());

		// Get Sync Map Reference IDs if needed
		foreach ($wsMapFields as $mapField => $mapFieldData)
		{
			$fieldModelName = $mapFieldData['model'];

			if (!$this->findRealItemId($keys, $mapField, $fieldModelName))
			{
				return false;
			}
		}

		if ($table->load($keys))
		{
			// Field Association was already found
			return false;
		}

		$row['category_id'] = $keys['category_id'];
		$row['field_id']    = $keys['field_id'];

		return (bool) $table->save($row);
	}

	/**
	 * Method to remove a field assocation to the category
	 *
	 * @param   integer  $categoryId  Id of the category
	 * @param   integer  $fieldId     Id of the field
	 *
	 * @return  boolean
	 */
	public function removeFieldAssociation($categoryId, $fieldId)
	{
		$table       = RedshopbTable::getAdminInstance('Category_Field_Xref');
		$keys        = array('category_id' => $categoryId, 'field_id' => $fieldId);
		$wsMapFields = $table->get('wsSyncMapFields', array());

		// Get Sync Map Reference IDs if needed
		foreach ($wsMapFields as $mapField => $mapFieldData)
		{
			$fieldModelName = $mapFieldData['model'];

			if (!$this->findRealItemId($keys, $mapField, $fieldModelName))
			{
				return false;
			}
		}

		if (!$table->load($keys))
		{
			// Field Association was not found
			return false;
		}

		return (bool) $table->delete();
	}

	/**
	 * Get the fields associated to this category.
	 *
	 * @param   integer  $categoryId  The product id
	 * @param   boolean  $idOnly      If true return only the field ids
	 *
	 * @return  array  An array of fields to display
	 */
	public function getFields($categoryId, $idOnly = false)
	{
		$db       = $this->_db;
		$fieldIds = array();
		$fields   = array();

		$query = $this->_db->getQuery(true)
			->select('field_id')
			->from('#__redshopb_category_field_xref')
			->where($db->qn('category_id') . ' = ' . (int) $categoryId)
			->order('field_id ASC');

		$results = $db->setQuery($query)->loadObjectList();

		foreach ($results as $result)
		{
			$fieldIds[] = $result->field_id;
		}

		if ($idOnly)
		{
			return $fieldIds;
		}

		foreach ($fieldIds as $fieldId)
		{
			$field = RedshopbHelperField::getFieldById($fieldId);

			$fields[] = $field;
		}

		return $fields;
	}

	/**
	 * Gets all the fields that are not selected by a certain filter fieldset
	 *
	 * @param   integer  $categoryId  Id of the category
	 *
	 * @return  array  An array of unassociated fields
	 */
	public function getUnassociatedFields($categoryId)
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select('id, name')
			->from($db->qn('#__redshopb_field'))
			->where('id NOT IN(SELECT field_id FROM #__redshopb_category_field_xref WHERE category_id = ' . (int) $categoryId . ')');

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Webservice method to add a field assocation to the category
	 *
	 * @param   string  $categoryId  Id of the category
	 * @param   string  $fieldId     Id of the field
	 *
	 * @return  boolean
	 */
	public function addLocalField($categoryId, $fieldId)
	{
		// This method is only used in Webservice so we will set it like that
		$this->operationWS = true;

		if (!$this->addFieldAssociation($categoryId, $fieldId))
		{
			return false;
		}

		return $categoryId;
	}

	/**
	 * Webservice method to remove a field assocation to the category
	 *
	 * @param   string  $categoryId  Id of the category
	 * @param   string  $fieldId     Id of the field
	 *
	 * @return  boolean
	 */
	public function removeLocalField($categoryId, $fieldId)
	{
		// This method is only used in Webservice so we will set it like that
		$this->operationWS = true;

		if (!$this->removeFieldAssociation($categoryId, $fieldId))
		{
			return false;
		}

		return $categoryId;
	}
}
