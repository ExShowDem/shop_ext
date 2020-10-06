<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;

/**
 * User Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerUser extends RedshopbControllerForm
{
	/**
	 * @var  string
	 */
	protected $text_prefix = 'COM_REDSHOPB_USER';

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('activate', 'activate');

		Factory::getLanguage()->load('com_users');
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 */
	protected function getRedirectToListAppend()
	{
		$append         = parent::getRedirectToListAppend();
		$fromDepartment = RedshopbInput::isFromDepartment();
		$fromCompany    = RedshopbInput::isFromCompany();

		// Append the tab name for the department or company view
		if ($fromDepartment || $fromCompany)
		{
			$append .= '&tab=users';
		}

		return $append;
	}

	/**
	 * Method for crediting money to users.
	 *
	 * @return  boolean  True on success, false otherwise.
	 */
	public function credit()
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$view = $this->input->get('view', 'users', 'string');
		$id   = $this->input->get('id', 0, 'int');

		// Prepare redirection
		if ($id && $view === 'user')
		{
			$redirect = 'index.php?option=com_redshopb&view=user&layout=edit&id=' . $id;
		}
		else
		{
			$redirect = 'index.php?option=com_redshopb&view=users';
		}

		$this->setRedirect($redirect);
		$cid = $this->input->post->get('cid', array(), 'array');

		// Adding credit to a single user on user view
		if ($id)
		{
			$cid[] = $id;
		}

		$assign      = $this->input->post->get('assign', array(), 'array');
		$currencyId  = (int) $assign['currency_id'];
		$amount      = (float) $assign['amount'];
		$creditMoney = $this->input->post->get('credit_money', array(), 'array');

		/** @var RedshopbModelUser $model */
		$model = $this->getModel();

		$failedUserId = array();

		// Iterate the users
		foreach ($cid as $userId)
		{
			// Credit the money
			if (!$model->credit($userId, $currencyId, $amount, $creditMoney))
			{
				$failedUserId[] = $userId;
			}
		}

		// No failure
		if (empty($failedUserId))
		{
			$this->setMessage(Text::sprintf('COM_REDSHOPB_USER_CREDIT_SUCCESS', count($cid)));

			return true;
		}

		$this->setMessage(Text::sprintf('COM_REDSHOPB_USER_CREDIT_FAIL', count($failedUserId)), 'warning');

		return false;
	}

	/**
	 * Update wallet expiration and starting date.
	 *
	 * @return boolean True on update.
	 */
	public function updatewallet()
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$view = $this->input->get('view', 'users', 'string');
		$id   = $this->input->get('id', 0, 'int');

		// Prepare redirection
		if ($id && $view === 'user')
		{
			$redirect = 'index.php?option=com_redshopb&view=user&layout=edit&id=' . $id;
		}
		else
		{
			$redirect = 'index.php?option=com_redshopb&view=users';
		}

		$this->setRedirect($redirect);
		$cid = $this->input->post->get('cid', array(), 'array');

		// Adding credit to a single user on user view
		if ($id)
		{
			$cid[] = $id;
		}

		$dates = $this->input->post->get('dates', array(), 'array');
		$start = $dates['start'];
		$end   = $dates['end'];

		/** @var RedshopbModelUser $model */
		$model = $this->getModel();

		$failedUserId = array();

		// Iterate the users
		foreach ($cid as $userId)
		{
			// Update expiration date
			if (!$model->updateWallet($userId, $start, $end))
			{
				$failedUserId[] = $userId;
			}
		}

		// No failure
		if (empty($failedUserId))
		{
			$this->setMessage(Text::sprintf('COM_REDSHOPB_USER_WALLET_UPDATE_SUCCESS', count($cid)));

			return true;
		}

		$this->setMessage(Text::sprintf('COM_REDSHOPB_USER_WALLET_UPDATE_FAIL', count($failedUserId)), 'warning');

		return false;
	}

	/**
	 * Method for resetting credit balance of the user to 0.
	 *
	 * @return  boolean  True on success, false otherwise.
	 */
	public function resetcredit()
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$view = $this->input->get('view', 'users', 'string');
		$id   = $this->input->get('id', 0, 'int');

		// Prepare redirection
		if ($id && $view === 'user')
		{
			$redirect = 'index.php?option=com_redshopb&view=user&layout=edit&id=' . $id;
		}
		else
		{
			$redirect = 'index.php?option=com_redshopb&view=users';
		}

		$this->setRedirect($redirect);
		$cid = $this->input->post->get('cid', array(), 'array');

		// Adding credit to a single user on user view
		if ($id)
		{
			$cid[] = $id;
		}

		$assign      = $this->input->post->get('assign', array(), 'array');
		$currencyId  = (int) $assign['currency_id'];
		$amount      = 0;
		$creditMoney = array('start_date', 'end_date');

		/** @var RedshopbModelUser $model */
		$model = $this->getModel();

		$failedUserId = array();

		// Iterate the users
		foreach ($cid as $userId)
		{
			// Credit the money
			if (!$model->saveWallet($userId, $currencyId, $amount, $creditMoney))
			{
				$failedUserId[] = $userId;
			}
		}

		// No failure
		if (empty($failedUserId))
		{
			$this->setMessage(Text::sprintf('COM_REDSHOPB_USER_CREDIT_SET_SUCCESS', count($cid)));

			return true;
		}

		$this->setMessage(Text::sprintf('COM_REDSHOPB_USER_CREDIT_SET_FAIL', count($failedUserId)), 'warning');

		return false;
	}

	/**
	 * Method for set credit balance of the user to 0.
	 *
	 * @return  boolean  True on success, false otherwise.
	 */
	public function setcredit()
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$view = $this->input->get('view', 'users', 'string');
		$id   = $this->input->get('id', 0, 'int');

		// Prepare redirection
		if ($id && $view === 'user')
		{
			$redirect = 'index.php?option=com_redshopb&view=user&layout=edit&id=' . $id;
		}
		else
		{
			$redirect = 'index.php?option=com_redshopb&view=users';
		}

		$this->setRedirect($redirect);
		$cid = $this->input->post->get('cid', array(), 'array');

		// Adding credit to a single user on user view
		if ($id)
		{
			$cid[] = $id;
		}

		$assign      = $this->input->post->get('assign', array(), 'array');
		$currencyId  = (int) $assign['currency_id'];
		$amount      = (float) $assign['amount'];
		$creditMoney = $this->input->post->get('credit_money', array(), 'array');

		/** @var RedshopbModelUser $model */
		$model = $this->getModel();

		$failedUserId = array();

		// Iterate the users
		foreach ($cid as $userId)
		{
			// Credit the money
			if (!$model->saveWallet($userId, $currencyId, $amount, $creditMoney))
			{
				$failedUserId[] = $userId;
			}
		}

		// No failure
		if (empty($failedUserId))
		{
			$this->setMessage(Text::sprintf('COM_REDSHOPB_USER_CREDIT_SET_SUCCESS', count($cid)));

			return true;
		}

		$this->setMessage(Text::sprintf('COM_REDSHOPB_USER_CREDIT_SET_FAIL', count($failedUserId)), 'warning');

		return false;
	}

	/**
	 * Method for crediting money to users and return new wallet via JSON
	 *
	 * @return  void
	 */
	public function ajaxcredit()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app = Factory::getApplication();

		$successMessage = '';
		$errorMessage   = '';

		$id         = $this->input->get('id', 0, 'int');
		$currencyId = $this->input->get('currency_id', 0, 'int');
		$amount     = $this->input->get('amount', 0, 'float');

		/** @var RedshopbModelUser $model */
		$model = $this->getModel();

		// Credit the money
		if ($model->credit($id, $currencyId, $amount))
		{
			$successMessage = Text::sprintf('COM_REDSHOPB_USER_CREDIT_SUCCESS', 1);
		}
		else
		{
			$messages = $app->getMessageQueue();

			if ($messages)
			{
				foreach ($messages as $message)
				{
					if ($message['type'] == 'warning')
					{
						$errorMessage .= $message['message'] . '<br />';
					}
				}
			}

			$errorMessage = Text::sprintf('COM_REDSHOPB_USER_CREDIT_FAIL', 1);
		}

		echo RedshopbLayoutHelper::render(
			'user.wallet.default',
			array(
				'wallet' => RedshopbHelperWallet::getUserWallet($id, 'redshopb', true),
				'errorMessage' => $errorMessage,
				'successMessage' => $successMessage
			)
		);

		$app->close();
	}

	/**
	 * Method for reset credit balance of the user to 0, via json
	 *
	 * @return  void
	 */
	public function ajaxresetcredit()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app = Factory::getApplication();

		$successMessage = '';
		$errorMessage   = '';

		$id         = $this->input->get('id', 0, 'int');
		$currencyId = $this->input->get('currency_id', 0, 'int');
		$amount     = 0;

		/** @var RedshopbModelUser $model */
		$model = $this->getModel();

		// Credit the money
		if ($model->saveWallet($id, $currencyId, $amount))
		{
			$successMessage = Text::sprintf('COM_REDSHOPB_USER_CREDIT_RESET_SUCCESS', 1);
		}
		else
		{
			$messages = $app->getMessageQueue();

			if ($messages)
			{
				foreach ($messages as $message)
				{
					if ($message['type'] == 'warning')
					{
						$errorMessage .= $message['message'] . '<br />';
					}
				}
			}

			$errorMessage .= Text::sprintf('COM_REDSHOPB_USER_CREDIT_RESET_FAIL', 1);
		}

		echo RedshopbLayoutHelper::render(
			'user.wallet.default',
			array(
				'wallet' => RedshopbHelperWallet::getUserWallet($id, 'redshopb', true),
				'errorMessage' => $errorMessage,
				'successMessage' => $successMessage
			)
		);

		$app->close();
	}

	/**
	 * Method for set credit balance of the user to 0, via json
	 *
	 * @return  void
	 */
	public function ajaxsetcredit()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app = Factory::getApplication();

		$successMessage = '';
		$errorMessage   = '';

		$id         = $this->input->get('id', 0, 'int');
		$currencyId = $this->input->get('currency_id', 0, 'int');
		$amount     = $this->input->get('amount', 0, 'float');

		/** @var RedshopbModelUser $model */
		$model = $this->getModel();

		// Credit the money
		if ($model->saveWallet($id, $currencyId, $amount))
		{
			$successMessage = Text::sprintf('COM_REDSHOPB_USER_CREDIT_SET_SUCCESS', 1);
		}
		else
		{
			$messages = $app->getMessageQueue();

			if ($messages)
			{
				foreach ($messages as $message)
				{
					if ($message['type'] == 'warning')
					{
						$errorMessage .= $message['message'] . '<br />';
					}
				}
			}

			$errorMessage .= Text::sprintf('COM_REDSHOPB_USER_CREDIT_SET_FAIL', 1);
		}

		echo RedshopbLayoutHelper::render(
			'user.wallet.default',
			array(
				'wallet' => RedshopbHelperWallet::getUserWallet($id, 'redshopb', true),
				'errorMessage' => $errorMessage,
				'successMessage' => $successMessage
			)
		);

		$app->close();
	}

	/**
	 * Ajax function for getting order for particular user.
	 *
	 * @return  void
	 */
	public function ajaxorders()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$userId = $input->getInt('id');

		if ($userId)
		{
			/** @var RedshopbModelOrders $model */
			$model = RModelAdmin::getInstance('Orders', 'RedshopbModel');
			$model->setState('filter.user_id', $userId);

			$formName   = 'ordersForm';
			$pagination = $model->getPagination();
			$pagination->set('formName', $formName);

			echo RedshopbLayoutHelper::render('user.orders', array(
					'state' => $model->getState(),
					'items' => $model->getItems(),
					'pagination' => $pagination,
					'filter_form' => $model->getForm(),
					'activeFilters' => $model->getActiveFilters(),
					'formName' => $formName,
					'showToolbar' => true,
					'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=user&model=orders'),
					'return' => base64_encode('index.php?option=com_redshopb&view=user&layout=edit&id='
						. $userId . '&tab=orders'
					)
				)
			);
		}

		$app->close();
	}

	/**
	 * Ajax function for getting company roles for particular user.
	 *
	 * @return  void
	 */
	public function ajaxMultiCompany()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$userId = $input->getInt('id', null);

		if ($userId)
		{
			/** @var RedshopbModelUser_Multi_Companies $model */
			$model = RModelAdmin::getInstance('User_Multi_Companies', 'RedshopbModel');
			$model->setState('filter.user_id', $userId);

			$formName   = 'user_multi_companyForm';
			$pagination = $model->getPagination();
			$pagination->set('formName', $formName);

			echo RedshopbLayoutHelper::render('user.user_multi_companies', array(
					'state' => $model->getState(),
					'items' => $model->getItems(),
					'pagination' => $pagination,
					'filter_form' => $model->getForm(),
					'activeFilters' => $model->getActiveFilters(),
					'formName' => $formName,
					'showToolbar' => true,
					'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=user&model=user_multi_companies'),
					'return' => base64_encode('index.php?option=com_redshopb&view=user&layout=edit&id='
						. $userId . '&tab=multicompany'
					)
				)
			);
		}

		$app->close();
	}

	/**
	 * Ajax function for getting list of departments.
	 *
	 * @return void
	 */
	public function ajaxGetDepartments()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app       = Factory::getApplication();
		$input     = $app->input;
		$companyId = $input->getInt('companyId', 0);
		$fieldName = $input->getString('fieldName', '');
		$fieldId   = $input->getString('fieldId', '');
		$userId    = $input->getInt('userId', 0);

		/** @var RedshopbModelUser $model */
		$model = $this->getModel();
		echo $model->getDepartmentList($companyId, $fieldName, $fieldId, $userId);

		$app->close();
	}

	/**
	 * Ajax call to get the user addresses
	 *
	 * @return  void
	 */
	public function ajaxaddresses()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$userId = $input->getInt('id');

		if ($userId)
		{
			/** @var RedshopbModelAddresses $model */
			$model = RModelAdmin::getInstance('Addresses', 'RedshopbModel');
			$model->setState('filter.ajax.companyid', 0);
			$model->setState('filter.ajax.departmentid', 0);
			$app->setUserState('address.companyid', 0);
			$app->setUserState('address.departmentid', 0);
			$model->setState('filter.ajax.user_id', $userId);
			$model->setState('filter.user.include_company_data', 0);
			$model->setState('filter.user.include_department_data', 0);
			$model->setFilterForm('filter_addresses_user');
			$formName   = 'addressesForm';
			$pagination = $model->getPagination();
			$pagination->set('formName', $formName);
			$app->setUserState('address.customer_type', 'employee');
			$app->setUserState('address.customer_id', $userId);

			echo RedshopbLayoutHelper::render('user.addresses', array(
					'state' => $model->getState(),
					'items' => $model->getItems(),
					'pagination' => $pagination,
					'filter_form' => $model->getForm(),
					'activeFilters' => $model->getActiveFilters(),
					'formName' => $formName,
					'showToolbar' => true,
					'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=user&model=addresses'),
					'return' => base64_encode('index.php?option=com_redshopb&view=user&layout=edit&id='
						. $userId . '&tab=addresses&from_user=1'
					)
				)
			);
		}

		$app->close();
	}

	/**
	 * Edit own user (profile)
	 *
	 * @return  boolean
	 */
	public function editown()
	{
		$layout = $this->input->set('layout', 'own');

		return $this->edit();
	}

	/**
	 * Overridden to normalize the input for WS
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 */
	public function save($key = null, $urlVar = null)
	{
		$this->normalizeImageInput();

		return parent::save($key, $urlVar);
	}

	/**
	 * Method to activate user.
	 *
	 * @return  boolean
	 *
	 * @since   1.12.60
	 */
	public function activate()
	{
		// Check for request forgeries
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

		// Get user id
		$id = $this->input->getInt('id', 0);

		// Get users model.
		$model = RedshopbModel::getInstance('Users', 'RedshopbModel');
		$table = $model->getTable();

		// Determine the name of the primary key for the data.
		if (empty($key))
		{
			$key = $table->getKeyName();
		}

		// To avoid data collisions the urlVar may be different from the primary key.
		if (empty($urlVar))
		{
			$urlVar = $key;
		}

		// Redirect back to the edit screen.
		$this->setRedirect(
			$this->getRedirectToItemRoute($this->getRedirectToItemAppend($id, $urlVar))
		);

		if ($id && $model->activate(array($id)))
		{
			$this->setMessage(Text::_('COM_REDSHOPB_USER_ACTIVATE_SUCCESS'));

			return true;
		}
		else
		{
			$this->setMessage(Text::_('COM_REDSHOPB_USER_ACTIVATE_ERROR'));

			return false;
		}
	}
}
