<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */
defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\Registry\Registry;
/**
 * Redshop Offer Model
 *
 * @package     Redshop.Component
 * @subpackage  Models.Order
 * @since       2.0
 *
 */
class RedshopbModelOffer extends RedshopbModelAdmin
{
	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'product';

	/**
	 * Method to validate the form data.
	 * Each field error is stored in session and can be retrieved with getFieldError().
	 * Once getFieldError() is called, the error is deleted from the session.
	 *
	 * @param   Form    $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 */
	public function validate($form, $data, $group = null)
	{
		switch ($data['customer_type'])
		{
			case 'employee':
				$form->setFieldAttribute('department_id', 'required', 'false');
				$form->setFieldAttribute('company_id', 'required', 'false');
				break;
			case 'department':
				$form->setFieldAttribute('user_id', 'required', 'false');
				$form->setFieldAttribute('company_id', 'required', 'false');
				break;
			case 'company':
				$form->setFieldAttribute('department_id', 'required', 'false');
				$form->setFieldAttribute('user_id', 'required', 'false');
				break;
			default:
				$form->setFieldAttribute('department_id', 'required', 'false');
				$form->setFieldAttribute('company_id', 'required', 'false');
				$form->setFieldAttribute('user_id', 'required', 'false');
		}

		if (!isset($data['expiration_date_switcher']) || $data['expiration_date_switcher'] == 0)
		{
			$data['expiration_date'] = '0000-00-00 00:00:00';
		}

		$form->removeField('expiration_date_switcher');
		unset($data['expiration_date_switcher']);

		return parent::validate($form, $data, $group);
	}

