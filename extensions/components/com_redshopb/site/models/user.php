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
use Joomla\CMS\Form\Form;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Helper\UserGroupsHelper;

jimport('models.trait.customfields', JPATH_ROOT . '/components/com_redshopb/');

/**
 * User Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelUser extends RedshopbModelAdmin
{
	use RedshopbModelsTraitCustomFields;

	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'user';

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

		$this->setScope('user');
	}

	/**
	 * Credit money to the user wallet.
	 *
	 * @param   integer  $userId       The redshopb user id
	 * @param   integer  $currencyId   The currency id
	 * @param   float    $amount       The money amount
	 * @param   array    $creditMoney  Valid dates start/end wallet
	 *
	 * @return  boolean  True on success, false otherwise.
	 */
	public function credit($userId, $currencyId, $amount, $creditMoney = array())
	{
		// Get the user wallet id
		$walletId = RedshopbHelperWallet::getUserWalletId($userId, 'redshopb', false);

		// If the wallet is not existing
		if (!$walletId)
		{
			return false;
		}

		if (count($creditMoney) > 0)
		{
			$walletTable = RedshopbTable::getAdminInstance('Wallet');

			if ($walletTable->load(
				array(
					'id' => $walletId
				)
			))
			{
				if (!isset($creditMoney['start_date']) || $creditMoney['start_date'] == '')
				{
					$creditMoney['start_date'] = '0000-00-00 00:00:00';
				}
				else
				{
					$date                      = new Date($creditMoney['start_date']);
					$creditMoney['start_date'] = $date->toSql();
				}

				if (!isset($creditMoney['end_date']) || $creditMoney['end_date'] == '')
				{
					$creditMoney['end_date'] = '0000-00-00 00:00:00';
				}
				else
				{
					$date                    = new Date($creditMoney['end_date']);
					$creditMoney['end_date'] = $date->toSql();
				}

				$walletTable->set('start_date', $creditMoney['start_date']);
				$walletTable->set('end_date', $creditMoney['end_date']);

				if (!$walletTable->store())
				{
					return false;
				}
			}
		}

		/** @var RedshopbTableWallet_Money $walletMoneyTable */
		$walletMoneyTable = RedshopbTable::getAdminInstance('Wallet_Money');
		$isNew            = true;
		$amount           = (float) $amount;

		// If a wallet money exist with this currency, update it
		if ($walletMoneyTable->load(
			array(
				'wallet_id' => $walletId,
				'currency_id' => $currencyId
			)
		))
		{
			$amount = (float) $walletMoneyTable->get('amount') + $amount;
			$isNew  = false;
		}

		$walletMoneyTable->set('amount', $amount);

		if ($isNew)
		{
			// Otherwise create the money
			return $walletMoneyTable->save(
				array(
					'wallet_id' => $walletId,
					'currency_id' => $currencyId,
					'amount' => $amount
				)
			);
		}
		else
		{
			return $walletMoneyTable->store();
		}
	}

	/**
	 * Set wallet money to the user.
	 *
	 * @param   integer  $userId       The redshopb user id
	 * @param   integer  $currencyId   The currency id
	 * @param   float    $amount       The money amount
	 * @param   array    $creditMoney  Walid dates start/end wallet
	 *
	 * @return  boolean  True on success, false otherwise.
	 */
	public function saveWallet($userId, $currencyId, $amount, $creditMoney = array())
	{
		// Get the user wallet id
		$walletId = RedshopbHelperWallet::getUserWalletId($userId, 'redshopb', false);

		// If the wallet is not existing
		if (!$walletId)
		{
			return false;
		}

		if (count($creditMoney) > 0)
		{
			$walletTable = RedshopbTable::getAdminInstance('Wallet');

			if ($walletTable->load(
				array(
					'id' => $walletId
				)
			))
			{
				if (!isset($creditMoney['start_date']) || $creditMoney['start_date'] == '')
				{
					$creditMoney['start_date'] = '0000-00-00 00:00:00';
				}
				else
				{
					$date                      = new Date($creditMoney['start_date']);
					$creditMoney['start_date'] = $date->toSql();
				}

				if (!isset($creditMoney['end_date']) || $creditMoney['end_date'] == '')
				{
					$creditMoney['end_date'] = '0000-00-00 00:00:00';
				}
				else
				{
					$date                    = new Date($creditMoney['end_date']);
					$creditMoney['end_date'] = $date->toSql();
				}

				$walletTable->set('start_date', $creditMoney['start_date']);
				$walletTable->set('end_date', $creditMoney['end_date']);

				if (!$walletTable->store())
				{
					return false;
				}
			}
		}

		/** @var RedshopbTableWallet_Money $walletMoneyTable */
		$walletMoneyTable = RedshopbTable::getAdminInstance('Wallet_Money');

		// If a wallet money exist with this currency, update it
		if ($walletMoneyTable->load(
			array(
				'wallet_id' => $walletId,
				'currency_id' => $currencyId
			)
		))
		{
			$walletMoneyTable->amount = $amount;

			return $walletMoneyTable->store();
		}

		// Otherwise create the money
		return $walletMoneyTable->save(
			array(
				'wallet_id' => $walletId,
				'currency_id' => $currencyId,
				'amount' => $amount
			)
		);
	}

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
		$id         = $data['id'];
		$issetStart = isset($data['start_date']) && !empty($data['start_date']);
		$issetEnd   = isset($data['end_date']) && !empty($data['end_date']);

		if ($id)
		{
			if ($issetStart)
			{
				$start = new Date($data['start_date']);
				$start = $start->toSql();
			}
			else
			{
				$start = '0000-00-00 00:00:00';
			}

			if ($issetEnd)
			{
				$end = new Date($data['end_date']);
				$end = $end->toSql();
			}
			else
			{
				$end = '0000-00-00 00:00:00';
			}

			if (!$this->updateWallet($id, $start, $end))
			{
				return false;
			}
		}

		if (isset($data['password']) && isset($data['password2']) && $data['password'] == '' && $data['password2'] == '' && $id > 0)
		{
			unset($data['password']);
			unset($data['password2']);
		}

		if (!parent::save($data))
		{
			return false;
		}

		$userId = $this->getState($this->getName() . '.id');
		$table  = $this->getTable();

		if (!is_null($data['extrafields']) && is_array($data['extrafields']))
		{
			if (!RedshopbHelperField::storeScopeFieldData(
				'user', $userId, 0, $data['extrafields'], true, $table->getOption('lockingMethod', 'User')
			))
			{
				Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_FIELDS_SAVE_FAILURE'), 'error');
			}
		}

		$table->load($userId);

		// Image loading and thumbnail creation from web service file
		if (!$this->saveImageFile($table, $data, 'users'))
		{
			Factory::getApplication()->enqueueMessage('COM_REDSHOPB_USER_IMAGE_NOT_VALID', 'warning');
		}

		return true;
	}

	/**
	 * Method to get a single record using possible related data from the web service
	 *
	 * @param   string  $pk              The pk to be retrieved
	 * @param   bool    $addRelatedData  Add the other related data fields from web service sync
	 *
	 * @return  false|object             Object on success, false on failure.
	 */
	public function getItemWS($pk, $addRelatedData = true)
	{
		$item = parent::getItemWS($pk, $addRelatedData);

		if (!$item)
		{
			return false;
		}

		$item->name          = $item->name1;
		$item->blocked       = $item->block;
		$item->address_name1 = $item->address_name;
		$item->address_line1 = $item->address;
		$item->address_line2 = $item->address2;
		$item->no_email      = $item->use_company_email;
		$item->role_id       = $item->role_type_id;

		return $item;
	}

	/**
	 * Get department options list HTML for given company.
	 * If companyId is not set, all departments are returned.
	 *
	 * @param   int     $companyId  Company id.
	 * @param   string  $fieldName  Field name for return input.
	 * @param   string  $fieldId    Field ID for return input.
	 * @param   int     $userId     Joomla User ID.
	 *
	 * @return string Department options list HTML.
	 */
	public function getDepartmentList($companyId = 0, $fieldName = 'jform[department_id]', $fieldId = 'jform_department_id', $userId = 0)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select(
				array (
					$db->qn('d.id', 'identifier'),
					'CONCAT(' . $db->qn('d.name') . ', ' . $db->quote('(') . ',' . $db->qn('c.name') . ',' . $db->quote(')') . ') as data'
				)
			)
			->from($db->qn('#__redshopb_department', 'd'))
			->innerJoin($db->qn('#__redshopb_company', 'c') . ' ON c.id = d.company_id AND ' . $db->qn('c.deleted') . ' = 0')
			->where('d.id > 1')
			->where($db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' = 1')
			->order('d.name');

		// Check for available departments for this user if not a system admin of the app
		if (!RedshopbHelperACL::isSuperAdmin($userId))
		{
			$user                 = Factory::getUser();
			$availableDepartments = RedshopbHelperACL::listAvailableDepartments($user->id);

			$query->where($db->qn('d.id') . ' IN (' . $availableDepartments . ')');
		}

		if ($companyId)
		{
			$query->where($db->qn('d.company_id') . ' = ' . (int) $companyId);
		}

		$db->setQuery($query);
		$items   = $db->loadObjectList();
		$options = array(HTMLHelper::_('select.option', '', Text::_('JOPTION_SELECT_DEPARTMENT')));

		// Build the field options.
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				$options[] = HTMLHelper::_('select.option', $item->identifier, $item->data);
			}
		}

		$list = HTMLHelper::_('select.genericlist', $options, $fieldName, array('id' => $fieldId));

		return $list;
	}

	/**
	 * Update wallet starting and ending date.
	 *
	 * @param   int     $userId  User id.
	 * @param   string  $start   Starting date.
	 * @param   string  $end     Ending date.
	 *
	 * @return boolean True on success, false otherwise.
	 */
	public function updateWallet($userId, $start, $end)
	{
		$user     = RedshopbHelperUser::getUser($userId);
		$walletId = $user->wallet;
		$db       = $this->getDbo();

		if (is_null($walletId) || $walletId == 0)
		{
			// Wallet doesn't exists, lets create new one for this user
			$walletTable = RedshopbTable::getAdminInstance('Wallet');

			if ($walletTable->save(array()))
			{
				$walletId = $walletTable->id;

				// Check if we managed to create new wallet
				if ($walletId)
				{
					// Update user record
					$query = $db->getQuery(true)
						->update($db->qn('#__redshopb_user'))
						->set($db->qn('wallet_id') . ' = ' . (int) $walletId)
						->where($db->qn('id') . ' = ' . (int) $user->id);
					$db->setQuery($query)->execute();
				}
			}
		}

		$query = $db->getQuery(true)
			->update($db->qn('#__redshopb_wallet'))
			->set($db->qn('start_date') . ' = ' . $db->q($start))
			->set($db->qn('end_date') . ' = ' . $db->q($end))
			->set($db->qn('modified_date') . ' = ' . $db->q(Factory::getDate()->toSql()))
			->set($db->qn('modified_by') . ' = ' . $db->q((int) Factory::getUser()->id))
			->where($db->qn('id') . ' = ' . (int) $walletId);
		$db->setQuery($query);

		return $db->execute();
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
		$input  = Factory::getApplication()->input;
		$layout = $input->getVar('layout');
		$id     = $input->getVar('id');

		if ($layout == 'own')
		{
			$user      = Factory::getUser();
			$rsbUserId = RedshopbHelperUser::getUserRSid($user->id);

			if ($id != $rsbUserId)
			{
				return false;
			}

			$form->setFieldAttribute('email', 'required', 'false');
			$form->setFieldAttribute('company_id', 'required', 'false');
			$form->setFieldAttribute('role_type_id', 'required', 'false');
		}
		else
		{
			// Un-require the email field if using the company email
			if (isset($data['use_company_email']) && (int) $data['use_company_email'] == 1)
			{
				if (!isset($data['company_id']) || (int) $data['company_id'] == 0)
				{
					Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_USER_COMPANY_MISSING_EMAIL_USAGE'), 'warning');

					return false;
				}

				$form->setFieldAttribute('email', 'required', 'false');
			}
		}

		// Validates that the selected department belongs to the selected company
		if (isset($data['company_id'])
			&& is_numeric($data['company_id'])
			&& isset($data['department_id'])
			&& is_numeric($data['department_id']))
		{
			$modelDepartment = RedshopbModelAdmin::getFrontInstance('Department');

			$departmentItem = $modelDepartment->getItem($data['department_id']);

			if ($departmentItem)
			{
				if ($departmentItem->company_id != $data['company_id'])
				{
					Factory::getApplication()->enqueueMessage(
						Text::sprintf('COM_REDSHOPB_USER_DEPARTMENT_COMPANY_NOT_BELONG', $data['department_id'], $data['company_id']), 'warning'
					);

					return false;
				}
			}
		}

		// Validate custom fields data
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
	 * @return  array
	 */
	public function validateWS($data)
	{
		// Sets the right fields
		if (isset($data['name']))
		{
			$data['name1'] = $data['name'];
			unset($data['name']);
		}

		if (isset($data['address_line1']))
		{
			$data['address'] = $data['address_line1'];
			unset($data['address_line1']);
		}

		if (isset($data['address_line2']))
		{
			$data['address2'] = $data['address_line2'];
			unset($data['address_line2']);
		}

		if (isset($data['address_name1']))
		{
			$data['address_name'] = $data['address_name1'];
			unset($data['address_name1']);
		}

		if (isset($data['blocked']))
		{
			$data['block'] = $data['blocked'];
			unset($data['blocked']);
		}

		if (isset($data['no_email']))
		{
			$data['use_company_email'] = ($data['no_email'] == 'true' || (int) $data['no_email'] == 1 ? '1' : '0');
			unset($data['no_email']);
		}

		if (isset($data['send_email']))
		{
			$data['send_email'] = ($data['send_email'] == 'true' || (int) $data['send_email'] == 1 ? '1' : '0');
		}

		if (isset($data['role_id']))
		{
			$data['role_type_id'] = $data['role_id'];
			unset($data['role_id']);
		}

		if ((isset($data['address']) && $data['address'] != '')
			|| (isset($data['zip']) && $data['zip'] != '')
			||  (isset($data['city']) && $data['city'] != '')
			|| (isset($data['country_code']) && $data['country_code'] != ''))
		{
			$form = $this->getForm();
			$form->setFieldAttribute('address', 'required', 'true');
			$form->setFieldAttribute('zip', 'required', 'true');
			$form->setFieldAttribute('city', 'required', 'true');
			$form->setFieldAttribute('country_id', 'required', 'true');
		}

		$data = parent::validateWS($data);

		if (!$data)
		{
			return false;
		}

		return $data;
	}

	/**
	 * Validate incoming data from the web service for creation
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  array
	 */
	public function validateCreateWS($data)
	{
		$roleTypeModel = RedshopbModelAdmin::getFrontInstance('Role_Type');

		$roleType = $roleTypeModel->getItem($data['role_id']);

		if ($roleType)
		{
			$form = $this->getForm($data, false);

			// Assume the role type does not need username/password
			$form->setFieldAttribute('username', 'required', 'false');
			$form->setFieldAttribute('password', 'required', 'false');

			// Adjust if they actually do need username/password
			if ($roleType->allow_access)
			{
				$this->checkUserName($data);
				$form->setFieldAttribute('username', 'required', 'true');
				$form->setFieldAttribute('password', 'required', 'true');
			}
		}

		$data = parent::validateCreateWS($data);

		if (!$data)
		{
			return false;
		}

		// Sends password validation data to avoid problems
		$data['password2'] = $data['password'];

		return $data;
	}

	/**
	 * Method to check if the username already exists
	 *
	 * @param   array  $data  data to check
	 *
	 * @return boolean
	 *
	 * @throws InvalidArgumentException
	 */
	private function checkUserName($data)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('COUNT(id)')
			->from('#__users')
			->where($db->qn('username') . ' = ' . $db->q($data['username']));

		$result = $db->setQuery($query)->loadResult();

		if (!empty($result))
		{
			throw new InvalidArgumentException(Text::_('JLIB_DATABASE_ERROR_USERNAME_INUSE'), 409);
		}

		return true;
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
		// If some of the manually updated fields is not sent, it brings it from the item itself to avoid validation errors
		$item = $this->getItemFromWSData($data['id']);

		// Tries to load the item to make sure it exist
		if ($item === false)
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_REDSHOPB_WEBSERVICE_RECORD_NOT_FOUND', $data["id"]), 'error');

			return false;
		}

		if (!isset($data['name']) || $data['name'] == '')
		{
			$data['name'] = $item->name1;
		}

		if (!isset($data['employee_number']) || $data['employee_number'] == '')
		{
			$data['employee_number'] = $item->employee_number;
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

		if (!isset($data['blocked']) || $data['blocked'] == '')
		{
			$data['blocked'] = $item->block;
		}

		if (!isset($data['no_email']) || $data['no_email'] == '')
		{
			$data['no_email'] = $item->no_email;
		}

		if (!isset($data['role_id']) || $data['role_id'] == '')
		{
			$data['role_id'] = $item->role_type_id;
		}

		if (isset($data['erp_id']) && $data['erp_id'] != '')
		{
			$data['remote_key'] = $data['erp_id'];
		}

		$newPassword = '';

		if (isset($data['password']) && $data['password'] != '')
		{
			$newPassword = $data['password'];
		}

		$data = parent::validateUpdateWS($data);

		if (!$data)
		{
			return false;
		}

		// Unsets password if it was not set, to avoid updating it with its encrypted version
		if ($newPassword == '')
		{
			$data['password'] = '';
		}
		else
		{
			$data['password2'] = $data['password'];
		}

		return $data;
	}

	/**
	 * Block access to a user via web service
	 *
	 * @param   int  $id           ID of the user to block
	 * @param   int  $blockStatus  Status to set to block field (0, 1)
	 *
	 * @return  record id | false
	 */
	public function block($id, $blockStatus)
	{
		$userTable = RedshopbTable::getAdminInstance('User');

		if ($userTable->load($id))
		{
			$userStatus = 1;

			if ($blockStatus == 1)
			{
				$userStatus = 0;
			}

			$newData = array(
				'userStatus' => $userStatus,
				'username' => $userTable->get('username')
			);

			if ($userTable->save($newData))
			{
				return $id;
			}
		}

		return false;
	}

	/**
	 *  Validate web service data for deliveryAddressAdd function
	 *
	 * @param   int  $data  Data to be validated ('address_id')
	 *
	 * @return  array | false
	 */
	public function validateDeliveryAddressAddWS($data)
	{
		$data = RedshopbHelperWebservices::validateExternalId($data, 'address');

		if (!$data)
		{
			return false;
		}

		if (RedshopbHelperAddress::validateDeliveryAddressAddWS($data['address_id'], $data['id'], 'employee'))
		{
			return $data;
		}

		return false;
	}

	/**
	 *  Add a delivery address to a user
	 *
	 * @param   int  $id         id of user table
	 * @param   int  $addressId  id of address table
	 *
	 * @return  boolean User ID on success. False otherwise.
	 */
	public function deliveryAddressAdd($id, $addressId)
	{
		if (RedshopbHelperAddress::deliveryAddressAdd($addressId, $id, 'employee'))
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
	 * @return  array | false
	 */
	public function validateDeliveryAddressRemoveWS($data)
	{
		$data = RedshopbHelperWebservices::validateExternalId($data, 'address');

		if (!$data)
		{
			return false;
		}

		if (RedshopbHelperAddress::validateDeliveryAddressRemoveWS($data['address_id'], $data['id'], 'employee'))
		{
			return $data;
		}

		return false;
	}

	/**
	 *  Remove a delivery address from a user
	 *
	 * @param   int  $id         id of user table
	 * @param   int  $addressId  id of address table
	 *
	 * @return  boolean User ID on success. False otherwise.
	 */
	public function deliveryAddressRemove($id, $addressId)
	{
		if (RedshopbHelperAddress::deliveryAddressRemove($addressId))
		{
			return $id;
		}

		return false;
	}

	/**
	 *  Validate web service data for deliveryAddressDefault function
	 *
	 * @param   int  $data  Data to be validated ('address_id')
	 *
	 * @return  array | false
	 */
	public function validateDeliveryAddressDefaultWS($data)
	{
		$data = RedshopbHelperWebservices::validateExternalId($data, 'address');

		if (!$data)
		{
			return false;
		}

		if (RedshopbHelperAddress::validateDeliveryAddressAddWS($data['address_id'], $data['id'], 'employee'))
		{
			return $data;
		}

		return false;
	}

	/**
	 *  Sets an address as the delivery address of a certain user
	 *
	 * @param   int  $id         id of user table
	 * @param   int  $addressId  id of address table
	 *
	 * @return  boolean User ID on success. False otherwise.
	 */
	public function deliveryAddressDefault($id, $addressId)
	{
		if (RedshopbHelperAddress::deliveryAddressDefault($addressId, $id, 'employee'))
		{
			return $id;
		}

		return false;
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

		if (!$item)
		{
			return false;
		}

		$this->attachExtraFields($item);
		$item->joomla_usergroups = $this->getJoomlaUserGroups($item->joomla_user_id, true);

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
		$form = parent::getForm(array(), true);
		$this->addExtraFields($form);

		$app = Factory::getApplication();

		if (!$form->getValue('company_id'))
		{
			$form->setValue('company_id', null, $app->getUserState('user.company_id', ''));
		}

		if (!$form->getValue('department_id'))
		{
			$form->setValue('department_id', null, $app->getUserState('user.department_id', ''));
		}

		return $form;
	}

	/**
	 * Function for getting Joomla usergroups.
	 *
	 * @param   int   $userId   Joomla user id.
	 * @param   bool  $idsOnly  Ids only.
	 *
	 * @return  mixed  Null on fail, array of usergroup objects otherwise.
	 *
	 * @since version
	 */
	public function getJoomlaUserGroups($userId = 0, $idsOnly = false)
	{
		$table  = RTable::getInstance('User', 'RedshopbTable');
		$groups = $table->getJoomlaUserGroups($userId, $idsOnly);

		if (!$idsOnly)
		{
			$ugHelper = UsergroupsHelper::getInstance();

			foreach ($groups as $i => $group)
			{
				$groups[$i] = $ugHelper->populateGroupData($group);
			}
		}

		return $groups;
	}
}
