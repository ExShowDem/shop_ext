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
 * Categories View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewCategories extends RedshopbView
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
	 * @var array
	 */
	protected $availableCompanies;

	/**
	 * @var array
	 */
	public $ordering = array();

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$model = $this->getModel('categories');

		// Set flag only show categories which can be manage (edit/delete)
		$model->setState('manageOnly', true);
		$userRSid = RedshopbHelperUser::getUserRSid();

		if ($userRSid)
		{
			$model->setState('includeMainWarehouse', RedshopbHelperUser::isFromMainCompany($userRSid, 'employee'));
		}

		$this->items                        = $model->getItems();
		$this->state                        = $model->getState();
		$this->pagination                   = $model->getPagination();
		$this->filterForm                   = $model->getForm();
		$this->activeFilters                = $model->getActiveFilters();
		$this->stoolsOptions['searchField'] = 'search_categories';

		$userId                     = Factory::getUser()->id;
		$this->availableCompanies   = explode(',', RedshopbHelperACL::listAvailableCompanies($userId, 'comma'));
		$this->availableCompanies[] = 0;

		if ($this->items)
		{
			// Preprocess the list of items to find ordering divisions.
			foreach ($this->items as &$item)
			{
				$this->ordering[$item->parent_id][] = $item->id;
			}
		}

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return Text::_('COM_REDSHOPB_CATEGORY_LIST_TITLE');
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

		// Add / edit
		if (RedshopbHelperACL::getPermission('manage', 'category', array('create'), true))
		{
			$new = RToolbarBuilder::createNewButton('category.add');
			$firstGroup->addButton($new);
		}

		if (RedshopbHelperACL::getPermission('manage', 'category', array('edit'), true))
		{
			$edit = RToolbarBuilder::createEditButton('category.edit');
			$firstGroup->addButton($edit);
		}

		// Publish / Unpublish
		if (RedshopbHelperACL::getPermission('manage', 'category', array('edit.state'), true))
		{
			$publish   = RToolbarBuilder::createPublishButton('categories.publish');
			$unpublish = RToolbarBuilder::createUnpublishButton('categories.unpublish');

			$firstGroup->addButton($publish)
				->addButton($unpublish);
		}

		// Delete / Trash
		if (RedshopbHelperACL::getPermission('manage', 'category', array('delete'), true))
		{
			$delete = RToolbarBuilder::createStandardButton('categories.delete', 'JTOOLBAR_DELETE', 'btn-danger', 'icon-trash');
			$secondGroup->addButton($delete);
		}

		// Rebuild
		if (RedshopbHelperUser::isRoot())
		{
			$rebuild = RToolbarBuilder::createStandardButton(
				'categories.rebuild', Text::_('COM_REDSHOPB_REBUILD'), 'btn-warning', 'icon-retweet', false
			);
			$secondGroup->addButton($rebuild);
		}

		// Csv
		$csv = RToolbarBuilder::createCsvButton();
		$thirdGroup->addButton($csv);

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup)
			->addGroup($secondGroup)
			->addGroup($thirdGroup);

		return $toolbar;
	}
}
