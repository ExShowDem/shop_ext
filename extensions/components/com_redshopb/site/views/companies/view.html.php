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
 * Companies View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewCompanies extends RedshopbView
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
		$app->setUserState('company.parent_id', '');
		$model = $this->getModel('companies');

		$this->items                        = $model->getItems();
		$this->state                        = $model->getState();
		$this->pagination                   = $model->getPagination();
		$this->filterForm                   = $model->getForm();
		$this->activeFilters                = $model->getActiveFilters();
		$this->stoolsOptions['searchField'] = 'search_companies';

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return Text::_('COM_REDSHOPB_COMPANY_LIST_TITLE');
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
		$thirdGroup  = new RToolbarButtonGroup('pull-right');
		$fourthGroup = new RToolbarButtonGroup('pull-right');

		// Add / edit
		if (RedshopbHelperACL::getPermission('manage', 'company', Array('create'), true))
		{
			$new = RToolbarBuilder::createNewButton('company.add');
			$firstGroup->addButton($new);
		}

		if (RedshopbHelperACL::getPermission('manage', 'company', Array('edit'), true))
		{
			$edit = RToolbarBuilder::createEditButton('company.edit');
			$firstGroup->addButton($edit);
		}

		// Publish / Unpublish
		if (RedshopbHelperACL::getPermission('manage', 'company', Array('edit.state'), true))
		{
			$publish   = RToolbarBuilder::createPublishButton('companies.publish');
			$unpublish = RToolbarBuilder::createUnpublishButton('companies.unpublish');

			$firstGroup->addButton($publish)
				->addButton($unpublish);
		}

		// Delete / Trash
		if (RedshopbHelperACL::getPermission('manage', 'company', Array('delete'), true))
		{
			$delete = RToolbarBuilder::createModalButton(
				'#companiesModal',
				'JTOOLBAR_DELETE',
				'btn-danger',
				'icon-trash'
			);
			$secondGroup->addButton($delete);
		}

		// Rebuild
		if (RedshopbHelperUser::isRoot())
		{
			$rebuild = RToolbarBuilder::createStandardButton(
				'companies.rebuild', Text::_('COM_REDSHOPB_REBUILD'), 'btn-warning', 'icon-retweet', false
			);
			$secondGroup->addButton($rebuild);
		}

		// Csv
		$csv = RToolbarBuilder::createCsvButton();
		$thirdGroup->addButton($csv);

		if (RedshopbHelperACL::getPermission('manage', 'company', Array('create'), true))
		{
			$import = new RedshopbHelperButton('import.form', 'companies');
			$fourthGroup->addButton($import);
		}

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup)
			->addGroup($secondGroup)
			->addGroup($thirdGroup)
			->addGroup($fourthGroup);

		return $toolbar;
	}
}
