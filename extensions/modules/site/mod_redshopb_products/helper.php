<?php
/**
 * @package     Aesir.E-Commerce.Site
 * @subpackage  mod_redshopb_megamenu
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
/**
 * Class ModRedshopbProduct
 *
 * @since  1.6.21
 */
class ModRedshopbProduct
{
	/**
	 * @var Registry
	 */
	protected static $params;

	/**
	 * Get product list
	 *
	 * @param   Registry   $params       The module options.
	 * @param   string     $currentType  Type selection
	 *
	 * @return array
	 */
	public static function getList($params, $currentType = 'new_products')
	{
		self::$params = $params;

		$values = new stdClass;
		RedshopbHelperShop::setUserStates($values);

		if (!$values->customerId
			&& !$values->b2cMode
			&& !$values->superUser)
		{
			return array();
		}

		$isRedShopBCat = false;
		$input         = Factory::getApplication()->input;

		if ($input->getCmd('option', '') == 'com_redshopb' && $input->getCmd('view', '') == 'shop' && $input->getInt('id', 0))
		{
			$isRedShopBCat = true;
		}

		$categoryRelation = $params->get('categoryRelation', 0);

		// If we have category relation, but the current page is not a shop category, then not display items
		if ($categoryRelation > 0 && $isRedShopBCat == false)
		{
			return array(0 => array());
		}

		$cid         = $input->getInt('id', 0);
		$collections = RedshopbHelperCollection::getCustomerCollectionsForShop($values->customerId, $values->customerType);

		switch ($categoryRelation)
		{
			case '1':
				// Current category only
				$category = $cid;

				break;
			case '2':
				// Current category and child categories
				$category   = explode(
					',', RedshopbHelperACL::listAvailableCategories(
						Factory::getUser()->id, $cid, 999, $values->companyId, $collections, 'comma', '', 'redshopb.category.view', 0, 0
					)
				);
				$category[] = $cid;

				break;
			case '0':
			default:
				// Without category filters
				$category = 0;
				break;
		}

		$countItems      = $params->get('countItems', 6);
		$withExtraFields = (bool) $params->get('useExtraFields', false);

		$collectionProducts = array();

		// Get the actual products per collection
		if ($collections)
		{
			foreach ($collections as $collectionId)
			{
				$products = self::getProductsForProductListLayout(
					$values->customerId,
					$values->customerType,
					$category,
					$collectionId,
					$countItems,
					$currentType,
					$withExtraFields
				);

				if (!empty($products))
				{
					$collectionProducts[$collectionId] = $products;
				}
			}
		}
		elseif ($collections === false)
		{
			$products = self::getProductsForProductListLayout(
				$values->customerId,
				$values->customerType,
				$category,
				0,
				$countItems,
				$currentType,
				$withExtraFields
			);

			if (!empty($products))
			{
				$collectionProducts[0] = $products;
			}
		}

		return $collectionProducts;
	}

