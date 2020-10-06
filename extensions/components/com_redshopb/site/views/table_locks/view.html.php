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
use Joomla\CMS\Pagination\Pagination;

/**
 * Table Locks View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewTable_Locks extends RedshopbView
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
		$model                              = $this->getModel('table_locks');
		$this->items                        = $model->getItems();
		$this->state                        = $model->getState();
		$this->pagination                   = $model->getPagination();
		$this->filterForm                   = $model->getForm();
		$this->activeFilters                = $model->getActiveFilters();
		$this->stoolsOptions['searchField'] = 'search_table_locks';

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return Text::_('COM_REDSHOPB_TABLE_LOCKS_LIST_TITLE');
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
		if (RedshopbHelperACL::getPermission('manage', 'company', Array('create'), true))
		{
			$new = RToolbarBuilder::createNewButton('table_lock.add');
			$firstGroup->addButton($new);
		}

		if (RedshopbHelperACL::getPermission('manage', 'company', Array('edit'), true))
		{
			$edit = RToolbarBuilder::createEditButton('table_lock.edit');
			$firstGroup->addButton($edit);
		}

		// Delete / Trash
		if (RedshopbHelperACL::getPermission('manage', 'company', Array('delete'), true))
		{
			$delete = RToolbarBuilder::createStandardButton('table_locks.delete', 'JTOOLBAR_DELETE', 'btn-danger', 'icon-trash');
			$secondGroup->addButton($delete);
		}

		if (RedshopbHelperACL::getPermission('manage', 'company', Array('delete'), true))
		{
			$delete = RToolbarBuilder::createModalButton(
				'#bulkDelete',
				'COM_REDSHOPB_TABLE_LOCKS_LIST_BULK_DELETE_BUTTON',
				'btn-danger',
				'icon-trash',
				false
			);
			$thirdGroup->addButton($delete);
		}

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup)
			->addGroup($secondGroup)
			->addGroup($thirdGroup);

		return $toolbar;
	}
}
