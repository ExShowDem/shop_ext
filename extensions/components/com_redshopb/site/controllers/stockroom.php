<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Stockroom Controller
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerStockroom extends RedshopbControllerForm
{
	/**
	 * @var  string
	 */
	protected $text_prefix = 'COM_REDSHOPB_STOCKROOM';

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 */
	protected function getRedirectToListAppend()
	{
		$append      = parent::getRedirectToListAppend();
		$fromCompany = RedshopbInput::isFromCompany();

		// Append the tab name for the company view
		if ($fromCompany)
		{
			$append .= '&tab=stockrooms';
		}

		return $append;
	}

	/**
	 * Method for load stockroom on product item.
	 *
	 * @return  void
	 */
	public function ajaxstockroom()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app = Factory::getApplication();

		$productId   = $app->input->getInt('product_id', 0);
		$stockroomId = $app->input->getInt('stockroom_id', 0);

		if ($productId && $stockroomId)
		{
			/** @var RedshopbModelProduct $productModel */
			$productModel  = RModelAdmin::getInstance('Product', 'RedshopbModel');
			$productEntity = RedshopbEntityProduct::getInstance($productId);
			$productEntity->loadItem();

			$unitMeasure = $productEntity->getUnitMeasure()->getItem();

			$stockrooms     = array();
			$productItemIds = array();

			$issetItems = $productModel->getIssetItems($productId);

			if (!empty($issetItems))
			{
				// Get individual stockroom data for each of product variant
				foreach ($issetItems as $issetItem)
				{
					$productItemIds[] = $issetItem->id;
				}

				$stockrooms = RedshopbHelperStockroom::getProductItemsStockroomData($productItemIds, $stockroomId);
			}

			$stockRoom = RedshopbHelperStockroom::getProductStockroomData($productId, $stockroomId);

			$productHtml = RedshopbLayoutHelper::render(
				'product.stock.product',
				array(
					'productId'    => $productId,
					'stockroomId'  => $stockroomId,
					'stockroom'    => $stockRoom,
					'unitMeasure'  => $unitMeasure
				)
			);

			$productItemsHtml = RedshopbLayoutHelper::render(
				'product.stock.product_variants',
				array(
					'productId'            => $productId,
					'stockroomId'          => $stockroomId,
					'stockroom'            => $stockRoom,
					'staticTypes'          => $productModel->getStaticTypes($productId),
					'dynamicTypes'         => $productModel->getDynamicTypes($productId),
					'issetItems'           => $issetItems,
					'stockroomData'        => $stockrooms,
					'issetDynamicVariants' => $productModel->getIssetDynamicVariants($productId),
					'unitMeasure'          => $unitMeasure
				)
			);

			echo $productHtml . '<hr />' . $productItemsHtml;
		}

		$app->close();
	}

	/**
	 * Method for store amount of product item in specific stock
	 *
	 * @return  void
	 */
	public function ajaxUpdateProductItemAmount()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app = Factory::getApplication();

		$amount        = $app->input->getFloat('amount', 0.00);
		$stockroomId   = $app->input->getInt('id', 0);
		$productItemId = $app->input->getInt('product_item', 0);
		$unlimited     = $app->input->getBool('unlimited', false);

		echo (int) $this->getModel('Stockroom')->saveProductItemAmount($stockroomId, $amount, $productItemId, $unlimited);

		$app->close();
	}

	/**
	 * Method for store amount of product in specific stock
	 *
	 * @return  void
	 */
	public function ajaxUpdateProductAmount()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app = Factory::getApplication();

		$amount      = $app->input->getFloat('amount', 0.00);
		$stockroomId = $app->input->getInt('id', 0);
		$productId   = $app->input->getInt('product', 0);
		$unlimited   = $app->input->getBool('unlimited', false);

		echo (int) $this->getModel('Stockroom')->saveProductAmount($stockroomId, $amount, $productId, $unlimited);

		$app->close();
	}
}
