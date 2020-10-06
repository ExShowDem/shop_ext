<?php
/**
 * @package     Aesir.E-Commerce.Backend
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
 * Discount Debtor Groups View
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewDiscount_Debtor_Groups extends RedshopbView
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
		$model = $this->getModel();

		$this->items                        = $model->getItems();
		$this->state                        = $model->getState();
		$this->pagination                   = $model->getPagination();
		$this->filterForm                   = $model->getForm();
		$this->activeFilters                = $model->getActiveFilters();
		$this->stoolsOptions['searchField'] = 'search_discount_debtor_groups';

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return Text::_('COM_REDSHOPB_DEBTOR_DISCOUNT_GROUPS');
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
		if (RedshopbHelperACL::getPermission('manage', 'product', array('create'), true))
		{
			$new = RToolbarBuilder::createNewButton('discount_debtor_group.add');
			$firstGroup->addButton($new);
		}

		if (RedshopbHelperACL::getPermission('manage', 'product', array('edit'), true))
		{
			$edit = RToolbarBuilder::createEditButton('discount_debtor_group.edit');
			$firstGroup->addButton($edit);
		}

		// Publish / Unpublish
		if (RedshopbHelperACL::getPermission('manage', 'product', array('edit.state'), true))
		{
			$publish   = RToolbarBuilder::createPublishButton('discount_debtor_groups.publish');
			$unpublish = RToolbarBuilder::createUnpublishButton('discount_debtor_groups.unpublish');

			$firstGroup->addButton($publish)
				->addButton($unpublish);
		}

		// Delete / Trash
		if (RedshopbHelperACL::getPermission('manage', 'product', array('delete'), true))
		{
			$delete = RToolbarBuilder::createStandardButton('discount_debtor_groups.delete', 'JTOOLBAR_DELETE', 'btn-danger', 'icon-trash');
			$secondGroup->addButton($delete);
		}

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup)
			->addGroup($secondGroup);

		return $toolbar;
	}
}
