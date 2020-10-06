<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;
jimport('models.trait.customfields', JPATH_ROOT . '/components/com_redshopb/');

use Joomla\CMS\Object\CMSObject;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\Registry\Registry;
use Joomla\CMS\Plugin\PluginHelper;
/**
 * Company Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelCompany extends RedshopbModelAdmin
{
	use RedshopbModelsTraitCustomFields;

	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'company';

	/**
	 * Constructor.
	 *
	 * @param   array  $config  Configuration array
	 *
	 * @throws  RuntimeException
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->setScope('company');
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 */
	public function saveContactInfo($data)
	{
		$table = $this->getTable()->setOption('forceWebserviceUpdate', true);

		$pk    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		// Load the row if saving an existing company.
		if ($table->load($pk))
		{
			$isNew = false;
		}

		// Change and store only Contact info
		$newData = array('contact_info' => $data['contact_info']);

		// Bind the data.
		if (!$table->bind($newData))
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

		$this->setState($this->getName() . '.id', $table->id);

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 * @throws Exception
	 */
	public function save($data)
	{
		/** @var RedshopbTableCompany $table */

		// True in this case implies that the WS sync table won't get updated.
		// See administrator/tables/company.php's store()
		$table = $this->getTable()->setOption('store.ws', true);

		$pk    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		// Load the row if saving an existing company.
		if ($table->load($pk))
		{
			$isNew = false;
		}

		// Process saved ruleset before table saving (as it could fail if locked by erp)
		if ($table->asset_id && isset($data['acl_ruleset']))
		{
			$res = true;

			foreach ($data['acl_ruleset'] as $roleId => $rulesAccess)
			{
				foreach ($rulesAccess as $accessId => $roleAccess)
				{
					$permission = ($roleAccess == '' ? null : $roleAccess);
					$res        = $res && RedshopbHelperACL::grantSimpleACLRule($accessId, $roleId, $table->asset_id, $permission);
				}
			}

			if ($res)
			{
				Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_COMPANY_PERMISSIONS_UPDATED'));
			}
		}

		// Set the new parent id if parent id not matched OR while New/Save as Copy .
		if ($table->parent_id != $data['parent_id'] || $data['id'] == 0)
		{
			$table->setLocation($data['parent_id'], 'last-child');
		}

		// When price_group_ids_exists is set, then it checks if the price_group_ids field is set, to unset it when sent null
		if (!isset($data['price_group_ids_exists']) && isset($data['price_group_ids_exists']))
		{
			$data['price_group_ids'] = array();
		}

		// When customer_discount_ids_exists is set, then it checks if the customer_discount_ids field is set, to unset it when sent null
		if (!isset($data['customer_discount_ids']) && isset($data['customer_discount_ids_exists']))
		{
			$data['customer_discount_ids'] = array();
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

		// Rebuild the path for the company
		if (!$table->rebuildPath($table->id)
			|| !$table->rebuild($table->id, $table->lft, $table->level, $table->path))
		{
			$this->setError($table->getError());

			return false;
		}

		$this->setState($this->getName() . '.id', $table->id);

		if ($pk)
		{
			// Store extra fields data if available.
			if (!is_null($data['extrafields']) && is_array($data['extrafields']))
			{
				if (!RedshopbHelperField::storeScopeFieldData(
					'company', $pk, 0, $data['extrafields'], true, $table->getOption('lockingMethod', 'User')
				))
				{
					Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_FIELDS_SAVE_FAILURE'), 'error');
				}
			}
		}

		// Image loading and thumbnail creation from web service file
		if (!$this->saveImageFile($table, $data, 'companies'))
		{
			return false;
		}

		// Clear the cache
		$this->cleanCache();

		RedshopbHelperACL::resetSessionLists();

		// Change each company user language
		RedshopbHelperCompany::setUsersLanguage($table->id, $table->site_language);

		return true;
	}

	/**
	 * Create image path for company if it doesn't exist
	 *
	 * @param   object  $row  data
	 *
	 * @return boolean
	 */
	private function initImagePath($row)
	{
		return RedshopbEntityCompany::getInstance($row->id)->getImageFolder() ? true : false;
	}

	/**
	 * Method to save permissions from a data form
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 */
	public function savePermissions($data)
	{
		/** @var RedshopbTableCompany $table */
		$table = $this->getTable();
		$pk    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');

		if (!$table->load($pk))
		{
			return false;
		}

		// Process saved ruleset before table saving (as it could fail if locked by erp)
		if ($table->asset_id && isset($data['acl_ruleset']))
		{
			$res = true;

			foreach ($data['acl_ruleset'] as $roleId => $rulesAccess)
			{
				foreach ($rulesAccess as $accessId => $roleAccess)
				{
					$permission = ($roleAccess == '' ? null : $roleAccess);
					$res        = $res && RedshopbHelperACL::grantSimpleACLRule($accessId, $roleId, $table->asset_id, $permission);
				}
			}

			if ($res)
			{
				Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_COMPANY_PERMISSIONS_UPDATED'));
			}
		}

		RedshopbHelperACL::resetSessionLists();

		return true;
	}

	/**
	 * Method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form|false  A Form object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = parent::getForm($data, $loadData);

		if (!$form)
		{
			return false;
		}

		$app = Factory::getApplication();
		$this->addExtraFields($form);

		if (!$form->getValue('parent_id'))
		{
			$form->setValue('parent_id', null, $app->getUserState('company.parent_id', ''));
		}

		return $form;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed         Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$pk = !empty($pk) ? $pk : (int) $this->getState($this->getName() . '.id');

		$table = $this->getTable();

		if (!$table->load($pk))
		{
			if ($table->getError())
			{
				$this->setError($table->getError());

				return false;
			}
		}

		// Convert to the CMSObject before adding other data.
		$properties = $table->getProperties(1);

		// Unset protected "_errors" for prevent PHP Fatal Error access protected variable
		if (isset($properties['propertiesAfterLoad']['_errors']))
		{
			unset($properties['propertiesAfterLoad']['_errors']);
		}

		$item = ArrayHelper::toObject($properties, CMSObject::class);

		// This is needed because toObject will transform
		// The price_group_ids ids array to an object.
		$item->price_group_ids = array();

		if (!empty($properties['price_group_ids']))
		{
			$item->price_group_ids = $properties['price_group_ids'];
		}

		// The customer_discount_ids ids array to an object.
		$item->customer_discount_ids = array();

		if (!empty($properties['customer_discount_ids']))
		{
			$item->customer_discount_ids = $properties['customer_discount_ids'];
		}

		$item->delivery_addresses = array();

		if (!empty($properties['delivery_addresses']))
		{
			$item->delivery_addresses = $properties['delivery_addresses'];
		}

		$item->sales_persons = array();

		if (!empty($properties['sales_persons']))
		{
			$item->sales_persons = $properties['sales_persons'];
		}

		if (property_exists($item, 'params'))
		{
			$registry = new Registry;
			$registry->loadString($item->params);
			$item->params = $registry->toArray();
		}

		$this->attachExtraFields($item);

		// Gets the image URL
		if (empty($item->id) || empty($item->image))
		{
			return $item;
		}

		$item->imageurl = $this->getImageUrl($item->image, 'companies');

		return $item;
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
	 * @return  false|array          Array of filtered data if valid, false otherwise.
	 */
	public function validate($form, $data, $group = null)
	{
		if (RedshopbApp::getConfig()->get('set_webservices', 0))
		{
			$form->setFieldAttribute('customer_number', 'required', 'false');
		}

		if (isset($data['id']) || isset($data['parent_id']))
		{
			$table = $this->getTable();

			// Looks for the parent id to determine the current company level and proper validations
			if (isset($data['parent_id']))
			{
				$parentId = $data['parent_id'];
			}
			else
			{
				if (!$table->load($data['id']))
				{
					Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_COMPANY_NO_ID_FOUND'), 'error');

					return false;
				}

				$parentId = $table->parent_id;
			}

			$table->id = null;
			$table->reset();

			if (!$table->load($parentId))
			{
				Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_COMPANY_NO_PARENT_ID'), 'error');

				return false;
			}

			// Forces company type according to the parent label - ignores view input to avoid it from being manipulated
			switch ($table->level)
			{
				case 0:
					$data['type'] = 'main';
					break;
				case 1:
					$data['type'] = 'customer';
					break;
				default:
					$data['type'] = 'end_customer';
			}
		}

		if ($data['type'] != 'end_customer')
		{
			$form->setFieldAttribute('address', 'required', 'true');
			$form->setFieldAttribute('city', 'required', 'true');
			$form->setFieldAttribute('zip', 'required', 'true');
			$form->setFieldAttribute('country_id', 'required', 'true');
		}
		else
		{
			$form->setFieldAttribute('address', 'required', 'false');
			$form->setFieldAttribute('city', 'required', 'false');
			$form->setFieldAttribute('zip', 'required', 'false');
			$form->setFieldAttribute('country_id', 'required', 'false');
		}

		if (isset($data['show_stock_as']))
		{
			switch ($data['show_stock_as'])
			{
				default:
					Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_COMPANY_INVALID_STOCK_INFO'), 'error');

					return false;
				case 'actual_stock':
				case 'color_codes':
				case 'hide':
				case 'not_set':
					break;
			}
		}

		// Validate custom fields
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
	 * @return  false|array   Array of data if success. False otherwise.
	 */
	public function validateWS($data)
	{
		// Sets the right address fields
		if (isset($data['address_line1']))
		{
			$data['address'] = $data['address_line1'];
		}

		if (isset($data['address_line2']))
		{
			$data['address2'] = $data['address_line2'];
		}

		if (isset($data['address_name1']))
		{
			$data['address_name'] = $data['address_name1'];
		}

		if (isset($data['language_code']))
		{
			$data['site_language'] = $data['language_code'];
		}

		if (isset($data['customer_price_groups']))
		{
			$data['price_group_ids'] = $data['customer_price_groups'];
		}

		if (isset($data['customer_discount_groups']))
		{
			$data['customer_discount_ids'] = $data['customer_discount_groups'];
		}

		$data = parent::validateWS($data);

		if (empty($data))
		{
			return false;
		}

		$this->verifyNotDeleted($data);
		$this->verifyB2C($data);

		return $data;
	}

	/**
	 * Method to insure that the record being altered has not been deleted
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return boolean
	 *
	 * @throws ErrorException
	 */
	private function verifyNotDeleted($data)
	{
		// If we don't have an id, then we don't need to worry about it
		if (empty($data['id']))
		{
			return true;
		}

		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(id)')
			->from($db->qn('#__redshopb_company'))
			->where($db->qn('id') . ' = ' . (int) $data['id'])
			->where($db->qn('deleted') . ' = 0');
		$result = $db->setQuery($query)->loadResult();

		if (empty($result))
		{
			throw new ErrorException(Text::sprintf('COM_REDSHOPB_WEBSERVICE_RECORD_NOT_FOUND', $data["id"]), 404);
		}

		return true;
	}

	/**
	 * Method to insure only b2c companies can have urls stored via WS
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return boolean
	 *
	 * @throws ErrorException
	 */
	private function verifyB2C($data)
	{
		if (empty($data->url) || (bool) $data->b2c == true)
		{
			return true;
		}

		throw new ErrorException(Text::_('COM_REDSHOPB_COMPANY_ERROR_ATTEMPTING_TO_STORE_URL_FOR_B2B_COMPANY'), 403);
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
		return parent::validateCreateWS($data);
	}

	/**
	 * Validate incoming data from the update web service - maps non-incoming data to avoid problems with actual validation
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  false|array
	 */
	public function validateUpdateWS($data)
	{
		// If some of the manually updated fields is not sent, it brings it from the item itself to avoid validation errors
		// Tries to load the item to make sure it exist
		$item = $this->getItemFromWSData($data['id']);

		if (!$item)
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_REDSHOPB_WEBSERVICE_RECORD_NOT_FOUND', $data["id"]), 'error');

			return false;
		}

		if (!isset($data['customer_number']) || $data['customer_number'] == '')
		{
			$data['customer_number'] = $item->customer_number;
		}

		if (!isset($data['address_line1']) || $data['address_line1'] == '')
		{
			$data['address_line1'] = $item->address;
		}

		if (!isset($data['address_line2']) || $data['address_line2'] == '')
		{
			$data['address_line2'] = $item->address2;
		}

		if (!isset($data['address_name1']) || $data['address_name1'] == '')
		{
			$data['address_name1'] = $item->address_name;
		}

		if (!isset($data['language_code']) || $data['language_code'] == '')
		{
			$data['language_code'] = $item->site_language;
		}

		if (!isset($data['parent_id']) || $data['parent_id'] == '')
		{
			$data['parent_id'] = $item->parent_id;
		}

		if (isset($data['erp_id']) && $data['erp_id'] != '')
		{
			$data['remote_key'] = $data['erp_id'];
		}

		return parent::validateUpdateWS($data);
	}

	/**
	 *  Validate web service data for deliveryAddressAdd function
	 *
	 * @param   int  $data  Data to be validated ('address_id')
	 *
	 * @return  false|array
	 */
	public function validateDeliveryAddressAddWS($data)
	{
		$data = RedshopbHelperWebservices::validateExternalId($data, 'address');

		if (!$data
			|| !RedshopbHelperAddress::validateDeliveryAddressAddWS($data['address_id'], $data['id'], 'company'))
		{
			return false;
		}

		return $data;
	}

	/**
	 *  Add a delivery address to a user
	 *
	 * @param   int  $id         Id of user table
	 * @param   int  $addressId  Id of address table
	 *
	 * @return  boolean|integer  User ID on success. False otherwise.
	 */
	public function deliveryAddressAdd($id, $addressId)
	{
		if (RedshopbHelperAddress::deliveryAddressAdd($addressId, $id, 'company'))
		{
			return $id;
		}

		return false;
	}

	/**
	 *  Validate web service data for deliveryAddressRemove function
	 *
	 * @param   int  $data  Data to be validated ('address_id')
	 *
	 * @return  boolean|array
	 */
	public function validateDeliveryAddressRemoveWS($data)
	{
		$data = RedshopbHelperWebservices::validateExternalId($data, 'address');

		if (!$data
			|| !RedshopbHelperAddress::validateDeliveryAddressRemoveWS($data['address_id'], $data['id'], 'company'))
		{
			return false;
		}

		return $data;
	}

	/**
	 *  Remove a delivery address from a user
	 *
	 * @param   int  $id         Id of user table
	 * @param   int  $addressId  Id of address table
	 *
	 * @return  boolean|integer  User ID on success. False otherwise.
	 */
	public function deliveryAddressRemove($id, $addressId)
	{
		if (!RedshopbHelperAddress::deliveryAddressRemove($addressId))
		{
			return false;
		}

		return $id;
	}

	/**
	 *  Validate web service data for deliveryAddressDefault function
	 *
	 * @param   int  $data  Data to be validated ('address_id')
	 *
	 * @return  boolean|array
	 */
	public function validateDeliveryAddressDefaultWS($data)
	{
		$data = RedshopbHelperWebservices::validateExternalId($data, 'address');

		if (!$data
			|| !RedshopbHelperAddress::validateDeliveryAddressAddWS($data['address_id'], $data['id'], 'company'))
		{
			return false;
		}

		return $data;
	}

	/**
	 *  Sets an address as the delivery address of a certain user
	 *
	 * @param   int  $id         Id of user table
	 * @param   int  $addressId  Id of address table
	 *
	 * @return  boolean|integer  User ID on success. False otherwise.
	 */
	public function deliveryAddressDefault($id, $addressId)
	{
		if (RedshopbHelperAddress::deliveryAddressDefault($addressId, $id, 'company'))
		{
			return $id;
		}

		return false;
	}

	/**
	 * Add a sales person for a company
	 *
	 * @param   integer  $companyId  The company id
	 * @param   integer  $userId     The user id
	 *
	 * @return  boolean              True on success. False otherwise.
	 */
	public function salespersonAdd($companyId, $userId)
	{
		$xrefTable = $this->getTable(
			'Company_Sales_Person_Xref',
			'RedshopbTable'
		);

		if ($xrefTable->load(array('company_id' => $companyId, 'user_id' => $userId))
			|| !$xrefTable->save(array('company_id' => $companyId, 'user_id' => $userId)))
		{
			return false;
		}

		return true;
	}

	/**
	 * Removes a sales person for a company
	 *
	 * @param   integer  $companyId  The company id
	 * @param   integer  $userId     The user id
	 *
	 * @return  boolean              True on success. False otherwise.
	 */
	public function salespersonRemove($companyId, $userId)
	{
		$xrefTable = $this->getTable(
			'Company_Sales_Person_Xref',
			'RedshopbTable'
		);

		if (!$xrefTable->load(array('company_id' => $companyId, 'user_id' => $userId)))
		{
			return false;
		}

		$db = Factory::getDbo();

		$conditions = array(
			$db->quoteName('company_id') . ' = ' . (int) $companyId,
			$db->quoteName('user_id') . ' = ' . (int) $userId
		);

		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__redshopb_company_sales_person_xref'))
			->where($conditions);
		$db->setQuery($query);

		return (bool) $db->execute();
	}

	/**
	 * Overridden to make sure the record hasn't been deleted
	 *
	 * @param   string  $pk              The pk to be retrieved
	 * @param   bool    $addRelatedData  Add the other related data fields from web service sync
	 *
	 * @return  boolean|object           Object on success, false on failure.
	 */
	public function getItemWS($pk, $addRelatedData = true)
	{
		$item = parent::getItemWS($pk, $addRelatedData);

		if (!$item)
		{
			return false;
		}

		$this->verifyNotDeleted(array('id' => $item->id));

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
