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
class RedshopbViewAddresses extends RedshopbView
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
		$app->setUserState('address.customer_type', '');
		$app->setUserState('address.customer_id', '');
		$model = $this->getModel('addresses');

		$this->items                        = $model->getItems();
		$this->state                        = $model->getState();
		$this->pagination                   = $model->getPagination();
		$this->filterForm                   = $model->getForm();
		$this->activeFilters                = $model->getActiveFilters();
		$this->stoolsOptions['searchField'] = 'search_addresses';

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return Text::_('COM_REDSHOPB_ADDRESS_LIST_TITLE');
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
		if (RedshopbHelperACL::getPermission('manage', 'address', array('create'), true))
		{
			$new = RToolbarBuilder::createNewButton('address.add');
			$firstGroup->addButton($new);
		}

		if (RedshopbHelperACL::getPermission('manage', 'address', array('edit'), true))
		{
			$edit = RToolbarBuilder::createEditButton('address.edit');
			$firstGroup->addButton($edit);
		}

		// Delete / Trash
		if (RedshopbHelperACL::getPermission('manage', 'address', array('delete'), true))
		{
			$delete = RToolbarBuilder::createStandardButton('addresses.delete', 'JTOOLBAR_DELETE', 'btn-danger', 'icon-trash');
			$secondGroup->addButton($delete);
		}

		// Csv
		$csv = RToolbarBuilder::createCsvButton();
		$thirdGroup->addButton($csv);

		$import = new RedshopbHelperButton('import.form', 'addresses');
		$fourthGroup->addButton($import);

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup)
			->addGroup($secondGroup)
			->addGroup($thirdGroup)
			->addGroup($fourthGroup);

		return $toolbar;
	}
}
