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
 * Offers View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewOffers extends RedshopbView
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
		$model = $this->getModel('Offers');

		$this->items                        = $model->getItems();
		$this->state                        = $model->getState();
		$this->pagination                   = $model->getPagination();
		$this->filterForm                   = $model->getForm();
		$this->activeFilters                = $model->getActiveFilters();
		$this->stoolsOptions['searchField'] = 'search_offers';

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return Text::_('COM_REDSHOPB_OFFER_LIST_TITLE');
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
		if (RedshopbHelperACL::getPermission('manage', 'product', Array('create'), true))
		{
			$new = RToolbarBuilder::createNewButton('offer.add');
			$firstGroup->addButton($new);
		}

		if (RedshopbHelperACL::getPermission('manage', 'product', Array('edit'), true))
		{
			$edit = RToolbarBuilder::createEditButton('offer.edit');
			$firstGroup->addButton($edit);
		}

		// Delete / Trash
		if (RedshopbHelperACL::getPermission('manage', 'product', Array('delete'), true))
		{
			$delete = RToolbarBuilder::createStandardButton('offers.delete', 'JTOOLBAR_DELETE', 'btn-danger', 'icon-trash');
			$secondGroup->addButton($delete);
		}

		$toolbar = new RToolbar('toolbar clearfix');
		$toolbar->addGroup($firstGroup)
			->addGroup($secondGroup);

		return $toolbar;
	}
}
