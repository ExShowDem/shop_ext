<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Base
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;
/**
 * Redshopb Admin Model
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Base
 * @since       1.0
 */
class RedshopbModelAdmin extends RModelAdmin
{
	/**
	 * Name to check in ACL. ex: company | department | category | ...
	 *
	 * @var  string
	 */
	protected $aclCheckName = '';

	/**
	 * Action to check. Default is manage
	 *
	 * @var  string
	 */
	protected $aclCheckAction = 'manage';

	/**
	 * Code field reference
	 *
	 * @var  string
	 */
	protected $codeField = 'alias';

	/**
	 * Controls if the model is being operated from the web service
	 *
	 * @var  boolean
	 */
	protected $operationWS = false;

	/**
	 * Base plugin type always import.
	 *
	 * @var array
	 */
	protected $basePluginTypes = array('system');

	/**
	 * Form control
	 * @var string
	 */
	protected $control = 'jform';

	/**
	 * Check if an item is locked by webservice
	 *
	 * @param   integer|null  $pk  The primary key of the node to delete.
	 *
	 * @return  boolean  True if item is locked
	 */
	public function getIslockedByWebservice($pk = null)
	{
		$pk    = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
		$table = $this->getTable();

		return $table->isLockedByWebservice($pk);
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	protected function canDelete($record)
	{
		if (RedshopbHelperACL::isSuperAdmin()
			|| empty($record->id)
			|| empty($this->aclCheckName))
		{
			return parent::canDelete($record);
		}

		if (!RedshopbHelperACL::getPermission($this->aclCheckAction, $this->aclCheckName, Array('delete', 'delete.own'), true)
			|| !$this->additionalACLCheck($record))
		{
			return false;
		}

		return parent::canDelete($record);
	}

	/**
	 * Method to test whether a record can be edited.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	protected function canEditState($record)
	{
		if (RedshopbHelperACL::isSuperAdmin()
			|| empty($this->aclCheckName)
			|| empty($record->id))
		{
			return parent::canEditState($record);
		}

		// If name is set to check, then we will check it
		if (!RedshopbHelperACL::getPermission($this->aclCheckAction, $this->aclCheckName, Array('edit', 'edit.own'), true)
			|| !$this->additionalACLCheck($record))
		{
			return false;
		}

		return parent::canEditState($record);
	}

	/**
	 * Method to test whether a record can be edited.
	 *
	 * @param   array  $data  A record array.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	public function canSave($data)
	{
		$record = (object) $data;

		if (RedshopbHelperACL::isSuperAdmin()
			|| empty($this->aclCheckName))
		{
			return true;
		}

		// Allows unrestricted access to edit its own user
		$isEditingUserProfile = ($this->aclCheckName == 'user' && $this->aclCheckAction == 'manage');

		if ($isEditingUserProfile)
		{
			$user      = Factory::getUser();
			$rsbUserId = RedshopbHelperUser::getUserRSid($user->id);

			if ($record->id == $rsbUserId)
			{
				return true;
			}
		}

		// If name is set to check, then we will check it
		$permissions = array('create');

		if (!empty($record->id))
		{
			$permissions = array('edit', 'edit.own');
		}

		if (!RedshopbHelperACL::getPermission($this->aclCheckAction, $this->aclCheckName, $permissions, true))
		{
			return false;
		}

		if (!empty($record->id) && !$this->additionalACLCheck($record))
		{
			return false;
		}

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
		// Fixes bug in joomla with updating and not setting item id in the model state
		$this->getState();

		if ($this->canSave($data))
		{
			return parent::save($data);
		}

		$msg = 'JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED';

		if (!empty($data['id']))
		{
			$msg = 'JLIB_APPLICATION_ERROR_EDIT_ITEM_NOT_PERMITTED';
		}

		Factory::getApplication()->enqueueMessage(Text::_($msg), 'error');

		return false;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  $pks  An array of record primary keys.
	 *
	 * @return  boolean      True if successful, false if an error occurs.
	 *
	 * @since   12.2
	 */
	public function delete(&$pks)
	{
		if (!is_array($pks))
		{
			$pks = array($pks);
		}

		// Main deletion
		if (!parent::delete($pks))
		{
			return false;
		}

		return true;
	}

	/**
	 * Called before delete / store / publish
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True on success.
	 */
	protected function additionalACLCheck($record)
	{
		// @todo *bump* this is disabled because it needs further checking
		if (false && !RedshopbHelperACL::isSuperAdmin())
		{
			switch ($this->aclCheckName)
			{
				case 'company':
					$userId               = RedshopbHelperUser::getUserRSid();
					$companyId            = RedshopbHelperUser::getUserCompanyId($userId);
					$availableCompanies   = RedshopbHelperACL::listAvailableCompanies($userId);
					$availableCompanies   = explode(',', $availableCompanies);
					$availableCompanies[] = $companyId;

					if (!in_array($record->id, $availableCompanies))
					{
						return false;
					}
					break;
				case 'department':
					$userId                 = RedshopbHelperUser::getUserRSid();
					$departmentId           = RedshopbHelperUser::getUserDepartmentId($userId);
					$availableDepartments   = RedshopbHelperACL::listAvailableDepartments($userId);
					$availableDepartments   = explode(',', $availableDepartments);
					$availableDepartments[] = $departmentId;

					if (!in_array($record->id, $availableDepartments))
					{
						return false;
					}
					break;
				case 'address':
					$userId               = RedshopbHelperUser::getUserRSid();
					$availableDepartments = RedshopbHelperACL::listAvailableAddresses($userId);
					$availableDepartments = explode(',', $availableDepartments);

					if (!in_array($record->id, $availableDepartments))
					{
						return false;
					}
					break;
				case 'user':
					$userId               = RedshopbHelperUser::getUserRSid();
					$companyId            = RedshopbHelperUser::getUserCompanyId($userId);
					$availableEmployees   = RedshopbHelperACL::listAvailableEmployees($companyId, 0, 'comma');
					$availableEmployees   = explode(',', $availableEmployees);
					$availableEmployees[] = $userId;

					if (!in_array($record->id, $availableEmployees))
					{
						return false;
					}
					break;
			}
		}

		return true;
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
				'control'   => $this->control,
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		$id = (int) $this->getState($this->getName() . '.id');

		if ($id && $this->getTable()->isLockedByWebservice($id))
		{
			$form->disableAllFormFields = true;
		}

		return $form;
	}

	/**
	 * Method to validate the form data.
	 * Each field error is stored in session and can be retrieved with getFieldError().
	 * Once getFieldError() is called, the error is deleted from the session.
	 *
	 * @param   Form         $form   The form to validate against.
	 * @param   array        $data   The data to validate.
	 * @param   string|null  $group  The name of the field group to validate.
	 *
	 * @return  false|array  Array of filtered data if valid, false otherwise.
	 */
	public function validate($form, $data, $group = null)
	{
		// Filter and validate the form data.
		$locks = isset($data['aECUnLockedColumns']) ? $data['aECUnLockedColumns'] : null;
		$data  = $form->filter($data);

		if ($locks)
		{
			$data['aECUnLockedColumns'] = $locks;
		}

		$return = $form->validate($data, $group);
		$app    = Factory::getApplication();

		if (!($return instanceof Exception)
			&& $return !== false)
		{
			return $data;
		}

		// Check for an error.
		if ($return instanceof Exception)
		{
			$app->enqueueMessage($return->getMessage(), 'warning');

			return false;
		}

		$session = Factory::getSession();

		// Get the validation messages from the form.
		foreach ($form->getErrors() as $key => $message)
		{
			if ($message instanceof Exception)
			{
				$app->enqueueMessage($message->getMessage(), 'warning');

				// Store the field error in session.
				$session->set($this->context . '.error.' . $key, $message->getMessage());
			}
			else
			{
				$app->enqueueMessage($message, 'warning');

				// Store the field error in session.
				$session->set($this->context . '.error.' . $key, $message);
			}
		}

		return false;
	}

	/**
	 * Method to get a single record using the alias as a reference
	 *
	 * @param   string  $alias  The alias to be retrieved
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function getItemAlias($alias)
	{
		$table = $this->getTable();

		if (!$table->load(array($this->codeField => $alias)))
		{
			return false;
		}

		$item = $this->getItem($table->id);

		if (!$item)
		{
			return false;
		}

		// Sets alias as a pseudo "code" field for web service purposes
		if (!isset($item->code))
		{
			$item->code = $item->{$this->codeField};
		}

		return $item;
	}

	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   Table  $table  A reference to a Table object.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function prepareTable($table)
	{
		if ($this->operationWS)
		{
			// Sets the table as stored from the web service to enable special operations in store function when needed
			$table->setOption('store.ws', true)
				->setOption('lockingMethod', 'Webservice');
		}
	}

	/**
	 * Get the associated JTable
	 *
	 * @param   string  $name    Table name
	 * @param   string  $prefix  Table prefix
	 * @param   array   $config  Configuration array
	 *
	 * @return  JTable
	 */
	public function getTable($name = null, $prefix = '', $config = array())
	{
		$table = parent::getTable($name, $prefix, $config);

		if ($this->operationWS)
		{
			$table->setOption('lockingMethod', 'Webservice');
		}

		return $table;
	}

	/**
	 * Method to get the url of an image by image name and section
	 *
	 * @param   string  $imageName  the name of the image
	 * @param   string  $section    the section the image is located in I.E. categories, tags, etc...
	 *
	 * @return string
	 */
	protected function getImageUrl($imageName, $section)
	{
		$increment  = RedshopbHelperMedia::getIncrementFromFilename($imageName);
		$folderName = RedshopbHelperMedia::getFolderName($increment);

		return Uri::root() . 'media/com_redshopb/images/originals/' . $section . '/' . $folderName . '/' . $imageName;
	}

	/**
	 * Method to get a single record using possible related data from the web service, without attaching any related data
	 *
	 * @param   string  $pk  The pk to be retrieved
	 *
	 * @return  false|object  Object on success, false on failure.
	 */
	public function getItemFromWSData($pk)
	{
		return static::getItemWS($pk, false);
	}

	/**
	 * Method to get a single record using possible related data from the web service and optionally adding related data to it
	 *
	 * @param   string  $pk              The pk to be retrieved
	 * @param   bool    $addRelatedData  Add the other related data fields from web service sync
	 *
	 * @return  false|object             Object on success, false on failure.
	 */
	public function getItemWS($pk, $addRelatedData = true)
	{
		// Sets a state variable to identify it's being loaded from the web service
		$this->getState();
		$this->setState('load.ws', true);

		$this->operationWS = true;

		$id    = null;
		$item  = null;
		$table = $this->getTable();
		$wsMap = $table->get('wsSyncMapPK');

		// If there is a dot in the given pk, then we are looking for related data
		if (strpos($pk, '.') !== false)
		{
			list($prefix, $syncId) = explode('.', $pk);

			// If there is possible sync related data it looks for alternatives
			if (count($wsMap) && isset($wsMap[$prefix]))
			{
				$searchRefs = $wsMap[$prefix];
				$syncHelper = new RedshopbHelperSync;

				// Looks for every possible reference depending on the prefix sent
				for ($i = 0; $i < count($searchRefs) && is_null($id); $i++)
				{
					$id = $syncHelper->findSyncedId($searchRefs[$i], $syncId);
				}
			}
		}
		else
		{
			$id = $pk;
		}

		$item    = $this->getItem($id);
		$pkField = $table->getKeyName();

		if (is_null($id)
			|| !$item
			|| !is_object($item)
			|| $item->{$pkField} != $id
			|| !RedshopbHelperWebservice_Permission::checkWSPermissionRestriction($id, $this)
			|| !RedshopbHelperWebservice_Permission::checkWSPermissionRestrictionRelations($item, $this))
		{
			return false;
		}

		$nestedTable = (stripos(get_parent_class($table), 'nested') === false ? false : true);

		/**
		 * @todo *bump*
		 * Prevent ROOT items from being exposed in the web services
		 * (disabled temporarily because it blocks create actions)
		 *
		 * if ($nestedTable && $item->level == 0)
		 * {
		 * return false;
		 * }
		 */

		// Fixes null dates
		$wsDateFix = $table->get('wsSyncMapDate', array());

		foreach ($wsDateFix as $field)
		{
			if ($item->{$field} == '0000-00-00 00:00:00' || $item->{$field} == '0000-00-00')
			{
				$item->{$field} = null;
			}
		}

		if (!$addRelatedData)
		{
			// Our work is done here
			return $item;
		}

		// Adds pk related data
		$this->addWSItemData($item, $pkField, $table->get('_tbl'), $pkField, $wsMap);
		$wsMapFields = $table->get('wsSyncMapFields', array());

		// Adds any other related fk field data with sync ref data to the query
		foreach ($wsMapFields as $mapField => $mapFieldData)
		{
			$fieldModelName = $mapFieldData['model'];

			// When on a nested table, if the object is in level 1, it omits the ROOT object, because it won't be displayed in WS data
			if ($nestedTable && $mapField == 'parent_id' && isset($item->level) && $item->level == 1)
			{
				$item->{$mapField} = null;

				continue;
			}

			/** @var RedshopbModelField $fieldModel */
			$fieldModel = RedshopbModel::getAdminInstance(RInflector::singularize($fieldModelName));
			$fieldTable = $fieldModel->getTable();
			$fieldModel->addWSItemData($item, $mapField, $fieldTable->get('_tbl'), $fieldTable->getKeyName(), $fieldTable->get('wsSyncMapPK'));
		}

		// Adds code-related models from fields
		$wsMapCodeFields = $table->get('wsSyncMapCodeFields', array());

		foreach ($wsMapCodeFields as $mapField => $fieldModelName)
		{
			// Gets the actual related field by substracting _code and adding _id
			$mapIdField = substr($mapField, 0, strlen($mapField) - 5) . '_id';

			if (empty($item->{$mapIdField}))
			{
				$item->{$mapField} = null;

				continue;
			}

			$fieldModel = RedshopbModel::getAdminInstance(RInflector::singularize($fieldModelName));
			$fieldTable = $fieldModel->getTable();

			if ($fieldTable->load($item->$mapIdField))
			{
				$item->$mapField = $fieldTable->{$fieldModel->get('codeField')};
			}
		}

		// Adds any other related fk field data with sync ref data to the query - multiple keys in array
		$wsMapFieldsMultiple = $table->get('wsSyncMapFieldsMultiple', array());

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
				$fieldModel->addWSItemData(
					$tempItem, $mapField, $fieldTable->get('_tbl'), $fieldTable->getKeyName(), $fieldTable->get('wsSyncMapPK')
				);

				if (is_array($tempItem->{$mapField . '_syncref'}) && count($tempItem->{$mapField . '_syncref'}))
				{
					$item->{$mapField . '_syncref'}[] = implode(',', $tempItem->{$mapField . '_syncref'});
				}
			}
		}

