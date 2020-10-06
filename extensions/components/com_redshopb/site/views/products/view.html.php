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
 * Products View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewProducts extends RedshopbView
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
		$model = $this->getModel('products');

		// Set state for get list of products include categories and tags
		$this->state = $model->getState();
		$this->state->set('list.include_categories', true);
		$this->state->set('list.include_tags', true);

		$this->items                        = $model->getItems();
		$this->pagination                   = $model->getPagination();
		$this->filterForm                   = $model->getForm();
		$this->activeFilters                = $model->getActiveFilters();
		$this->stoolsOptions['searchField'] = 'search_products';

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return Text::_('COM_REDSHOPB_PRODUCT_LIST_TITLE');
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
		if (RedshopbHelperACL::getPermission('manage', 'product', Array('create'), true))
		{
			$new = RToolbarBuilder::createNewButton('product.add');
			$firstGroup->addButton($new);
		}

		if (RedshopbHelperACL::getPermission('manage', 'product', Array('edit'), true))
		{
			$edit = RToolbarBuilder::createEditButton('product.edit');
			$firstGroup->addButton($edit);
		}

		// Publish / Unpublish
		if (RedshopbHelperACL::getPermission('manage', 'product', Array('edit.state'), true))
		{
			$publish   = RToolbarBuilder::createPublishButton('products.publish');
			$unpublish = RToolbarBuilder::createUnpublishButton('products.unpublish');

			$firstGroup->addButton($publish)->addButton($unpublish);

			// Discontinue
			$discontinued = RToolbarBuilder::createModalButton(
				'#productDiscontinue',
				'JTOOLBAR_DISCONTINUE',
				'btn-warning',
				'icon-warning-sign'
			);
			$firstGroup->addButton($discontinued);
		}

		// Delete / Trash
		if (RedshopbHelperACL::getPermission('manage', 'product', Array('delete'), true))
		{
			$delete = RToolbarBuilder::createStandardButton('products.delete', 'JTOOLBAR_DELETE', 'btn-danger', 'icon-trash');
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
