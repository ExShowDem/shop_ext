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
 * Tax_Groups View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewTax_Groups extends RedshopbView
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
		$model = $this->getModel('Tax_Groups');

		$this->items                        = $model->getItems();
		$this->state                        = $model->getState();
		$this->pagination                   = $model->getPagination();
		$this->filterForm                   = $model->getForm();
		$this->activeFilters                = $model->getActiveFilters();
		$this->stoolsOptions['searchField'] = 'search_tax_groups';

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return Text::_('COM_REDSHOPB_TAX_GROUP_LIST_TITLE');
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

		// Add / edit
		if (RedshopbHelperACL::getPermission('manage', 'product', Array('create'), false))
		{
			$new = RToolbarBuilder::createNewButton('tax_group.add');
			$firstGroup->addButton($new);
		}

		if (RedshopbHelperACL::getPermission('manage', 'product', Array('edit'), false))
		{
			$edit = RToolbarBuilder::createEditButton('tax_group.edit');
			$firstGroup->addButton($edit);
		}

		// Publish / Unpublish
		if (RedshopbHelperACL::getPermission('manage', 'product', Array('edit.state'), false))
		{
			$publish   = RToolbarBuilder::createPublishButton('tax_groups.publish');
			$unpublish = RToolbarBuilder::createUnpublishButton('tax_groups.unpublish');

			$firstGroup->addButton($publish)
				->addButton($unpublish);
		}

		// Delete / Trash
		if (RedshopbHelperACL::getPermission('manage', 'product', Array('delete'), false))
		{
			$delete = RToolbarBuilder::createStandardButton('tax_groups.delete', 'JTOOLBAR_DELETE', 'btn-danger', 'icon-trash');
			$secondGroup->addButton($delete);
		}

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup)
			->addGroup($secondGroup);

		return $toolbar;
	}
}
