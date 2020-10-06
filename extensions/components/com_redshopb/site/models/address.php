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

/**
 * Address Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelAddress extends RedshopbModelAdmin
{
	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'address';

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
		$result = parent::save($data);

		if ($result && !RedshopbHelperACL::isSuperAdmin())
		{
			$userId = RedshopbHelperUser::getUserRSid();
			RedshopbHelperACL::resetSessionList('Addresses_' . $userId . '_redshopb.address.view');
		}

		return $result;
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

		if (!$form->getValue('customer_type'))
		{
			$form->setValue('customer_type', null, $app->getUserState('address.customer_type', ''));
		}

		switch ($form->getValue('customer_type'))
		{
			case 'employee':
				if ($form->getValue('employee_customer_id'))
				{
					$form->setValue('customer_id', null, $form->getValue('employee_customer_id'));
				}
				else
				{
					$form->setValue('employee_customer_id', null, $form->getValue('customer_id'));
				}

				if (!$form->getValue('employee_customer_id'))
				{
					$form->setValue('employee_customer_id', null, $app->getUserState('address.customer_id', ''));
				}
				break;
			case 'department':
				if ($form->getValue('department_customer_id'))
				{
					$form->setValue('customer_id', null, $form->getValue('department_customer_id'));
				}
				else
				{
					$form->setValue('department_customer_id', null, $form->getValue('customer_id'));
				}

				if (!$form->getValue('department_customer_id'))
				{
					$form->setValue('department_customer_id', null, $app->getUserState('address.customer_id', ''));
				}
				break;
			case 'company':
				if ($form->getValue('company_customer_id'))
				{
					$form->setValue('customer_id', null, $form->getValue('company_customer_id'));
				}
				else
				{
					$form->setValue('company_customer_id', null, $form->getValue('customer_id'));
				}

				if (!$form->getValue('company_customer_id'))
				{
					$form->setValue('company_customer_id', null, $app->getUserState('address.customer_id', ''));
				}
				break;
		}

		return $form;
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
		if (isset($data['id']))
		{
			$addressTable = RedshopbTable::getAdminInstance('Address');

			if (!$addressTable->load($data['id']))
			{
				return false;
			}

			// If the address being saved is type 2 (default) then the customer_type field is not required
			if ($addressTable->type == 2)
			{
				$form->setFieldAttribute('customer_type', 'required', 'false');
			}
		}

		if (Factory::getApplication()->input->get('from_user', '', 'int'))
		{
			$form->setFieldAttribute('department_customer_id', 'required', 'false');
			$form->setFieldAttribute('company_customer_id', 'required', 'false');
			$form->setFieldAttribute('employee_customer_id', 'required', 'false');
			$form->setFieldAttribute('customer_type', 'required', 'false');
		}

		if (!isset($data['customer_type']))
		{
			$data['customer_type'] = '';
		}

		switch ($data['customer_type'])
		{
			case 'employee':
				$form->setFieldAttribute('department_customer_id', 'required', 'false');
				$form->setFieldAttribute('company_customer_id', 'required', 'false');
				$data['customer_id'] = $data['employee_customer_id'];
				break;
			case 'department':
				$form->setFieldAttribute('employee_customer_id', 'required', 'false');
				$form->setFieldAttribute('company_customer_id', 'required', 'false');
				$data['customer_id'] = $data['department_customer_id'];
				break;
			case 'company':
				$form->setFieldAttribute('department_customer_id', 'required', 'false');
				$form->setFieldAttribute('employee_customer_id', 'required', 'false');
				$data['customer_id'] = $data['company_customer_id'];
				break;
			default:
				$form->setFieldAttribute('department_customer_id', 'required', 'false');
				$form->setFieldAttribute('company_customer_id', 'required', 'false');
				$form->setFieldAttribute('employee_customer_id', 'required', 'false');
		}

		return parent::validate($form, $data, null);
	}

	/**
	 * Validate incoming data from the web service
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  false|array
	 */
	public function validateWS($data)
	{
		$form = $this->getForm($data, false);
		$form->setFieldAttribute('department_customer_id', 'required', 'false');
		$form->setFieldAttribute('company_customer_id', 'required', 'false');
		$form->setFieldAttribute('employee_customer_id', 'required', 'false');
		$form->setFieldAttribute('customer_type', 'required', 'false');

		// Sets the right address fields
		if (isset($data['address_line1']))
		{
			$data['address'] = $data['address_line1'];
		}

		if (isset($data['address_line2']))
		{
			$data['address2'] = $data['address_line2'];
		}

		if (isset($data['name1']))
		{
			$data['name'] = $data['name1'];
		}

		return parent::validateWS($data);
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
		// Sets fixed type 1 (regular delivery address)
		$data['type'] = 1;

		// Sets the predefined company/department/user if sent and valid (as default delivery address)
		if ($data['company_id'] != '')
		{
			$companyModel = RedshopbModelAdmin::getFrontInstance('Company');
			$company      = $companyModel->getItemFromWSData($data['company_id']);

			if (!$company)
			{
				Factory::getApplication()->enqueueMessage(Text::sprintf('COM_REDSHOPB_WEBSERVICE_RECORD_NOT_FOUND', $data['company_id']), 'error');

				return false;
			}

			if ($data['delivery_default'] == 'true'
				|| $data['delivery_default'] == true
				|| $data['delivery_default'] == 1)
			{
				$data['type'] = 3;
			}

			$data['customer_type'] = 'company';
			$data['customer_id']   = $company->id;
		}
		elseif ($data['department_id'] != '')
		{
			$departmentModel = RedshopbModelAdmin::getFrontInstance('Department');
			$department      = $departmentModel->getItemFromWSData($data['department_id']);

			if (!$department)
			{
				Factory::getApplication()->enqueueMessage(
					Text::sprintf('COM_REDSHOPB_WEBSERVICE_RECORD_NOT_FOUND', $data['department_id']), 'error'
				);

				return false;
			}

			if ($data['delivery_default'] == 'true'
				|| $data['delivery_default'] == true
				|| $data['delivery_default'] == 1)
			{
				$data['type'] = 3;
			}

			$data['customer_type'] = 'department';
			$data['customer_id']   = $department->id;
		}
		elseif ($data['user_id'] != '')
		{
			$userModel = RedshopbModelAdmin::getFrontInstance('User');
			$user      = $userModel->getItemFromWSData($data['user_id']);

			if (!$user)
			{
				Factory::getApplication()->enqueueMessage(Text::sprintf('COM_REDSHOPB_WEBSERVICE_RECORD_NOT_FOUND', $data['user_id']), 'error');

				return false;
			}

			if ($data['delivery_default'] == 'true'
				|| $data['delivery_default'] == true
				|| $data['delivery_default'] == 1)
			{
				$data['type'] = 3;
			}

			$data['customer_type'] = 'employee';
			$data['customer_id']   = $user->id;
		}

		return parent::validateCreateWS($data);
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
		$table = $this->getTable();

		// If some of the manually updated fields is not sent, it brings it from the item itself to avoid validation errors
		$item = $this->getItemFromWSData($data['id']);

		// Tries to load the item to make sure it exist
		if ($item === false)
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_REDSHOPB_WEBSERVICE_RECORD_NOT_FOUND', $data['id']), 'error');

			return false;
		}

		// Limits web service returns to types 1 and 3 (delivery addresses)
		if ($item->type != 1 && $item->type != 3)
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_REDSHOPB_ADDRESS_CANNOT_EDIT_WS', $data['id']), 'error');

			return false;
		}

		if (!isset($data['address_line1']) || $data['address_line1'] == '')
		{
			$data['address_line1'] = $item->address;
		}

		if (!isset($data['address_line2']) || $data['address_line2'] == '')
		{
			$data['address_line2'] = $item->address2;
		}

		if (!isset($data['name1']) || $data['name1'] == '')
		{
			$data['name1'] = $item->name;
		}

		$data = parent::validateUpdateWS($data);

		if (!$data)
		{
			return false;
		}

		return $data;
	}

	/**
	 * Validate incoming data for some web service task requiring id - it transformates external to internal ids as well
	 *
	 * @param   array  $data  Web service data
	 *
	 * @return  array
	 */
	public function validatePkWS($data)
	{
		$item = $this->getItemFromWSData($data['id']);

		// Tries to load the item to make sure it exist
		if ($item === false)
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_REDSHOPB_WEBSERVICE_RECORD_NOT_FOUND', $data['id']), 'error');

			return false;
		}

		// Limits web service returns to types 1 and 3 (delivery addresses)
		if ($item->type != 1 && $item->type != 3)
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_REDSHOPB_ADDRESS_CANNOT_EDIT_WS', $data['id']), 'error');

			return false;
		}

		$data = parent::validatePkWS($data);

		if (!$data)
		{
			return false;
		}

		return $data;
	}
}
