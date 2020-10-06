<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Application\ApplicationHelper;
/**
 * B2B User Register  Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerB2buserregister extends RedshopbControllerForm
{
	/**
	 * We don't have a list view
	 *
	 * @var string
	 */
	protected $view_list = 'b2buserregister';

	/**
	 * Method to activate a user.
	 *
	 * @return  boolean  True on success, false on failure.
	 * @throws  Exception
	 */
	public function activate()
	{
		$input        = Factory::getApplication()->input;
		$registerFlow = RedshopbEntityConfig::getInstance()->getString('register_flow', 'normal');
		$model        = $this->getModel();

		// If account activation is disabled, throw a 403.
		if ($registerFlow === 'normal')
		{
			$this->setMessage(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 'error');

			return false;
		}

		if ($registerFlow === 'admin_approval')
		{
			$auth = urldecode($input->getString('auth', ''));

			if (empty($auth) || !$model->authorize($auth))
			{
				$this->setMessage(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 'error');

				return false;
			}
		}

		$token = $input->getAlnum('token');

		// Check that the token is in a valid format.
		if ($token === null || strlen($token) !== 32)
		{
			$this->setMessage(Text::_('JINVALID_TOKEN'), 'error');

			return false;
		}

		// Attempt to activate the user.
		$return = $model->activate($token);

		// Check for errors.
		if ($return === false)
		{
			// Redirect back to the homepage.
			$this->setMessage(Text::sprintf('COM_REDSHOPB_USER_REGISTRATION_SAVE_FAILED', $model->getError()), 'warning');
			$this->setRedirect('index.php');

			return false;
		}

		if ($registerFlow === 'admin_approval')
		{
			// Send email to user
			$sender          = RedshopbHelperEmail::getSenderInfo();
			$user            = RedshopbEntityUser::getInstance()->loadFromJoomlaUser($return->id);
			$subject         = Text::_('COM_REDSHOPB_REGISTER_FLOW_USER_APPROVED_MAIL_SUBJECT');
			$emailTemplateId = RedshopbEntityConfig::getInstance()->get('register_flow_user_approved_email_template', 0);
			$emailTemplate   = RedshopbHelperTemplate::renderTemplate(
				'user-approved', 'email', $emailTemplateId, array('user' => $user), '', null, true
			);

			if (!empty($emailTemplate->params))
			{
				$templateParams = new Registry($emailTemplate->params);
				$pSubject       = $templateParams->get('mail_subject', '');

				if (!empty($pSubject))
				{
					$subject = $pSubject;
				}
			}

			$body   = RedshopbHelperEmail::fixImagesPaths($emailTemplate->content);
			$mailer = RFactory::getMailer();

			$mailer->setSender(array($sender->sender, $sender->fromName));
			$mailer->addRecipient(array($return->email));
			$mailer->isHtml(true);
			$mailer->setSubject($subject);
			$mailer->setBody($body);
			$mailer->Encoding = 'base64';

			if (!$mailer->Send())
			{
				// Do stuff send activation email
				$this->setMessage(Text::_('COM_REDSHOPB_B2B_REGISTRATION_ERROR_COULD_NOT_SEND_EMAIL'), 'error');
			}
			else
			{
				$this->setMessage(Text::_('COM_REDSHOPB_USER_REGISTRATION_ADMIN_ACTIVATE_SUCCESS'));
			}

			$this->setRedirect(Route::_('index.php?Itemid=' . Factory::getApplication()->getMenu()->getDefault()->id, false));
		}
		else
		{
			$this->setMessage(Text::_('COM_REDSHOPB_USER_REGISTRATION_ACTIVATE_SUCCESS'));
			$this->setRedirect(RedshopbRoute::_('index.php?option=com_redshopb&view=b2buserregister&active=login', false));
		}

		return true;
	}

	/**
	 * Save B2b User
	 *
	 * @return  boolean  True on success. False otherwise.
	 *
	 * @throws  Exception
	 */
	public function saveUser()
	{
		// Check form token
		Session::checkToken('post') or jexit(Text::_('JInvalid_Token'));

		/** @var RedshopbModelB2BUserRegister $model */
		$model   = $this->getModel();
		$data    = $this->input->get('jform', array(), 'array');
		$app     = Factory::getApplication();
		$context = $this->option . '.edit.' . $this->context;
		$userId  = 0;

		// Register flow process
		$registerFlow = RedshopbEntityConfig::getInstance()->getString('register_flow', 'normal');
		$base64Return = $this->input->getBase64('return', '');
		$returnFail   = $this->input->getBase64('returnFail', '');

		// Set the return success URL if empty.
		if (empty($returnFail))
		{
			$returnFail = 'index.php?option=com_redshopb&view=b2buserregister';
		}
		else
		{
			$returnFail = base64_decode($returnFail);
		}

		if (empty($base64Return))
		{
			$return = RedshopbRoute::_('index.php?option=com_redshopb', false);
		}
		else
		{
			$return       = base64_decode($base64Return);
			$base64Return = '&return=' . $base64Return;
		}

		$returnFail = RedshopbRoute::_($returnFail . $base64Return, false);

		// Validate the posted data. Sometimes the form needs some posted data, such as for plugins and modules.
		/** @var RedshopbForm $form */
		$form = $model->getForm($data, false);

		if (!$form)
		{
			$app->enqueueMessage($model->getError(), 'error');

			return false;
		}

		// Try to remove un-necessary fieldset.
		if (strcmp($data['register_type'], 'personal') === 0)
		{
			$removeFieldset = 'company_extra_fields';
			$elements		= $form->getXml()->xpath('//fieldset[@name="' . $removeFieldset . '" and not(ancestor::field/form/*)]');
		}

		if (!empty($elements))
		{
			$dom = dom_import_simplexml($elements[0]);
			$dom->parentNode->removeChild($dom);
		}

		// Test whether the data is valid.
		$validData = $model->validate($form, $data);

		// Check for validation errors.
		if ($validData === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			$len = count($errors);

			for ($i = 0; $i < $len && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session.
			$app->setUserState($context . '.data', $data);
			$this->setRedirect($returnFail);

			return false;
		}

		if ($validData['register_type'] === 'business'
			&& (!isset($data['vat_number_invalid']) || !$data['vat_number_invalid'])
			&& RedshopbEntityCountry::getInstance($validData['billing_country_id'])->get('eu_zone')
		)
		{
			$validationResult = $this->checkViesValidation(true);

			if ($validationResult !== true)
			{
				if (is_string($validationResult))
				{
					$app->enqueueMessage($validationResult, 'warning');
				}
				else
				{
					$app->enqueueMessage(Text::_('COM_REDSHOPB_VIES_REGISTRATION_INVALID_VAT_NUMBER'), 'warning');
				}

				$app->setUserState($context . '.data', $data);
				$this->setRedirect($returnFail);
			}
		}

		if (in_array($registerFlow, array('email', 'admin_approval')))
		{
			$token                   = ApplicationHelper::getHash(UserHelper::genRandomPassword());
			$validData['userStatus'] = 0;
			$validData['activation'] = $token;
		}

		if (!$validData['company_id'])
		{
			$app->enqueueMessage(Text::_('COM_REDSHOPB_OPERATION_NOT_PERMITTED'), 'warning');
			$this->setRedirect(Uri::root());
		}

		try
		{
			$model->save($validData);
		}
		catch (Exception $e)
		{
			$errors = implode(',', $model->getErrors());
			$app->enqueueMessage($e->getMessage(), 'error');
			unset($validData['password'], $validData['password2']);
			$app->setUserState($context . '.data', $validData);
			$this->setRedirect($returnFail, $errors, 'error');

			return false;
		}

		$userId = $model->getState($model->getName() . '.id');
		$app->setUserState($context . '.data', array());

		$companyActivation = (bool) RedshopbEntityConfig::getInstance()->get('autoactivate_b2b_company', false);

		// If this is company register. Notice to user.
		if ($validData['register_type'] === 'business')
		{
			if (false === $companyActivation)
			{
				$this->setRedirect(
					RedshopbRoute::_('index.php?option=com_redshopb', false),
					Text::sprintf('COM_REDSHOPB_B2B_BUSINESS_REGISTER_SUCCESS_NOTICE', $validData['business_company_name']),
					'notice'
				);
			}
			else
			{
				$app->enqueueMessage(
					Text::sprintf('COM_REDSHOPB_B2B_BUSINESS_REGISTER_SUCCESS_NOTICE_AUTO_ACTIVATED', $validData['business_company_name']),
					'success'
				);
			}
		}

		$deliveryAddress = RedshopbEntityUser::load($userId)->getAddress(true)->getExtendedData();
		$app->setUserState('checkout.delivery_address_id', $deliveryAddress->id);
		$app->setUserState("checkout.usebilling", true);

		$config         = RedshopbEntityConfig::getInstance();
		$userAdd        = $config->getInt('user_notification_user_add', 0);
		$userRecipients = trim($config->getString('user_notification_user_add_to_users'));
		$templateId     = $config->getInt('user_notification_user_add_template', 0);
		$sender         = RedshopbHelperEmail::getSenderInfo();

		if ($userAdd && $userId && !empty($userRecipients))
		{
			$emails        = explode(',', $userRecipients);
			$mailer        = RFactory::getMailer();
			$templateParam = RedshopbEntityTemplate::getInstance($templateId)->getParams(true);

			foreach ($emails as $i => $email)
			{
				$mailer->addRecipient(trim($email));
			}

			$subject = $templateParam->get('mail_subject', Text::_('COM_REDSHOPB_USER_NOTIFICATION_NEW_USER_ADDED'));

			$mailer->setSender(array($sender->sender, $sender->fromName));
			$mailer->isHtml(true);
			$mailer->Encoding = 'base64';
			$mailer->setSubject($subject);
			$mailer->setBody(
				RedshopbHelperTemplate::renderTemplate(
					'user-added', 'email', $templateId,
					array('user' => RedshopbEntityUser::load($userId))
				)
			);

			if (!$mailer->Send())
			{
				$this->setMessage(Text::_('COM_REDSHOPB_USER_NOTIFICATION_NEW_USER_ADDED_ERROR'), 'warning');
				$this->setRedirect(RedshopbRoute::_($returnFail, false));
			}
		}

		// For activated by email.
		if ($registerFlow === 'email')
		{
			// Handle account activation/confirmation emails.
			$uri            = Uri::getInstance();
			$urlBase        = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
			$activationLink = $urlBase . RedshopbRoute::_('index.php?option=com_redshopb&task=b2buserregister.activate&token=' . $token, false);
			$subject        = '';
			$body           = '';

			$emailTemplateId    = RedshopbEntityConfig::getInstance()->get('activation_email_template', 0);
			$emailTemplateParam = RedshopbEntityTemplate::getInstance($emailTemplateId)->getParams(true);

			$emailTemplate = RedshopbHelperTemplate::renderTemplate(
				'email', 'email', $emailTemplateId, compact(array_keys(get_defined_vars())), '', null, true
			);

			if (!$emailTemplateId || !$emailTemplate)
			{
				$lang = Factory::getLanguage();
				$lang->load('com_users', JPATH_SITE, null, false, true);

				$subject = Text::sprintf(
					'COM_USERS_EMAIL_ACTIVATE_WITH_ADMIN_ACTIVATION_SUBJECT',
					$validData['name1'],
					Factory::getConfig()->get('sitename')
				);

				$body = Text::sprintf(
					'COM_USERS_EMAIL_ACTIVATE_WITH_ADMIN_ACTIVATION_BODY',
					Factory::getConfig()->get('sitename'),
					$validData['name1'],
					$validData['email'],
					$validData['username'],
					$activationLink
				);
			}
			else
			{
				$subject = $emailTemplateParam->get('mail_subject');
				$body    = RedshopbHelperEmail::fixImagesPaths($emailTemplate->content);
			}

			$mailer = RFactory::getMailer();
			$mailer->setSender(array($sender->sender, $sender->fromName));
			$mailer->addRecipient(array($validData['email']));
			$mailer->isHtml(true);
			$mailer->Encoding = 'base64';
			$mailer->setSubject($subject);
			$mailer->setBody($body);

			if (!$mailer->Send())
			{
				// Do stuff send activation email
				$this->setMessage(Text::_('COM_REDSHOPB_B2B_REGISTRATION_ERROR_COULD_NOT_SEND_EMAIL'), 'error');
				$this->setRedirect($returnFail);

				return true;
			}

			// Do stuff send activation email
			$this->setMessage(Text::_('COM_REDSHOPB_B2B_REGISTRATION_NOTIFY_USER_ACTIVE_BY_EMAIL'), 'notice');
			$this->setRedirect(RedshopbRoute::_($base64Return, false));
		}
		elseif ($registerFlow === 'admin_approval')
		{
			// Handle account activation/confirmation emails.
			$uri     = Uri::getInstance();
			$urlBase = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
			$emails  = trim($config->getString('register_flow_to_emails', ''));

			// Send admin approval email for admin
			if (!empty($emails))
			{
				$emails = explode(',', $emails);

				$adminEmailTemplateId     = RedshopbEntityConfig::getInstance()->get('register_flow_approval_email_template', 0);
				$adminEmailTemplateParams = RedshopbEntityTemplate::getInstance($adminEmailTemplateId)->getParams(true);

				foreach ($emails as $email)
				{
					$admin = RedshopbEntityUser::getInstance()->loadFromEmail($email);
					$user  = RedshopbEntityUser::getInstance()->loadFromEmail($validData['email']);

					$linkSuffix     = '&token=' . $token . '&auth=' . $model->generateAuthorizationCode($email);
					$activationLink = $urlBase . RedshopbRoute::_('index.php?option=com_redshopb&task=b2buserregister.activate' . $linkSuffix, false);
					$editLink       = $urlBase . RedshopbRoute::_('index.php?option=com_redshopb&task=user.edit&id=' . (int) $user->get('id'), false);

					$emailTemplate = RedshopbHelperTemplate::renderTemplate(
						'admin-approval', 'email', $adminEmailTemplateId, compact(array_keys(get_defined_vars())), '', null, true
					);

					$subject = $adminEmailTemplateParams->get(
						'mail_subject',
						Text::sprintf(
							'COM_USERS_EMAIL_ACTIVATE_WITH_ADMIN_ACTIVATION_SUBJECT',
							$validData['name1'],
							Factory::getConfig()->get('sitename')
						)
					);

					$body   = RedshopbHelperEmail::fixImagesPaths($emailTemplate->content);
					$mailer = RFactory::getMailer();
					$mailer->setSender(array($sender->sender, $sender->fromName));
					$mailer->addRecipient(array($email));
					$mailer->isHtml(true);
					$mailer->Encoding = 'base64';
					$mailer->setSubject($subject);
					$mailer->setBody($body);

					if (!$mailer->Send())
					{
						// Do stuff send activation email
						$this->setMessage(Text::_('COM_REDSHOPB_B2B_REGISTRATION_ERROR_COULD_NOT_SEND_EMAIL'), 'error');
						$this->setRedirect($returnFail);

						return true;
					}
				}
			}

			// Send notify email to user after registration
			$user     = RedshopbEntityUser::getInstance()->loadFromEmail($validData['email']);
			$siteName = Factory::getConfig()->get('sitename');

			$userNotifyEmailTemplateId = RedshopbEntityConfig::getInstance()->get('register_flow_user_notify_after_register_email_template', 0);
			$userNotifyEmailTemplate   = RedshopbHelperTemplate::renderTemplate(
				'user-approve-after-register', 'email', $userNotifyEmailTemplateId, compact(array_keys(get_defined_vars())), '', null, true
			);

			$userNotifyEmailTemplateParam = RedshopbEntityTemplate::getInstance($userNotifyEmailTemplateId)->getParams(true);

			$userNotifyEmailSubject = $userNotifyEmailTemplateParam->get(
				'mail_subject',
				Text::sprintf('COM_REDSHOPB_MAIL_USER_NOTIFY_AFTER_REGISTER_SUBJECT', $siteName)
			);

			$body = RedshopbHelperEmail::fixImagesPaths($userNotifyEmailTemplate->content);

			$mailer = RFactory::getMailer();
			$mailer->setSender(array($sender->sender, $sender->fromName));
			$mailer->addRecipient(array($validData['email']));
			$mailer->isHtml(true);
			$mailer->Encoding = 'base64';
			$mailer->setSubject($userNotifyEmailSubject);
			$mailer->setBody($body);

			if (!$mailer->Send())
			{
				// Do stuff send activation email
				$this->setMessage(Text::_('COM_REDSHOPB_B2B_REGISTRATION_ERROR_COULD_NOT_SEND_EMAIL'), 'error');
				$this->setRedirect($returnFail);

				return true;
			}

			// Do stuff send activation email
			$this->setMessage(Text::_('COM_REDSHOPB_B2B_REGISTRATION_NOTIFY_USER_ACTIVE_BY_ADMIN_APPROVAL'), 'notice');
			$this->setRedirect(RedshopbRoute::_('index.php?option=com_redshopb&view=b2buserregister&active=login' . $base64Return, false));
		}
		elseif ('personal' === $validData['register_type']
			|| ('business' === $validData['register_type'] && true === $companyActivation)
		)
		{
			// For normal process. Login after register successful.
			$credentials = array(
				'username' => $validData['username'],
				'password' => $validData['password']
			);

			if (RUser::userLogin($credentials))
			{
				$this->setMessage(Text::_('COM_REDSHOPB_B2B_REGISTRATION_SAVE_SUCCESS'));
				$this->setRedirect($return);
			}
			else
			{
				$this->setMessage(Text::_('COM_REDSHOPB_B2B_REGISTRATION_LOGIN_ERROR'), 'error');
				$this->setRedirect($returnFail);
			}
		}

		return true;
	}

	/**
	 * Method for user login
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 */
	public function login()
	{
		// Check form token
		Session::checkToken('post') or jexit(Text::_('JINVALID_TOKEN'));

		$app     = Factory::getApplication();
		$input   = $app->input;
		$method  = $input->getMethod();
		$context = $this->option . '.edit.' . $this->context . '.login';

		$base64ReturnSuccess = $input->post->getBase64('returnSuccess', '');
		$returnFail          = $input->post->getBase64('returnFail', '');

		// Set the return success URL if empty.
		$returnFail = empty($returnFail) ? 'index.php?option=com_redshopb' : base64_decode($returnFail);

		// Get the log in options.
		$options = array(
			'remember' => $this->input->getBool('remember', false)
		);

		// Get the log in credentials.
		$credentials = array(
			'username' => $input->{$method}->get('username', '', 'USERNAME'),
			'password' => $input->{$method}->get('password', '', 'RAW')
		);

		// Set the return success URL if empty.
		if (!empty($base64ReturnSuccess))
		{
			$returnSuccess       = base64_decode($base64ReturnSuccess);
			$base64ReturnSuccess = '&return=' . $base64ReturnSuccess;
		}

		// Perform the log in.
		if ($app->login($credentials, $options) === true)
		{
			if (empty($base64ReturnSuccess))
			{
				$menu       = Factory::getApplication()->getMenu();
				$activeMenu = $menu->getActive();

				if ($activeMenu->query['option'] === 'com_redshopb' && $activeMenu->query['view'] === 'b2buserregister')
				{
					$redirectMenuId = RedshopbApp::getUser()->hasId() ?
						$activeMenu->getParams()->get('redirect_vanir_user', 0) : $activeMenu->getParams()->get('redirect_not_vanir_user', 0);
					$redirectMenuId = !$redirectMenuId ? $menu->getDefault() : $menu->getItem((int) $redirectMenuId);
					$returnSuccess  = $redirectMenuId->link . '&Itemid=' . $redirectMenuId->id;
				}

				$returnSuccess = empty($returnSuccess) ? 'index.php?option=com_redshopb' : $returnSuccess;
			}

			// Set the return URL in the user state to allow modification by plugins
			$app->setUserState('users.login.form.return', $returnSuccess);

			// Success
			if (true === (boolean) $options['remember'])
			{
				$app->setUserState('rememberLogin', true);
			}

			$app->setUserState($context . '.data', array());
			$app->redirect(RedshopbRoute::_($app->getUserState('users.login.form.return'), false));
		}

		// Login failed !
		$data = array(
			'remember' => (int) $options['remember']
		);
		$data = array_replace($data, $credentials);

		$app->setUserState($context . '.data', $data);
		$app->redirect(RedshopbRoute::_($returnFail . $base64ReturnSuccess, false));
	}

	/**
	 * Check VIES validation
	 *
	 * @param   boolean $returnResult Return result
	 *
	 * @throws  Exception
	 *
	 * @return  mixed
	 */
	public function checkViesValidation($returnResult = false)
	{
		// Check form token
		Session::checkToken('post') or jexit(Text::_('JINVALID_TOKEN'));

		$app   = Factory::getApplication();
		$db    = Factory::getDbo();
		$input = $app->input;
		$jform = $input->get('jform', array(), 'array');

		if (!array_key_exists('country_id', $jform))
		{
			$jform['country_id'] = $jform['billing_country_id'];
		}

		$query = $db->getQuery(true)
			->select($db->qn('alpha2'))
			->from($db->qn('#__redshopb_country'))
			->where('id = ' . $db->q($jform['country_id']));

		$params = array(
			'countryCode' => $db->setQuery($query, 0, 1)
				->loadResult(),
			'vatNumber'   => $jform['vat_number']
		);

		if (Factory::getUser()->guest)
		{
			$vendorEntity = RedshopbEntityCompany::getInstance(RedshopbApp::getMainCompany()->get('id'))
				->getVendor();

			if ($vendorEntity->isLoaded()
				&& $vendorEntity->get('vat_number')
				&& $vendorEntity->getAddress()->getCountry()->get('alpha2')
			)
			{
				$params['requesterCountryCode'] = $vendorEntity->getAddress()->getCountry()->get('alpha2');
				$params['requesterVatNumber']   = $vendorEntity->get('vat_number');
			}
		}

		$client = new SoapClient('http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl');

		$result = false;

		try
		{
			$r = $client->checkVatApprox($params);

			if ($r->valid == true)
			{
				$result = true;
			}
			else
			{
				$result = Text::_('COM_REDSHOPB_VIES_REGISTRATION_VALIDATION')
					. ': ' . Text::_('COM_REDSHOPB_VIES_REGISTRATION_INVALID_VAT_NUMBER');
			}
		}
		catch (SoapFault $e)
		{
			$ret = $e->faultstring;

			switch (strtoupper($ret))
			{
				case 'INVALID_INPUT':
					$mainMsg = Text::_('COM_REDSHOPB_VIES_REGISTRATION_COUNTRYCODE_IS_INVALID');
					break;
				case 'SERVICE_UNAVAILABLE':
					$mainMsg = Text::_('COM_REDSHOPB_VIES_REGISTRATION_SOAP_UNAVAILABLE');
					break;
				case 'MS_UNAVAILABLE':
					$mainMsg = Text::_('COM_REDSHOPB_VIES_REGISTRATION_MEMBER_STATE_UNAVAILABLE');
					break;
				case 'TIMEOUT':
					$mainMsg = Text::_('COM_REDSHOPB_VIES_REGISTRATION_MEMBER_STATE_NOT_BE_REACHED');
					break;
				case 'SERVER_BUSY':
					$mainMsg = Text::_('COM_REDSHOPB_VIES_REGISTRATION_SERVICE_CANNOT_PROCESS');
					break;
				default:
					$mainMsg = Text::_('COM_REDSHOPB_VIES_REGISTRATION_INVALID');
					break;
			}

			$result = Text::_('COM_REDSHOPB_VIES_REGISTRATION_VALIDATION') . ' - ' . $mainMsg;
		}

		if (!$returnResult)
		{
			echo json_encode($result);

			$app->close();
		}

		return $result;
	}
}
