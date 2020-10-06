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
use Joomla\CMS\Form\Form;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;

/**
 * Company Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerCompany extends RedshopbControllerForm
{
	/**
	 * @var  string
	 */
	protected $text_prefix = 'COM_REDSHOPB_COMPANY';

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 */
	protected function getRedirectToListAppend()
	{
		$append      = parent::getRedirectToListAppend();
		$fromCompany = RedshopbInput::isFromCompany();

		// Append the tab name for the company view
		if ($fromCompany)
		{
			$append .= '&tab=companies';
		}

		return $append;
	}

	/**
	 * Ajax call to get users tab content.
	 *
	 * @return  void
	 */
	public function ajaxusers()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$companyId = $input->getInt('id');

		if ($companyId)
		{
			/** @var RedshopbModelUsers $usersModel */
			$model = RModelAdmin::getInstance('Users', 'RedshopbModel');
			$model->set('filterFormName', 'filter_users_company');
			$state         = $model->getState();
			$filterCompany = $app->getUserState('user.company_id', 0);

			if ($filterCompany != $companyId)
			{
				$model->setState('filter.department', 0);
				$app->setUserState('filter.department_id', 0);
			}

			$model->setState('filter.company', $companyId);
			$app->setUserState('user.company_id', $companyId);

			$formName   = 'usersForm';
			$pagination = $model->getPagination();
			$pagination->set('formName', $formName);

			echo RedshopbLayoutHelper::render('company.users', array(
					'state' => $state,
					'items' => $model->getItems(),
					'pagination' => $pagination,
					'filterForm' => $model->getForm(),
					'activeFilters' => $model->getActiveFilters(),
					'formName' => $formName,
					'showToolbar' => true,
					'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=company&model=users'),
					'return' => base64_encode('index.php?option=com_redshopb&view=company&layout=edit&id='
						. $companyId . '&tab=users&from_company=1'
					)
				)
			);
		}

		$app->close();
	}

	/**
	 * Ajax call to get companies tab content.
	 *
	 * @return  void
	 */
	public function ajaxcompanies()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$companyId = $input->getInt('id');

		if ($companyId)
		{
			/** @var RedshopbModelCompanies $model */
			$model = RModelAdmin::getInstance('Companies', 'RedshopbModel');
			$model->setState('filter.parent', $companyId);
			$app->setUserState('company.parent_id', $companyId);

			$formName   = 'companiesForm';
			$pagination = $model->getPagination();
			$pagination->set('formName', $formName);

			echo RedshopbLayoutHelper::render('company.companies', array(
					'state' => $model->getState(),
					'items' => $model->getItems(),
					'pagination' => $pagination,
					'filter_form' => $model->getForm(),
					'activeFilters' => $model->getActiveFilters(),
					'formName' => $formName,
					'showToolbar' => true,
					'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=company&model=companies'),
					'return' => base64_encode('index.php?option=com_redshopb&view=company&layout=edit&id='
						. $companyId . '&tab=companies&from_company=1'
					)
				)
			);
		}

		$app->close();
	}

	/**
	 * Ajax call to get departments tab content.
	 *
	 * @return  void
	 */
	public function ajaxdepartments()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$companyId = $input->getInt('id');

		if ($companyId)
		{
			/** @var RedshopbModelDepartments $model */
			$model = RModelAdmin::getInstance('Departments', 'RedshopbModel');
			$model->setState('filter.company', $companyId);
			$app->setUserState('department.company_id', $companyId);

			$formName   = 'departmentsForm';
			$pagination = $model->getPagination();
			$pagination->set('formName', $formName);

			echo RedshopbLayoutHelper::render('company.departments', array(
					'state' => $model->getState(),
					'items' => $model->getItems(),
					'pagination' => $pagination,
					'filter_form' => $model->getForm(),
					'activeFilters' => $model->getActiveFilters(),
					'formName' => $formName,
					'showToolbar' => true,
					'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=company&model=departments'),
					'return' => base64_encode('index.php?option=com_redshopb&view=company&layout=edit&id='
						. $companyId . '&tab=departments&from_company=1'
					)
				)
			);
		}

		$app->close();
	}

	/**
	 * Ajax call to get collections tab content.
	 *
	 * @return  void
	 */
	public function ajaxcollections()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$companyId = $input->getInt('id');

		if ($companyId)
		{
			/** @var RedshopbModelCollections $model */
			$model = RModelAdmin::getInstance('Collections', 'RedshopbModel');
			$model->setState('filter.company', $companyId);

			$formName   = 'collectionsForm';
			$pagination = $model->getPagination();
			$pagination->set('formName', $formName);

			echo RedshopbLayoutHelper::render('company.collections', array(
					'state' => $model->getState(),
					'items' => $model->getItems(),
					'pagination' => $pagination,
					'filter_form' => $model->getForm(),
					'activeFilters' => $model->getActiveFilters(),
					'formName' => $formName,
					'showToolbar' => true,
					'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=company&model=collections'),
					'return' => base64_encode('index.php?option=com_redshopb&view=company&layout=edit&id='
						. $companyId . '&tab=collections&from_company=1'
					)
				)
			);
		}

		$app->close();
	}

	/**
	 * Ajax call to get the company permissions set
	 *
	 * @return  void
	 */
	public function ajaxpermissions()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$companyId   = $input->getInt('id');
		$userId      = RedshopbHelperUser::getUserRSid();
		$userCompany = RedshopbHelperUser::getUserCompanyId($userId);

		if ($companyId)
		{
			$model = $this->getModel();
			$table = RTable::getAdminInstance('Company');
			$table->load(array('id' => $companyId));

			// Loads the form to get the input with the permission set
			$form = $model->getForm();
			$form->setValue('id', null, $companyId);
			$form->setValue('asset_id', null, $table->asset_id);

			if ($companyId == $userCompany && !RedshopbHelperACL::isSuperAdmin())
			{
				$form->setFieldAttribute('acl_rules', 'allowAdministratorRoleChange', 'false');
			}

			echo $form->getInput('acl_rules');
		}
		else
		{
			echo RedshopbLayoutHelper::render('notification.warning', array(
					'message' => Text::_('COM_REDSHOPB_COMPANY_PERMISSIONS_DENIED'),
				)
			);
		}

		$app->close();
	}

	/**
	 * Ajax call to get the company addresses
	 *
	 * @return  void
	 */
	public function ajaxaddresses()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$companyId = $input->getInt('id');

		if ($companyId)
		{
			/** @var RedshopbModelAddresses $model */
			$model = RModelAdmin::getInstance('Addresses', 'RedshopbModel');
			$model->setState('filter.ajax.companyid', $companyId);
			$model->setState('filter.ajax.departmentid', 0);
			$model->setFilterForm('filter_addresses_company');
			$app->setUserState('address.companyid', $companyId);
			$app->setUserState('address.departmentid', 0);
			$formName   = 'addressesForm';
			$pagination = $model->getPagination();
			$pagination->set('formName', $formName);
			$app->setUserState('address.customer_type', 'company');
			$app->setUserState('address.customer_id', $companyId);

			echo RedshopbLayoutHelper::render('company.addresses', array(
					'state' => $model->getState(),
					'items' => $model->getItems(),
					'pagination' => $pagination,
					'filter_form' => $model->getForm(),
					'activeFilters' => $model->getActiveFilters(),
					'formName' => $formName,
					'showToolbar' => true,
					'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=company&model=addresses'),
					'return' => base64_encode('index.php?option=com_redshopb&view=company&layout=edit&id='
						. $companyId . '&tab=addresses&from_company=1'
					)
				)
			);
		}

		$app->close();
	}

	/**
	 * Ajax call to get sales persons tab content.
	 *
	 * @return  void
	 */
	public function ajaxsalespersons()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$companyId = $input->getInt('id');

		if ($companyId)
		{
			/** @var RedshopbModelUsers $usersModel */
			$model = RModelAdmin::getInstance('SalesPersons', 'RedshopbModel');
			$model->setState('filter.company', $companyId);
			$app->setUserState('user.company_id', $companyId);

			$formName   = 'salespersonsForm';
			$pagination = $model->getPagination();
			$pagination->set('formName', $formName);

			$availableSalesPersons = RedshopbHelperUser::getSalesPersonsAdd($companyId);

			echo RedshopbLayoutHelper::render('company.salespersons', array(
					'state' => $model->getState(),
					'items' => $model->getItems(),
					'pagination' => $pagination,
					'filterForm' => $model->getForm(),
					'activeFilters' => $model->getActiveFilters(),
					'formName' => $formName,
					'showToolbar' => true,
					'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=company&model=salespersons'),
					'return' => base64_encode('index.php?option=com_redshopb&view=company&layout=edit&id='
						. $companyId . '&tab=salespersons&from_company=1'
					),
					'availableSalesPersons' => $availableSalesPersons
				)
			);
		}

		$app->close();
	}

	/**
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 */
	public function savePermissions($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app     = Factory::getApplication();
		$lang    = Factory::getLanguage();
		$model   = $this->getModel();
		$table   = $model->getTable();
		$data    = $this->input->post->get('jform', array(), 'array');
		$context = "$this->option.edit.$this->context";

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

		$recordId = $this->input->getInt($urlVar);

		if (!$this->checkEditId($context, $recordId))
		{
			// Somehow the person just went to the form and tried to save it. We don't allow that.
			$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $recordId));
			$this->setMessage($this->getError(), 'error');

			// Redirect to the list screen
			$this->setRedirect(
				$this->getRedirectToListRoute($this->getRedirectToListAppend())
			);

			return false;
		}

		// Populate the row id from the session.
		$data[$key] = $recordId;

		// Access check.
		if (!$this->allowSave($data, $key))
		{
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			// Redirect to the list screen
			$this->setRedirect(
				$this->getRedirectToListRoute($this->getRedirectToListAppend())
			);

			return false;
		}

		// Attempt to save the data.
		if (!$model->savePermissions($data))
		{
			// Redirect back to the edit screen.
			$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			// Redirect back to the edit screen.
			$this->setRedirect(
				$this->getRedirectToItemRoute($this->getRedirectToItemAppend($recordId, $urlVar))
			);

			return false;
		}

		// Redirect back to the edit screen.
		$this->setRedirect(
			$this->getRedirectToItemRoute($this->getRedirectToItemAppend($recordId, $urlVar))
		);

		return true;
	}

	/**
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 */
	public function saveContactInfo($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app     = Factory::getApplication();
		$lang    = Factory::getLanguage();
		$model   = $this->getModel();
		$table   = $model->getTable();
		$data    = $this->input->post->get('jform', array(), 'array');
		$context = "$this->option.edit.$this->context";

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

		$recordId = $this->input->getInt($urlVar);

		if (!$this->checkEditId($context, $recordId))
		{
			// Somehow the person just went to the form and tried to save it. We don't allow that.
			$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $recordId));
			$this->setMessage($this->getError(), 'error');

			// Redirect to the list screen
			$this->setRedirect(
				$this->getRedirectToListRoute($this->getRedirectToListAppend())
			);

			return false;
		}

		// Populate the row id from the session.
		$data[$key] = $recordId;

		// Access check.
		if (!$this->allowSave($data, $key))
		{
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			// Redirect to the list screen
			$this->setRedirect(
				$this->getRedirectToListRoute($this->getRedirectToListAppend())
			);

			return false;
		}

		// Attempt to save the data.
		if (!$model->saveContactInfo($data))
		{
			// Redirect back to the edit screen.
			$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			// Redirect back to the edit screen.
			$this->setRedirect(
				$this->getRedirectToItemRoute($this->getRedirectToItemAppend($recordId, $urlVar))
			);

			return false;
		}

		$this->setMessage(
			Text::_(
				($lang->hasKey($this->text_prefix . ($recordId == 0 && $app->isClient('site') ? '_SUBMIT' : '') . '_SAVE_SUCCESS')
					? $this->text_prefix
					: 'JLIB_APPLICATION') . ($recordId == 0 && $app->isClient('site') ? '_SUBMIT' : '') . '_SAVE_SUCCESS'
			)
		);

		// Redirect back to the edit screen.
		$this->setRedirect(
			$this->getRedirectToItemRoute($this->getRedirectToItemAppend($recordId, $urlVar))
		);

		return true;
	}

	/**
	 * Method to add sales persons to the company (submit call from company form)
	 *
	 * @throws Exception
	 *
	 * @return  true
	 */
	public function addsalespersons()
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$input        = Factory::getApplication()->input;
		$companyId    = $input->getInt('company_id');
		$salesPersons = json_decode($input->getVar('newsalespersons'));

		if ($salesPersons)
		{
			$table = RedshopbTable::getAdminInstance('User')
				->setOption('forceCustomersUpdate', true)
				->setOption('noPasswordUpdate', true);

			foreach ($salesPersons as $salesPerson)
			{
				if ($table->load($salesPerson))
				{
					$companyIds = array_unique(array_merge($table->companyIds, array($companyId)));

					if (!$table->save(array('companyIds' => $companyIds)))
					{
						throw new Exception($table->getError());
					}
				}
			}
		}

		// Redirect back to the company edit screen.
		$this->setRedirect(base64_decode($input->getVar('return')));

		return true;
	}

	/**
	 * Method to remove sales persons from the company (submit call from company form)
	 *
	 * @throws Exception
	 *
	 * @return  true
	 */
	public function removesalespersons()
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$input        = Factory::getApplication()->input;
		$companyId    = $input->getInt('company_id');
		$salesPersons = $input->getVar('cid');

		if ($salesPersons)
		{
			$table = RedshopbTable::getAdminInstance('User')
				->setOption('forceCustomersUpdate', true)
				->setOption('noPasswordUpdate', true);

			foreach ($salesPersons as $salesPerson)
			{
				if ($table->load($salesPerson))
				{
					$companyIds = $table->companyIds;
					$key        = array_search($companyId, $companyIds);

					if ($key !== false)
					{
						unset($companyIds[$key]);
					}

					if (!$table->save(array('companyIds' => $companyIds)))
					{
						throw new Exception($table->getError());
					}
				}
			}
		}

		// Redirect back to the company edit screen.
		$this->setRedirect(base64_decode($input->getVar('return')));

		return true;
	}

	/**
	 * Ajax call to get stockrooms tab content.
	 *
	 * @return  void
	 */
	public function ajaxstockrooms()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$companyId = $input->getInt('id');

		if (!$companyId)
		{
			$app->close();
		}

		/** @var RedshopbModelStockrooms $model */
		$model = RModelAdmin::getInstance('Stockrooms', 'RedshopbModel');
		$model->set('filterFormName', 'filter_stockrooms_company');
		$state = $model->getState();

		$model->setState('filter.company_id', $companyId);
		$app->setUserState('user.company_id', $companyId);

		$formName   = 'stockroomsForm';
		$pagination = $model->getPagination();
		$pagination->set('formName', $formName);

		echo RedshopbLayoutHelper::render(
			'company.stockrooms',
			array(
				'state' => $state,
				'items' => $model->getItems(),
				'pagination' => $pagination,
				'filterForm' => $model->getForm(),
				'activeFilters' => $model->getActiveFilters(),
				'formName' => $formName,
				'showToolbar' => true,
				'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=company&model=stockrooms'),
				'return' => base64_encode('index.php?option=com_redshopb&view=company&layout=edit&id='
					. $companyId . '&tab=stockrooms&from_company=1'
				)
			)
		);

		$app->close();
	}

	/**
	 * Method to refresh the available price groups for the companies form
	 * when the parent company changes
	 *
	 * @return void
	 */
	public function ajaxRefreshPriceGroups()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app = Factory::getApplication();

		/** @var  $model RedshopbModelField */
		$model = $this->getModel();
		$data  = $app->input->get('jform', array(), 'array');

		/** @var Form $form */
		$form = $model->getForm(array(), false);

		$form->bind($data);

		$return                        = new stdClass;
		$return->price_group_ids       = $form->getInput('price_group_ids');
		$return->customer_discount_ids = $form->getInput('customer_discount_ids');

		echo json_encode($return);

		$app->close();
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
}
