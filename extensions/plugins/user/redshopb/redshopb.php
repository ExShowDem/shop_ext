<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  User.Redshopb
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Language;

/**
 * Redshopb User plugin
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Authentication.Redshopb
 * @since       1.0
 */
class PlgUserRedshopb extends CMSPlugin
{
	/**
	 * @var boolean
	 */
	protected $autoloadLanguage = true;

	/**
	 * Application object
	 *
	 * @var    CMSApplication
	 * @since  3.2
	 */
	protected $app;

	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  3.2
	 */
	protected $db;

	/**
	 * @var object
	 */
	protected $oldUser;

	/**
	 * @var array
	 */
	protected $oldB2CCart;

	/**
	 * @var boolean
	 */
	protected $isWebservice = false;

	/**
	 * Constructor
	 *
	 * @param   object  $subject   The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);

		// Load redSHOPB Library
		JLoader::import('redshopb.library');

		if ($this->app->isClient('site'))
		{
			$lang = Factory::getLanguage();
			$lang->load('com_redshopb', JPATH_SITE);
		}

		$api = strtolower($this->app->input->getString('api', ''));

		if (in_array($api, array('hal', 'soap')))
		{
			$this->isWebservice = true;
		}
	}

	/**
	 * This method will return a user object
	 *
	 * If options['autoregister'] is true, if the user doesn't exist yet he will be created
	 *
	 * @param   array  $user     Holds the user data.
	 * @param   array  $options  Array holding options (remember, autoregister, group).
	 *
	 * @return  object  A User object
	 *
	 * @since   1.5
	 */
	protected function _getUser($user, $options = array())
	{
		$instance = User::getInstance();
		$id       = (int) UserHelper::getUserId($user['username']);

		if ($id)
		{
			$instance->load($id);

			return $instance;
		}

		// TODO : move this out of the plugin *bump*
		$config = ComponentHelper::getParams('com_users');

		// Hard coded default to match the default value from com_users.
		$defaultUserGroup = $config->get('new_usertype', 2);

		$instance->set('id', 0);
		$instance->set('name', $user['fullname']);
		$instance->set('username', $user['username']);
		$instance->set('password_clear', $user['password_clear']);

		// Result should contain an email (check).
		$instance->set('email', $user['email']);
		$instance->set('groups', array($defaultUserGroup));

		// If autoregister is set let's register the user
		$autoregister = isset($options['autoregister']) ? $options['autoregister'] : $this->params->get('autoregister', 1);

		if ($autoregister)
		{
			if (!$instance->save())
			{
				Log::add('Error in autoregistration for user ' . $user['username'] . '.', Log::WARNING, 'error');
			}
		}
		else
		{
			// No existing user and autoregister off, this is a temporary user.
			$instance->set('tmp_user', true);
		}

		return $instance;
	}

