<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Cart Entity.
 *
 * @since  2.0
 */
class RedshopbEntityCart extends RedshopbEntity
{
	use RedshopbEntityTraitCompany, RedshopbEntityTraitDepartment, RedshopbEntityTraitUser;

	/**
	 * Items in this cart
	 *
	 * @var  RedshopbEntitiesCollection
	 */
	protected $items;

	/**
	 * Get the items on the cart
	 *
	 * @return  RedshopbEntitiesCollection
	 */
	public function getItems()
	{
		if (null === $this->items)
		{
			$this->loadItems();
		}

		return $this->items;
	}

	/**
	 * Check if active user is the owner of this cart
	 *
	 * @return  boolean
	 */
	public function isOwner()
	{
		$item = $this->getItem();

		if (!$item)
		{
			return false;
		}

		$activeUser = RedshopbApp::getUser();

		if (!$activeUser->isLoaded())
		{
			return false;
		}

		return ($item->user_id === $activeUser->id);
	}

	/**
	 * Load cart items from DB
	 *
	 * @return  self
	 */
	protected function loadItems()
	{
		$this->items = new RedshopbEntitiesCollection;

		if (!$this->hasId())
		{
			return $this;
		}

		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select('ci.*')
			->from($db->qn('#__redshopb_cart_item', 'ci'))
			->where($db->qn('ci.cart_id') . ' = ' . (int) $this->id);

		$db->setQuery($query);

		$items = $db->loadObjectList();

		if (!$items)
		{
			return $this;
		}

		foreach ($items as $item)
		{
			$entity = RedshopbEntityCart_Item::getInstance($item->id)->bind($item);

			$this->items->add($entity);
		}

		return $this;
	}

