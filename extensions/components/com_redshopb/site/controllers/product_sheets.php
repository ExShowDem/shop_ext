<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;

/**
 * Product sheets Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerProduct_Sheets extends RedshopbControllerAdmin
{
	/**
	 * Print selected products as PDF file.
	 *
	 * @return void
	 */
	public function printProductSheets()
	{
		// Check for request forgeries
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));
		$app = Factory::getApplication();

		/** @var RedshopbModelProduct_sheets $model */
		$model = RModelAdmin::getInstance('Product_sheets', 'RedshopbModel');

		$model->printPDF();

		$app->close();
	}

	/**
	 * List all items from session in the product sheets layout
	 *
	 * @return void
	 */
	public function ajaxSelectedProducts()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app     = Factory::getApplication();
		$session = Factory::getSession();
		$items   = $session->get('productSheets.products', null, 'redshopb');

		if (empty($items))
		{
			$itemIds = array(0);
		}
		else
		{
			$itemIds = array_keys($items);
		}

			/** @var RedshopbModelProducts $model */
		$model = RModelAdmin::getInstance('Products', 'RedshopbModel');
		$model->setState('list.allow_parent_companies_products', true);
		$model->setState('list.allow_mainwarehouse_products', true);
		$model->setState('filter.product_id', $itemIds);
		$model->set('context', 'com_redshopb.product_sheets_selected.products');

		$finalItems    = $model->getItems();
		$selectedItems = array();

		foreach ($finalItems as $finalItem)
		{
			if (isset($items[$finalItem->id]))
			{
				$dropDownItems = array_keys($items[$finalItem->id]);

				foreach ($dropDownItems as $dropDownItem)
				{
					$selectedItems[$finalItem->id . '_' . $dropDownItem]                   = clone $finalItem;
					$selectedItems[$finalItem->id . '_' . $dropDownItem]->dropDownSelected = $dropDownItem;
				}
			}
		}

		$dropdownModel = RModelAdmin::getInstance('Shop', 'RedshopbModel');
		$dropdownModel->setState('product_collection', null);
		$dropdownModel->customerCType = '';

		echo RedshopbLayoutHelper::render('product_sheets.productitems', array(
				'items' => $selectedItems,
				'selectedProducts' => true,
				'dropDownTypes' => $dropdownModel->getDropDownTypes($itemIds)
			)
		);
		$app->close();
	}

	/**
	 * Add item to Cart or update quantity
	 *
	 * @return void
	 */
	public function ajaxAddProductToList()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app              = Factory::getApplication();
		$session          = Factory::getSession();
		$input            = $app->input;
		$productId        = $input->getInt('productId', 0);
		$dropDownSelected = $input->getInt('dropDownSelected', 0);

		$items = $session->get('productSheets.products', null, 'redshopb');

		if (empty($items))
		{
			$items = array();
		}

		if (!isset($items[$productId][$dropDownSelected]))
		{
			$items[$productId][$dropDownSelected] = array('productId' => $productId, 'dropDownSelected' => $dropDownSelected);
			$session->set('productSheets.products', $items, 'redshopb');
			echo json_encode(Text::_('COM_REDSHOPB_PRODUCT_SHEETS_PRODUCT_ADDED'));
		}
		else
		{
			echo json_encode(Text::_('COM_REDSHOPB_PRODUCT_SHEETS_PRODUCT_EXISTS'));
		}

		$app->close();
	}

	/**
	 * Remove item from Cart
	 *
	 * @return void
	 */
	public function ajaxRemoveProductFromList()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app              = Factory::getApplication();
		$session          = Factory::getSession();
		$input            = $app->input;
		$productId        = $input->getInt('productId', 0);
		$dropDownSelected = $input->getInt('dropDownSelected', 0);

		$items = $session->get('productSheets.products', null, 'redshopb');

		if (empty($items))
		{
			$items = array();
		}

		unset($items[$productId][$dropDownSelected]);

		$session->set('productSheets.products', $items, 'redshopb');

		echo json_encode(Text::_('COM_REDSHOPB_PRODUCT_SHEETS_PRODUCT_REMOVED'));
		$app->close();
	}

	/**
	 * Clears the cart
	 *
	 * @return void
	 */
	public function clearProductList()
	{
		$app     = Factory::getApplication();
		$session = Factory::getSession();

		$session->set('productSheets.products', array(), 'redshopb');
		$app->redirect(RedshopbRoute::_('index.php?option=com_redshopb&view=product_sheets'));
	}

	/**
	 * Ajax call to get products already added in collection
	 *
	 * @return  void
	 */
	public function ajaxProducts()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;
		$user  = Factory::getUser();

		$company = RedshopbHelperUser::getUserCompany($user->id, 'joomla');

		/** @var RedshopbModelProducts $model */
		$model = RModelAdmin::getInstance('Products', 'RedshopbModel');
		$model->setState('list.ordering', $input->get('data-order', ''));
		$model->setState('list.direction', $input->get('data-direction', ''));
		$model->setState('list.product_state', '1');
		$model->setState('list.product_discontinued', '0');
		$model->setState('list.allow_parent_companies_products', true);
		$model->setState('list.allow_mainwarehouse_products', true);
		$model->setState('list.disallow_freight_fee_products', true);

		if ($company)
		{
			if (RedshopbHelperShop::inCollectionMode(RedshopbEntityCompany::getInstance($company->id)))
			{
				$model->setState('list.force_collection', true);
			}
		}

		$formName   = 'product_sheetsProductsForm';
		$pagination = $model->getPagination();
		$pagination->set('formName', $formName);

		$finalItems = $model->getItems();
		$filterForm = $model->getForm();
		$filterForm->setFieldAttribute('product_state', 'default', '1', 'filter');
		$filterForm->setFieldAttribute('product_state', 'disabled', 'true', 'filter');
		$filterForm->setFieldAttribute('product_discontinued', 'default', '0', 'filter');
		$filterForm->setFieldAttribute('product_discontinued', 'disabled', 'true', 'filter');

		echo RedshopbLayoutHelper::render('product_sheets.products', array(
				'state' => $model->getState(),
				'items' => $finalItems,
				'pagination' => $pagination,
				'filter_form' => $filterForm,
				'activeFilters' => $model->getActiveFilters(),
				'formName' => $formName,
				'showToolbar' => false,
				'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=product_sheets&model=products'),
				'return' => base64_encode('index.php?option=com_redshopb&view=product_sheets&tab=products&from_collection=1')
			)
		);

		$app->close();
	}

	/**
	 * Ajax call to get change drop down attribute from product
	 *
	 * @return  void
	 */
	public function ajaxChangeDropDownAttribute()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		$productId        = $input->getInt('product_id', 0);
		$dropDownSelected = $input->getInt('drop_down_selected', 0);

		if ($productId > 0)
		{
			echo RedshopbHelperProduct::getProductImageThumbHtml($productId, 0, $dropDownSelected);
		}

		$app->close();
	}
}