	/**
	 * Method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A Form object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = parent::getForm($data, $loadData);

		if ($form->getValue('customer_type', null, '') == '')
		{
			if ($form->getValue('user_id'))
			{
				$form->setValue('customer_type', null, 'employee');
			}
			elseif ($form->getValue('department_id'))
			{
				$form->setValue('customer_type', null, 'department');
			}
			elseif ($form->getValue('company_id'))
			{
				$form->setValue('customer_type', null, 'company');
			}
		}

		if ($form->getValue('expiration_date') != '0000-00-00 00:00:00' && !is_null($form->getValue('expiration_date')))
		{
			$form->setValue('expiration_date_switcher', null, '1');
		}

		return $form;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 */
	public function save($data)
	{
		if ($data['customer_type'] == "company")
		{
			$data['department_id'] = 0;
			$data['user_id']       = 0;
		}
		elseif ($data['customer_type'] == "department")
		{
			$data['company_id'] = RedshopbHelperDepartment::getCompanyId((int) $data['department_id']);
			$data['user_id']    = 0;
		}
		elseif ($data['customer_type'] == "employee")
		{
			$data['department_id'] = RedshopbHelperUser::getUserDepartmentId((int) $data['user_id'], 'redshopb');
			$data['company_id']    = RedshopbHelperUser::getUserCompanyId((int) $data['user_id'], 'redshopb');
		}

		$data['vendor_id'] = RedshopbHelperCompany::getCompanyById((int) $data['company_id'])->parent;
		$data['state']     = 1;

		if (isset($data['id']) && $data['id'])
		{
			$db    = Factory::getDbo();
			$table = $this->getTable('Offer');
			$table->load($data['id']);

			// If some main value changed, then need remove products, which not matched for main values
			if ($table->get('customer_type') != $data['customer_type']
				|| (int) $table->get('company_id') != (int) $data['company_id']
				|| (int) $table->get('department_id') != (int) $data['department_id']
				|| (int) $table->get('user_id') != (int) $data['user_id']
				|| (int) $table->get('collection_id') != (int) $data['collection_id'])
			{
				// Getting products just from parents companies tree
				$allowedCompanies = RedshopbEntityCompany::getInstance($data['vendor_id'])->getTree();
				$allowedCompanies = implode(',', $allowedCompanies);
				$whereOr          = array();

				$query     = $db->getQuery(true)
					->select('oix.id')
					->from($db->qn('#__redshopb_offer_item_xref', 'oix'))
					->leftJoin($db->qn('#__redshopb_product', 'p') . ' ON p.id = oix.product_id')
					->where('oix.offer_id = ' . (int) $data['id']);
				$whereOr[] = '(p.company_id NOT IN (' . $allowedCompanies . ') AND p.company_id IS NOT NULL)';

				if ($table->get('collection_id') != $data['collection_id'] && $data['collection_id'])
				{
					$query->leftJoin($db->qn('#__redshopb_collection_product_xref', 'cpx') . ' ON oix.product_id = cpx.product_id');
					$whereOr[] = 'cpx.collection_id != ' . (int) $data['collection_id'];
				}

				if (count($whereOr) > 0)
				{
					$query->where('(' . implode(' OR ', $whereOr) . ')');
				}

				$ids = $db->setQuery($query)
					->loadColumn();

				if ($ids)
				{
					$query->clear()
						->delete($db->qn('#__redshopb_offer_item_xref'))
						->where('id IN (' . implode(',', $ids) . ')');

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

			if (!$this->recalculateOfferItems($data['id']))
			{
				return false;
			}

			$data = array_replace($data, $this->getOfferTotal($data['id'], $data['discount_type'], $data['discount']));
		}

		$zeroUnixTime   = Factory::getDate('0000-00-00 00:00:00')
			->format('U');
		$expirationDate = Factory::getDate($data['expiration_date']);
		$expirationUnix = $expirationDate
			->format('U');
		$todayUnix      = Factory::getDate('now')
			->format('U');

		if (is_numeric($data['expiration_date']))
		{
			if ($data['expiration_date'] == 0)
			{
				$data['expiration_date'] = '0000-00-00 00:00:00';
				$expirationUnix          = $zeroUnixTime;
			}
			else
			{
				$data['expiration_date'] = $expirationDate->toSql();
			}
		}

		if ($expirationUnix != $zeroUnixTime && $expirationUnix < $todayUnix)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_OFFER_EXPIRATION_DAY_BEFORE_TODAY'), 'warning');
		}

		return parent::save($data);
	}

	/**
	 * Recalculate Offer Items
	 *
	 * @param   int  $offerId  Offer id
	 *
	 * @return  boolean
	 */
	public function recalculateOfferItems($offerId)
	{
		$db         = Factory::getDbo();
		$query      = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__redshopb_offer_item_xref'))
			->where('offer_id = ' . (int) $offerId);
		$offerItems = $db->setQuery($query)
			->loadObjectList();

		if ($offerItems)
		{
			foreach ($offerItems as $offerItem)
			{
				$prices = $this->getProductPrice($offerItem->offer_id, $offerItem->product_item_id, $offerItem->product_id, $offerItem->quantity);

				if ($prices['price'] != $offerItem->unit_price)
				{
					$query->clear()
						->update($db->qn('#__redshopb_offer_item_xref'))
						->where('id = ' . (int) $offerItem->id)
						->set(
							array(
								'unit_price = ' . $db->q($prices['price']),
								'subtotal = ' . $db->q($prices['price'] * $offerItem->quantity),
								'total = ' . $db->q(
									RedshopbHelperPrices::calculateDiscount(
										$prices['price'] * $offerItem->quantity,
										$offerItem->type_discount, $offerItem->discount
									)
								)
							)
						);

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
		}

		return true;
	}

	/**
	 * Recalculate Offer Total
	 *
	 * @param   int  $offerId  Offer id
	 *
	 * @return boolean
	 */
	public function recalculateOfferTotal($offerId)
	{
		$table = $this->getTable('Offer');

		if (!$table->load($offerId))
		{
			return false;
		}

		$row = $this->getOfferTotal($offerId, $table->get('discount_type'), $table->get('discount'));

		if (!$table->save($row))
		{
			$this->setError($table->getError());

			return false;
		}

		return true;
	}

	/**
	 * Get offer total
	 *
	 * @param   int     $offerId       Offer id
	 * @param   string  $discountType  Discount type
	 * @param   int     $discount      Discount
	 *
	 * @return  array
	 */
	public function getOfferTotal($offerId, $discountType = 'percent', $discount = 0)
	{
		$db       = Factory::getDbo();
		$query    = $db->getQuery(true)
			->select('SUM(total)')
			->from($db->qn('#__redshopb_offer_item_xref'))
			->where('offer_id = ' . (int) $offerId);
		$subTotal = (float) $db->setQuery($query, 0, 1)->loadResult();

		return array(
			'subtotal' => $subTotal,
			'total' => RedshopbHelperPrices::calculateDiscount($subTotal, $discountType, $discount)
		);
	}

	/**
	 * Calculate discount
	 *
	 * @param   float   $price         Price
	 * @param   string  $discountType  Discount type
	 * @param   int     $discount      Discount
	 *
	 * @return  integer
	 *
	 * @deprecated Use RedshopbHelperPrices::calculateDiscount instead
	 */
	public function calculateDiscount($price, $discountType = 'percent', $discount = 0)
	{
		return RedshopbHelperPrices::calculateDiscount($price, $discountType, $discount);
	}

	/**
	 * Method for remove  a product from offer list
	 *
	 * @param   int  $offerItemId  ID offer item.
	 *
	 * @return  boolean  True on success. False otherwise.
	 */
	public function removeOfferItem($offerItemId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->delete($db->qn('#__redshopb_offer_item_xref'))
			->where($db->qn('id') . ' = ' . (int) $offerItemId);

		return $db->setQuery($query)->execute();
	}

	/**
	 * Method for save offer with data from cart session
	 *
	 * @param   array  $formData  Name of offer
	 *
	 * @return  boolean            True on success. False otherwise.
	 */
	public function saveOfferFromCart($formData)
	{
		$data         = array(
			'name' => $formData['name'],
			'status' => 'requested',
			'comments' => $formData['comments'],
			'company_id' => 0
		);
		$customerType = '';
		$customerId   = 0;

		if (RedshopbHelperACL::getPermissionInto('impersonate', 'order') && array_key_exists('source', $formData) && $formData['source'])
		{
			$source = explode('_', $formData['source']);

			if (count($source) == 2)
			{
				$customerType = $source[0];
				$customerId   = $source[1];

				if ($customerType && $customerId)
				{
					$data['company_id'] = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);

					if (!$data['company_id'])
					{
						$customerType = '';
						$customerId   = 0;
					}
					else
					{
						$data['customer_type'] = $source[0];

						switch ($data['customer_type'])
						{
							case 'company':
								break;
							case 'department':
								$data['department_id'] = $source[1];
								break;
							case 'employee':
								$data['user_id'] = $source[1];
								break;
							default:
								$data['customer_type'] = '';
								$customerType          = '';
								$customerId            = 0;
								break;
						}
					}
				}
			}
		}

		if (!$customerType || !$customerId)
		{
			$app          = Factory::getApplication();
			$customerType = $app->getUserState('shop.customer_type', '');
			$customerId   = $app->getUserState('shop.customer_id', 0);

			if ($customerType && $customerId)
			{
				$data['company_id'] = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);

				if (!$data['company_id'])
				{
					$customerType = '';
					$customerId   = 0;
				}
				else
				{
					$data['customer_type'] = $customerType;

					switch ($data['customer_type'])
					{
						case 'company':
							break;
						case 'department':
							$data['department_id'] = $customerId;
							break;
						case 'employee':
							$data['user_id'] = $customerId;
							break;
						default:
							$data['customer_type'] = '';
							$customerType          = '';
							$customerId            = 0;
							break;
					}
				}
			}
		}

		if (!$customerType || !$customerId)
		{
			$this->setError(Text::_('COM_REDSHOPB_CHOOSE_CUSTOMER'));

			return false;
		}

		// Update cart item for this cart.
		$cartData = RedshopbHelperCart::getCart($customerId, $customerType)->get('items', array());

		if (empty($cartData))
		{
			$this->setError(Text::_('COM_REDSHOPB_OFFER_ADD_PRODUCTS_IN_CART_FIRST'));

			return false;
		}

		$collectionId = null;
		$firstProduct = reset($cartData);

		if (array_key_exists('collectionId', $firstProduct))
		{
			$collectionId = $firstProduct['collectionId'];
		}

		foreach ($cartData as $oneProduct)
		{
			if ($collectionId != $oneProduct['collectionId'])
			{
				$this->setError(Text::_('COM_REDSHOPB_ERROR_PUT_DIFFERENCE_COLLECTIONS_IN_ONE_OFFER'));

				return false;
			}
		}

		if (!$collectionId)
		{
			$collectionId = null;
		}

		$data['collection_id'] = $collectionId;
		$data['vendor_id']     = RedshopbEntityCompany::getInstance($data['company_id'])->getParentId();
		$offerTable            = RedshopbTable::getAutoInstance('Offer');

		if (!$offerTable->save($data))
		{
			$this->setError($offerTable->getError());

			return false;
		}

		$id = $offerTable->get('id');

		foreach ($cartData as $cartItem)
		{
			$productPrice   = $this->getProductPrice($id, $cartItem['productItem'], $cartItem['productId'], $cartItem['quantity']);
			$offerItemTable = RedshopbTable::getAutoInstance('Offer_Item_Xref');
			$offerItem      = array(
				'offer_id'   => $id,
				'product_id' => $cartItem['productId'],
				'quantity'   => $cartItem['quantity'],
				'unit_price' => $productPrice['price'],
				'subtotal'   => $productPrice['price'] * $cartItem['quantity'],
				'total'      => $productPrice['price'] * $cartItem['quantity']
			);

			RFactory::getDispatcher()->trigger('onRedshopbOfferItemStore', array(&$offerItem, $cartItem['customText']));

			if (!empty($cartItem['productItem']))
			{
				$offerItem['product_item_id'] = $cartItem['productItem'];
			}

			if (!$offerItemTable->save($offerItem))
			{
				$this->setError($offerItemTable->getError());

				return false;
			}
		}

		if (!$this->recalculateOfferTotal($id))
		{
			return false;
		}

		// Send email to vendor company on the offer requested
		RedshopbHelperEmail::sendRequestedOfferEmail($id);

		RFactory::getDispatcher()->trigger('onAECOfferRequested', array($id));

		return true;
	}

	/**
	 * Method for requesting price of a product
	 *
	 * @param   int  $offerId        Offer id
	 * @param   int  $productItemId  product item id
	 * @param   int  $productId      product id
	 * @param   int  $quantity       Quantity
	 *
	 * @return  boolean    True on success. False otherwise.
	 */
	public function getProductPrice($offerId, $productItemId, $productId, $quantity = 0)
	{
		$offerTable = $this->getTable('Offer');
		$offerTable->load($offerId);
		$currency = RedshopbHelperPrices::getCurrency(
			$offerTable->get('customer_id'), $offerTable->get('customer_type'), $offerTable->get('collection_id')
		);
		$price    = 0;

		// Format quantity as decimal number format
		$quantity = RedshopbHelperProduct::decimalFormat($quantity, $productId);

		if ($productItemId)
		{
			$priceObject = RedshopbHelperPrices::getProductItemPrice(
				$productItemId, $offerTable->get('customer_id'), $offerTable->get('customer_type'),
				$currency, array($offerTable->get('collection_id')), '', 0, $quantity, true
			);

			if ($priceObject !== false)
			{
				$price = $priceObject->price;
			}
		}
		else
		{
			$priceObject = RedshopbHelperPrices::getProductPrice(
				$productId, $offerTable->get('customer_id'), $offerTable->get('customer_type'), $currency,
				array($offerTable->get('collection_id')), '', 0, $quantity, true
			);

			if ($priceObject !== false)
			{
				$price = $priceObject->price;
			}
		}

		return array(
			'price' => $price,
			'currency' => $currency,
			'subtotal' => $price * $quantity,
			'quantity' => $quantity
		);
	}

	/**
	 * Method for requesting offer for favorite list
	 *
	 * @param   int    $favoriteId  favorite list id
	 * @param   array  $favData     Favorite Products data
	 *
	 * @return  boolean    True on success. False otherwise.
	 */
	public function requestOfferForFavList($favoriteId, $favData)
	{
		$favListTable = RedshopbTable::getAutoInstance('Myfavoritelist');

		if (!$favoriteId || !$favListTable->load($favoriteId))
		{
			$this->setError(Text::_('COM_REDSHOPB_ERROR_NOT_FOUND_FAVORITELIST'));

			return false;
		}

		$collectionId = null;

		if (array_key_exists('collection_id', $favData))
		{
			foreach ($favData['collection_id'] as $productId => $collectionInfo)
			{
				$collectionId = reset($favData['collection_id'][$productId]);

				foreach ($collectionInfo as $productItemId => $oneCollection)
				{
					if ($collectionId != $oneCollection)
					{
						$this->setError(
							Text::_('COM_REDSHOPB_ERROR_PUT_DIFFERENCE_COLLECTIONS_IN_ONE_OFFER')
						);

						return false;
					}
				}
			}

			if (!$collectionId)
			{
				$collectionId = null;
			}
		}

		$offerTable = RedshopbTable::getAutoInstance('Offer');
		$companyId  = RedshopbHelperUser::getUserCompanyId($favListTable->get('user_id'));
		$data       = array(
			'name' => $favListTable->get('name'),
			'user_id' => $favListTable->get('user_id'),
			'company_id' => $companyId,
			'department_id' => RedshopbHelperUser::getUserDepartmentId($favListTable->get('user_id')),
			'status' => 'requested',
			'collection_id' => $collectionId,
			'vendor_id' => RedshopbEntityCompany::getInstance($companyId)->getParentId()
		);

		if (!$offerTable->save($data))
		{
			$this->setError(Text::_('COM_REDSHOPB_OFFER_REQUEST_ERROR'));

			return false;
		}

		$id = $offerTable->get('id');

		foreach ($favData['product_id'] as $key => $productId)
		{
			$quantity     = $favData['quantity'][$key];
			$productPrice = $this->getProductPrice($id, 0, $productId, $quantity);

			$offerItemTable = RedshopbTable::getAutoInstance('Offer_Item_Xref');
			$offerItem      = array(
				'offer_id' => $id,
				'product_id' => $productId,
				'quantity' => $quantity,
				'unit_price' => $productPrice['price'],
				'subtotal' => $productPrice['price'] * $quantity,
				'total' => $productPrice['price'] * $quantity
			);

			if (!$offerItemTable->save($offerItem))
			{
				echo Text::_('COM_REDSHOPB_OFFER_REQUEST_ERROR');

				return false;
			}
		}

		if (!$this->recalculateOfferTotal($id))
		{
			return false;
		}

		// Send email to vendor company on the offer requested
		RedshopbHelperEmail::sendRequestedOfferEmail($id);

		RFactory::getDispatcher()->trigger('onAECOfferRequested', array($id));

		return true;
	}
}
