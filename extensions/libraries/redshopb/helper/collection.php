<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Helpers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
/**
 * collection helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperCollection
{
	/**
	 * Get product item value from type.
	 *
	 * @param   string  $type         The section.
	 * @param   object  $productItem  Product item object.
	 * @param   bool    $includeSku   Whether to include Sku before the value or not (only applies for strings)
	 *
	 * @return  mixed|null Value on succes, null on failure.
	 */
	public static function getProductItemValueFromType($type = '0', $productItem = null, $includeSku = false)
	{
		if (empty($productItem))
		{
			return null;
		}

		$sku = ($includeSku ? $productItem->sku . ' ' : '');

		switch ($type)
		{
			case 1:
				return $sku . (string) $productItem->string_value;
			case 2:
				return (float) $productItem->float_value;
			case 3:
				return (int) $productItem->int_value;
			default:
				return strcmp($sku, (string) $productItem->string_value)
						? (string) $productItem->string_value
						: $sku . (string) $productItem->string_value;
		}
	}

	/**
	 * Gets all products in searched collection.
	 *
	 * @param   integer  $productId  The primary key id for the item.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 */
	public static function getCollectionProductItems($productId)
	{
		$db	= Factory::getDbo();

		$query = $db->getQuery(true)
			->select('pav.*')
			->from('#__redshopb_product_attribute_value AS pav')

			->select('pa.name AS product_attribute_name, pa.type_id as product_attribute_type')
			->leftJoin('#__redshopb_product_attribute AS pa ON pa.id = pav.product_attribute_id')

			->leftJoin('#__redshopb_product_item_attribute_value_xref AS piav ON piav.product_attribute_value_id = pav.id')

			->select('pi.id AS product_item_id')
			->leftJoin('#__redshopb_product_item AS pi ON pi.id = piav.product_item_id')

			->where('pi.product_id = ' . (int) $productId)
			->order('pav.ordering');

		RedshopbHelperProduct_Attribute::replaceSizeLanguageQuery(
			array(RDatabaseSqlparserSqltranslation::createTableJoinParam('pa.name', '=', $db->quote('Str.')),
			)
		);
		$db->setQuery($query);
		$result = $db->loadObjectList();
		RedshopbHelperProduct_Attribute::clearSizeLanguageQuery();

		return $result;
	}

	/**
	 * Get dynamic types
	 *
	 * @param   int  $productId  Id product
	 *
	 * @return mixed
	 */
	public static function getDynamicTypes($productId)
	{
		$db = Factory::getDbo();

		// Select minimal ordering from current product
		$subQuery = $db->getQuery(true)
			->select('MIN(pa2.ordering)')
			->from($db->qn('#__redshopb_product_attribute', 'pa2'))
			->where('pa2.product_id = pa.product_id')
			->where('pa2.state = 1');

		$query = $db->getQuery(true)
			->select(array('pav.*', 'pa.name', 'pa.type_id', 'pa.main_attribute'))
			->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
			->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pa.id = pav.product_attribute_id')
			->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'pivx') . ' ON pivx.product_attribute_value_id = pav.id')
			->leftJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON pi.id = pivx.product_item_id')
			->where('pa.product_id = ' . (int) $productId)
			->where('pa.state = 1')
			->where('pav.state = 1')
			->where('pa.ordering != (' . $subQuery . ')')
			->where('pi.state = 1')
			->group('pav.id')
			->order('pav.ordering')
			->order('pav.id ASC');

		RedshopbHelperProduct_Attribute::replaceSizeLanguageQuery(
			array(RDatabaseSqlparserSqltranslation::createTableJoinParam('pa.name', '=', $db->quote('Str.')),
			)
		);
		$db->setQuery($query);
		$results = $db->loadObjectList('id');
		RedshopbHelperProduct_Attribute::clearSizeLanguageQuery();

		return $results;
	}

	/**
	 * Get isset item and relating items with attribute values
	 *
	 * @param   int  $productId     Id product
	 * @param   int  $collectionId  Id current collection
	 *
	 * @return array|null
	 */
	public static function getIssetItems($productId, $collectionId)
	{
		$db = Factory::getDbo();

		// Select attribute values ids from items
		$subQuery = $db->getQuery(true)
			->select('GROUP_CONCAT(pav.id ORDER BY pa.ordering asc SEPARATOR ' . $db->q('_') . ')')
			->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
			->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pav.product_attribute_id = pa.id')
			->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'pivx') . ' ON pivx.product_attribute_value_id = pav.id')
			->where('pa.product_id = ' . (int) $productId)
			->where('pi.id = pivx.product_item_id')
			->where('pa.state = 1')
			->where('pav.state = 1')
			->order('pa.ordering ASC')
			->order('pav.ordering')
			->order('pav.id ASC');

		$query = $db->getQuery(true)
			->select(
				array('(' . $subQuery . ') AS values_ids', 'wpix.collection_id', 'pi.id', 'wpix.state', 'pi.sku')
			)
			->from($db->qn('#__redshopb_product_item', 'pi'))
			->leftJoin(
				$db->qn('#__redshopb_collection_product_item_xref', 'wpix')
				. ' ON wpix.product_item_id = pi.id AND wpix.state = 1 AND wpix.collection_id = ' . (int) $collectionId
			)
			->where('pi.product_id = ' . (int) $productId)
			->where('pi.state = 1');

		$db->setQuery($query);

		return $db->loadObjectList('values_ids');
	}

	/**
	 * Get isset dynamic variants
	 *
	 * @param   int  $productId  Id product
	 *
	 * @return mixed
	 */
	public static function getIssetDynamicVariants($productId)
	{
		$db = Factory::getDbo();

		// Select minimal ordering from current product
		$subQuery = $db->getQuery(true)
			->select('MIN(pa2.ordering)')
			->from($db->qn('#__redshopb_product_attribute', 'pa2'))
			->where('pa2.product_id = pa.product_id')
			->where('pa2.state = 1');

		$subQuery2 = $db->getQuery(true)
			->select('GROUP_CONCAT(pav2.id ORDER BY pa2.ordering asc SEPARATOR ' . $db->q('_') . ')')
			->from($db->qn('#__redshopb_product_attribute_value', 'pav2'))
			->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'pivx2') . ' ON pivx2.product_attribute_value_id = pav2.id')
			->leftJoin($db->qn('#__redshopb_product_attribute', 'pa2') . ' ON pa2.id = pav2.product_attribute_id')
			->where('pi.id = pivx2.product_item_id')
			->where('pav2.state = 1')
			->where('pa2.state = 1')
			->where('pa2.product_id = ' . (int) $productId)
			->where('pa2.ordering != (' . $subQuery . ')')
			->order('pa2.ordering ASC')
			->order('pav2.ordering')
			->order('pav2.id ASC');

		$query = $db->getQuery(true)
			->select(
				array('(' . $subQuery2 . ') AS concat_dynamics')
			)
			->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
			->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pa.id = pav.product_attribute_id')
			->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'pivx') . ' ON pivx.product_attribute_value_id = pav.id')
			->leftJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON pi.id = pivx.product_item_id')
			->where('pa.product_id = ' . (int) $productId)
			->where('pa.state = 1')
			->where('pav.state = 1')
			->where('pa.ordering = (' . $subQuery . ')')
			->where('pi.state = 1')
			->group('concat_dynamics')
			->order('pav.ordering')
			->order('pav.id ASC');
		$db->setQuery($query);

		return $db->loadColumn();
	}

	/**
	 * Get types from X axis
	 *
	 * @param   int  $productId  Id product
	 *
	 * @return mixed
	 */
	public static function getStaticTypes($productId)
	{
		$db = Factory::getDbo();

		// Select minimal ordering from current product
		$subQuery = $db->getQuery(true)
			->select('MIN(pa2.ordering)')
			->from($db->qn('#__redshopb_product_attribute', 'pa2'))
			->where('pa2.product_id = pa.product_id')
			->where('pa2.state = 1');

		$query = $db->getQuery(true)
			->select(
				array(
					'pav.*', 'pa.product_id', 'pa.ordering', 'pa.name', 'pa.type_id'
				)
			)
			->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
			->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pa.id = pav.product_attribute_id')
			->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'pivx') . ' ON pivx.product_attribute_value_id = pav.id')
			->leftJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON pi.id = pivx.product_item_id')
			->where('pa.product_id = ' . (int) $productId)
			->where('pa.state = 1')
			->where('pav.state = 1')
			->where('pa.ordering = (' . $subQuery . ')')
			->where('pi.state = 1')
			->group('pav.id')
			->order('pav.ordering')
			->order('pav.id ASC');

		RedshopbHelperProduct_Attribute::replaceSizeLanguageQuery(
			array(RDatabaseSqlparserSqltranslation::createTableJoinParam('pa.name', '=', $db->quote('Str.')),
			)
		);
		$db->setQuery($query);

		$result = $db->loadObjectList();
		RedshopbHelperProduct_Attribute::clearSizeLanguageQuery();

		return $result;
	}

	/**
	 * Gets all products in searched collection.
	 *
	 * @param   integer  $collectionId  The primary key id for the item.
	 *
	 * @return  array  List of Collection Products.
	 */
	public static function getCollectionProducts($collectionId)
	{
		$db	= Factory::getDbo();

		$query = $db->getQuery(true)
			->select('cpx.product_id')
			->from($db->qn('#__redshopb_collection_product_xref', 'cpx'))
			->join('inner', $db->qn('#__redshopb_product', 'p') . ' ON ' . $db->qn('p.id') . ' = ' . $db->qn('cpx.product_id'))
			->where('cpx.collection_id = ' . (int) $collectionId)
			->order(array('ISNULL(cpx.ordering)', 'cpx.ordering', 'p.name'));

		$db->setQuery($query);

		$result = $db->loadColumn();

		return $result;
	}

	/**
	 * Gets all products in searched Collection assigned to department.
	 *
	 * @param   integer  $departmentId  Department id
	 *
	 * @return  array  List of Collection Products.
	 */
	public static function getCollectionProductsByDepartment($departmentId)
	{
		$db	= Factory::getDbo();

		$parentDepartments = RedshopbHelperDepartment::getParentDepartments($departmentId);
		$parentDepartments = ArrayHelper::toInteger($parentDepartments);
		$parentDepartments = implode(',', $parentDepartments);

		$query = $db->getQuery(true)
			->select('p.id AS identifier, p.name AS data')
			->from('#__redshopb_product AS p')
			->leftJoin($db->qn('#__redshopb_collection_product_xref', 'wpx') . ' ON wpx.product_id = p.id')
			->where('p.state = 1')
			->where('(p.publish_date = ' . $db->q($db->getNullDate()) . ' OR p.publish_date <= STR_TO_DATE('
				. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where('(p.unpublish_date = ' . $db->q($db->getNullDate()) . ' OR p.unpublish_date >= STR_TO_DATE('
				. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			)
			->order(array('ISNULL(wpx.ordering)', 'wpx.ordering', 'p.name'));

		// Relating with collection
		$query->leftJoin($db->qn('#__redshopb_collection', 'w') . ' ON wpx.collection_id = w.id')
			->leftJoin($db->qn('#__redshopb_collection_department_xref', 'wdx') . ' ON wdx.collection_id = w.id')
			->where('wdx.department_id IN (' . $parentDepartments . ')')
			->where('w.state = 1');

		// Exclude products without items
		$subQuery = $db->getQuery(true)
			->select('COUNT(wpix.product_item_id)')
			->from($db->qn('#__redshopb_collection_product_item_xref', 'wpix'))
			->leftJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON pi.id = wpix.product_item_id')
			->where('wpx.collection_id = wpix.collection_id')
			->where('wpix.state = 1')
			->where('pi.product_id = p.id')
			->where('pi.state = 1');
		$query->where('(' . $subQuery . ') > 0');

		$query->group('p.id');
		$db->setQuery($query);

		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Get list of customer collections.
	 *
	 * @param   int      $customerId    Customer id.
	 * @param   string   $customerType  Customer type.
	 * @param   array    $departments   Departments list.
	 * @param   boolean  $collection    Collection
	 *
	 * @return  array|boolean  Collection ids.
	 */
	public static function getCustomerCollectionsForShop($customerId = 0, $customerType = '', $departments = array(), $collection = false)
	{
		$collections = array();
		$app         = Factory::getApplication();

		if ($customerType == '')
		{
			$customerType = $app->getUserState('shop.customer_type', '');
		}

		if ($customerId == 0)
		{
			$customerId = $app->getUserState('shop.customer_id', 0);
		}

		$collectionShopping = RedshopbHelperShop::inCollectionMode(
			RedshopbEntityCompany::getInstance(
				RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType)
			)
		);

		if (($customerId != 0 && $collectionShopping) || $collection)
		{
			if (empty($departments))
			{
				if ($customerType == 'employee' && is_null(RedshopbHelperUser::getUser($customerId)->department))
				{
					// Find out employee's role
					$userRoleTypeId = (int) $app->getUserState('shop.role_type_id', 0);

					$roleType = RedshopbEntityRole_Type::load($userRoleTypeId)->get('type', '');

					if ($userRoleTypeId === 6)
					{
						$roleType .= '_wl';
					}

					// Check if employee's role allow them to see all collections (departments)
					$config = RedshopbEntityConfig::getInstance();

					// If user is admin or sales, we need a different default value
					$defaultSeeAllValue    = in_array($userRoleTypeId, array(2, 4)) ? 1 : 0;
					$roleSeeAllCollections = (boolean) $config->get($roleType . '_see_all_collections', $defaultSeeAllValue);

					if ($roleSeeAllCollections)
					{
						$customerCompany    = RedshopbEntityCompany::getInstanceByCustomer($customerId, $customerType);
						$collectionIds      = array();
						$companyCollections = $customerCompany->searchCollections(array('filter.skipUserCheck' => true));

						foreach ($companyCollections as $companyCollection)
						{
							$collectionIds[] = (int) $companyCollection->get('id');
						}

						return $collectionIds;
					}
					else
					{
						$user        = RedshopbHelperUser::getUser($customerId);
						$departments = $user->department ? array($user->department) : null;
					}
				}
				else
				{
					$departments = RedshopbHelperDepartment::getCustomerDepartments($customerId, $customerType, true, true);
				}
			}
			elseif (is_array($departments))
			{
				$ids = array();

				foreach ($departments as $department)
				{
					if (is_object($department))
					{
						$ids[] = $department->id;
					}
					elseif (is_array($department) && isset($department['id']))
					{
						$ids[] = $department['id'];
					}
					elseif (is_numeric($department))
					{
						$ids[] = $department;
					}
				}

				if (!empty($ids))
				{
					$departments = $ids;
				}
				else
				{
					$departments = RedshopbHelperDepartment::getCustomerDepartments($customerId, $customerType, true, true);
				}
			}

			$collections = self::getCollectionsFromDepartments($departments);
		}
		elseif (!$collectionShopping)
		{
			return false;
		}

		return $collections;
	}

	/**
	 * Get collection currency.
	 *
	 * @param   int   $collectionId  Collection id.
	 * @param   bool  $string        Return the alpha3 (currency symbol) representation
	 *
	 * @return integer|string
	 */
	public static function getCurrency($collectionId, $string = false)
	{
		$db       = Factory::getDbo();
		$query    = $db->getQuery(true);
		$currency = ($string ? 'DKK' : 38);

		if ($collectionId)
		{
			$query->from($db->qn('#__redshopb_collection', 'w'))
				->where($db->qn('w.id') . ' = ' . (int) $collectionId);

			if ($string)
			{
				$query->select($db->qn('c.alpha3'))
					->innerJoin($db->qn('#__redshopb_currency', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('w.currency_id'));
			}
			else
			{
				$query->select($db->qn('w.currency_id'));
			}

			$db->setQuery($query);

			$currency = $db->loadResult();
		}

		return $currency;
	}

	/**
	 * Get collection name.
	 *
	 * @param   int   $collectionId         Collection id.
	 * @param   bool  $includeProductCount  Include collection products count.
	 *
	 * @return string Collection name.
	 */
	public static function getName($collectionId, $includeProductCount = false)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$name  = '';

		if ($collectionId)
		{
			$query->select($db->qn('w.name'))
				->from($db->qn('#__redshopb_collection', 'w'))
				->where($db->qn('w.id') . ' = ' . (int) $collectionId);
			$db->setQuery($query);

			$name = $db->loadResult();
		}

		if ($includeProductCount)
		{
			$prSearch = new RedshopbDatabaseProductsearch;
			$count    = $prSearch->getProductCount($collectionId);

			return $name . ' (' . $count . ')';
		}

		return $name;
	}

	/**
	 * Get shop collection products for display
	 *
	 * @param   bool     $placeOrderPermission  Permission to place orders
	 * @param   mixed    $collectionId          The id of the collection
	 * @param   int      $start                 Query start.
	 * @param   int      $limit                 Query limit.
	 * @param   int      $onSale                On sale filter.
	 * @param   string   $search                Search filter.
	 * @param   int      $category              Category filter.
	 * @param   string   $flatDisplay           Flat display filter.
	 * @param   int      $collection            Collection filter.
	 * @param   array    $filters               Array of filters
	 * @param   boolean  $forceCollection       Flag for set to Shop model to force use collection or not.
	 *
	 * @return string
	 */
	public static function getShopCollectionProducts(
		$placeOrderPermission = true, $collectionId = null, $start = 0, $limit = 0, $onSale = 0, $search = '',
		$category = 0, $flatDisplay = '', $collection = 0, $filters = array(), $forceCollection = false
	)
	{
		$app   = Factory::getApplication();
		$input = $app->input;

		if (!is_null($collectionId))
		{
			$tags = !empty($filters['product_tag']) ? $filters['product_tag'] : array();

			/** @var RedshopbModelShop $shopModel */
			$shopModel = RModelAdmin::getInstance('Shop', 'RedshopbModel', array('ignore_request' => true));
			$shopModel->setState('product_collection', $collectionId);
			$shopModel->setState('list.limit', $limit);
			$shopModel->setState('list.start', $start);
			$shopModel->setState('filter.product_collection', $collection);
			$shopModel->setState('filter.search_shop_products', $search);
			$shopModel->setState('filter.product_category', $category);
			$shopModel->setState('filter.attribute_flat_display', $flatDisplay);
			$shopModel->setState('filter.onsale', $onSale);
			$shopModel->setState('filter.product_tag', $tags);
			$shopModel->setState('force_collection', (boolean) $forceCollection);
		}
		else
		{
			/** @var RedshopbModelShop $shopModel */
			$shopModel = RModelAdmin::getInstance('Shop', 'RedshopbModel');
		}

		$customerId   = Factory::getApplication()->getUserStateFromRequest('shop.customer_id', 'customer_id', 0, 'int');
		$customerType = Factory::getApplication()->getUserStateFromRequest('shop.customer_type', 'customer_type', '', 'string');
		$itemId       = $input->getInt('Itemid', 0);

		$formName   = 'collectionForm_' . ($collectionId ? $collectionId : 'unique');
		$pagination = $shopModel->getPagination();
		$pagination->set('formName', $formName);

		$items         = $shopModel->getItems();
		$preparedItems = $shopModel->prepareItemsForShopView($items, $customerId, $customerType, $collectionId);

		$collectionProducts = RedshopbLayoutHelper::render('shop.collection', array(
				'collectionId' => $collectionId,
				'state' => $shopModel->getState(),
				'preparedItems' => $preparedItems,
				'pagination' => $pagination,
				'filter_form' => $shopModel->getForm(),
				'activeFilters' => $shopModel->getActiveFilters(),
				'formName' => $formName,
				'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=shop&model=shop'),
				'return' => base64_encode(
					'index.php?option=com_redshopb&view=shop&collectionId=' . $collectionId .
					'&tab=collection_' . $collectionId .
					'&from_shop=1&Itemid=' . $itemId
				),
				'customerId' => $customerId,
				'customerType' => $customerType,
				'placeOrderPermission' => $placeOrderPermission
			)
		);

		return $collectionProducts;
	}

	/**
	 * Get collections list for given departments list.
	 *
	 * @param   array  $departments  Departments list of ids.
	 *
	 * @return array Collection list of ids.
	 */
	public static function getCollectionsFromDepartments($departments = array())
	{
		static $collections = array();
		$key                = serialize($departments);

		if (!array_key_exists($key, $collections))
		{
			if (!is_array($departments) || count($departments) == 0)
			{
				$collections[$key] = array();
			}
			else
			{
				$db     = Factory::getDbo();
				$query  = $db->getQuery(true)
					->select('DISTINCT ' . $db->qn('w.id'))
					->from($db->qn('#__redshopb_collection', 'w'))
					->innerJoin($db->qn('#__redshopb_collection_department_xref', 'wdx') . ' ON wdx.collection_id = w.id')
					->where('w.state = 1')
					->where('wdx.department_id IN (' . implode(',', $departments) . ')')
					->group($db->qn('w.id'));
				$result = $db->setQuery($query)->loadColumn();

				if (empty($result) || !is_array($result))
				{
					$collections[$key] = array();
				}
				else
				{
					$collections[$key] = $result;
				}
			}
		}

		$collectionsToReturn = $collections[$key];

		Factory::getApplication()
			->triggerEvent('onRedshopbAdjustAvailableOfCollections', [&$collectionsToReturn]);

		return $collectionsToReturn;
	}

	/**
	 * Get active user's collections
	 *
	 * @param   bool  $filter  Determines if the shop filter for departments must be used or not
	 *
	 * @return  array  object with name and identifier of each collection
	 */
	public static function getUserCollections($filter = false)
	{
		if ($filter)
		{
			$parentDepartments = RedshopbHelperDepartment::getParentDepartments();
			$parentDepartments = ArrayHelper::toInteger($parentDepartments);
			$parentDepartments = implode(',', $parentDepartments);
		}

		$db = Factory::getDbo();

		// Product sub query
		$productSubQuery = $db->getQuery(true)
			->select('COUNT(p.id)')
			->from($db->qn('#__redshopb_product', 'p'))

			->leftJoin($db->qn('#__redshopb_collection_product_xref', 'cpx') . ' ON ' . $db->qn('p.id') . ' = ' . $db->qn('cpx.product_id'))
			->leftJoin($db->qn('#__redshopb_collection', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('cpx.collection_id'))

			->where($db->qn('c.state') . ' = 1')
			->where($db->qn('p.state') . ' = 1')
			->where('(p.publish_date = ' . $db->q($db->getNullDate()) . ' OR p.publish_date <= STR_TO_DATE('
				. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where('(p.unpublish_date = ' . $db->q($db->getNullDate()) . ' OR p.unpublish_date >= STR_TO_DATE('
				. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where($db->qn('cpx.state') . ' = 1')
			->where($db->qn('w3.id') . ' = ' . $db->qn('c.id'));

		// Product item sub query
		$productItemsSubQuery = $db->getQuery(true)
			->select('COUNT(pi.id)')
			->from($db->qn('#__redshopb_product_item', 'pi'))

			->leftJoin(
				$db->qn('#__redshopb_collection_product_item_xref', 'cpif') . ' ON ' . $db->qn('pi.id') . ' = ' . $db->qn('cpif.product_item_id')
			)
			->leftJoin($db->qn('#__redshopb_collection', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('cpif.collection_id'))

			->where($db->qn('c.state') . ' = 1')
			->where($db->qn('pi.state') . ' = 1')
			->where($db->qn('cpif.state') . ' = 1')
			->where($db->qn('w3.id') . ' = ' . $db->qn('c.id'));

		$query = $db->getQuery(true)
			->select($db->qn('w3.id', 'identifier'))
			->select($db->qn('w3.name', 'data'))
			->from($db->qn('#__redshopb_collection', 'w3'))
			->leftJoin($db->qn('#__redshopb_collection_department_xref', 'wdx') . ' ON w3.id = wdx.collection_id')
			->where('w3.state = 1')
			->where('((' . $productSubQuery . ') > 0 OR (' . $productItemsSubQuery . ') > 0)')
			->group('w3.id');

		if ($filter && !empty($parentDepartments))
		{
			$query->where('wdx.department_id IN (' . $parentDepartments . ')');
		}

		// Check for available companies and departments for this user if not a system admin of the app
		if (!RedshopbHelperACL::isSuperAdmin())
		{
			$user = Factory::getUser();
			$query->where('w3.company_id IN (' . RedshopbHelperACL::listAvailableCompanies($user->id) . ')');

			$query->where('wdx.department_id IN (' . RedshopbHelperACL::listAvailableDepartments($user->id) . ')');
		}

		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Determine if a company type uses collections or not for shopping
	 *
	 * @param   string  $companyId  Company Id from company table
	 *
	 * @return  boolean
	 *
	 * @deprecated  1.13.0  Use RedshopbHelperShop::inCollectionMode(RedshopbEntityCompany);
	 */
	public static function isCollectionShopping($companyId)
	{
		return RedshopbHelperShop::inCollectionMode(RedshopbEntityCompany::getInstance((int) $companyId));
	}
}
