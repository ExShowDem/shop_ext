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
 * Orders View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewOrders extends RedshopbView
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
		$model = $this->getModel('Orders');

		$this->items = $model->getItems();

		$this->state                        = $model->getState();
		$this->pagination                   = $model->getPagination();
		$this->filterForm                   = $model->getForm();
		$this->activeFilters                = $model->getActiveFilters();
		$this->stoolsOptions['searchField'] = 'search_orders';

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return Text::_('COM_REDSHOPB_ORDER_LIST_TITLE');
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
		if (RedshopbHelperACL::getPermission('manage', 'order', array('create'), true))
		{
			$new = RToolbarBuilder::createNewButton('order.add');
			$firstGroup->addButton($new);
		}

		if (RedshopbHelperACL::getPermission('manage', 'order', array('edit'), true))
		{
			$edit = RToolbarBuilder::createEditButton('order.edit');
			$firstGroup->addButton($edit);

			$orderCollection = (boolean) RedshopbEntityConfig::getInstance()->get('order_collect', 0);

			if ($orderCollection)
			{
				// Collect
				$collect = RToolbarBuilder::createStandardButton(
					'orders.collect',
					Text::_('COM_REDSHOPB_ORDERS_COLLECT'),
					'',
					'icon-list-alt'
				);

				$thirdGroup->addButton($collect);
			}
		}

		// Csv
		$csv = RToolbarBuilder::createCsvButton();
		$thirdGroup->addButton($csv);

		$print = RToolbarBuilder::createStandardButton('orders.printPDF', 'COM_REDSHOPB_PRINT_PDF', '', 'icon-print');
		$thirdGroup->addButton($print);

		// Delete / Trash
		if (RedshopbHelperACL::getPermission('manage', 'order', array('delete'), true))
		{
			$delete = RToolbarBuilder::createStandardButton('orders.delete', 'JTOOLBAR_DELETE', 'btn-danger', 'icon-trash');
			$secondGroup->addButton($delete);
		}

		$orderExpedition = (boolean) RedshopbEntityConfig::getInstance()->get('order_expedition', 0);

		if ($orderExpedition)
		{
			if (RedshopbHelperACL::isSuperAdmin())
			{
				$xml = RToolbarBuilder::createStandardButton('orders.xmloutput', 'XML');
				$thirdGroup->addButton($xml);

				$raw = RToolbarBuilder::createStandardButton('orders.rawoutput', 'RAW');
				$thirdGroup->addButton($raw);
			}

			$user              = Factory::getUser();
			$departmentAssetId = RedshopbHelperUser::getUserDepartmentAssetId($user->id, 'joomla');

			// Expedite order (change status)
			if (RedshopbHelperACL::getPermission('statusupdate', 'order', array(), true, $departmentAssetId))
			{
				$send = RToolbarBuilder::createStandardButton(
					'orders.expedite',
					Text::_('COM_REDSHOPB_ORDERS_EXPEDITE_TO_UPPER_LEVEL'),
					'btn-primary',
					'icon-level-up'
				);
				$thirdGroup->addButton($send);
			}
		}

		$toolbar = new RToolbar('toolbar clearfix');
		$toolbar->addGroup($firstGroup)
			->addGroup($secondGroup)
			->addGroup($thirdGroup);

		return $toolbar;
	}
}
