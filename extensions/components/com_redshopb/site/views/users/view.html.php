<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Pagination\Pagination;
/**
 * Users View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewUsers extends RedshopbView
{
	/**
	 * @var  array
	 */
	public $items;

	/**
	 * @var  object
	 */
	public $state;

	/**
	 * @var  Pagination
	 */
	public $pagination;

	/**
	 * @var  Form
	 */
	public $filterForm;

	/**
	 * @var array
	 */
	public $activeFilters;

	/**
	 * @var array
	 */
	public $stoolsOptions = array();

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$app = Factory::getApplication();

		if (!RedshopbHelperACL::getPermission('manage', 'user', Array(), true)
			&& !RedshopbHelperACL::getPermission('view', 'user', Array(), true))
		{
			$app->redirect(RedshopbRoute::_('index.php?option=com_redshopb&view=dashboard'));
		}

		$app->setUserState('user.company_id', '');
		$app->setUserState('user.department_id', '');
		$model = $this->getModel('Users');

		$this->items                        = $model->getItems();
		$this->state                        = $model->getState();
		$this->pagination                   = $model->getPagination();
		$this->filterForm                   = $model->getForm();
		$this->activeFilters                = $model->getActiveFilters();
		$this->stoolsOptions['searchField'] = 'search_users';

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return Text::_('COM_REDSHOPB_USER_LIST_TITLE');
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$firstGroup  = new RToolbarButtonGroup;
		$secondGroup = new RToolbarButtonGroup;
		$thirdGroup  = new RToolbarButtonGroup;
		$fourthGroup = new RToolbarButtonGroup('pull-right');
		$fifthGroup  = new RToolbarButtonGroup('pull-right');

		// Add / edit
		if (RedshopbHelperACL::getPermission('manage', 'user', Array('create'), true)
			&& (RedshopbHelperACL::getPermission('manage', 'company', Array('create'), true)
			|| RedshopbHelperACL::getPermission('manage', 'department', Array('create'), true)))
		{
			$new = RToolbarBuilder::createNewButton('user.add');
			$firstGroup->addButton($new);
		}

		if (RedshopbHelperACL::getPermission('manage', 'user', Array('edit'), true))
		{
			$edit = RToolbarBuilder::createEditButton('user.edit');
			$firstGroup->addButton($edit);

			$activate = RToolbarBuilder::createStandardButton('users.activate', 'COM_REDSHOPB_USERS_ACTIVATE', '', 'icon-publish');
			$firstGroup->addButton($activate);

			$block = RToolbarBuilder::createStandardButton('users.block', 'COM_REDSHOPB_USERS_BLOCK', '', 'icon-unpublish');
			$firstGroup->addButton($block);
		}

		// Delete / Trash
		if (RedshopbHelperACL::getPermission('manage', 'user', Array('delete'), true))
		{
			$delete = RToolbarBuilder::createStandardButton('users.delete', 'JTOOLBAR_DELETE', 'btn-danger', 'icon-trash');
			$secondGroup->addButton($delete);
		}

		// Points manage (general user management permission, since it cannot be pinpointed to an specific company or department in the full list)
		if (RedshopbHelperACL::getPermission('manage', 'user', Array('edit', 'edit.own'), true))
		{
			$assignPoints = RToolbarBuilder::createModalButton(
				'#walletModal',
				Text::_('COM_REDSHOPB_USER_ADD_CREDIT_MONEY'),
				'',
				'icon-money',
				true
			);

			$thirdGroup->addButton($assignPoints);
		}

		// Csv
		$exportCsv = RToolbarBuilder::createCsvButton();
		$fourthGroup->addButton($exportCsv);

		if (RedshopbHelperACL::getPermission('manage', 'user', Array('edit'), true))
		{
			$importCsv = new RedshopbHelperButton('import.form', 'users');
			$fifthGroup->addButton($importCsv);
		}

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup)
			->addGroup($secondGroup)
			->addGroup($thirdGroup)
			->addGroup($fourthGroup)
			->addGroup($fifthGroup);

		return $toolbar;
	}
}