	/**
	 * Apply Cart Items Prices
	 *
	 * @return  RedshopbEntitiesCollection
	 *
	 * @since  1.13.0
	 */
	public function applyCartItemsPrices()
	{
		if (!$this->hasId())
		{
			return $this->items;
		}

		if (null === $this->items)
		{
			$this->loadItems();
		}

		$collectionProducts = array();
		$accessoriesArray   = array();

		foreach ($this->items as $cartItem)
		{
			$collectionId  = (int) $cartItem->get('collection_id');
			$productItemId = (int) $cartItem->get('product_item_id');

			if ($cartItem->get('parent_cart_item_id'))
			{
				if ($productItemId)
				{
					$secondKey = 'accessory_product_item';
					$fourthKey = 'product_item_id';
				}
				else
				{
					$secondKey = 'accessory_product';
					$fourthKey = 'product_id';
				}

				$thirdKey = $this->items->get($cartItem->get('parent_cart_item_id'))->get('product_id');
				$fifthKey = $cartItem->get('parent_cart_item_id');
			}
			else
			{
				if ($productItemId)
				{
					$secondKey = 'product_item';
				}
				else
				{
					$secondKey = 'product';
				}
			}

			if (!array_key_exists($collectionId, $collectionProducts))
			{
				$collectionProducts[$collectionId] = array();
			}

			if (!array_key_exists($secondKey, $collectionProducts[$collectionId]))
			{
				$collectionProducts[$collectionId][$secondKey] = array();
			}

			// Found accessory
			if ($cartItem->get('parent_cart_item_id'))
			{
				if (!isset($collectionProducts[$collectionId][$secondKey][$fifthKey]))
				{
					$collectionProducts[$collectionId][$secondKey][$fifthKey] = array();
				}

				$collectionProducts[$collectionId][$secondKey][$fifthKey][] = (object) array(
					'id' => $cartItem->get($fourthKey),
					'quantity' => ($cartItem->get('quantity') > 0 ? $cartItem->get('quantity') : 1),
					'product_id' => $thirdKey
				);
			}
			else
			{
				if ($productItemId)
				{
					$collectionProducts[$collectionId][$secondKey][] = $cartItem->get('product_item_id');
				}
				else
				{
					$collectionProducts[$collectionId][$secondKey][$cartItem->get('product_id')] = $cartItem->get('quantity');
				}
			}
		}

		$userComp = RedshopbHelperUser::getUserCompany();

		if (!$userComp)
		{
			$userComp = RedshopbHelperCompany::getCustomerCompanyByCustomer($this->get('user_id'), 'employee');
		}

		$company = RedshopbHelperCompany::getCompanyByCustomer($this->get('user_id'), 'employee');

		if ($company)
		{
			$currency = RedshopbEntityCompany::getInstance($company->id)->getCustomerCurrency();
		}
		else
		{
			$currency = RedshopbApp::getConfig()->get('default_currency', 38);
		}

		/** @var RedshopbModelProduct $modelProduct */
		$modelProduct = RModelAdmin::getInstance('Product', 'RedshopbModel');

		foreach ($collectionProducts as $collectionId => &$collection)
		{
			$collections     = array();
			$currentCurrency = $currency;

			if ($collectionId)
			{
				$collections[]   = $collectionId;
				$currentCurrency = RedshopbHelperCollection::getCurrency($collectionId);

				if (array_key_exists('product_item', $collection))
				{
					$collection['product_item'] = RedshopbHelperPrices::getProductItemsPrice(
						$collection['product_item'], array(), $userComp->id, 'company', $currentCurrency, $collections
					);
				}

				if (array_key_exists('product', $collection))
				{
					$collection['product'] = RedshopbHelperPrices::getProductsPrice(
						$collection['product'], $this->get('user_id'), 'employee', $currentCurrency, $collections, '', 0, null, false, true
					);
				}
			}
			else
			{
				if (array_key_exists('product_item', $collection))
				{
					$collection['product_item'] = RedshopbHelperPrices::getProductItemsPrice(
						$collection['product_item'], array(), $userComp->id, 'company', $currentCurrency, array(0), '', $company->id
					);
				}

				if (array_key_exists('product', $collection))
				{
					$collection['product'] = RedshopbHelperPrices::getProductsPrice(
						$collection['product'], $this->get('user_id'), 'employee', $currentCurrency, array(0), '', 0, null, false, true
					);
				}
			}

			if (array_key_exists('accessory_product', $collection))
			{
				foreach ($collection['accessory_product'] as $cartId => $accessories)
				{
					$firstAccessory  = reset($accessories);
					$accessoriesData = $modelProduct->getAccessoriesIds(
						$firstAccessory->product_id,
						$accessories,
						false,
						0,
						array(),
						0,
						$this->get('user_id'),
						'employee',
						$currency
					);

					if (!empty($accessoriesData))
					{
						$accessoriesArray[$cartId] = array();

						foreach ($accessoriesData as $accessory)
						{
							$accessoriesArray[$cartId][$accessory['accessory_id']] = $accessory;
						}
					}
				}
			}
		}

		foreach ($this->items as $cartItem)
		{
			if ($cartItem->get('parent_cart_item_id'))
			{
				$cartItem->accessory = $accessoriesArray[$cartItem->get('parent_cart_item_id')][$cartItem->get('product_id')];
			}
			else
			{
				$productItemId   = (int) $cartItem->get('product_item_id');
				$cartItem->price = null;

				if ($productItemId)
				{
					if (isset($collectionProducts[(int) $cartItem->get('collection_id')]['product_item'][$cartItem->get('product_item_id')]))
					{
						$cartItem->price = $collectionProducts
						[
							(int) $cartItem->get('collection_id')]['product_item'][$cartItem->get('product_item_id')
						];
					}
				}
				else
				{
					if (isset($collectionProducts[(int) $cartItem->get('collection_id')]['product'][$cartItem->get('product_id')]))
					{
						$cartItem->price = $collectionProducts[(int) $cartItem->get('collection_id')]['product'][$cartItem->get('product_id')];
					}
				}
			}
		}

		return $this->items;
	}

