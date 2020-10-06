<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Cart Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelCart extends RedshopbModelAdmin
{
	/**
	 * Method for save cart with data from session
	 *
	 * @param   string      $cartName      Name of save cart
	 * @param   integer     $orderId       Id of order. If this available get items from this order. If not, get from cart session
	 * @param   integer     $cartId        Cart ID
	 * @param   integer     $customerId    Customer ID
	 * @param   string      $customerType  Customer Type
	 *
	 * @return  boolean            True on success. False otherwise.
	 */
	public function saveCart($cartName, $orderId = 0, $cartId = 0, $customerId = 0, $customerType = '')
	{
		$user = RedshopbHelperUser::getUser(Factory::getUser()->id, 'joomla');

		if ((empty($cartName) && !$cartId) || !$user)
		{
			return false;
		}

		$table = $this->getTable();
		$row   = array(
			'user_id' => $user->id,
			'company_id' => $user->company,
			'department_id' => $user->department,
			'name' => $cartName
		);

		if ($cartId)
		{
			if (!$table->load($cartId))
			{
				return false;
			}
		}
		else
		{
			$table->reset();
		}

		$db = $this->getDbo();

		if (!$table->save($row))
		{
			return false;
		}

		if (RedshopbApp::getConfig()->get('save_to_cart_by', 'overwrite') === 'overwrite')
		{
			// If the cart is exist, then need delete all related items before apply new
			if ($cartId)
			{
				$query = $db->getQuery(true)
					->delete($db->qn('#__redshopb_cart_item'))
					->where('cart_id = ' . (int) $cartId);

				try
				{
					$db->setQuery($query)->execute();
				}
				catch (Exception $e)
				{
					$this->setError($e->getMessage());

					return false;
				}
			}
		}

		// If not from an order. Get items from cart session.
		$cartId   = $table->get('id');
		$cartData = $this->getCartData($orderId, $customerId, $customerType);

		if (empty($cartData))
		{
			return true;
		}

		// Process save cart for items get from cart session.
		foreach ($cartData as $cartItem)
		{
			$cartItemTable = RTable::getAdminInstance('Cart_Item', array(), 'com_redshopb');

			$cartItemTable->cart_id             = $cartId;
			$cartItemTable->product_id          = $cartItem['productId'];
			$cartItemTable->product_item_id     = $cartItem['productItem'];
			$cartItemTable->parent_cart_item_id = null;
			$cartItemTable->collection_id       = $cartItem['collectionId'];
			$cartItemTable->quantity            = $cartItem['quantity'];

			if (!$cartItemTable->store())
			{
				return false;
			}

			if (empty($cartItem['accessories']))
			{
				continue;
			}

			$parentCartItemId = $cartItemTable->get('id');

			foreach ($cartItem['accessories'] as $accessory)
			{
				$cartItemTable = RTable::getAdminInstance('Cart_Item', array(), 'com_redshopb');

						$cartItemTable->set('cart_id', $table->get('id'));
						$cartItemTable->set('product_id', $accessory['accessory_id']);
						$cartItemTable->set('product_item_id', 0);
						$cartItemTable->set('parent_cart_item_id', $parentCartItemId);
						$cartItemTable->set('collection_id', 0);
						$cartItemTable->set('quantity', $accessory['quantity']);

				if (!$cartItemTable->store())
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method to get the cartData from either the session or by orderId
	 *
	 * @param   integer  $orderId       order id if we're processing by orderId
	 * @param   integer  $customerId    Customer ID
	 * @param   string   $customerType  Custoemr Type
	 *
	 * @return  array
	 */
	protected function getCartData($orderId, $customerId = 0, $customerType ='')
	{
		if (!$orderId)
		{
			return RedshopbHelperCart::getCart($customerId, $customerType)->get('items', array());
		}

		// If from an existing order. Get items from order data.
		$ordersModel = RedshopbModel::getInstance('Orders', 'RedshopbModel');
		$orderData   = $ordersModel->getCustomerOrder($orderId);

		$cartData = $orderData->regular->items;

		foreach ($cartData AS &$item)
		{
			$item = $this->prepareCartItem($item);
		}

		return $cartData;
	}

	/**
	 * Method to prepare cart items for saving
	 * This method helps normalize the data structure between session and order records
	 *
	 * @param   object  $item  item to prepare
	 *
	 * @return  mixed
	 */
	protected function prepareCartItem($item)
	{
		if (empty($item->accessories))
		{
			$item->accessories = array();

			return;
		}

		$db = $this->getDbo();

		foreach ($item->accessories as &$accessory)
		{
			$accessory = (array) $accessory;

			$query = $db->getQuery(true)
				->select('id')
				->from($db->qn('#__redshopb_product_accessory'))
				->where('product_id = ' . (int) $item->product_id)
				->where('accessory_product_id = ' . (int) $accessory['product_id']);

			$accessory['accessory_id'] = $db->setQuery($query, 0, 1)->loadResult();
		}

		return (array) $item;
	}

	/**
	 * Method for remove an saved cart.
	 *
	 * @param   int  $cartId  ID of saved cart
	 *
	 * @return  boolean       True on success. False otherwise.
	 */
	public function removeCart($cartId)
	{
		$cartId = (int) $cartId;

		if (!$cartId)
		{
			return false;
		}

		$table = $this->getTable();

		if (!$table->load($cartId))
		{
			return false;
		}

		// Check permission of owner
		$user = RedshopbApp::getUser();

		if (RedshopbHelperACL::isSuperAdmin() || ($user->isLoaded() && $user->id == $table->user_id))
		{
			return $table->delete();
		}

		return false;
	}

	/**
	 * Method for load cart data from saved cart to cart session
	 *
	 * @param   int  $cartId  ID of cart
	 *
	 * @return  boolean       True on success. False otherwise.
	 */
	public function loadCart($cartId)
	{
		$cartId = (int) $cartId;

		if (!$cartId)
		{
			return false;
		}

		$table = $this->getTable();

		if (!$table->load($cartId))
		{
			return false;
		}

		// Check permission of owner
		$user = RedshopbHelperUser::getUser(Factory::getUser()->id, 'joomla');

		if (!$user || ($user->id != $table->get('user_id')))
		{
			return false;
		}

		$cartItems = $this->loadCartItem($cartId);

		if (empty($cartItems))
		{
			return false;
		}

		// Clear cart sessions
		RedshopbHelperCart::clearCartFromSession();
		$app          = Factory::getApplication();
		$customerId   = $app->getUserState('shop.customer_id', 0);
		$customerType = $app->getUserState('shop.customer_type', '');
		$session      = Factory::getSession();
		$session->set('saved_cart.' . $customerType . '.' . $customerId, $cartId, 'redshopb');
		$foundAccessories = array();

		foreach ($cartItems as $key => $cartItem)
		{
			if ($cartItem->parent_cart_item_id)
			{
				if (!isset($foundAccessories[$cartItem->parent_cart_item_id]))
				{
					$foundAccessories[$cartItem->parent_cart_item_id] = array();
				}

				$foundAccessories[$cartItem->parent_cart_item_id][] = (object) array(
					'id' => $cartItem->product_id,
					'quantity' => ($cartItem->quantity > 1 ? $cartItem->quantity : 1)
				);
				unset($cartItems[$key]);
			}
		}

		foreach ($cartItems as $cartItem)
		{
			if (!$cartItem->product_item_id)
			{
				$productPrice = RedshopbHelperPrices::getProductPrice($cartItem->product_id, $customerId, $customerType);
			}
			else
			{
				$productPrice = RedshopbHelperPrices::getProductItemPrice($cartItem->product_item_id, $customerId, $customerType);
			}

			RedshopbHelperCart::addToCartById(
				$cartItem->product_id,
				$cartItem->product_item_id,
				(array_key_exists($cartItem->id, $foundAccessories) ? $foundAccessories[$cartItem->id] : null),
				$cartItem->quantity,
				$productPrice ? $productPrice->price : 0.0,
				$productPrice ? $productPrice->currency : '',
				$customerId,
				$customerType
			);
		}

		return true;
	}

	/**
	 * Method for load product items in cart
	 *
	 * @param   int  $cartId  ID of cart
	 *
	 * @return  array         List of product in saved cart.
	 */
	public function loadCartItem($cartId)
	{
		$cartId = (int) $cartId;

		if (!$cartId)
		{
			return false;
		}

		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select('sci.*')
			->from($db->qn('#__redshopb_cart_item', 'sci'))
			->where($db->qn('sci.cart_id') . ' = ' . (int) $cartId);
		$db->setQuery($query);

		return $db->loadObjectList();
	}
}