	/**
	 * This method should handle any login logic and report back to the subject
	 *
	 * @param   array  $user     Holds the user data
	 * @param   array  $options  Array holding options (remember, autoregister, group)
	 *
	 * @return  boolean  True on success
	 */
	public function onUserLogin($user, $options = array())
	{
		if (!$this->app->isClient('site'))
		{
			return true;
		}

		// Cleans up impersonation input
		$this->app->input->set('company_id', 0);
		$this->app->input->set('department_id', 0);
		$this->app->input->set('rsbuser_id', 0);
		$this->app->setUserState('list.company_id', 0);
		$this->app->setUserState('list.department_id', 0);
		$this->app->setUserState('list.rsbuser_id', 0);

		$this->app->setUserState('shop.multi_company_id', 0);
		$this->app->setUserState('shop.role_type_id', 0);

		$this->app->setUserState('shop.customer_type', '');
		$this->app->setUserState('shop.customer_id', 0);

		if (!$this->isWebservice)
		{
			$this->oldUser    = RedshopbHelperCommon::getUser();
			$this->oldB2CCart = RedshopbHelperCart::getCart(RedshopbHelperCompany::getCompanyB2C(), 'company')->get('items', array());
		}

		$instance = $this->_getUser($user, $options);

		// If _getUser returned an error, then pass it back.
		if ($instance instanceof Exception)
		{
			return false;
		}

		// If Super Admin, returns true
		if ($instance->authorise('core.admin'))
		{
			return true;
		}

		$vanirUser   = RedshopbEntityUser::getInstanceByField('joomla_user_id', $instance->get('id'))->loadItem();
		$userCompany = $vanirUser->getSelectedCompany();

		// If User Company is selected Check if that company is currently enabled
		if ($userCompany)
		{
			$db      = Factory::getDbo();
			$query   = $db->getQuery(true)
				->select('parent.id, parent.name')
				->from($db->qn('#__redshopb_company', 'parent'))
				->where($db->qn('parent.deleted') . ' = 0')
				->leftJoin(
					$db->qn('#__redshopb_company', 'node') . ' ON node.lft BETWEEN parent.lft AND parent.rgt AND ' . $db->qn('node.deleted') . ' = 0'
				)
				->where('parent.state = 0')
				->where('parent.level > 0')
				->where('node.id = ' . $userCompany->getId())
				->order('parent.lft');
			$company = $db->setQuery($query)->loadObject();

			if ($company)
			{
				$this->app->enqueueMessage(Text::sprintf('COM_REDSHOPB_AUTH_ERROR_UNPUBLISH_COMPANY', $company->name), 'warning');
				$this->app->redirect(Route::_('index.php?option=com_users&view=login', false));

				return false;
			}
		}

		return true;
	}

