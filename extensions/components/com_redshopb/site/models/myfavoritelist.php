<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
/**
 * My favorite list Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelMyfavoritelist extends RedshopbModelAdmin
{
	/**
	 * Favorite list object
	 *
	 * @var  null
	 */
	public $item = null;

	/**
	 * Method for get product list of an favorite
	 *
	 * @param   int              $favoriteId  ID of favorite
	 * @param   boolean          $fullData    True for get full data of products in result. False just return array of product ID.
	 *
	 * @return  array|boolean    $products    List of products if success. False otherwise.
	 */
	public function getProducts($favoriteId, $fullData = false)
	{
		$favoriteId = (int) $favoriteId;

		if (!$favoriteId)
		{
			return false;
		}

		$db = $this->_db;

		$query = $db->getQuery(true)
			->select($db->qn(array('product_id', 'quantity')))
			->from($db->qn('#__redshopb_favoritelist_product_xref'))
			->where($db->qn('favoritelist_id') . ' = ' . $favoriteId);
		$db->setQuery($query);

		$productData = $db->loadObjectList();

		if (empty($productData))
		{
			return false;
		}

		if (!$fullData)
		{
			return $productData;
		}

		$products = array();

		foreach ($productData as $product)
		{
			$tmpProduct           = RedshopbHelperProduct::loadProduct($product->product_id);
			$tmpProduct->quantity = $product->quantity;
			$products[]           = $tmpProduct;
		}

		return $products;
	}

	/**
	 * Method for get product item list of an favorite
	 *
	 * @param   int              $favoriteId       ID of favorite
	 * @param   boolean          $fullData         True for get full data of product items in result. False just return array of product ID.
	 *
	 * @return  array|boolean    $productItems     List of product items if success. False otherwise.
	 */
	public function getProductItems($favoriteId, $fullData = false)
	{
		$favoriteId = (int) $favoriteId;

		if (!$favoriteId)
		{
			return false;
		}

		$db = $this->_db;

		$query = $db->getQuery(true)
			->select($db->qn(array('product_item_id', 'quantity')))
			->from($db->qn('#__redshopb_favoritelist_product_item_xref'))
			->where($db->qn('favoritelist_id') . ' = ' . $favoriteId);
		$db->setQuery($query);

		$productItemData = $db->loadObjectList();

		if (empty($productItemData))
		{
			return false;
		}

		if (!$fullData)
		{
			return $productItemData;
		}

		$productItems = array();

		foreach ($productItemData as $productItem)
		{
			$tmpProductItem           = RedshopbHelperProduct_Item::loadProductItem($productItem->product_item_id);
			$tmpProductItem->quantity = $productItem->quantity;
			$productItems[]           = $tmpProductItem;
		}

		return $productItems;
	}

	/**
	 * Method for remove reference of a product from favorite list
	 *
	 * @param   int  $favoriteId  ID of favorite list.
	 * @param   int  $productId   ID of product
	 *
	 * @return  boolean           True on success. False otherwise.
	 */
	public function removeSingleProduct($favoriteId, $productId)
	{
		$favoriteId = (int) $favoriteId;
		$productId  = (int) $productId;

		if (!$favoriteId || !$productId)
		{
			return false;
		}

		if (Factory::getUser()->guest)
		{
			return false;
		}

		$table = RTable::getInstance('Myfavoritelist', 'RedshopbTable');

		// Check favorite list exist.
		if (!$table->load($favoriteId))
		{
			return false;
		}

		if (!$this->checkCanEdit($table))
		{
			return false;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->delete($db->qn('#__redshopb_favoritelist_product_xref'))
			->where($db->qn('favoritelist_id') . ' = ' . $favoriteId)
			->where($db->qn('product_id') . ' = ' . $productId);
		$db->setQuery($query);

		return $db->execute();
	}

	/**
	 * Method for remove reference of a product item from favorite list
	 *
	 * @param   int  $favoriteId      ID of favorite list.
	 * @param   int  $productItemId   ID of product item
	 *
	 * @return  boolean           True on success. False otherwise.
	 */
	public function removeSingleProductItem($favoriteId, $productItemId)
	{
		$favoriteId    = (int) $favoriteId;
		$productItemId = (int) $productItemId;

		if (!$favoriteId || !$productItemId)
		{
			return false;
		}

		if (Factory::getUser()->guest)
		{
			return false;
		}

		$table = RTable::getInstance('Myfavoritelist', 'RedshopbTable');

		// Check favorite list exist.
		if (!$table->load($favoriteId))
		{
			return false;
		}

		if (!$this->checkCanEdit($table))
		{
			return false;
		}

		$db                   = Factory::getDbo();
		$favProdItemXrefTable = $db->qn('#__redshopb_favoritelist_product_item_xref');

		if (is_array($favProdItemXrefTable))
		{
			return false;
		}

		$query = $db->getQuery(true)
			->delete($favProdItemXrefTable)
			->where($db->qn('favoritelist_id') . ' = ' . $favoriteId)
			->where($db->qn('product_item_id') . ' = ' . $productItemId);
		$db->setQuery($query);

		return $db->execute();
	}

	/**
	 * Check whether current user can edit the current product list
	 *
	 * @param   RedshopbTable  $table   Myfavoritelist table loaded with the current favourite list ID
	 *
	 * @return  boolean        $canEdit
	 */
	protected function checkCanEdit($table)
	{
		$canEdit = false;

		// If current user is Super Admin
		if (RedshopbHelperACL::isSuperAdmin())
		{
			$canEdit = true;
		}
		// Make sure current user has same company with owner of favourite list
		elseif (RedshopbHelperUser::getUserCompanyId() == $table->company_id)
		{
			// If current user is Administrator of this company
			if (RedshopbHelperACL::getPermission('manage', 'company'))
			{
				$canEdit = true;
			}
			// If current user is Head of Department of this department
			elseif (RedshopbHelperACL::getPermission('manage', 'department')
				&& (RedshopbHelperUser::getUserDepartmentId() == $table->department_id || is_null($table->department_id)))
			{
				$canEdit = true;
			}
			else
			{
				$user = RedshopbHelperUser::getUser(Factory::getUser()->id, 'joomla');

				// Check if current user is B2B user / Favourite list exist / And this user is owner
				if ($user && $table->user_id == $user->id)
				{
					$canEdit = true;
				}
			}
		}

		return $canEdit;
	}

	/**
	 * Method for adding product to favorite list
	 *
	 * @param   int  $favoriteId  ID of favorite list.
	 * @param   int  $productId   ID of product
	 *
	 * @return  boolean           True on success. False otherwise.
	 */
	public function addProduct($favoriteId, $productId)
	{
		if (!$favoriteId || !$productId)
		{
			return false;
		}

		$table        = $this->getTable('Favoritelist_Product_Xref');
		$favListTable = RedshopbTable::getAdminInstance('Myfavoritelist');

		// Check favorite list exist.
		if (!$favListTable->load($favoriteId))
		{
			return false;
		}

		if ($table->load(
			array(
					'favoritelist_id' => $favoriteId,
					'product_id' => $productId
				)
		))
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_ERROR_PRODUCT_ALREADY_ADDED_FAVORITELIST'), 'error');

			return false;
		}

		$row                    = array();
		$row['product_id']      = (int) $productId;
		$row['favoritelist_id'] = (int) $favoriteId;
		$row['quantity']        = 1;

		if (!$table->save($row))
		{
			return false;
		}

		return true;
	}

	/**
	 * Method for adding product item to favorite list
	 *
	 * @param   int  $favoriteId      ID of favorite list.
	 * @param   int  $productItemId   ID of product item
	 *
	 * @return  boolean           True on success. False otherwise.
	 */
	public function addProductItem($favoriteId, $productItemId)
	{
		if (!$favoriteId || !$productItemId)
		{
			return false;
		}

		$table        = $this->getTable('Favoritelist_Product_Item_Xref');
		$favListTable = RedshopbTable::getAdminInstance('Myfavoritelist');

		// Check favorite list exist.
		if (!$favListTable->load($favoriteId))
		{
			return false;
		}

		if ($table->load(
			array(
				'favoritelist_id' => $favoriteId,
				'product_item_id' => $productItemId
			)
		))
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_ERROR_PRODUCT_ITEM_ALREADY_ADDED_FAVORITELIST'), 'error');

			return false;
		}

		$row                    = array();
		$row['product_item_id'] = (int) $productItemId;
		$row['favoritelist_id'] = (int) $favoriteId;
		$row['quantity']        = 1;

		if (!$table->save($row))
		{
			return false;
		}

		return true;
	}

	/**
	 * Method for deleting a favorite list owned by the current user
	 *
	 * @param   int  $favoriteId  ID of favorite list.
	 *
	 * @return  boolean           True on success. False otherwise.
	 */
	public function deleteOwnList($favoriteId)
	{
		$favoriteId = (int) $favoriteId;

		if (!$favoriteId)
		{
			return false;
		}

		$user = Factory::getUser();

		if ($user->guest)
		{
			return false;
		}

		$redshopbUser = RedshopbHelperUser::getUser($user->id, 'joomla');
		$table        = RTable::getInstance('Myfavoritelist', 'RedshopbTable');

		// Check favorite list exist.
		if (!$table->load($favoriteId))
		{
			return false;
		}

		// Check owner of favorite list.
		if ((!$redshopbUser && ($table->created_by != $user->id)) || ($redshopbUser && ($table->user_id != $redshopbUser->id)))
		{
			return false;
		}

		return $table->delete($favoriteId);
	}

	/**
	 * Method for requesting price of a product
	 *
	 * @param   int  $myFavoriteListId  My Favorite List Id
	 * @param   int  $productId         product id
	 * @param   int  $collectionId      Collection id
	 * @param   int  $quantity          Quantity
	 *
	 * @return  array.
	 */
	public function getProductPrice($myFavoriteListId, $productId, $collectionId = 0, $quantity = 0)
	{
		$myFavoriteListTable = $this->getTable();
		$myFavoriteListTable->load($myFavoriteListId);
		$currency = RedshopbHelperPrices::getCurrency(
			$myFavoriteListTable->get('user_id'), 'employee', $collectionId
		);
		$price    = 0;

		// Format quantity as decimal number format
		$quantity = RedshopbHelperProduct::decimalFormat($quantity, $productId);

		$priceObject = RedshopbHelperPrices::getProductPrice(
			$productId, $myFavoriteListTable->get('user_id'), 'employee', $currency,
			array($collectionId), '', 0, $quantity, true
		);

		if ($priceObject)
		{
			$price = $priceObject->price;
		}

		return array(
			'price' => $price,
			'currency' => $currency,
			'subtotal' => $price * $quantity,
			'quantity' => $quantity
		);
	}
}