		return $item;
	}

	/**
	 * Add sync related data to a given item
	 *
	 * @param   object  $item     The item to be added data to
	 * @param   string  $field    Field name to map
	 * @param   string  $table    DB table name
	 * @param   string  $pkField  PK field of the given table
	 * @param   array   $wsMap    Web service mapping
	 *
	 * @return  void
	 */
	public function addWSItemData(&$item, $field, $table, $pkField, $wsMap)
	{
		$fieldName          = $field . '_syncref';
		$item->{$fieldName} = array();

		if (isset($item->{$field}) && $item->$field != '')
		{
			$item->{$fieldName}[] = $item->{$field};
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*')
			->from($db->qn($table, 'a'));

		switch ($field)
		{
			case 'author_employee_id':
				$query->where($db->qn('a.joomla_user_id') . ' = ' . $db->q($item->created_by));

				break;
			default:
				$query->where($db->qn('a.' . $pkField) . ' = ' . $db->q($item->$field));

				break;
		}

		RedshopbHelperWebservices::addWSDataQuery($query, array($pkField), array($wsMap), 'a');
		$db->setQuery($query);

		$itemTemp = $db->loadObject();

		if ($itemTemp)
		{
			RedshopbHelperWebservices::addWSItemData($itemTemp, $pkField, $wsMap);
			$item->{$fieldName} = $itemTemp->{$pkField . '_syncref'};

			return;
		}

		$item->{$fieldName} = null;
	}

	/**
	 * Validate incoming data from the web service
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  false|array  Array of validate data if success. False otherwise.
	 */
	public function validateWS($data)
	{
		$this->operationWS = true;

		$result = $this->triggerEvent('onBeforeValidateWS', array(&$data));

		if (count($result) && in_array(false, $result, true))
		{
			return false;
		}

		$form  = $this->getForm();
		$table = $this->getTable();

		// Maps boolean entries "true" and "false" to actual boolean vaues
		$booleanFields = $table->get('wsSyncMapBoolean', array());

		foreach ($booleanFields as $booleanField)
		{
			if (isset($data[$booleanField]))
			{
				$data[$booleanField] = ($data[$booleanField] == 'true' || $data[$booleanField] == '1' ? true : false);
			}
		}

		$fields = $form->getFieldset();

		// Fills up any unset values with the form default
		foreach ($fields as $field)
		{
			if ($field->__get('required', false) && !isset($data[$field->__get('fieldname')]))
			{
				$data[$field->__get('fieldname')] = $form->getFieldAttribute($field->__get('fieldname'), 'default');
			}
		}

		// Matches related data from from related models using their "codes"
		$wsMapCodeFields = $table->get('wsSyncMapCodeFields', array());

		foreach ($wsMapCodeFields as $mapField => $fieldModelName)
		{
			if (!isset($data[$mapField]))
			{
				continue;
			}

			$fieldModel = RedshopbModel::getAdminInstance(RInflector::singularize($fieldModelName));
			$fieldTable = $fieldModel->getTable();

			// Gets the actual related field by substracting _code and adding _id
			$mapIdField = substr($mapField, 0, strlen($mapField) - 5) . '_id';

			if ($data[$mapField] == 'null' || empty($data[$mapField]))
			{
				$data[$mapIdField] = '';
			}
			elseif ($fieldTable->load(array($fieldModel->get('codeField') => $data[$mapField])))
			{
				$data[$mapIdField] = $fieldTable->{$fieldTable->getKeyName()};
			}
			else
			{
				Factory::getApplication()->enqueueMessage(
					Text::sprintf('COM_REDSHOPB_WEBSERVICE_ID_NOT_FOUND', $mapField, $data[$mapField]),
					'error'
				);

				return false;
			}
		}

		// Matches related data from related web services to extract the internal IDs when needed
		$wsMapFields = $table->get('wsSyncMapFields', array());

		foreach ($wsMapFields as $mapField => $mapFieldData)
		{
			$fieldModelName = $mapFieldData['model'];

			if (!$this->findRealItemId($data, $mapField, $fieldModelName))
			{
				return false;
			}
		}

		// Matches related data from related web services to extract the internal multiple IDs when needed
		$wsMapFieldsMultiple = $table->get('wsSyncMapFieldsMultiple', array());

		foreach ($wsMapFieldsMultiple as $mapField => $fieldModelName)
		{
			if (!$this->findRealItemId($data, $mapField, $fieldModelName, true))
			{
				return false;
			}
		}

		if (!$this->validate($form, $data))
		{
			return false;
		}

		if (isset($data['image']))
		{
			// Creates an temp image so it can be saved by the model, only if !empty($data[image])
			$data = RedshopbHelperWebservices::getTempImageURL($data);
		}

		$result = $this->triggerEvent('onAfterValidateWS', array(&$data));

		if (count($result) && in_array(false, $result, true))
		{
			return false;
		}

		return $data;
	}

	/**
	 * Find real Item Id
	 *
	 * @param   array   $data             Data
	 * @param   string  $mapField         Map Field
	 * @param   string  $fieldModelName   Field model name
	 * @param   bool    $isMultipleField  It is multiple field
	 *
	 * @return  boolean
	 */
	public function findRealItemId(&$data, $mapField, $fieldModelName, $isMultipleField = false)
	{
		if (isset($data[$mapField]) && $data[$mapField] == 'null')
		{
			$data[$mapField] = '';
		}

		if (isset($data[$mapField]) && !is_null($data[$mapField]) && $data[$mapField] != '')
		{
			$fieldModel = RedshopbModel::getAdminInstance(RInflector::singularize($fieldModelName));
			$fieldTable = $fieldModel->getTable();
			$fieldPK    = $fieldTable->getKeyName();

			if ($isMultipleField)
			{
				foreach ($data[$mapField] as $i => $fieldData)
				{
					$fieldItem = $fieldModel->getItemFromWSData($fieldData);

					if (!$fieldItem)
					{
						Factory::getApplication()->enqueueMessage(
							Text::sprintf('COM_REDSHOPB_WEBSERVICE_ID_NOT_FOUND', $mapField, $fieldData),
							'error'
						);

						return false;
					}

					$data[$mapField][$i] = $fieldItem->$fieldPK;
				}
			}
			else
			{
				$fieldItem = $fieldModel->getItemFromWSData($data[$mapField]);

				if (!$fieldItem)
				{
					Factory::getApplication()->enqueueMessage(
						Text::sprintf('COM_REDSHOPB_WEBSERVICE_ID_NOT_FOUND', $mapField, $data[$mapField]),
						'error'
					);

					return false;
				}

				$data[$mapField] = $fieldItem->$fieldPK;
			}
		}

		return true;
	}

	/**
	 * Validate incoming data from the web service for creation
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  false|array
	 */
	public function validateCreateWS($data)
	{
		$this->operationWS = true;

		$table   = $this->getTable();
		$pkField = $table->getKeyName();
		$pkVal   = null;

		if (isset($data[$pkField]))
		{
			// Temporarily stores sent PK to avoid validating it with the other fields, so the validate() function can distinguish update/create
			$pkVal = $data[$pkField];

			if (strpos($pkVal, '.'))
			{
				Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_WEBSERVICE_ID_DOTS_NOT_ALLOWED'), 'error');

				return false;
			}

			unset($data[$pkField]);
		}

		$data = $this->validateWS($data);

		if (!$data)
		{
			return false;
		}

		if (!is_null($pkVal))
		{
			$data[$pkField] = $pkVal;
		}

		// Duplicate validation
		$wsMap   = $table->get('wsSyncMapPK');
		$syncRef = '';

		if (isset($wsMap['erp']) && isset($wsMap['erp'][0]))
		{
			// Extracts the id sent by the web service from the data array and submits the data to create the item
			$syncRef = $wsMap['erp'][0];
		}

		// Checks if the web service id is already used (when the related table uses erp/ws sync)
		if ($syncRef != '' && isset($data[$pkField]) && $data[$pkField] != '')
		{
			$syncHelper = new RedshopbHelperSync;

			if ($syncHelper->findSyncedId($syncRef, $data[$pkField]))
			{
				Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_WEBSERVICE_ID_EXISTS'), 'error');

				return false;
			}
		}

		return $data;
	}

	/**
	 * Validate incoming data from the update web service - maps non-incoming data to avoid problems with actual validation
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @throws Exception   If Factory::getApplication() fails
	 *
	 * @return  array|boolean
	 */
	public function validateUpdateWS($data)
	{
		$this->operationWS = true;

		$table   = $this->getTable();
		$pkField = $table->getKeyName();

		$item = $this->getItemWS($data[$pkField]);

		// Tries to load the item to make sure it exist
		if ($item === false)
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_REDSHOPB_WEBSERVICE_RECORD_NOT_FOUND', $data[$pkField]), 'error');

			return false;
		}

		// Sets the right id in case it's using an external one
		$data[$pkField] = $item->$pkField;

		// Checks if the web service id is already used (when the related table uses erp/ws sync)
		if (isset($data['erp_id']) && $data['erp_id'] != '')
		{
			if (strpos($data['erp_id'], '.'))
			{
				Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_WEBSERVICE_ID_DOTS_NOT_ALLOWED'), 'error');

				return false;
			}

			$wsMap = $table->get('wsSyncMapPK');

			if (isset($wsMap['erp']) && isset($wsMap['erp'][0]))
			{
				// Extracts the id sent by the web service from the data array and submits the data to create the item
				$syncRef = $wsMap['erp'][0];

				if ($syncRef != '')
				{
					$syncHelper = new RedshopbHelperSync;
					$currentId  = $syncHelper->findSyncedId($syncRef, $data['erp_id']);

					if (!is_null($currentId) && $data[$pkField] != $currentId)
					{
						Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_WEBSERVICE_ID_EXISTS'), 'error');

						return false;
					}
				}
			}
		}

		// Fills up every other data from the loaded item - except for images to avoid problems saving them
		foreach ($data as $field => $fielddata)
		{
			if (is_null($fielddata) && isset($item->$field) && $field != 'image')
			{
				$data[$field] = $item->$field;
			}
		}

		// Fills up every other data from the loaded item - except for images to avoid problems saving them
		foreach ($item as $field => $fieldData)
		{
			if ((!isset($data[$field]) || is_null($data[$field]))
				&& $field != 'image' && !is_object($fieldData) && !is_array($fieldData) && !is_null($fieldData))
			{
				$data[$field] = $fieldData;
			}
		}

		return $this->validateWS($data);
	}

	/**
	 * Validate incoming data for some web service task requiring id - it transformates external to internal ids as well
	 *
	 * @param   array  $data  Web service data
	 *
	 * @return  false|array
	 */
	public function validatePkWS($data)
	{
		$this->operationWS = true;

		$table   = $this->getTable();
		$pkField = $table->getKeyName();

		$item = $this->getItemFromWSData($data[$pkField]);

		// Tries to load the item to make sure it exist
		if ($item === false)
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_REDSHOPB_WEBSERVICE_RECORD_NOT_FOUND', $data[$pkField]), 'error');

			return false;
		}

		// Overwrites the pk to ensure it's the internal one and not the given one (it can be a related one from sync)
		$data[$pkField] = $item->$pkField;

		// Gets the task name to allow custom validations
		$task = isset($data['task']) ? $data['task'] : RApiHalHelper::getTask();

		// Calls a custom validation for the specific task
		if ($task != '' && method_exists($this, 'validate' . $task . 'WS'))
		{
			return $this->{'validate' . $task . 'WS'}($data);
		}

		return $data;
	}

	/**
	 * Validate incoming data from the translate/translateRemove web service tasks
	 *
	 * @param   array  $data  Data to be stored (id, language code, extra data)
	 *
	 * @return  false|array
	 */
	public function validateTranslateWS($data)
	{
		$this->operationWS = true;

		$table   = $this->getTable();
		$pkField = $table->getKeyName();

		$item = $this->getItemFromWSData($data[$pkField]);

		// Tries to load the item to make sure it exist
		if ($item === false)
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_REDSHOPB_WEBSERVICE_RECORD_NOT_FOUND', $data[$pkField]), 'error');

			return false;
		}

		// Prepares the clean array
		$finalData = array();

		// Validates language code
		$languageTable = Table::getInstance('Language');

		if (!$languageTable->load(array('lang_code' => $data['language'])))
		{
			Factory::getApplication()->enqueueMessage(
				Text::sprintf('COM_REDSHOPB_WEBSERVICE_TRANSLATION_LANGUAGE_NOT_FOUND', $data['lang_code']),
				'error'
			);

			return false;
		}

		$task = isset($data['task']) ? $data['task'] : RApiHalHelper::getTask();

		// If it's translating, cleans up the data according to the translation xml file
		if ($task == 'translate')
		{
			$tableName         = $table->get('_tbl');
			$translationTables = RTranslationHelper::getInstalledTranslationTables();

			// Check existing translate table
			if (!isset($translationTables[$tableName]))
			{
				return false;
			}

			foreach ($translationTables[$tableName]->columns as $field)
			{
				if (isset($data[$field]))
				{
					$finalData[$field] = $data[$field];
				}
			}
		}

		// Overwrites the pk to ensure it's the internal one and not the given one (it can be a related one from sync)
		$finalData[$pkField]   = $item->$pkField;
		$finalData['language'] = $data['language'];

		return $finalData;
	}

	/**
	 * Create a new item from the web service - storing the related sync id
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  integer|false
	 */
	public function createWS($data)
	{
		$this->operationWS = true;

		$table       = $this->getTable();
		$nestedTable = (stripos(get_parent_class($table), 'nested') === false ? false : true);
		$wsMap       = $table->get('wsSyncMapPK');
		$syncRef     = '';
		$syncHelper  = null;
		$wsPK        = '';

		if (isset($wsMap['erp']) && isset($wsMap['erp'][0]))
		{
			// Extracts the id sent by the web service from the data array and submits the data to create the item
			$syncRef = $wsMap['erp'][0];
			$pkField = $table->getKeyName();

			if (isset($data[$pkField]))
			{
				$wsPK = $data[$pkField];
				unset($data[$pkField]);
			}
		}

		// Sets the right parent id when dealing with a nested table, to avoid ROOT objects
		if ($nestedTable)
		{
			if ((isset($data['parent_id']) && (is_null($data['parent_id']) || $data['parent_id'] == '' || $data['parent_id'] == 'null'))
				|| !isset($data['parent_id']))
			{
				$data['parent_id'] = 1;
			}
		}

		// Unsets id to avoid saving the item with the ERP id and overwrite our records
		$this->getState();
		$this->setState($this->getName() . '.id', null);

		try
		{
			if (!$this->save($data))
			{
				return false;
			}

			$pk = $this->getState($this->getName() . '.id');

			if ($pk && $syncRef != '' && $wsPK != '')
			{
				if (is_null($syncHelper))
				{
					$syncHelper = new RedshopbHelperSync;
				}

				// We treat all data as overrides since it is new item and all data is locked by it
				$tableProperties = call_user_func('get_object_vars', $table);
				$properties      = array('changedProperties' => array());

				foreach ($tableProperties as $propertyName => $propertyValue)
				{
					if (array_key_exists($propertyName, $data) && $data[$propertyName] != '')
					{
						$properties["changedProperties"][$propertyName] = $data[$propertyName];
					}
				}

				$table->set('changedProperties', $properties["changedProperties"]);

				// If there is sync associated data set for the table, it stores the association in the sync table
				$wsMap   = $table->get('wsSyncMapPK');
				$syncRef = $wsMap['erp'][0];
				$syncHelper->recordSyncedId(
					$syncRef, $wsPK, $pk, '', true, 0, '', false, '', $table, 1
				);
			}

			return $pk;
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * Update an item from the web service - storing the related sync id
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  integer|false
	 */
	public function updateWS($data)
	{
		$this->operationWS = true;

		$table       = $this->getTable();
		$pkField     = $table->getKeyName();
		$nestedTable = (stripos(get_parent_class($table), 'nested') === false ? false : true);
		$table->setOption('lockingMethod', 'Webservice');

		// Sets the right parent id when dealing with a nested table, to avoid ROOT objects
		if ($nestedTable)
		{
			if ((isset($data['parent_id']) && (is_null($data['parent_id']) || $data['parent_id'] == '' || $data['parent_id'] == 'null'))
				|| !isset($data['parent_id']))
			{
				$data['parent_id'] = 1;
			}
		}

		try
		{
			if (!$this->save($data))
			{
				return false;
			}

			$this->updateERPId($data['erp_id'], $data[$pkField]);
		}
		catch (Exception $e)
		{
			return false;
		}

		return $data[$pkField];
	}

	/**
	 * Updates the ERP id
	 *
	 * @param   string  $erpId   ERP id to associate
	 * @param   string  $id      Local id
	 *
	 * @return  integer|false
	 */
	public function updateERPId($erpId, $id)
	{
		$table = $this->getTable();
		$wsMap = $table->get('wsSyncMapPK');

		// Updates ERP id if it's set
		if ($id && isset($erpId) && $erpId != '' && isset($wsMap['erp']) && isset($wsMap['erp'][0]))
		{
			$syncRef = $wsMap['erp'][0];

			if ($syncRef != '')
			{
				$syncHelper = new RedshopbHelperSync;
				$syncData   = $syncHelper->findSyncedLocalId($syncRef, $id, true);

				if ($syncData)
				{
					$syncHelper->deleteSyncedId($syncRef, $syncData->remote_key);
				}

				if ($erpId != 'null')
				{
					$mainReference = (isset($syncData->main_reference) ? $syncData->main_reference : null);

					if (is_null($mainReference))
					{
						$mainReference = 0;
						$wsRefs        = array();

						foreach ($wsMap as $wsSyncMapFieldsRefs)
						{
							$wsRefs = array_merge($wsRefs, $wsSyncMapFieldsRefs);
						}

						if (!empty($wsRefs))
						{
							$mainReference = !$syncHelper->mainReferenceExists($wsRefs, $syncData->remote_key) ? 1 : 0;
						}
					}

					$syncHelper->recordSyncedId(
						$syncRef, $erpId, $id, '', true, 0, '', false, '', $table, $mainReference
					);

					return $id;
				}
			}
		}

		return false;
	}

	/**
	 * Delete an item from the web service - storing the related sync id
	 *
	 * @param   mixed  $pk  PK to be found to delete (internal id)
	 *
	 * @return  boolean
	 */
	public function deleteWS($pk)
	{
		$this->operationWS = true;

		try
		{
			$result = $this->delete($pk);

			if (!$result)
			{
				return false;
			}
		}
		catch (Exception $e)
		{
			return false;
		}

		return $result;
	}

	/**
	 * Publish an item via web service
	 *
	 * @param   mixed  $pk  PK to be found to publish (internal id)
	 *
	 * @return  integer|false
	 */
	public function publishWS($pk)
	{
		$this->operationWS = true;

		try
		{
			$pks = array($pk);
			$this->publish($pks, 1);
		}
		catch (Exception $e)
		{
			return false;
		}

		return $pk;
	}

	/**
	 * Unpublish an item via web service
	 *
	 * @param   mixed  $pk  PK to be found to publish (internal id)
	 *
	 * @return  integer|false
	 */
	public function unpublishWS($pk)
	{
		$this->operationWS = true;

		try
		{
			$pks = array($pk);
			$this->publish($pks, 0);
		}
		catch (Exception $e)
		{
			return false;
		}

		return $pk;
	}

	/**
	 * Create translation for an item
	 *
	 * @param   array  $data  Translation data (pk, language, extra data)
	 *
	 * @return  integer|false
	 */
	public function translate($data)
	{
		$table     = $this->getTable();
		$tableName = $table->get('_tbl');
		$pkField   = $table->getKeyName();
		$table->load($data[$pkField]);

		$translationTables = RTranslationHelper::getInstalledTranslationTables();
		$translationTable  = $translationTables[$tableName];

		$language = $data['language'];
		unset($data['language']);

		// Unsets variables with empty strings
		foreach ($data as $var => $value)
		{
			if ($value == '')
			{
				unset($data[$var]);
			}
		}

		try
		{
			if (RedshopbHelperTranslations::storeTranslation(
				$translationTable,
				$table,
				$language,
				$data
			))
			{
				return $table->$pkField;
			}
		}
		catch (Exception $e)
		{
			return false;
		}

		return false;
	}

	/**
	 * Remove translation for an item
	 *
	 * @param   array  $data  Translation data (pk, language)
	 *
	 * @return  integer|false
	 */
	public function translateRemove($data)
	{
		$table     = $this->getTable();
		$tableName = $table->get('_tbl');
		$pkField   = $table->getKeyName();

		$db         = Factory::getDbo();
		$query      = $db->getQuery(true);
		$conditions = array(
			$db->qn($pkField) . ' = ' . $db->q($data[$pkField]),
			$db->qn('rctranslations_language') . ' = ' . $db->q($data['language'])
		);

		$query->delete($db->quoteName($tableName . '_rctranslations'));
		$query->where($conditions);
		$db->setQuery($query);

		if (!$db->execute())
		{
			return false;
		}

		return $data[$pkField];
	}

	/**
	 * Method for upload image to an item
	 *
	 * @param   int     $pk                Primary key
	 * @param   string  $imageData         Image data (base64 encoded) encoded by Base64
	 * @param   string  $fieldName         Name of the expected input field with the image
	 * @param   bool    $simulateMultiple  Simulates a multiple file input array
	 *
	 * @return  false|integer              Record ID if success. False otherwise.
	 */
	public function imageUpload($pk, $imageData, $fieldName = 'imageFileUpload', $simulateMultiple = false)
	{
		// This method is only used in Webservice so we will set it like that
		$this->operationWS = true;

		$table   = $this->getTable();
		$pkField = $table->getKeyName();

		$imageData = base64_decode($imageData);
		$mimeType  = RedshopbHelperMedia::getMimeType($imageData);

		$split = explode('/', $mimeType);

		if (!isset($split[1]))
		{
			Factory::getApplication()->enqueueMessage(
				Text::sprintf('COM_REDSHOPB_WEBSERVICE_IMAGE_TYPE_ERROR', $mimeType),
				'error'
			);

			return false;
		}

		$imageName = md5(time()) . '.' . $split[1];
		$imagePath = RedshopbHelperWebservices::saveTempImage($imageData, $imageName);
		$data      = array(
			$pkField => $pk,
			'image_file' => array(
				$fieldName => array (
					'name'     => $imageName,
					'tmp_name' => $imagePath
				)
			)
		);

		if ($simulateMultiple)
		{
			$data['image_file'] = array(
				$fieldName => array (
					array (
						'name' => $imageName,
						'tmp_name' => $imagePath
					)
				)
			);
		}

		if (!$this->save($data))
		{
			return false;
		}

		return $pk;
	}

	/**
	 * Method for remove an image from an item
	 *
	 * @param   int  $pk  Primary key
	 *
	 * @return  integer|false
	 */
	public function imageRemove($pk)
	{
		// This method is only used in Webservice so we will set it like that
		$this->operationWS = true;

		$table   = $this->getTable();
		$pkField = $table->getKeyName();

		$data = array(
			$pkField => $pk,
			'deleteImage' => 1
		);

		if (!$this->save($data))
		{
			return false;
		}

		return $pk;
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
	protected function saveImageFile(Table $table, $data, $section)
	{
		// Image loading and thumbnail creation from web service file
		$imageFile = $data['image_file'];
		$hasImage  = !empty($imageFile['imageFileUpload']['name']);

		if ($hasImage)
		{
			$data['deleteImage'] = 1;
		}

		// Delete old if exists
		if (!$this->deleteOldImages($table, $data, $section))
		{
			return false;
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

		$table->set('image', $imageFileName);

		if (!$table->store())
		{
			$this->setError($table->getError());

			return false;
		}

		return true;
	}

	/**
	 * Method to delete old image files from both DB/filesystem
	 *
	 * @param   Table   $table    the category table
	 * @param   array   $data     the data being saved
	 * @param   string  $section  the section being saved
	 *
	 * @return boolean
	 */
	protected function deleteOldImages(Table $table, $data, $section)
	{
		if (empty($table->image)
			|| empty($data['deleteImage'])
			|| $data['deleteImage'] != 1)
		{
			return true;
		}

		RedshopbHelperThumbnail::deleteImage($table->get('image'), 1, $section);
		$table->set('image', '');

		return (bool) $table->store();
	}

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
		RedshopbForm::addFormPath(JPATH_COMPONENT . '/models/forms');
		RedshopbForm::addFieldPath(JPATH_COMPONENT . '/models/fields');

		// Add form path from MVC override plugin
		if (method_exists('RModelAdminBase', 'addComponentFormPath'))
		{
			RedshopbForm::addFormPath(self::addComponentFormPath());
		}

		// Add field path from MVC override plugin
		if (method_exists('RModelAdminBase', 'addComponentFieldPath'))
		{
			RedshopbForm::addFieldPath(self::addComponentFieldPath());
		}

		try
		{
			$form = RedshopbForm::getInstance($name, $source, $options, false, $xpath);

			try
			{
				// Not always Table exist, then just ignore it
				$form->table = $this->getTable();
			}
			catch (Throwable $e)
			{
			}

			if (isset($options['load_data']) && $options['load_data'])
			{
				// Get the data for the form.
				$data = $this->loadFormData();
			}
			else
			{
				$data = array();
			}

			$this->changeForm($form, $data, $options);

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
	 * Here we can change form or data
	 *
	 * @param   Form    $form      A Form object.
	 * @param   mixed   $data      The data expected for the form.
	 * @param   array   $options   Optional array of options for the form creation.
	 * @param   string  $group     The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 */
	protected function changeForm(Form &$form, &$data, &$options = array(), $group = 'content')
	{
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  obect|array|boolean  The default data is an empty array.
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

		return $data;
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
		$item = parent::getItem($pk);

		// We can add additional fields through plugin if needed
		RFactory::getDispatcher()->trigger('onRedshopbModelAdminAfterGetItem', array($this, &$item));

		$pkField = $this->getTable()->getKeyName();

		if (!$item->$pkField)
		{
			return $item;
		}

		// Adds related ids when plugins have the option
		$syncReference = RedshopbHelperSync::getEnrichmentBase($this);

		if ($syncReference == '')
		{
			return $item;
		}

		$sync                  = new RedshopbHelperSync;
		$item->sync_related_id = $sync->findSyncedLocalId($syncReference, $item->$pkField);

		return $item;
	}

	/**
	 * Method to save the sync related id when it applies
	 *
	 * @param   array  $data  Data containing pk and related id
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @TODO: add support for remote_parent_key and metadata fields, including the distinction between references using them and the ones that don't
	 */
	public function saveSyncRelatedId($data)
	{
		$syncReference = RedshopbHelperSync::getEnrichmentBase($this);

		if ($syncReference == '')
		{
			return true;
		}

		if (!isset($data['sync_related_id']))
		{
			return true;
		}

		$sync      = new RedshopbHelperSync;
		$pk        = $this->getState($this->getName() . '.id');
		$remoteKey = trim($data['sync_related_id']);

		if (empty($remoteKey))
		{
			return $sync->deleteSyncedLocalId($syncReference, $pk);
		}

		$localId = $sync->findSyncedId($syncReference, $remoteKey);

		if ($localId && $localId != $pk)
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_REDSHOPB_SYNC_RELATED_ID_IN_USE', $this->getName(), $localId), 'warning');

			return true;
		}

		$syncRemote = $sync->findSyncedLocalId($syncReference, $pk);

		if ($syncRemote != $remoteKey)
		{
			$table = $this->getTable();

			if (!$table->load($pk))
			{
				return false;
			}

			if (!$sync->deleteAllReferences($table))
			{
				return false;
			}
		}
		elseif ($syncRemote)
		{
			$sync->deleteSyncedId($syncReference, $remoteKey);
		}

		if ($pk)
		{
			return $sync->recordSyncedId($syncReference, $remoteKey, $pk);
		}

		return true;
	}

	/**
	 * Import plugins types.
	 *
	 * @return  void
	 */
	private function importPluginTypes()
	{
		foreach ($this->basePluginTypes as $type)
		{
			PluginHelper::importPlugin($type);
		}

		// Allow that classes using this variables include their own plugin types
		if (property_exists($this, 'pluginTypesToImport'))
		{
			foreach ($this->pluginTypesToImport as $type)
			{
				PluginHelper::importPlugin($type);
			}
		}
	}

	/**
	 * Trigger an event.
	 *
	 * @param   string  $eventName  Name of the event to trigger
	 * @param   array   $params     Arguments for the event being triggered
	 *
	 * @return  array
	 */
	protected function triggerEvent($eventName, $params = array())
	{
		// Import the plugin types
		$this->importPluginTypes();

		// First param will be always this class
		array_unshift($params, $this);

		// Trigger the event
		return RFactory::getDispatcher()->trigger($eventName, $params);
	}

	/**
	 * Get the action used by the webservice call, e.g. create, update, delete etc.
	 *
	 * @return   string|null
	 */
	protected function getWSAction()
	{
		$headers     = RApi::getHeaderVariablesFromGlobals();
		$contentType = end(explode(';', $headers['CONTENT_TYPE']));

		preg_match("#[\"](.*?)[\"]#", $contentType, $match);

		$action = end($match);

		if (!$action)
		{
			return null;
		}

		return $action;
	}
}