	/**
	 * Remove Not Available Products
	 *
	 * @return  boolean
	 *
	 * @since 1.13.0
	 */
	public function removeNotAvailableProducts()
	{
		$this->getItems();

		if (empty($this->items))
		{
			return false;
		}

		$parents     = array();
		$cartItemIds = $this->items->ids();

		foreach ($this->items as $item)
		{
			$parentId = $item->get('parent_cart_item_id');

			if ($parentId && !in_array($parentId, $parents))
			{
				$parents[] = $parentId;
			}
		}

		$db = $this->getDbo();

		/** @var $shopModel RedshopbModelShop */
		$shopModel = RedshopbModel::getFrontInstance('Shop', array('ignore_request' => true));
		$shopModel->set('customerId', $this->get('user_id'));
		$shopModel->set('customerType', 'employee');
		$shopModel->set('customerCType', RedshopbHelperShop::getCustomerType($this->get('user_id'), 'employee'));
		$shopModel->setState('disable_user_states', true);
		$query = $shopModel->getListQueryOnly();
		$query->clear('select')
			->clear('group')
			->select('ci.id')
			->leftJoin($db->qn('#__redshopb_cart_item', 'ci') . ' ON ci.product_id = p.id')
			->where($db->qn('ci.cart_id') . ' = ' . (int) $this->id)
			->leftJoin(
				$db->qn('#__redshopb_collection_product_item_xref', 'wpix2')
				. ' ON wpix2.product_item_id = ci.product_item_id AND wpix2.collection_id = ci.collection_id'
			)
			->leftJoin(
				$db->qn('#__redshopb_collection_product_xref', 'wpx2')
				. ' ON wpx2.product_id = ci.product_id AND wpx2.collection_id = ci.collection_id'
			)
			->leftJoin($db->qn('#__redshopb_collection', 'w2') . ' ON w2.id = ci.collection_id')
			->where(
				'IF (ci.collection_id > 0 AND ci.collection_id IS NOT NULL, '
				. '(IF (ci.product_item_id > 0 AND ci.product_item_id IS NOT NULL, wpix2.state = 1, wpx2.state = 1) '
				. 'AND w2.id = ci.collection_id AND w2.state = 1), '
				. 'TRUE)'
			)
			->where(
				'IF (
					ci.product_item_id > 0 AND ci.product_item_id IS NOT NULL,
					pi.state = 1 AND pi.discontinued = 0 AND pi.id = ci.product_item_id,
					TRUE
				)'
			)
			->group('ci.id');

		$availableCartItemIds = (array) $db->setQuery($query)->loadColumn();
		$diff                 = array_diff($cartItemIds, $availableCartItemIds);

		if (empty($diff))
		{
			return false;
		}

		$deletedParents = array();
		$cartItemIds    = array();

		foreach ($this->items as $item)
		{
			$id = $item->get('id');

			if (in_array($id, $diff))
			{
				if (in_array($id, $parents))
				{
					$deletedParents[] = $item->get('id');
				}

				$cartItemIds[] = $id;
				$this->items->remove($id);
				RedshopbEntityCart_Item::clearInstance($id);
			}
		}

		if (!empty($deletedParents) && !empty($this->items))
		{
			foreach ($this->items as $item)
			{
				if (in_array($item->get('parent_cart_item_id'), $deletedParents))
				{
					$id            = $item->get('id');
					$cartItemIds[] = $id;
					$this->items->remove($id);
					RedshopbEntityCart_Item::clearInstance($id);
				}
			}
		}

		if (empty($cartItemIds))
		{
			return false;
		}

		$query = $db->getQuery(true)
			->delete($db->qn('#__redshopb_cart_item'))
			->where('id IN (' . implode(',', $cartItemIds) . ')');

		if (!$db->setQuery($query)->execute())
		{
			return false;
		}

		return true;
	}
}
