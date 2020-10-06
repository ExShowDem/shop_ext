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
use Joomla\CMS\Form\Form;
use Joomla\CMS\Pagination\Pagination;
/**
 * Collections View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewCollections extends RedshopbView
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
		$model = $this->getModel('collections');

		$this->items                        = $model->getItems();
		$this->state                        = $model->getState();
		$this->pagination                   = $model->getPagination();
		$this->filterForm                   = $model->getForm();
		$this->activeFilters                = $model->getActiveFilters();
		$this->stoolsOptions['searchField'] = 'search_collections';

		parent::display($tpl);
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

		// Add / edit from existing
		if (RedshopbHelperACL::getPermission('manage', 'collection', Array('create'), true))
		{
			$createFromExisting = RToolbarBuilder::createStandardButton(
				'collection.createFromExisting',
				Text::_('COM_REDSHOPB_COLLECTION_CREATE_FROM_EXISTING'),
				'btn-primary',
				'icon-file-text-alt'
			);
			$firstGroup->addButton($createFromExisting);
		}

		// Add / edit
		if (RedshopbHelperACL::getPermission('manage', 'collection', Array('create'), true))
		{
			$new = RToolbarBuilder::createNewButton('collection.create');
			$firstGroup->addButton($new);
		}

		if (RedshopbHelperACL::getPermission('manage', 'user', Array('edit'), true))
		{
			$edit = RToolbarBuilder::createEditButton('collection.edit');
			$firstGroup->addButton($edit);
		}

		// Delete / Trash
		if (RedshopbHelperACL::getPermission('manage', 'collection', Array('delete'), true))
		{
			$delete = RToolbarBuilder::createStandardButton('collections.delete', 'JTOOLBAR_DELETE', 'btn-danger', 'icon-trash');
			$secondGroup->addButton($delete);
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
