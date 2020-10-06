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
use Joomla\CMS\User\User;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Crypt\Key;
use Joomla\CMS\Crypt\Cipher\CryptoCipher;
use Joomla\CMS\Crypt\Crypt;

jimport('models.trait.customfields', JPATH_ROOT . '/components/com_redshopb/');

/**
 * B2B User Register Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelB2BUserRegister extends RedshopbModelAdmin
{
	use RedshopbModelsTraitCustomFields;

	/**
	 * We don't have a list view
	 *
	 * @var string
	 */
	protected $view_list = 'b2buserregister';

	/**
	 * Constructor.
	 *
	 * @param   array $config Configuration array
	 *
	 * @throws  RuntimeException
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->setScope('user');
	}

	/**
	 * Get the associated Table
	 *
	 * @param   string $name   Table name
	 * @param   string $prefix Table prefix
	 * @param   array  $config Configuration array
	 *
	 * @return  Table
	 */
	public function getTable($name = null, $prefix = '', $config = array())
	{
		$name = is_null($name) ? 'user' : $name;

		return parent::getTable($name, $prefix, $config);
	}

	/**
	 * Method to activate a user account.
	 *
	 * @param   string $token The activation token.
	 *
	 * @return  User|boolean   False on failure, user object on success.
	 */
	public function activate($token)
	{
		$db = $this->getDbo();

		// Get the user id based on the token.
		$query = $db->getQuery(true)
			->select($db->qn('id'))
			->from($db->qn('#__users'))
			->where($db->qn('activation') . ' = ' . $db->quote($token))
			->where($db->qn('block') . ' = 1')
			->where($db->qn('lastvisitDate') . ' = ' . $db->quote($db->getNullDate()));

		$userId = (int) $db->setQuery($query)->loadResult();

		// Check for a valid user id.
		if (!$userId)
		{
			$this->setError(Text::_('COM_REDSHOPB_USER_ACTIVATION_TOKEN_NOT_FOUND'));

			return false;
		}

		// Load the users plugin group.
		PluginHelper::importPlugin('user');

		// Activate the user.
		$user = Factory::getUser($userId);
		$user->set('activation', '');
		$user->set('block', '0');

		// Store the user object.
		if (!$user->save())
		{
			$this->setError(Text::sprintf('COM_REDSHOPB_USER_REGISTRATION_ACTIVATION_SAVE_FAILED', $user->getError()));

			return false;
		}

		return $user;
	}

	/**
	 * Function for authorizing admin user approval.
	 *
	 * @param   string $auth Auth token.
	 *
	 * @return  boolean        True/False
	 *
	 * @since   1.12.56
	 */
	public function authorize($auth)
	{
		$secret = Factory::getConfig()->get('secret', null);
		$crypt  = new Crypt(new CryptoCipher, new Key('crypto', $secret, $secret));
		$mail   = $crypt->decrypt($auth);
		$mails  = explode(',', RedshopbEntityConfig::getInstance()->getString('register_flow_to_emails', ''));

		return in_array($mail, $mails);
	}

	/**
	 * Function for generating authorization code using email address.
	 *
	 * @param   string $email Email to use for generating authorization code.
	 *
	 * @return  string          Authorization code result.
	 *
	 * @since   1.12.56
	 */
	public function generateAuthorizationCode($email)
	{
		$secret = Factory::getConfig()->get('secret', null);
		$crypt  = new Crypt(new CryptoCipher, new Key('crypto', $secret, $secret));
		$auth   = $crypt->encrypt($email);

		return urlencode($auth);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array $data The form data.
	 *
	 * @return  true       True on success, Throws exception on error
	 *
	 * @throws Exception
	 *
	 * @since   12.2
	 */
	public function save($data)
	{
		$isCompanyRegister = (boolean) RedshopbEntityConfig::getInstance()->get('allow_company_register');
		$isCompanyRegister = $isCompanyRegister && $data['register_type'] === 'business';
		$companyId         = 0;
		$db                = Factory::getDbo();

		$data['address_name']  = $data['name1'];
		$data['address_name2'] = $data['name2'];
		$data['country_id']    = $data['billing_country_id'];
		$data['state_id']      = $data['billing_state_id'];
		$data['address']       = $data['billing_address'];
		$data['address2']      = $data['billing_address2'];
		$data['zip']           = $data['billing_zip'];
		$data['city']          = $data['billing_city'];
		$data['address_phone'] = $data['billing_phone'];

		try
		{
			$db->transactionStart();

			if ($isCompanyRegister)
			{
				$companyId = $this->registerCompany($data);

				if (!$companyId)
				{
					throw new Exception($this->getError());
				}

				$data['company_id']    = $companyId;
				$data['department_id'] = null;

				// 01 :: Administrator
				$data['role_type_id'] = 2;
			}
			elseif (isset($data['company_id']))
			{
				$companyId = $data['company_id'];
			}

			$data['use_company_email'] = (!$companyId) ? 1 : 0;

			if (!parent::save($data))
			{
				throw new Exception(Text::_('COM_REDSHOPB_B2B_REGISTRATION_ERROR_CAN_NOT_CREATE_USER'));
			}

			$b2bUserId = $this->getState($this->getName() . '.id');
			$userTable = $this->getTable();

			// Store extra fields data if available.
			if (null !== $data['extrafields'] && is_array($data['extrafields']))
			{
				if (!RedshopbHelperField::storeScopeFieldData(
					'company', $companyId, 0, $data['extrafields'], false, $userTable->getOption('lockingMethod', 'User')
				))
				{
					throw new Exception(Text::_('COM_REDSHOPB_FIELDS_SAVE_FAILURE'));
				}

				if (!RedshopbHelperField::storeScopeFieldData(
					'user', $b2bUserId, 0, $data['extrafields'], false, $userTable->getOption('lockingMethod', 'User')
				))
				{
					throw new Exception(Text::_('COM_REDSHOPB_FIELDS_SAVE_FAILURE'));
				}
			}

			if ($isCompanyRegister)
			{
				$db->transactionCommit();
			}

			$userTable->load($b2bUserId);

			$this->saveShippingAddress($userTable->id, $data);

			// Update company of user
			if ($isCompanyRegister)
			{
				/** @var RedshopbTableCompany $companyTable */
				$companyTable = RedshopbTable::getAutoInstance('Company');

				if (!$companyTable->load($companyId))
				{
					throw new Exception($companyTable->getError());
				}

				$newUser = RedshopbEntityUser::getInstance($this->getState($this->getName() . '.id'));

				$userId = $newUser->getJoomlaUser()->id;

				$companyTable->set('created_by', $userId);
				$companyTable->set('modified_by', $userId);
				$companyTable->setOption('rebuildACL', false);

				if (!$companyTable->store(false))
				{
					throw new Exception($companyTable->getError());
				}

				RFactory::getDispatcher()->trigger('onAECB2BUserRegisterAfterCompanyRegister', array(&$companyId, &$data));

				// Send email notification to admin
				RedshopbHelperEmail::sendCompanyRegisterNotification($companyId);
			}

			$db->transactionCommit();
		}
		catch (Exception $exception)
		{
			$db->transactionRollback();

			if ($isCompanyRegister)
			{
				$this->companyRollback($companyId);
			}

			throw $exception;
		}

		return true;
	}

	/**
	 * Method to store the users shipping address
	 *
	 * @param   int   $b2bUserId User ID
	 * @param   array $rawData   All valid data submitted
	 *
	 * @return  boolean            True on success, false otherwise.
	 *
	 * @throws  Exception
	 */
	private function saveShippingAddress($b2bUserId, $rawData)
	{
		$addressTable = RedshopbTable::getAdminInstance('Address');
		$addressData  = array();

		$keyPrefix = ($rawData['usebilling'] == 1) ? 'billing' : 'shipping';

		$addressData['address']    = $rawData[$keyPrefix . '_address'];
		$addressData['address2']   = $rawData[$keyPrefix . '_address2'];
		$addressData['zip']        = $rawData[$keyPrefix . '_zip'];
		$addressData['city']       = $rawData[$keyPrefix . '_city'];
		$addressData['phone']      = $rawData[$keyPrefix . '_phone'];
		$addressData['country_id'] = $rawData[$keyPrefix . '_country_id'];
		$addressData['state_id']   = $rawData[$keyPrefix . '_state_id'];
		$addressData['name']       = empty($rawData['shipping_name1'])
			? $rawData['name1']
			: $rawData['shipping_name1'];
		$addressData['name2']      = empty($rawData['shipping_name2'])
			? $rawData['name2']
			: $rawData['shipping_name2'];
		$addressData['email']      = $rawData['email'];
		$addressData['type']       = 3;

		$addressData['customer_type'] = 'employee';
		$addressData['customer_id']   = $b2bUserId;

		if (!empty($addressData['name']) && !empty($addressData['address']) && !empty($addressData['zip'])
			&& !empty($addressData['city']) && !empty($addressData['country_id']) && !$addressTable->save($addressData))
		{
			throw new Exception($addressTable->getError());
		}

		return true;
	}

	/**
	 * Override AdminModel::preprocessForm to ensure the correct plugin group is loaded.
	 *
	 * @param   Form    $form   A Form object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @throws  Exception if there is an error in the form event.
	 */
	protected function preprocessForm(Form $form, $data, $group = null)
	{
		$group = null === $group ? 'user' : $group;

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to get the login form.
	 *
	 * The base form is loaded from XML and then an event is fired
	 * for users plugins to extend the form with extra fields.
	 * In login form not use 'control' prefix!
	 *
	 * @param   array   $data     An optional array of data for the form to integrate.
	 * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form|boolean A Form object on success, false on failure
	 */
	public function getLoginForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			$this->context . '.' . $this->formName, $this->formName,
			array(
				'load_data' => $loadData
			)
		);

		return empty($form) ? false : $form;
	}

	/**
	 * Method for getting the form from the model.
	 *
	 * @param   array   $data     Data for the form.
	 * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form|boolean  A Form object on success, false on failure
	 *
	 * @throws  Exception
	 */
	public function getForm($data = array(), $loadData = true)
	{
		/** @var Form|boolean $form */
		$form = parent::getForm($data, $loadData);

		if (false === $form)
		{
			return false;
		}

		$fixedValues = $this->getFixedValues();
		$this->addExtraFields($form, array('filter.b2c' => true));
		$this->scope = 'company';
		$this->addExtraFields($form, array('filter.b2c' => true));
		$this->scope = 'user';

		foreach ($fixedValues as $key => $value)
		{
			$form->setValue($key, null, $value);
		}

		$config = RedshopbEntityConfig::getInstance();

		if (!$config->get('register_address_required', 1))
		{
			$form->setFieldAttribute('shipping_city', 'required', 'false');
			$form->setFieldAttribute('shipping_zip', 'required', 'false');
			$form->setFieldAttribute('shipping_address', 'required', 'false');
			$form->setFieldAttribute('shipping_country_id', 'required', 'false');
			$form->setFieldAttribute('billing_city', 'required', 'false');
			$form->setFieldAttribute('billing_zip', 'required', 'false');
			$form->setFieldAttribute('billing_address', 'required', 'false');
			$form->setFieldAttribute('billing_country_id', 'required', 'false');
		}

		if ($config->get('register_billing_phone_required', 0))
		{
			$form->setFieldAttribute('billing_phone', 'required', 'true');
		}

		if ($config->get('register_shipping_phone_required', 0))
		{
			$form->setFieldAttribute('shipping_phone', 'required', 'true');
		}

		return $form;
	}

	/**
	 * Method to validate the form data.
	 * Each field error is stored in session and can be retrieved with getFieldError().
	 * Once getFieldError() is called, the error is deleted from the session.
	 *
	 * @param   Form     $form    The form to validate against.
	 * @param   array    $data    The data to validate.
	 * @param   string   $group   The name of the field group to validate.
	 *
	 * @throws  Exception
	 *
	 * @return  array|boolean   Array of filtered data if valid, false otherwise.
	 */
	public function validate($form, $data, $group = null)
	{
		if (!RedshopbEntityConfig::getInstance()->get('register_address_required', 1))
		{
			$form->setFieldAttribute('shipping_city', 'required', 'false');
			$form->setFieldAttribute('shipping_zip', 'required', 'false');
			$form->setFieldAttribute('shipping_address', 'required', 'false');
			$form->setFieldAttribute('shipping_country_id', 'required', 'false');
			$form->setFieldAttribute('billing_city', 'required', 'false');
			$form->setFieldAttribute('billing_zip', 'required', 'false');
			$form->setFieldAttribute('billing_address', 'required', 'false');
			$form->setFieldAttribute('billing_country_id', 'required', 'false');
		}

		if (isset($data['usebilling']) && $data['usebilling'] == 1)
		{
			$form->setFieldAttribute('shipping_city', 'required', 'false');
			$form->setFieldAttribute('shipping_zip', 'required', 'false');
			$form->setFieldAttribute('shipping_address', 'required', 'false');
			$form->setFieldAttribute('shipping_country_id', 'required', 'false');
			$form->setFieldAttribute('shipping_phone', 'required', 'false');
		}

		$data = array_replace($data, $this->getFixedValues());

		// Add custom fields validation
		if (!$this->operationWS)
		{
			$this->addCustomFieldsValidation($form);
		}

		return parent::validate($form, $data, $group);
	}

	/**
	 * Get fixed values
	 *
	 * @return  array
	 * @throws  Exception
	 */
	protected function getFixedValues()
	{
		$app          = Factory::getApplication();
		$menu         = $app->getMenu()->getActive();
		$user         = RedshopbHelperCommon::getUser();
		$companyId    = 0;
		$departmentId = 0;
		$roleTypeId   = 0;

		// Prevent access from already logged in user.
		if (!$user->guest)
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->redirect(RedshopbRoute::_('index.php?option=com_redshopb', false));
		}
		// If have an active menu, try to fetch config data from menu.
		elseif (!empty($menu))
		{
			$params       = $menu->params;
			$companyId    = $params->get('company_id', 0);
			$departmentId = $params->get('department_id', 0);
			$roleTypeId   = $params->get('role_type_id', 0);
		}

		// If no config from menu.
		if (!$companyId || !$roleTypeId)
		{
			// If no have any B2C company. Prevent access to register page.
			if ($user->b2cMode)
			{
				// Get config data from B2C company.
				$companyId = $user->b2cCompany;
			}

			// 05 :: Employee with login
			$roleTypeId = 6;
		}

		// Check department exist in this company
		if ($departmentId)
		{
			/** @var RedshopbEntitiesCollection $departments */
			$departments  = RedshopbEntityCompany::getInstance($companyId)->searchDepartments(array('check.user_departments' => false));
			$departmentId = $departments->has($departmentId) ? $departmentId : 0;
		}

		$data = array(
			'company_id'    => $companyId,
			'department_id' => $departmentId,
			'role_type_id'  => $roleTypeId
		);

		if (!RedshopbEntityConfig::getInstance()->getBool('allow_company_register'))
		{
			$data['register_type'] = 'personal';
		}

		return $data;
	}

	/**
	 * Method for register an company
	 *
	 * @param   array $data Array data of register user for business
	 *
	 * @return  integer|null|boolean         ID of company if success. False otherwise.
	 */
	public function registerCompany($data)
	{
		/** @var   RedshopbTableCompany $companyTable */
		$companyTable = RedshopbTable::getAutoInstance('Company');

		$config   = RedshopbEntityConfig::getInstance();
		$parentId = RedshopbApp::getMainCompany()->get('id');

		$autoActivation = $config->getInt('autoactivate_b2b_company', 0);

		$companyTable->set('name', $data['business_company_name']);
		$companyTable->set('vat_number', $data['vat_number']);
		$companyTable->set('parent_id', $parentId);
		$companyTable->setLocation($parentId, 'first-child');
		$companyTable->set('state', $autoActivation);
		$companyTable->set('address_name', $data['name1']);
		$companyTable->set('address_name2', $data['name2']);
		$companyTable->set('type', 'customer');
		$companyTable->set('wallet_product_id', 0);
		$companyTable->set('currency_id', $config->getInt('default_currency'));
		$companyTable->set('email', $data['email']);

		if ($config->get('show_invoice_email_field', 0))
		{
			$companyTable->set('invoice_email', $data['invoice_email']);
		}

		if (1 === $autoActivation)
		{
			$companyTable->set('use_wallets', $config->getInt('use_wallets', 1));
		}

		$companyTable->set('address', $data['billing_address']);
		$companyTable->set('address2', $data['billing_address2']);
		$companyTable->set('zip', $data['billing_zip']);
		$companyTable->set('city', $data['billing_city']);
		$companyTable->set('address_phone', $data['billing_phone']);
		$companyTable->set('country_id', $data['billing_country_id']);
		$companyTable->set('state_id', $data['billing_state_id']);

		if (!$companyTable->store())
		{
			$this->setError($companyTable->getError());

			return false;
		}

		return $companyTable->get('id');
	}

	/**
	 * Deletes the created company
	 *
	 * @param   integer $id Company to delete
	 *
	 * @return   boolean
	 */
	protected function companyRollback($id)
	{
		/** @var   RedshopbTableCompany $company */
		$company = RedshopbTable::getAutoInstance('Company');

		try
		{
			$company->delete($id, true);

			$db = Factory::getDbo();

			$query = $db->getQuery(true)
				->delete($db->qn('#__redshopb_company'))
				->where("{$db->qn('id')} = {$id}");

			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return true;
	}
}
