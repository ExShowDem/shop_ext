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

/**
 * Department Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerDepartment extends RedshopbControllerForm
{
	/**
	 * @var  string
	 */
	protected $text_prefix = 'COM_REDSHOPB_DEPARTMENT';

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
			$append .= '&tab=departments';
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

		$departmentId = $input->getInt('id');
		$companyId    = $input->getInt('company_id');

		if ($departmentId)
		{
			/** @var RedshopbModelUsers $usersModel */
			$model = RModelAdmin::getInstance('Users', 'RedshopbModel');
			$model->set('filterFormName', 'filter_users_department');
			$state = $model->getState();

			$model->setState('filter.company', $companyId);
			$model->setState('filter.department', $departmentId);
			$app->setUserState('user.company_id', $companyId);
			$app->setUserState('user.department_id', $departmentId);

			$formName   = 'usersForm';
			$pagination = $model->getPagination();
			$pagination->set('formName', $formName);

			echo RedshopbLayoutHelper::render('department.users', array(
					'state' => $state,
					'items' => $model->getItems(),
					'pagination' => $pagination,
					'filterForm' => $model->getForm(),
					'activeFilters' => $model->getActiveFilters(),
					'formName' => $formName,
					'showToolbar' => true,
					'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=department&model=users'),
					'return' => base64_encode('index.php?option=com_redshopb&view=department&layout=edit&id='
						. $departmentId . '&tab=users&from_department=1'
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

		$departmentId = $input->getInt('id');

		if ($departmentId)
		{
			/** @var RedshopbModelCollections $model */
			$model = RModelAdmin::getInstance('Collections', 'RedshopbModel');
			$model->setState('filter.department', $departmentId);

			$formName   = 'collectionsForm';
			$pagination = $model->getPagination();
			$pagination->set('formName', $formName);

			echo RedshopbLayoutHelper::render('department.collections', array(
					'state' => $model->getState(),
					'items' => $model->getItems(),
					'pagination' => $pagination,
					'filter_form' => $model->getForm(),
					'activeFilters' => $model->getActiveFilters(),
					'formName' => $formName,
					'showToolbar' => true,
					'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=department&model=collections'),
					'return' => base64_encode('index.php?option=com_redshopb&view=department&layout=edit&id='
						. $departmentId . '&tab=collections&from_department=1'
					)
				)
			);
		}

		$app->close();
	}

	/**
	 * Ajax call to get addresses tab content.
	 *
	 * @return  void
	 */
	public function ajaxaddresses()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$departmentId = $input->getInt('id');

		if ($departmentId)
		{
			/** @var RedshopbModelAddresses $model */
			$model = RModelAdmin::getInstance('Addresses', 'RedshopbModel');
			$model->setState('filter.ajax.companyid', 0);
			$model->setState('filter.ajax.departmentid', $departmentId);
			$model->setFilterForm('filter_addresses_department');
			$app->setUserState('address.companyid', 0);
			$app->setUserState('address.departmentid', $departmentId);
			$formName   = 'addressesForm';
			$pagination = $model->getPagination();
			$pagination->set('formName', $formName);
			$app->setUserState('address.customer_type', 'department');
			$app->setUserState('address.customer_id', $departmentId);

			echo RedshopbLayoutHelper::render('department.addresses', array(
					'state' => $model->getState(),
					'items' => $model->getItems(),
					'pagination' => $pagination,
					'filter_form' => $model->getForm(),
					'activeFilters' => $model->getActiveFilters(),
					'formName' => $formName,
					'showToolbar' => true,
					'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=department&model=addresses'),
					'return' => base64_encode('index.php?option=com_redshopb&view=department&layout=edit&id='
						. $departmentId . '&tab=addresses&from_department=1'
					)
				)
			);
		}

		$app->close();
	}

	/**
	 * Ajax call to get departments based on selected company.
	 *
	 * @return  void
	 */
	public function ajaxGetFieldDepartment()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$model = $this->getModel();

		$companyId = $app->input->getInt('companyId', 0);
		$parentId  = $app->input->getInt('parentId', 0);
		$currentId = $app->input->getInt('id', 0);
		echo $model->getDepartmentsFormField($companyId, $parentId, $currentId);

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