	/**
	 * Get category products list.
	 *
	 * @param   int        $customerId       Customer id.
	 * @param   string     $customerType     Customer type.
	 * @param   int|array  $category         Category id.
	 * @param   int        $collectionId     Collection id
	 * @param   int        $limit            Optional  Query limit
	 * @param   string     $currentType      Type filtering/ordering products
	 * @param   boolean    $withExtraFields  Load extra field data
	 *
	 * @return array
	 */
	public static function getProductsForProductListLayout(
		$customerId, $customerType, $category = 0, $collectionId = 0, $limit = 0, $currentType = 'new_products', $withExtraFields = false
	)
	{
		/** @var RedshopbModelShop $shopModel */
		$config = RedshopbEntityConfig::getInstance();
		RModelAdmin::addIncludePath(JPATH_SITE . '/components/com_redshopb/models');
		$shopModel = RModelAdmin::getInstance('Shop', 'RedshopbModel', array('ignore_request' => true));

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('p2.*');

		$volumePriceQuery = $db->getQuery(true);
		$volumePriceQuery->select('vp.id')
			->from($db->qn('#__redshopb_product_price', 'vp'))
			->where($db->qn('vp.type') . ' = ' . $db->q('product'))
			->where($db->qn('vp.type_id') . ' = ' . $db->qn('p2.id'))
			->where('(' . $db->qn('vp.quantity_min') . ' IS NOT NULL OR ' . $db->qn('vp.quantity_max') . ' IS NOT NULL)');

		$query->select('(' . $volumePriceQuery . ' LIMIT 0, 1) AS hasVolumePricing');

		$subQuery = $db->getQuery(true)
			->select('DISTINCT(p.id)')
			->from($db->qn('#__redshopb_product', 'p'))
			->where($db->qn('p.state') . ' = 1')
			->where('(p.publish_date = ' . $db->q($db->getNullDate()) . ' OR p.publish_date <= STR_TO_DATE('
				. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where('(p.unpublish_date = ' . $db->q($db->getNullDate()) . ' OR p.unpublish_date >= STR_TO_DATE('
				. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where($db->qn('p.service') . ' = 0');

		// Order by Media image (if it exists)
		$subQuery->leftJoin(
			$db->qn('#__redshopb_media', 'm') . ' ON '
			. $db->qn('m.product_id') . ' = ' . $db->qn('p.id') . ' AND ' . $db->qn('m.state') . ' = 1'
		)
			->order('m.id IS NULL');

		// This join avoid products without any categories
		$subQuery->innerJoin($db->qn('#__redshopb_product_category_xref', 'pcx') . ' ON ' . $db->qn('p.id') . ' = ' . $db->qn('pcx.product_id'));

		if ($category)
		{
			if (is_array($category))
			{
				$category = ArrayHelper::toInteger($category);
				$category = implode(',', $category);
			}
			else
			{
				$category = (int) $category;
			}

			if (!empty($category))
			{
				$subQuery->where($db->qn('pcx.category_id') . ' IN (' . $category . ')');
			}
		}

		if ($collectionId)
		{
			$subQuery->innerJoin($db->qn('#__redshopb_collection_product_xref', 'wpx') . ' ON ' . $db->qn('wpx.product_id') . ' = ' . $db->qn('p.id'))
				->where($db->qn('wpx.collection_id') . ' = ' . $collectionId);
		}

		if (RedshopbHelperUser::isFromMainCompany($customerId, $customerType))
		{
			$subQuery->where('1 = 0');
		}
		else
		{
			$companies          = array();
			$impersonateCompany = RedshopbHelperCompany::getCompanyByCustomer($customerId, $customerType);

			if ($impersonateCompany)
			{
				$companies = RedshopbEntityCompany::getInstance($impersonateCompany->id)
					->getTree(false, false);
			}

			if (empty($companies))
			{
				$companies[] = 0;
			}

			$subQuery->where('(p.company_id IN (' . implode(',', $companies) . ') OR p.company_id IS NULL)');
		}

		$orderBy = false;

		switch ($currentType)
		{
			case 'current_offers':

				// Optional product item join
				$subQuery->join(
					'left',
					$db->qn('#__redshopb_product_item', 'pi') . ' ON ' . $db->qn('pi.product_id') . ' = ' .
					$db->qn('p.id') . ' AND ' . $db->qn('pi.state') . ' = 1'
				);

				RedshopbHelperShop::setOnSaleItemsQuery($subQuery, false, 'p', 'pi');
				$orderBy = self::getOrdering($subQuery);

				break;
			case 'featured':
				$subQuery->where('featured = 1');
				$orderBy = self::getOrdering($subQuery);

				break;
			case 'most_popular':

				$subQuery2 = $db->getQuery(true)
					->select('SUM(' . $db->qn('oi.quantity') . ') AS qty, oi.product_id')
					->from($db->qn('#__redshopb_order_item', 'oi'))
					->group('oi.product_id');

				$subQuery->select('orderItems.qty')
					->leftJoin('(' . $subQuery2 . ') orderItems ON orderItems.product_id = p.id')
					->where('orderItems.qty > 0')
					->order($db->qn('orderItems.qty') . ' DESC');

				break;
			case 'new_products';

				$unixOffset = strtotime('-' . $config->getInt('date_new_product', 14) . ' day', Date::getInstance()->toUnix());
				$now        = date('Y-m-d', $unixOffset);
				$subQuery->where(
					'IF(p.date_new = STR_TO_DATE(' . $db->q('0000-00-00') . ', ' . $db->q('%Y-%m-%d')
						. '), p.created_date >= STR_TO_DATE(' . $db->q($now) . ', ' . $db->q('%Y-%m-%d')
						. '), p.date_new >= STR_TO_DATE(' . $db->q($now) . ', ' . $db->q('%Y-%m-%d') . '))'
				)
					->order('p.date_new DESC, p.created_date DESC');

				break;
			case 'random_products':
			default:
				$orderBy = self::getOrdering($subQuery);
				break;
		}

		$query->from('(' . $subQuery . ' LIMIT ' . (int) $limit . ') as p1')
			->innerJoin($db->qn('#__redshopb_product', 'p2') . ' ON p1.id = p2.id');

		if ($orderBy)
		{
			$query->order($db->qn('p1.ordering'));
		}

		$products = $db->setQuery($query, 0, (int) $limit)->loadObjectList('id');

		if (!empty($products))
		{
			self::attachExtraFields($products, $withExtraFields);

			RedshopbHelperProduct::setProduct($products);
			$preparedItems              = $shopModel->prepareItemsForShopView($products, $customerId, $customerType, $collectionId, true);
			$preparedItems->productData = $products;

			return $preparedItems;
		}

		return array();
	}

	/**
	 * Add ordering clause
	 *
	 * @param   JDatabaseQuery  $query  the query
	 *
	 * @return boolean
	 */
	private static function getOrdering($query)
	{
		$orderField = self::$params->get('orderFieldAlias', '');

		if (empty($orderField))
		{
			$query->order('rand()');

			return false;
		}

		$field = RedshopbHelperField::getFieldByAlias($orderField, 'product');

		if (empty($field->id))
		{
			$query->order('rand()');

			return false;
		}

		$db = Factory::getDbo();

		$query->select($db->qn('order.' . $field->value_type, 'ordering'));

		$query->leftJoin(
			$db->qn('#__redshopb_field_data', 'order') . ' ON ' . $db->qn('order.field_id') . ' = ' . (int) $field->id
			. ' AND ' . $db->qn('order.item_id') . ' = ' . $db->qn('p.id')
		);

		$query->order($db->qn('order.' . $field->value_type) . 'ASC');

		return true;
	}

	/**
	 * Method to attach extra fields to each product
	 *
	 * @param   array  $products         array of product objects
	 * @param   bool   $withExtraFields  should extra field data be included?
	 *
	 * @return void
	 */
	private static function attachExtraFields(&$products, $withExtraFields = false)
	{
		if (!$withExtraFields)
		{
			return;
		}

		foreach ($products AS $product)
		{
			$product->extrafields = RedshopbHelperField::loadScopeFieldData('product', $product->id, 0, true);
		}
	}
}