	/**
	 * We set default values for the current user and other permission values
	 *
	 * @return  true|null
	 */
	public function setVanirUserDefaultValues()
	{
		// Reset 'shop' session values first
		$this->app->setUserState('shop.customer_id', 0);
		$this->app->setUserState('shop.customer_type', '');
		$userRSid       = RedshopbHelperUser::getUserRSid();
		$vanirUser      = RedshopbEntityUser::getInstance($userRSid)->loadItem();
		$userCompany    = $vanirUser->getSelectedCompany();
		$canImpersonate = false;
		$companiesCount = array();

		if ($userRSid)
		{
			$user           = Factory::getUser();
			$companiesCount = RedshopbHelperACL::listAvailableCompanies(
				$user->id, 'comma', 0, '', 'redshopb.order.impersonate', '', false, false, false, false
			);
			$companiesCount = explode(',', $companiesCount);
			$canImpersonate = RedshopbHelperACL::getPermissionInto('impersonate', 'order', 0, 'redshopb', $user->id);
			$userDepartment = RedshopbHelperUser::getUserDepartmentId($userRSid);
			$userCustomerId = RedshopbHelperUser::getUserCompanyId($userRSid);

			// Set 'shop' session values
			$this->app->setUserState('shop.customer_type', 'employee');
			$this->app->setUserState('shop.customer_id', $userRSid);

			// Set 'list' session values
			if ($canImpersonate)
			{
				$listCompanyId    = $this->app->getUserState('list.company_id', 0);
				$listDepartmentId = $this->app->getUserState('list.department_id', 0);
				$listUserId       = $this->app->getUserState('list.rsbuser_id', 0);
				$companyId        = 0;
				$departmentId     = 0;

				if ($listCompanyId)
				{
					if (!in_array($listCompanyId, $companiesCount))
					{
						if (count($companiesCount) > 0)
						{
							$companyId = $companiesCount[0];
							$this->app->setUserState('list.company_id', $companyId);
						}
						else
						{
							$this->app->setUserState('list.company_id', 0);
						}
					}
					else
					{
						$companyId = $listCompanyId;
					}
				}

				if ($listDepartmentId)
				{
					$departmentsCount = RedshopbHelperACL::listAvailableDepartments(
						$user->id, 'comma', $companyId, false, 0, '', 'redshopb.order.impersonate'
					);
					$departmentsCount = explode(',', $departmentsCount);

					if (!in_array($listDepartmentId, $departmentsCount))
					{
						if (count($departmentsCount) > 0)
						{
							$departmentId = $departmentsCount[0];
							$this->app->setUserState('list.department_id', $departmentId);
						}
						else
						{
							$this->app->setUserState('list.department_id', 0);
						}
					}
					else
					{
						$departmentId = $listDepartmentId;
					}
				}

				if ($listUserId)
				{
					$employeeCount = RedshopbHelperACL::listAvailableEmployees(
						$companyId, $departmentId, 'comma', '', '', 0, 0, 'redshopb.order.impersonate'
					);
					$employeeCount = explode(',', $employeeCount);

					if (!in_array($listUserId, $employeeCount))
					{
						if (count($employeeCount) > 0)
						{
							$this->app->setUserState('list.rsbuser_id', $employeeCount[0]);
						}
						else
						{
							$this->app->setUserState('list.rsbuser_id', 0);
						}
					}
				}
			}

			// For can not impersonate
			else
			{
				$this->app->setUserState('list.company_id', $userCustomerId);
				$this->app->setUserState('list.department_id', $userDepartment);
				$this->app->setUserState('list.rsbuser_id', $userRSid);
			}
		}

		if (!empty($this->oldB2CCart))
		{
			// Clear cart session
			RedshopbHelperCart::clearCartFromSession(true);
			$this->app->enqueueMessage(Text::_('COM_REDSHOPB_CART_UPDATED_CART'), "warning");
		}

		// If currently not in B2C mode, run as normal. Or in b2c mode but there are no item in cart.
		if (!$this->oldUser || !$this->oldUser->b2cMode || empty($this->oldB2CCart))
		{
			return true;
		}

		if ($userRSid)
		{
			$companyId = RedshopbHelperCompany::getCompanyIdByCustomer($userRSid, 'employee');

			// If config is use collection for shopping.
			if (RedshopbHelperShop::inCollectionMode(RedshopbEntityCompany::getInstance($companyId)))
			{
				$collections = RedshopbHelperCollection::getCustomerCollectionsForShop($userRSid, 'employee');

				// User doesn't have any collection. No need to re-add item.
				if (empty($collections))
				{
					return true;
				}

				foreach ($this->oldB2CCart as $item)
				{
					if (!in_array($item['collectionId'], $collections))
					{
						// Product is not in user collection, skip it.
						continue;
					}

					RFactory::getDispatcher()->trigger('setVanirUserDefaultValuesBeforeAddToCart', array(&$item));

					// Re-add product into cart session.
					RedshopbHelperCart::addToCartById(
						$item['productId'],
						$item['productItem'],
						null,
						$item['quantity'],
						$item['price'],
						$item['currency'],
						$userRSid,
						'employee',
						$item['collectionId']
					);
				}
			}
			else
			{
				if (!$canImpersonate && !RedshopbHelperUser::isEmployee($userRSid, $companyId)
					|| ($canImpersonate && !in_array($companyId, $companiesCount)))
				{
					return true;
				}

				$customerId         = $this->app->getUserState('shop.customer_id');
				$customerType       = $this->app->getUserState('shop.customer_type');
				$purchaserCompanyId = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);

				if ($purchaserCompanyId)
				{
					$currencyId = RedshopbEntityCompany::getInstance($purchaserCompanyId)->getCustomerCurrency();
				}
				else
				{
					$currencyId = RedshopbApp::getDefaultCurrency()->get('id');
				}

				foreach ($this->oldB2CCart as $item)
				{
					$price = null;

					if (!$item['productItem'])
					{
						$price = RedshopbHelperPrices::getProductPrice(
							$item['productId'], $customerId, $customerType, $currencyId, array(), '', 0, $item['quantity'], false, true
						);
					}
					else
					{
						$price = RedshopbHelperPrices::getProductItemPrice(
							$item['productItem'],
							$customerId,
							$customerType,
							$currencyId,
							array($item['collectionId']),
							'',
							0,
							$item['quantity'],
							false,
							true
						);
					}

					RFactory::getDispatcher()->trigger('setVanirUserDefaultValuesBeforeAddToCart', array(&$item));

					// Re-add product into cart session.
					RedshopbHelperCart::addToCartById(
						$item['productId'],
						$item['productItem'],
						null,
						$item['quantity'],
						($price->price ? $price->price : 0),
						$currencyId,
						$customerId,
						$customerType,
						$item['collectionId'],
						0,
						$item['stockroomId']
					);
				}
			}
		}
	}

	/**
	 * We set the authentication cookie only after login is successfully finished.
	 * We set a new cookie either for a user with no cookies or one
	 * where the user used a cookie to authenticate.
	 *
	 * @param   array  $options  Array holding options
	 *
	 * @return  boolean  True on success
	 */
	public function onUserAfterLogin($options)
	{
		// Not execute for admin side and for web services authentications
		if ($this->app->isAdmin() || $this->isWebservice)
		{
			return true;
		}

		$userRSid    = RedshopbHelperUser::getUserRSid();
		$vanirUser   = RedshopbEntityUser::getInstance($userRSid)->loadItem();
		$userCompany = $vanirUser->getSelectedCompany();

		// If User Company is selected Check if that company is currently enabled
		if ($userCompany)
		{
			// Set Default values for the user
			$this->setVanirUserDefaultValues();
		}

		return true;
	}

	/**
	 * Utility method to act on a user after it has been saved.
	 *
	 * This method sends a registration email to new users created in the backend.
	 *
	 * @param   array    $user     Holds the new user data.
	 * @param   boolean  $isnew    True if a new user is stored.
	 * @param   boolean  $success  True if user was successfully stored in the database.
	 * @param   string   $msg      Message.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		$mailToUser   = $this->params->get('mail_to_user', 1);
		$registerFlow = RedshopbEntityConfig::getInstance()->get('register_flow', 'normal');

		if ($isnew && $this->app->isClient('site') && $mailToUser
			&& isset($user['send_email']) && $user['send_email'] == 1
			&& (!isset($user['use_company_email']) || $user['use_company_email'] == 0)
			&& $registerFlow == 'normal')
		{
			$userParams = new Registry($user['params']);

			if ($userParams->get('migratedFromCSV', 0) == 1)
			{
				return;
			}

			/**
			 * Look for user language. Priority:
			 * 	1. User frontend language
			 * 	2. Default site language
			 */
			$lang          = Factory::getLanguage();
			$defaultLocale = $lang->getTag();
			$userLocale    = $userParams->get('language', $defaultLocale);

			if ($userLocale != $defaultLocale)
			{
				$lang = Language::getInstance($userLocale);
			}

			$lang->load('plg_user_joomla', JPATH_ADMINISTRATOR);

			// Compute the mail subject.
			$emailSubject = Text::sprintf(
				'PLG_USER_JOOMLA_NEW_USER_EMAIL_SUBJECT',
				$user['name'],
				$config   = $this->app->get('sitename')
			);

			// Compute the mail body.
			$emailBody = RedshopbLayoutFile::getInstance(
				'email.register'
			)->render(
				array(
					'name' => $user['name'],
					'siteName' => $this->app->get('sitename'),
					'url' => Uri::root(),
					'userName' => $user['username'],
					'password' => $user['password_clear']
				)
			);

			// Assemble the email data...the sexy way!
			$mail = RFactory::getMailer()
				->setSender(
					array(
						$this->app->get('mailfrom'),
						$this->app->get('fromname')
					)
				)
				->isHtml(true)
				->addRecipient($user['email'])
				->setSubject($emailSubject)
				->setBody($emailBody);

			// Set application language back to default if we changed it
			if ($userLocale != $defaultLocale)
			{
				$lang = Language::getInstance($defaultLocale);
			}

			if (!$mail->Send())
			{
				$this->app->enqueueMessage(Text::_('JERROR_SENDING_EMAIL'), 'warning');
			}
		}
	}
}
