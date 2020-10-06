<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
/**
 * A Quick Order helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperQuick_Order
{
	/**
	 * Method for search products/variants for Quick Order
	 *
	 * @param   string  $term  Search string
	 *
	 * @return  array/boolean  List of products/variants if success. False otherwise.
	 */
	public static function searchItems($term = '')
	{
		if (empty($term) || !is_string($term))
		{
			return false;
		}

		$app          = Factory::getApplication();
		$customerType = $app->getUserState('shop.customer_type', '');
		$customerId   = $app->getUserState('shop.customer_id', 0);

		if (!$customerId || !$customerType)
		{
			$customerId   = RedshopbApp::getUser()->get('id', 0);
			$customerType = 'employee';
		}

		if (!$customerId || !$customerType
			|| RedshopbHelperUser::isFromMainCompany($customerId, $customerType))
		{
			return false;
		}

		// If config is use collection for shopping.
		if (RedShopbHelperShop::inCollectionMode(RedshopbEntityCompany::getInstance(RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType))))
		{
			return self::prepareCollectionItems($term, $customerId, $customerType);
		}

		return self::prepareCatalogItems($term, $customerId, $customerType);
	}

	/**
	 * Method for prepare result items in collection shopping mode
	 *
	 * @param   string   $term          Search term
	 * @param   integer  $customerId    ID of customer
	 * @param   string   $customerType  Type of customer. Default is "employee"
	 *
	 * @return  array/boolean           List of items if success. False otherwise.
	 */
	public static function prepareCollectionItems($term = '', $customerId = 0, $customerType = 'employee')
	{
		if (empty($term))
		{
			return false;
		}

		if (!$customerId)
		{
			$customerId = RedshopbHelperUser::getUserRSid(Factory::getUser()->id);
		}

		$collections = RedshopbHelperCollection::getCustomerCollectionsForShop($customerId, $customerType);

		// If there are no collections available for this user, skip this result
		if (empty($collections))
		{
			return false;
		}

		// Get matched products
		$companyId     = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);
		$productsModel = RModelAdmin::getInstance('Products', 'RedshopbModel');
		$productsModel->setState('filter.search_products', $term);
		$productsModel->setState('filter.product_company', $companyId);
		$productsModel->setState('list.force_collection', true);

		$items                = array();
		$collectionCurrencies = array();
		$collectionNames      = array();
		$products             = $productsModel->getItems();

		if (empty($products))
		{
			return false;
		}

		// Prepare products list.
		foreach ($products as $productKey => $product)
		{
			$hasVariants = false;

			// Get variants of product
			foreach ($collections as $collectionId)
			{
				$variants = RedshopbHelperCollection::getIssetItems($product->id, $collectionId);

				// If product doesn't have variant on this collection, skip it.
				if (empty($variants))
				{
					continue;
				}

				// Get the currency of collection
				if (empty($collectionCurrencies[$collectionId]))
				{
					$collectionCurrencies[$collectionId] = RedshopbHelperCollection::getCurrency($collectionId);
				}

				// Get the name of collection
				if (empty($collectionNames[$collectionId]))
				{
					$collectionNames[$collectionId] = RedshopbHelperCollection::getName($collectionId);
				}

				$hasVariants = true;

				foreach ($variants as $variant)
				{
					// Skip empty variant
					if (!$variant->id)
					{
						continue;
					}

					// Prepare an item for results list.
					$item              = new stdClass;
					$item->id          = $product->id . '_' . $variant->id . '_' . $collectionId;
					$item->productId   = $product->id;
					$item->productItem = $variant->id;
					$item->sku         = $variant->sku;
					$item->currencyId  = $collectionCurrencies[$collectionId];
					$item->collection  = $collectionId;
					$item->title       = $product->name . ' ' . $item->sku . ' (' . $collectionNames[$collectionId] . ')';
					$item->price       = '';

					// Unit Measure
					$unitMeasure       = RedshopbEntityUnit_Measure::load($product->unit_measure_id);
					$item->unitMeasure = $unitMeasure->get('name', Text::_('COM_REDSHOPB_PRODUCT_UOM_PCS'));
					$item->unitDecimal = $unitMeasure->get('decimal_position', 0);

					$price = RedshopbHelperPrices::getProductItemPrice(
						$variant->id,
						$customerId,
						$customerType,
						$item->currencyId,
						array($collectionId)
					);

					if ($price)
					{
						$item->price = $price->price;
					}

					$items[] = $item;
				}
			}

			// If this product not have any variants, remove this from the results list.
			if (!$hasVariants)
			{
				unset($products[$productKey]);
			}
		}

		return $items;
	}

	/**
	 * Method for prepare result items in catalog shopping mode
	 *
	 * @param   string   $term          Search term
	 * @param   integer  $customerId    ID of customer
	 * @param   string   $customerType  Type of customer. Default is "employee"
	 *
	 * @return  array/boolean           List of items if success. False otherwise.
	 */
	public static function prepareCatalogItems($term = '', $customerId = 0, $customerType = 'employee')
	{
		if (empty($term))
		{
			return false;
		}

		if (!$customerId)
		{
			$customerId = RedshopbHelperUser::getUserRSid(Factory::getUser()->id);
		}

		$app = Factory::getApplication();
		$app->setUserState('shop.search', $term);

		$companyId = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);

		$productSearch = new RedshopbDatabaseProductsearch;
		$products      = $productSearch->getProductForProductListLayout(0, 20, 0);

		// Reset session search value
		$app->setUserState('shop.search', null);

		if (empty($products))
		{
			return false;
		}

		$products = $products->productData;

		$currency = self::getUserCurrency($customerId);
		$items    = array();

		foreach ($products as $product)
		{
			$item              = new stdClass;
			$item->id          = $product->id . '_';
			$item->productId   = $product->id;
			$item->productItem = 0;
			$item->sku         = $product->sku;
			$item->currencyId  = $currency;
			$item->collection  = '';
			$item->title       = $product->name . ' ' . $product->sku;
			$item->price       = '';

			// Unit Measure
			$unitMeasure       = RedshopbEntityUnit_Measure::load($product->unit_measure_id);
			$item->unitMeasure = $unitMeasure->get('name', Text::_('COM_REDSHOPB_PRODUCT_UOM_PCS'));
			$item->unitDecimal = $unitMeasure->get('decimal_position', 0);

			$price = RedshopbHelperPrices::getProductPrice($product->id, $customerId, $customerType, $currency);

			if (!empty($price))
			{
				$item->price = $price->price;

				// Calculate discount if available
				if ($price->allow_discount == 1)
				{
					$discount                = RedshopbHelperPrices::getDiscount($product->id, $companyId, $price->currency_id);
					$item->discountPrice     = $item->price * ($discount->percent / 100);
					$item->formattedDiscount = RedshopbHelperProduct::getProductFormattedPrice($item->discountPrice, $currency);
					$item->discount          = $discount->percent;
				}
			}

			$item->variants = RedshopbHelperCollection::getIssetItems($product->id, 0);

			$items[] = $item;

			// This product doesn't have variant
			if (empty($item->variants))
			{
				continue;
			}

			foreach ($item->variants as $variant)
			{
				// Skip empty variant
				if (!$variant->id)
				{
					continue;
				}

				// Prepare an item for results list.
				$itemVariant              = new stdClass;
				$itemVariant->id          = $product->id . '_' . $variant->id;
				$itemVariant->productId   = $product->id;
				$itemVariant->productItem = $variant->id;
				$itemVariant->sku         = $variant->sku;
				$itemVariant->currencyId  = $currency;
				$itemVariant->collection  = '';
				$itemVariant->title       = $product->name . ' (' . $itemVariant->sku . ')';
				$itemVariant->price       = '';
				$itemVariant->unitMeasure = $item->unitMeasure;
				$itemVariant->unitDecimal = $item->unitDecimal;

				$price = RedshopbHelperPrices::getProductItemPrice(
					$variant->id,
					$customerId,
					$customerType,
					$currency
				);

				if ($price)
				{
					$itemVariant->price = $price->price;
				}

				$items[] = $itemVariant;
			}
		}

		return $items;
	}

	/**
	 * Method for get available currency of current user
	 *
	 * @param   integer  $employeeId  ID of customer
	 *
	 * @return  integer               ID of currency
	 */
	public static function getUserCurrency($employeeId = 0)
	{
		$employeeId = (int) $employeeId;

		if (!$employeeId)
		{
			$employeeId = RedshopbHelperUser::getUserRSid(Factory::getUser()->id);
		}

		$userCompany = RedshopbHelperUser::getUserCompany($employeeId);

		if (!empty($userCompany))
		{
			if (!empty($userCompany->currency_id))
			{
				return $userCompany->currency_id;
			}
			elseif (!empty($userCompany->id))
			{
				// If current company of user doesn't config currency. Get from it's parent.
				$parentCompany = RedshopbEntityCompany::getInstance($userCompany->id)->getParent();

				if (!empty($parentCompany->currency_id))
				{
					return $parentCompany->currency_id;
				}
			}
		}

		// If not, get from global configuration
		return (int) RedshopbApp::getConfig()->get('default_currency', 38);
	}
}
