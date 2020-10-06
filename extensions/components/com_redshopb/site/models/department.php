<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * Department Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelDepartment extends RedshopbModelAdmin
{
	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'department';

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 */
	public function save($data)
	{
		// True in this case implies that the WS sync table won't get updated.
		// See administrator/tables/department.php's store()

		/** @var   RedshopbTableDepartment   $table */
		$table = $this->getTable();

		$table->setOption('store.ws', true);

		$pk    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		// Load the row if saving an existing category.
		if ($table->load($pk))
		{
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

		// Include the content plugins for the on save events.
		$dispatcher = RFactory::getDispatcher();
		PluginHelper::importPlugin('content');

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
		if (!$this->saveImageFile($table, $data, 'departments'))
		{
			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Get Departments form field with selected value
	 *
	 * @param   int  $companyId  Company id.
	 * @param   int  $parentId   Parent department Id
	 * @param   int  $currentId  Current Department id
	 *
	 * @return  boolean  False if failed
	 */
	public function getDepartmentsFormField($companyId = 0, $parentId = 0, $currentId = 0)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select(
				array (
					$db->qn('d.id', 'identifier'),
					'CONCAT(' . $db->qn('d.name') . ', ' . $db->quote(' (') . ',' . $db->qn('c.name') . ',' . $db->quote(')') . ') as data',
					$db->qn('d.level'),
					$db->qn('d.state')
				)
			)
			->from($db->qn('#__redshopb_department', 'd'))
			->innerJoin($db->qn('#__redshopb_company', 'c') . ' ON c.id = d.company_id AND ' . $db->qn('c.deleted') . ' = 0')
			->where('d.id > 1')
			->where($db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' IN (0,1)')
			->order($db->qn('d.lft'));

		// Check for available departments for this user if not a system admin of the app
		if (!RedshopbHelperACL::isSuperAdmin())
		{
			$user                 = Factory::getUser();
			$availableDepartments = RedshopbHelperACL::listAvailableDepartments($user->id);

			$query->where('d.id IN (' . $availableDepartments . ')');
		}

		if ($companyId)
		{
			$query->where('d.company_id = ' . (int) $companyId);
		}

		// Prevent parenting to children of this item.
		if ($currentId)
		{
			$query->leftJoin($db->qn('#__redshopb_department', 'p') . ' ON p.id = ' . (int) $currentId . ' AND ' . $db->qn('p.deleted') . ' = 0')
				->where('NOT(d.lft >= p.lft AND d.rgt <= p.rgt)');
		}

		$db->setQuery($query);
		$items   = $db->loadObjectList();
		$options = array();

		if (RedshopbHelperACL::getPermission('manage', 'company', array(), false, RedshopbHelperCompany::getCompanyById($companyId)->asset_id))
		{
			$options[] = HTMLHelper::_('select.option', '1', Text::_('JOPTION_SELECT_DEPARTMENT'));
		}

		// Build the field options.
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				if (!$item->state)
				{
					$item->data = '[' . $item->data . ']';
				}

				$options[] = HTMLHelper::_('select.option', $item->identifier, str_repeat('- ', $item->level - 1) . $item->data);
			}
		}

		$list = HTMLHelper::_(
			'select.genericlist',
			$options,
			'jform[parent_id]',
			array(
				'id' => 'jform_parent_id'
			),
			'value',
			'text',
			$parentId
		);

		return $list;
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
		$form = parent::getForm(array(), true);
		$app  = Factory::getApplication();

		if (!$form->getValue('company_id'))
		{
			$form->setValue('company_id', null, $app->getUserState('department.company_id', ''));
		}

		return $form;
	}

	/**
	 * Validate incoming data from the web service
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  array|boolean
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

		$this->verifyNotDeleted($data);

		if (!$data)
		{
			return false;
		}

		$data = parent::validateWS($data);

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
			->from($db->qn('#__redshopb_department'))
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
	 * @return  array|boolean
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

		if (!isset($data['department_number']) || $data['department_number'] == '')
		{
			$data['department_number'] = $item->department_number;
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

		if (!isset($data['parent_id']) || $data['parent_id'] == '')
		{
			$data['parent_id'] = $item->parent_id;
		}

		if (!isset($data['company_id']) || $data['company_id'] == '')
		{
			$data['company_id'] = $item->company_id;
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
	 * @param   array  $data  Data to be validated. Must contain key 'address_id'
	 *
	 * @return  array|boolean
	 */
	public function validateDeliveryAddressAddWS($data)
	{
		$data = RedshopbHelperWebservices::validateExternalId($data, 'address');

		if (!$data
			|| !RedshopbHelperAddress::validateDeliveryAddressAddWS($data['address_id'], $data['id'], 'department'))
		{
			return false;
		}

		return $data;
	}

	/**
	 *  Add a delivery address to a user
	 *
	 * @param   integer  $id         id of user table
	 * @param   integer  $addressId  id of address table
	 *
	 * @return  integer|boolean User ID on success. False otherwise.
	 */
	public function deliveryAddressAdd($id, $addressId)
	{
		if (!RedshopbHelperAddress::deliveryAddressAdd($addressId, $id, 'department'))
		{
			return false;
		}

		return $id;
	}

	/**
	 *  Validate web service data for deliveryAddressRemove function
	 *
	 * @param   array  $data  Data to be validated. Must contain key 'address_id'
	 *
	 * @return  array|boolean
	 */
	public function validateDeliveryAddressRemoveWS($data)
	{
		$data = RedshopbHelperWebservices::validateExternalId($data, 'address');

		if (!$data
			|| !RedshopbHelperAddress::validateDeliveryAddressRemoveWS($data['address_id'], $data['id'], 'department'))
		{
			return false;
		}

		return $data;
	}

	/**
	 *  Remove a delivery address from a user
	 *
	 * @param   integer  $id         id of user table
	 * @param   integer  $addressId  id of address table
	 *
	 * @return  integer|boolean User ID on success. False otherwise.
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
	 * @param   array  $data  Data to be validated. Must contain key 'address_id'
	 *
	 * @return  array|boolean
	 */
	public function validateDeliveryAddressDefaultWS($data)
	{
		$data = RedshopbHelperWebservices::validateExternalId($data, 'address');

		if (!$data
			|| !RedshopbHelperAddress::validateDeliveryAddressAddWS($data['address_id'], $data['id'], 'department'))
		{
			return false;
		}

		return $data;
	}

	/**
	 *  Sets an address as the delivery address of a certain user
	 *
	 * @param   integer  $id         id of user table
	 * @param   integer  $addressId  id of address table
	 *
	 * @return  integer|boolean User ID on success. False otherwise.
	 */
	public function deliveryAddressDefault($id, $addressId)
	{
		if (!RedshopbHelperAddress::deliveryAddressDefault($addressId, $id, 'department'))
		{
			return false;
		}

		return $id;
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

		// Gets the image URL
		if (empty($item->id) || empty($item->image))
		{
			return $item;
		}

		$item->imageurl = $this->getImageUrl($item->image, 'departments');

		return $item;
	}

	/**
	 * Overridden to make sure the record hasn't been deleted
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
