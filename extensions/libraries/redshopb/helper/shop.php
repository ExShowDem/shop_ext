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
use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * Shop helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperShop
{
	/**
	 * List of product ID available in the shop view
	 *
	 * @var string
	 */
	public static $filteredProductIds;

	/**
	 * Function for getting customer type ("customer"/"end_customer").
	 *
	 * @param   int    $customerId   Customer id.
	 * @param   string $customerType Customer type.
	 *
	 * @return string Customer type ("customer" or "end_customer").
	 */
	public static function getCustomerType($customerId, $customerType)
	{
		$type = '';

		switch ($customerType)
		{
			case 'employee':
				$company = RedshopbHelperUser::getUserCompany($customerId);

				if ($company)
				{
					$type = $company->type;
				}

				break;

			case 'department':
				$companyId = RedshopbHelperDepartment::getCompanyId($customerId);
				$company   = RedshopbHelperCompany::getCompanyById($companyId);

				if ($company)
				{
					$type = $company->type;
				}

				break;

			case 'company':
				$company = RedshopbHelperCompany::getCompanyById($customerId);

				if ($company)
				{
					$type = $company->type;
				}

				break;

			default:
				break;
		}

		return $type;
	}

	/**
	 * Get customer entity (summary) for given customer id & customer type.
	 *
	 * @param   int    $customerId   Customer id.
	 * @param   string $customerType Customer type.
	 *
	 * @return  stdClass  properties: company_id, department_id, user_id, name
	 */
	public static function getCustomerEntity($customerId, $customerType)
	{
		$entity                = new stdClass;
		$entity->company_id    = 0;
		$entity->department_id = 0;
		$entity->user_id       = 0;
		$entity->name          = '';

		switch ($customerType)
		{
			case 'employee':
				$user                  = RedshopbHelperUser::getUser($customerId);
				$entity->company_id    = $user->company;
				$entity->department_id = $user->department;
				$entity->user_id       = $customerId;
				$entity->name          = $user->name . ' ' . $user->name2;

				break;
			case 'department':
				$department            = RedshopbHelperDepartment::getDepartmentById($customerId);
				$entity->company_id    = $department->company_id;
				$entity->department_id = $customerId;
				$entity->name          = $department->name . ' ' . $department->name2;

				break;
			case 'company':
				$company            = RedshopbHelperCompany::getCompanyById($customerId);
				$entity->company_id = $customerId;
				$entity->name       = $company->name . ' ' . $company->name2;

				break;
		}

		return $entity;
	}

	/**
	 * Get customer name for given customer id & customer type.
	 *
	 * @param   int    $customerId   Customer id.
	 * @param   string $customerType Customer type.
	 *
	 * @return string Customer name.
	 */
	public static function getCustomerName($customerId, $customerType)
	{
		switch ($customerType)
		{
			case 'employee':
				$name = RedshopbHelperUser::getName($customerId);

				break;
			case 'department':
				$name = RedshopbHelperDepartment::getName($customerId, false, false);

				break;
			case 'company':
				return RedshopbEntityCompany::load($customerId)->name;
			default:
				$name = Factory::getUser()->name;
		}

		return $name;
	}

	/**
	 * Get customer name2 for given customer id & customer type.
	 *
	 * @param   int    $customerId   Customer id.
	 * @param   string $customerType Customer type.
	 *
	 * @return string Customer name2.
	 */
	public static function getCustomerName2($customerId, $customerType)
	{
		switch ($customerType)
		{
			case 'employee':
				$name = RedshopbHelperUser::getName2($customerId);

				break;
			case 'department':
				$name = RedshopbHelperDepartment::getName2($customerId, false);

				break;
			case 'company':
				$name = RedshopbHelperCompany::getName2($customerId, false);

				break;
			default:
				$name = '';
		}

		return $name;
	}

	/**
	 * Get additional charges for an order.
	 *
	 * @param   int|string $currency Total currency.
	 * @param   string     $type     Charges type. (fee | freight)
	 *
	 * @return object|null Charge product to add to cart or null if there is no charge set.
	 */
	public static function getAdditionalCharges($currency, $type)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		if ($type == 'fee')
		{
			$table  = $db->qn('#__redshopb_fee', 'charge');
			$select = array(
				$db->qn('charge.fee_amount', 'price'),
				$db->qn('charge.fee_limit', 'limit'),
				$db->qn('charge.currency_id', 'currency_id'),
				$db->qn('c.alpha3', 'currency'),
				$db->qn('p') . '.*',
				$db->qn('pi.id', 'itemId')
			);
		}
		elseif ($type == 'freight')
		{
			// @TODO We are currently returning null since table doesn't exists
			return null;

			$table  = $db->qn('#__redshopb_freight', 'charge');
			$select = array(
				$db->qn('charge.freight_amount', 'price'),
				$db->qn('charge.freight_limit', 'limit'),
				$db->qn('charge.currency_id', 'currency_id'),
				$db->qn('c.alpha3', 'currency'),
				$db->qn('p') . '.*',
				$db->qn('pi.id', 'itemId')
			);
		}
		else
		{
			return null;
		}

		if (!is_numeric($currency) && is_string($currency))
		{
			$currency = RedshopbHelperProduct::getCurrency($currency)->id;
		}

		$query->select($select)
			->from($table)
			->innerJoin($db->qn('#__redshopb_product', 'p') . ' ON ' . $db->qn('charge.product_id') . ' = ' . $db->qn('p.id'))
			->innerJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON ' . $db->qn('pi.product_id') . ' = ' . $db->qn('p.id'))
			->innerJoin($db->qn('#__redshopb_currency', 'c') . ' ON ' . $db->qn('charge.currency_id') . ' = ' . $db->qn('c.id'))
			->where($db->qn('charge.currency_id') . ' = ' . (int) $currency);

		return $db->setQuery($query)->loadObject();
	}

	/**
	 * Function to receive & pre-process javascript options
	 *
	 * @param   Registry|array  $options Associative array|Registry object with options
	 *
	 * @return  Registry        Options converted to Registry object
	 */
	public static function options2Jregistry($options)
	{
		// Support options array
		if (is_array($options))
		{
			$options = new Registry($options);
		}

		if (!($options instanceof Registry))
		{
			$options = new Registry;
		}

		return $options;
	}

	/**
	 * Function to receive & pre-process javascript options
	 *
	 * @param   boolean $filterStatus True for get "Filtered" product Ids. False for all product Ids.
	 * @param   bool    $returnQuery  Return product query instead execute
	 *
	 * @return  string|JDatabaseQuery
	 */
	public static function getFilteredProductIds($filterStatus = true, $returnQuery = false)
	{
		$filterStatus = (boolean) $filterStatus;
		$app          = Factory::getApplication();
		$view         = $app->input->getVar('view');
		$layout       = $app->input->getVar('layout');
		$id           = $app->input->getInt('id', 0);
		$key          = $view . '.' . $layout . '.' . $id;

		// Add filterStatus in cache key
		$key .= '.';
		$key .= ($filterStatus) ? 'yes' : 'no';

		if (!isset(self::$filteredProductIds[$key]))
		{
			/** @var RedshopbModelShop $model */
			$model        = RModelAdmin::getInstance('Shop', 'RedshopbModel');
			$customerType = $app->getUserState('shop.customer_type', '');
			$customerId   = $app->getUserState('shop.customer_id', 0);

			if (self::inCollectionMode(RedshopbEntityCompany::getInstance(RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType))))
			{
				$model->setState('product_collection', RedshopbHelperCollection::getCustomerCollectionsForShop($customerId, $customerType));
			}

			$model->setState('use_filter', $filterStatus);

			if ($view == 'shop' && $layout == 'productrecent')
			{
				$model->setState('filter.product_recent', true);
			}

			if ($view == 'shop' && $layout == 'productfeatured')
			{
				$model->setState('filter.product_featured', true);
			}

			$config = RedshopbEntityConfig::getInstance();

			if ($view == 'shop'
				&& $layout == 'category'
				&& !empty($id)
				&& $config->get('show_subcategories_products', 0) == 1)
			{
				$categories[] = $id;

				$currentCategory = RedshopbEntityCategory::getInstance($id);
				$currentCategory->getItem();

				$subCategories = $currentCategory->getAllChildrenIds();

				$categories += $subCategories;

				$model->setState('filter.product_category', $categories);
			}

			$query = $model->getListQueryOnly();

			if ($query)
			{
				$query->clear('select')
					->clear('order')
					->select('p.id');
			}

			if ($returnQuery)
			{
				return $query;
			}
			else
			{
				self::$filteredProductIds[$key] = '';

				if ($query)
				{
					$db         = Factory::getDbo();
					$productIds = $db->setQuery($query)
						->loadColumn();

					if (!empty($productIds))
					{
						self::$filteredProductIds[$key] = implode(',', $productIds);
					}
				}
			}
		}

		return self::$filteredProductIds[$key];
	}

	/**
	 * Get list of all products used as charge products.
	 *
	 * @param   string $type Charge type.
	 *
	 * @return array
	 */
	public static function getChargeProducts($type)
	{
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true);
		$select = 'DISTINCT ' . $db->qn('pi.id', 'itemId');

		if ($type == 'fee')
		{
			$table = $db->qn('#__redshopb_fee', 'charge');
		}
		elseif ($type == 'freight')
		{
			// @TODO We are currently returning empty array since table doesn't exists
			return array();

			// $table = $db->qn('#__redshopb_freight', 'charge');
		}
		else
		{
			return null;
		}

		$query->select($select)
			->from($table)
			->innerJoin($db->qn('#__redshopb_product', 'p') . ' ON ' . $db->qn('charge.product_id') . ' = ' . $db->qn('p.id'))
			->innerJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON ' . $db->qn('pi.product_id') . ' = ' . $db->qn('p.id'));

		$chargeProducts = $db->setQuery($query)->loadColumn();

		return $chargeProducts;
	}

	/**
	 * Returns id from erp side for given customer.
	 *
	 * @param   int    $customerId   Customer id.
	 * @param   string $customerType Customer type.
	 *
	 * @return string Erp id.
	 */
	public static function getCustomerErpId($customerId, $customerType)
	{
		switch ($customerType)
		{
			case 'employee':
				$reference = 'fengel.user';
				break;
			case 'department':
				$reference = 'fengel.department';
				break;
			case 'company':
				$reference = 'fengel.customer';
				break;
			default:
				$reference = '';
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(
				$db->qn('remote_key')
			)
			->from($db->qn('#__redshopb_sync'))
			->where($db->qn('local_id') . ' = ' . (int) $customerId . ' AND ' . $db->qn('reference') . ' = ' . $db->q($reference));

		return $db->setQuery($query)->loadResult();
	}

	/**
	 * Function with cache to determine if there are products on sale or not
	 *
	 * @return boolean
	 */
	public static function areThereCampaignItems()
	{
		$app    = Factory::getApplication();
		$return = $app->getUserState('shop.campaignItems', null);

		if ($return === null)
		{
			$return = false;
			$db     = Factory::getDbo();
			$query  = $db->getQuery(true);

			$query->select($db->qn('pi.id'))
				->from($db->qn('#__redshopb_product', 'p'))
				->innerJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON ' . $db->qn('p.id') . ' = ' . $db->qn('pi.product_id'))
				->where($db->qn('p.state') . ' = 1')
				->where('(p.publish_date = ' . $db->q($db->getNullDate()) . ' OR p.publish_date <= STR_TO_DATE('
					. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
				)
				->where('(p.unpublish_date = ' . $db->q($db->getNullDate()) . ' OR p.unpublish_date >= STR_TO_DATE('
					. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
				)
				->where($db->qn('p.discontinued') . ' = 0')
				->where($db->qn('pi.state') . ' = 1')
				->where($db->qn('pi.discontinued') . ' = 0');
			self::setOnSaleItemsQuery($query, false, 'p', 'pi', true);

			$db->setQuery($query);

			if ($db->loadResult())
			{
				$return = true;
			}

			$app->setUserState('shop.campaignItems', $return);
		}

		return $return;
	}

	/**
	 * This function is used for adding to an specific query the joins and restrictions to search for campaign and optionally discounted products
	 *
	 * @param   object $query               JDatabaseQuery of products to be filtered
	 *                                      (#__redshopb_product and #__redshopb_product_item must be included)
	 * @param   bool   $includeDiscounts    (optional) Whether to include discounts or not (if not, only campaign prices)
	 * @param   string $productAlias        (optional) Alias of #__redshopb_product table
	 * @param   string $productItemAlias    (optional) Alias of #__redshopb_product_item table
	 * @param   bool   $filterCollections   (optional) Include collection filters in the query if required (default false)
	 *
	 * @return void
	 */
	public static function setOnSaleItemsQuery(
		&$query,
		$includeDiscounts = false,
		$productAlias = '#__redshopb_product',
		$productItemAlias = '#__redshopb_product_item',
		$filterCollections = false
	)
	{
		$app          = Factory::getApplication();
		$customerType = $app->getUserState('shop.customer_type', '');
		$customerId   = $app->getUserState('shop.customer_id', 0);

		$companyId = 0;

		$companyType = self::getCustomerType($customerId, $customerType);

		// It will not show any discounts if an end customer is shopping as themselves (sets a false where)

		if ($companyType == 'end_customer')
		{
			$user = Factory::getUser();

			$userCompany = RedshopbHelperUser::getUserCompany($user->id, 'joomla');

			if ($userCompany)
			{
				if ($userCompany->type == 'end_customer')
				{
					$query->where('1 = 0');

					return;
				}
			}
		}

		$now = Date::getInstance()->toSql();
		$db  = Factory::getDbo();

		$mainQueryRestrictions = array();

		// Subqueries to be added to the main query
		$queryCampaignProduct     = $db->getQuery(true);
		$queryCampaignProductItem = $db->getQuery(true);

		// Campaign prices for products
		$queryCampaignProduct->select($db->qn('price_campaign_product.type_id', 'id'))
			->from($db->qn('#__redshopb_product_price', 'price_campaign_product'))
			->where($db->qn('price_campaign_product.sales_type') . ' = ' . $db->q('campaign'))
			->where($db->qn('price_campaign_product.type') . ' = ' . $db->q('product'))
			->where('(price_campaign_product.starting_date = '
				. $db->q($db->getNullDate()) . ' OR price_campaign_product.starting_date <= STR_TO_DATE('
				. $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where('(price_campaign_product.ending_date = ' . $db->q($db->getNullDate()) . ' OR price_campaign_product.ending_date >= STR_TO_DATE('
				. $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			);

		// Campaign prices for product items
		$queryCampaignProductItem->select($db->qn('price_campaign_product_item.type_id', 'id'))
			->from($db->qn('#__redshopb_product_price', 'price_campaign_product_item'))
			->where($db->qn('price_campaign_product_item.sales_type') . ' = ' . $db->q('campaign'))
			->where($db->qn('price_campaign_product_item.type') . ' = ' . $db->q('product_item'))
			->where(
				'(price_campaign_product_item.starting_date = '
				. $db->q($db->getNullDate()) . ' OR price_campaign_product_item.starting_date <= STR_TO_DATE('
				. $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where('(price_campaign_product_item.ending_date = '
				. $db->q($db->getNullDate()) . ' OR price_campaign_product_item.ending_date >= STR_TO_DATE('
				. $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			);

		$mainQueryRestrictions[] = $productAlias . '.id IN (' . $queryCampaignProduct->__toString() . ')';
		$mainQueryRestrictions[] = $productItemAlias . '.id IN (' . $queryCampaignProductItem->__toString() . ')';

		if ($includeDiscounts)
		{
			$queryProductDiscount = $db->getQuery(true);
			$queryDebtorDiscount  = $db->getQuery(true);

			// Product based discounts
			$queryProductDiscount = $db->getQuery(true);
			$queryProductDiscount->select($db->qn('product_discount.type_id'))
				->from($db->qn('#__redshopb_product_discount', 'product_discount'))
				->where($db->qn('product_discount.state') . ' = 1')
				->where($db->qn('product_discount.type') . ' = ' . $db->q('product'))
				->where('(product_discount.starting_date = ' . $db->q($db->getNullDate()) . ' OR product_discount.starting_date <= STR_TO_DATE('
					. $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
				)
				->where('(product_discount.ending_date = ' . $db->q($db->getNullDate()) . ' OR product_discount.ending_date >= STR_TO_DATE('
					. $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
				);

			$queryDebtorDiscount = $db->getQuery(true);
			$queryDebtorDiscount->select($db->qn('product_discount_xref.product_id'))
				->from($db->qn('#__redshopb_product_discount', 'product_discount'))
				->join(
					'inner',
					$db->qn('#__redshopb_product_discount_group_xref', 'product_discount_xref') . ' ON ' .
					$db->qn('product_discount_xref.discount_group_id') . ' = ' . $db->qn('product_discount.type_id')
				)
				->where($db->qn('product_discount.state') . ' = 1')
				->where($db->qn('product_discount.type') . ' = ' . $db->q('product_discount_group'))
				->where('(product_discount.starting_date = ' . $db->q($db->getNullDate()) . ' OR product_discount.starting_date <= STR_TO_DATE('
					. $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
				)
				->where('(product_discount.ending_date = ' . $db->q($db->getNullDate()) . ' OR product_discount.ending_date >= STR_TO_DATE('
					. $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
				);

			// Sets debtor restrictions based on user shopping for both discount queries
			$debtorRestrictions   = array();
			$debtorRestrictions[] = '(' . $db->qn('product_discount.sales_type') . ' = ' . $db->q('all_debtor') . ')';

			if ($customerId)
			{
				if (!$companyId)
				{
					$companyId = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);
				}

				$debtorRestrictions[] = '(' . $db->qn('product_discount.sales_type') . ' = ' . $db->q('debtor') .
					' AND' . $db->qn('product_discount.sales_id') . ' = ' . (int) $companyId . ')';

				$queryProductDiscount->join(
					'left',
					$db->qn('#__redshopb_customer_discount_group_xref', 'debtor_discount_xref')
					. ' ON ' . $db->qn('debtor_discount_xref.discount_group_id') . ' = ' . $db->qn('product_discount.sales_id')
					. ' AND ' . $db->qn('product_discount.sales_type') . ' = ' . $db->q('debtor_discount_group')
				);
				$queryDebtorDiscount->join(
					'left',
					$db->qn('#__redshopb_customer_discount_group_xref', 'debtor_discount_xref')
					. ' ON ' . $db->qn('debtor_discount_xref.discount_group_id') . ' = ' . $db->qn('product_discount.sales_id')
					. ' AND ' . $db->qn('product_discount.sales_type') . ' = ' . $db->q('debtor_discount_group')
				);
				$debtorRestrictions[] = '(' . $db->qn('product_discount.sales_type') . ' = ' . $db->q('debtor_discount_group') .
					' AND ' . $db->qn('debtor_discount_xref.customer_id') . ' = ' . (int) $companyId . ')';
			}

			$queryProductDiscount->where('(' . implode(' OR ', $debtorRestrictions) . ')');
			$queryDebtorDiscount->where('(' . implode(' OR ', $debtorRestrictions) . ')');

			$mainQueryRestrictions[] = $productAlias . '.id IN (' . $queryProductDiscount->__toString() . ')';
			$mainQueryRestrictions[] = $productAlias . '.id IN (' . $queryDebtorDiscount->__toString() . ')';
		}

		$query->where('(' . implode(' OR ', $mainQueryRestrictions) . ')');

		if (self::inCollectionMode(RedshopbEntityCompany::getInstance(RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType)))
			&& $filterCollections
		)
		{
			if ($customerType != 'company')
			{
				$departments = RedshopbHelperDepartment::getCustomerDepartments($customerId, $customerType);
			}

			if ($customerType == 'company' || ($departments && count($departments)))
			{
				if (!$companyId)
				{
					$companyId = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);
				}

				$query->join(
					'inner', $db->qn('#__redshopb_collection_product_item_xref', 'onsale_product_collection_item_xref') .
					' ON ' . $db->qn('onsale_product_collection_item_xref.product_item_id') . ' = ' . $db->qn($productItemAlias . '.id')
				);
				$query->join(
					'inner', $db->qn('#__redshopb_collection', 'onsale_collection') .
					' ON ' . $db->qn('onsale_product_collection_item_xref.collection_id') . ' = ' . $db->qn('onsale_collection.id')
				);
				$query->where($db->qn('onsale_collection.state') . ' = 1');
				$query->where($db->qn('onsale_collection.company_id') . ' = ' . (int) $companyId);

				if ($customerType != 'company')
				{
					$departments = implode(',', $departments);
					$query->join(
						'inner', $db->qn('#__redshopb_collection_department_xref', 'onsale_collection_department_xref')
						. ' ON ' . $db->qn('onsale_collection_department_xref.collection_id') . ' = ' . $db->qn('onsale_collection.id')
					);
					$query->where($db->qn('onsale_collection_department_xref.department_id') . ' IN (' . $departments . ')');
				}
			}
			else
			{
				$query->where('0 = 1');
			}
		}
	}

	/**
	 * Print products list.
	 *
	 * @param   object|null  $preparedItems Products array.
	 * @param   boolean      $listAllColors List all product colors.
	 *
	 * @return void
	 */
	public static function generateProductListPDF($preparedItems = null, $listAllColors = false)
	{
		/*
		 * @var   int  $trCount Row counts per page
		 */
		$trCount = 0;

		/**
		 * @param   array $items Document items (products)
		 */
		$items = array();

		/**
		 * @param   int $rowsPerPage Number of rows per each product list page
		 */
		$rowsPerPage = 30;

		$app          = Factory::getApplication();
		$customerId   = $app->getUserState('shop.customer_id', 0);
		$customerType = $app->getUserState('shop.customer_type', '');
		$customerName = RedshopbHelperCompany::getCompanyByCustomer($customerId, $customerType)->name;
		/** @var  RedshopbModelShop $model */
		$model      = RedshopbModel::getInstance('Shop', 'RedshopbModel');
		$mPDF       = RedshopbHelperMpdf::getInstance(
			'<div style="font-size: 8pt; padding: 5pt 0 10pt 0;">' . Text::_('COM_REDSHOPB_PDF_PRODUCTS_LIST') . ' - ' . $customerName . '</div>'
		);
		$stylesheet = file_get_contents(JPATH_ROOT . '/media/redcore/css/component.min.css');
		$mPDF->WriteHTML($stylesheet, 1);
		$stylesheet = file_get_contents(JPATH_ROOT . '/media/com_redshopb/css/pdf_products_list.css');
		$config     = RedshopbEntityConfig::getInstance();

		$imagesWidth  = 60;
		$imagesHeight = 60;
		$mPDF->WriteHTML($stylesheet, 1);
		$mPDF->SetTitle(Text::_('COM_REDSHOPB_PDF_PRODUCTS_LIST') . ' - ' . $customerName);
		$mPDF->SetSubject(Text::_('COM_REDSHOPB_PDF_PRODUCTS_LIST') . ' - ' . $customerName);
		$mPDF->AddPage();

		$products = $preparedItems->items;

		// Gathering all items(products in list) for showing
		foreach ($products as $product)
		{
			if ($listAllColors)
			{
				$dropDownTypes = $model->getDropDownTypes(array($product->id));

				foreach ($dropDownTypes as $productId => $colors)
				{
					foreach ($colors as $color)
					{
						$item               = $product;
						$temp               = $model->getIssetItems(array($productId), array($productId => $color->id));
						$item->issetItems   = $temp[$productId];
						$temp               = $model->getStaticTypes(array($productId), array($productId => $color->id), false);
						$item->staticTypes  = $temp[$productId];
						$temp               = $model->getDynamicTypes(array($productId), array($productId => $color->id), false);
						$item->dynamicTypes = $temp[$productId];
						$temp               = $model->getProductImages(array($productId), array($productId => $color->id));

						if (is_array($temp[$product->id]))
						{
							$item->productImage = $temp[$product->id][0];
						}
						elseif (is_object($temp[$product->id]))
						{
							$item->productImage = $temp[$product->id];
						}
						else
						{
							$item->productImage = null;
						}

						$temp                       = $model->getIssetDynamicVariants(array($productId), array($productId => $color->id));
						$item->issetDynamicVariants = $temp[$productId];
						$item->prices               = $preparedItems->prices[$product->id];
						$item->showStockAs          = $preparedItems->showStockAs;
						$item->colorName            = $color->sku . ' ' . $color->string_value;
						$items[]                    = $item;
					}
				}
			}
			else
			{
				$product->staticTypes  = $preparedItems->staticTypes[$product->id];
				$product->dynamicTypes = $preparedItems->dynamicTypes[$product->id];
				$product->colorName    = '';

				if (is_array($preparedItems->productImages[$product->id]))
				{
					$product->productImage = $preparedItems->productImages[$product->id][0];
				}
				elseif (is_object($preparedItems->productImages[$product->id]))
				{
					$product->productImage = $preparedItems->productImages[$product->id];
				}
				else
				{
					$product->productImage = null;
				}

				$dropDownTypes    = $preparedItems->dropDownTypes[$product->id];
				$selectedDropdown = !empty($preparedItems->dropDownSelected[$product->id]) ? $preparedItems->dropDownSelected[$product->id] : '';

				foreach ($dropDownTypes as $dropDownType)
				{
					if ($product->colorName == '')
					{
						$product->colorName = $dropDownType->sku . ' ' . $dropDownType->string_value;
					}

					if ($dropDownType->id == $selectedDropdown)
					{
						$product->colorName = $dropDownType->sku . ' ' . $dropDownType->string_value;
						break;
					}
				}

				$product->dropDownTypes        = $preparedItems->dropDownTypes[$product->id];
				$product->issetItems           = $preparedItems->issetItems[$product->id];
				$product->issetDynamicVariants = $preparedItems->issetDynamicVariants[$product->id];
				$product->prices               = $preparedItems->prices[$product->id];
				$product->showStockAs          = $preparedItems->showStockAs;

				$items[] = $product;
			}
		}

		// Make products list pdf
		foreach ($items as $item)
		{
			$staticCount    = count($item->staticTypes);
			$firstPartCount = count($item->dynamicTypes) + 1;

			if ($firstPartCount < 2)
			{
				$firstPartCount = 2;
			}

			if ($staticCount < 13)
			{
				// Adding another line for product name
				$rowsCount = $firstPartCount + 1;

				// Check if product can fit the page
				if ($trCount + $rowsCount <= $rowsPerPage)
				{
					$trCount += $rowsCount;
					$mPDF->WriteHTML(
						RedshopbLayoutHelper::render(
							'shop.products_list_pdf.product_layout',
							array(
								'item'   => $item,
								'width'  => $imagesWidth,
								'height' => $imagesHeight
							)
						),
						2
					);
				}
				// We can't fit this product, create another page for it
				else
				{
					$trCount = $rowsCount;
					$mPDF->AddPage();
					$mPDF->WriteHTML(
						RedshopbLayoutHelper::render(
							'shop.products_list_pdf.product_layout',
							array(
								'item'   => $item,
								'width'  => $imagesWidth,
								'height' => $imagesHeight
							)
						),
						2
					);
				}
			}
			else
			{
				$multi         = ceil(($staticCount - 12) / 12);
				$i             = 0;
				$dynamicsCount = count($item->dynamicTypes) + 1;

				if ($dynamicsCount < 2)
				{
					$dynamicsCount = 2;
				}

				// While we have product parts
				while ($i <= $multi)
				{
					if ($i == 0)
					{
						// Check if part can't fit the page
						if ($firstPartCount + 1 + $trCount > $rowsPerPage)
						{
							$mPDF->AddPage();
							$trCount = 0;
						}

						$trCount += $firstPartCount + 1;
					}
					else
					{
						// Check if part can't fit the page
						if ($dynamicsCount + $trCount > $rowsPerPage)
						{
							$mPDF->AddPage();
							$trCount = 0;
						}

						$trCount += $dynamicsCount;
					}

					// Write product part into the list page
					$mPDF->WriteHTML(
						RedshopbLayoutHelper::render(
							'shop.products_list_pdf.product_layout',
							array(
								'item'   => $item,
								'part'   => $i,
								'width'  => $imagesWidth,
								'height' => $imagesHeight
							)
						),
						2
					);

					$i++;
				}
			}
		}

		$mPDF->Output(Text::_('COM_REDSHOPB_PDF_PRODUCTS_LIST') . ' - ' . $customerName . '.pdf', 'D');
	}

	/**
	 * Determines if an entity (company, department, employee) can shop or not
	 *
	 * @param   int $companyId       Company ID of the current entity
	 * @param   int $parentCompanyId Parent company ID of the entity's company
	 * @param   int $assetId         Asset ID of the entity
	 * @param   int $customerType    company, department or employee
	 *
	 * @return  boolean
	 */
	public static function canShop($companyId, $parentCompanyId, $assetId, $customerType)
	{
		$app     = Factory::getApplication();
		$canShop = true;
		$vendor  = self::getVendor();

		// Cannot shop if an employee is mandatory, and shopper is a company or a department
		if (($customerType == 'company' || $customerType == 'department') && RedshopbHelperCompany::checkEmployeeMandatory($companyId))
		{
			$canShop = false;
		}

		// Cannot shop because of ACL (only if AssetID is present)
		if ($assetId)
		{
			if (!RedshopbHelperACL::getPermission('impersonate', 'order', Array(), false, $assetId))
			{
				$canShop = false;
			}
		}

		$companiesVendor = RedshopbEntityConfig::getInstance()
			->get('vendor_of_companies', 'parent');

		if ($companiesVendor == 'parent' && !is_null($vendor))
		{
			$vendorCompany = RedshopbHelperCompany::getCustomerCompanyByCustomer($vendor->id, $vendor->pType);
			$shopCustomers = array();

			$cartCustomers = RedshopbHelperCart::getCartCustomers();

			if ($cartCustomers)
			{
				foreach ($cartCustomers as $cartCustomer)
				{
					$customerInfo    = explode('.', $cartCustomer);
					$shopCustomers[] = RedshopbHelperCompany::getCompanyIdByCustomer($customerInfo[1], $customerInfo[0]);
				}
			}

			// Cannot shop if vendor company is not this company's parent
			if ($parentCompanyId != $vendorCompany->id)
			{
				$canShop = false;
			}
			// Cannot shop if vendor company is the main company and this company is not the shopper already (a customer company is already shopping)
			elseif ($vendorCompany->type == 'main' && !empty($shopCustomers) && $shopCustomers[0] != $companyId)
			{
				$canShop = false;
			}
		}

		return $canShop;
	}

	/**
	 * Checks the department in the impersonation view, so an empty screen will never be displayed
	 *
	 * @return  void
	 */
	public static function checkImpersonationDepartment()
	{
		$app  = Factory::getApplication();
		$user = Factory::getUser();

		if (!$app->getUserState('list.department_id'))
		{
			$companiesCount = RedshopbHelperACL::listAvailableCompanies(
				$user->id,
				'count',
				0,
				'',
				'redshopb.order.impersonate',
				'',
				false,
				false,
				false,
				true
			);

			$departmentId = RedshopbHelperUser::getUserDepartmentId($user->id, 'joomla');

			if (!$companiesCount && $departmentId)
			{
				$app->setUserState('list.department_id', $departmentId);
			}
		}
	}

	/**
	 * Set User States
	 *
	 * @param   object $values   Values current view
	 * @param   bool   $recreate If true - recreate values
	 *
	 * @return  void
	 *
	 * @TODO *bump* Very odd function, needs to be moved to simple values to the user/dept/company entities
	 */
	public static function setUserStates(&$values, $recreate = false)
	{
		// Static (cached) values from previous execution
		static $staticValues = array();

		if (!empty($staticValues) && count($staticValues) && !$recreate)
		{
			foreach ($staticValues as $key => $value)
			{
				$values->{$key} = $value;
			}

			return;
		}

		$values->superUser      = 0;
		$values->customerId     = 0;
		$values->customerType   = '';
		$values->b2cCompany     = null;
		$values->b2cMode        = false;
		$values->companyId      = 0;
		$values->departmentId   = 0;
		$values->userRSid       = 0;
		$values->canImpersonate = false;

		$app  = Factory::getApplication();
		$user = RedshopbHelperCommon::getUser(Factory::getUser()->id);

		$values->superUser = 0;

		// B2C feature
		if ($user->b2cMode)
		{
			$values->b2cCompany = $user->b2cCompany;
			$values->b2cMode    = true;
			$userCompany        = $values->b2cCompany;
			$userDepartment     = null;
			$values->userRSid   = 0;
		}
		elseif (RedshopbHelperACL::isSuperAdmin())
		{
			$values->superUser = 1;
			$userCompany       = 0;
			$userDepartment    = 0;
			$values->userRSid  = 0;

			if (isset($values->customerType) && isset($values->customerId))
			{
				$userCompany = RedshopbHelperCompany::getCompanyIdByCustomer($values->customerId, $values->customerType);

				if ($userCompany)
				{
					$userDepartment = RedshopbHelperDepartment::getDepartmentIdByCustomer($values->customerId, $values->customerType);
				}
			}

			$mainCompany = RedshopbApp::getMainCompany();

			if (!$userCompany && $mainCompany)
			{
				$userCompany = (int) $mainCompany->get('id');
				unset($mainCompany);
			}

			$values->companyId    = $userCompany;
			$values->departmentId = $userDepartment;
			$values->rsbUserId    = 0;
		}
		else
		{
			$values->b2cMode  = false;
			$userCompany      = RedshopbHelperUser::getUserCompanyId($user->id, 'joomla');
			$userDepartment   = RedshopbHelperUser::getUserDepartmentId($user->id, 'joomla');
			$values->userRSid = RedshopbHelperUser::getUserRSid($user->id);
		}

		if (RedshopbHelperACL::getPermissionInto('impersonate', 'order'))
		{
			$values->canImpersonate = 1;
		}

		if (!((int) $app->getUserState('shop.user_redirection', 0)))
		{
			// If in B2C mode
			if ($user->b2cMode)
			{
				$values->companyId    = $values->b2cCompany;
				$values->departmentId = 0;
				$app->setUserState('list.company_id', $values->companyId);
				$app->setUserState('list.department_id', $values->departmentId);
				$app->setUserState('list.rsbuser_id', 0);
				$app->setUserState('shop.customer_type', 'company');
				$app->setUserState('shop.customer_id', $values->companyId);
				$values->customerId   = $values->companyId;
				$values->customerType = 'company';
			}

			// Check if employee is accessing the shop, if so, customer is automatically selected.
			elseif ($values->canImpersonate === false)
			{
				$values->rsbUserId    = $values->userRSid;
				$values->companyId    = $userCompany;
				$values->departmentId = $userDepartment;
				$app->setUserState('list.company_id', $values->companyId);
				$app->setUserState('list.department_id', $values->departmentId);
				$app->setUserState('list.rsbuser_id', $values->rsbUserId);
				$app->setUserState('shop.customer_type', 'employee');
				$app->setUserState('shop.customer_id', $values->rsbUserId);
				$values->customerId   = $values->rsbUserId;
				$values->customerType = 'employee';
			}

			// Current user can order on behalf of the others.
			else
			{
				$values->customerType = $app->getUserState('shop.customer_type', '');
				$values->customerId   = $app->getUserState('shop.customer_id', 0);

				if ($values->customerType && $values->customerId)
				{
					$values->companyId = RedshopbHelperCompany::getCompanyIdByCustomer($values->customerId, $values->customerType);
					$app->setUserState('list.company_id', $values->companyId);
					$values->departmentId = RedshopbHelperDepartment::getDepartmentIdByCustomer($values->customerId, $values->customerType);
					$app->setUserState('list.department_id', $values->departmentId);
					$values->rsbUserId = $values->customerType == 'employee' ? $values->customerId : 0;
					$app->setUserState('list.rsbuser_id', $values->rsbUserId);
				}
				else
				{
					$values->companyId    = $app->getUserStateFromRequest('list.company_id', 'company_id', $userCompany, 'int');
					$values->departmentId = $app->getUserStateFromRequest('list.department_id', 'department_id', $userDepartment, 'int');
					$values->rsbUserId    = $app->getUserStateFromRequest('list.rsbuser_id', 'rsbuser_id', $values->userRSid, 'int');
				}
			}
		}
		else
		{
			$values->companyId = $app->getUserState('list.company_id', 0);
			$app->input->set('company_id', $values->companyId);
			$values->departmentId = $app->getUserState('list.department_id', 0);
			$app->input->set('department_id', $values->departmentId);
			$values->rsbUserId = $app->getUserState('list.rsbuser_id', 0);
			$app->input->set('rsbuser_id', $values->rsbUserId);

			$values->customerType = $app->getUserState('shop.customer_type', '');
			$values->customerId   = $app->getUserState('shop.customer_id', 0);
			$app->setUserState('shop.user_redirection', 0);
		}

		if (!self::checkCustomerAvailable($values->customerType, $values->customerId))
		{
			$values->customerId   = 0;
			$values->customerType = '';
			$app->setUserState('shop.customer_id', $values->customerId);
			$app->setUserState('shop.customer_type', $values->customerType);
		}

		if ((is_null($values->companyId) || !$values->companyId))
		{
			$values->companyId    = $userCompany;
			$values->departmentId = $userDepartment;
			$values->rsbUserId    = $values->userRSid;
		}

		static::setCustomerStates($values->customerType, $values->customerId, $values->companyId, $values->departmentId, $values->userRSid);
		$staticValues = get_object_vars($values);
	}

	/**
	 * Set User States
	 *
	 * @param   string  $customerType    Customer type
	 * @param   int     $customerId      Customer id
	 * @param   int     $companyId       Company id
	 * @param   int     $departmentId    Department id
	 * @param   int     $userId          User id
	 *
	 * @return  void
	 */
	public static function setCustomerStates($customerType, $customerId, $companyId, $departmentId, $userId)
	{
		$app = Factory::getApplication();

		$app->setUserState('shop.customer_type', $customerType);
		$app->setUserState('shop.customer_id', $customerId);
		$app->setUserState('list.company_id', $companyId);
		$app->setUserState('list.department_id', $departmentId);
		$app->setUserState('list.rsbuser_id', $userId);
	}

	/**
	 * Check customer available
	 *
	 * @param   string  $customerType  Customer type
	 * @param   integer $customerId    Customer id
	 *
	 * @return  boolean
	 */
	public static function checkCustomerAvailable($customerType = '', $customerId = 0)
	{
		$customerAvailable = true;

		switch ($customerType)
		{
			case 'company':
				$customer = RedshopbHelperCompany::getCompanyById($customerId);

				if (!$customer || $customer->state == 0)
				{
					$customerAvailable = false;
				}
				break;
			case 'department':
				if (!RedshopbHelperDepartment::getDepartmentById($customerId))
				{
					$customerAvailable = false;
				}

				break;
			case 'employee':
				if (!RedshopbHelperUser::getUser($customerId))
				{
					$customerAvailable = false;
				}

				break;
			default:
				$customerAvailable = false;
				break;
		}

		return $customerAvailable;
	}

	/**
	 * Method for get current shopping vendor
	 *
	 * @return  mixed  Vendor object if available. Null otherwise.
	 */
	public static function getVendor()
	{
		$vendor = Factory::getApplication()->getUserState('shop.vendor', null);

		/**
		 * Avoid PHP error __PHP_Incomplete_Class Object
		 *
		 * @reference http://www.phpini.in/php/php-incomplete-class-object.html
		 */
		if (is_null($vendor) || (!is_object($vendor) && gettype($vendor) == 'object'))
		{
			return null;
		}

		return $vendor;
	}

	/**
	 * Set a no-shop mode to select vendor
	 *
	 * @return  void
	 */
	public static function unsetVendor()
	{
		$app = Factory::getApplication();
		$app->setUserState('list.company_id', RedshopbHelperUser::getUserCompanyId());
		$app->setUserState('list.department_id', 0);
		$app->setUserState('list.rsbuser_id', 0);
		$app->setUserState('shop.vendor', null);
		$app->setUserState('shop.customer_type', '');
		$app->setUserState('shop.customer_id', 0);
		$app->setUserState('shop.campaignItems', null);
		$app->setUserState('shop.filter', null);
		$app->setUserState('shop.setImpersonation', 1);

		/** @var RedshopbModelShop $model */
		$model = RedshopbModel::getFrontInstance('Shop');
		$model->clearCart(true);

		self::checkImpersonationDepartment();
	}

	/**
	 * Check whether in collection mode or not
	 *
	 * @param   RedshopbEntityCompany|null  $customersCompany  Company of the currently shopping customer
	 *
	 * @return  boolean
	 */
	public static function inCollectionMode($customersCompany = null)
	{
		$app          = Factory::getApplication();
		$customerType = $app->getUserState('shop.customer_type', '');

		if (is_null($customersCompany))
		{
			$customerId       = $app->getUserState('shop.customer_id', 0);
			$companyId        = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);
			$customersCompany = RedshopbEntityCompany::load($companyId);
		}

		$app               = Factory::getApplication();
		$config            = RedshopbEntityConfig::getInstance();
		$customerType      = $app->getUserState('shop.customer_type', '');
		$shopAsCollection  = $config->get('show_shop_as', '');
		$forceCollection   = strcmp($shopAsCollection, 'collections') === 0;
		$companyCollection = (boolean) $customersCompany->get('use_collections', false);
		$inCollectionMode  = $forceCollection || $companyCollection;

		if ($companyCollection && !$forceCollection && strcmp($customerType, 'employee') === 0)
		{
			$userRoleTypeId = (int) $app->getUserState('shop.role_type_id', 0);

			$roleType = RedshopbEntityRole_Type::load($userRoleTypeId)->get('type', '');

			if ($userRoleTypeId === 6)
			{
				$roleType .= '_wl';
			}

			$collectionEnforcement = $config->get('collection_enforcement_' . $roleType, 0);

			if ($collectionEnforcement == 1)
			{
				$inCollectionMode = false;
			}
		}

		return $inCollectionMode;
	}
}
