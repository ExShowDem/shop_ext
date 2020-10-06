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
/**
 * Redshop Order Model
 *
 * @package     Redshop.Component
 * @subpackage  Models.Order
 * @since       2.0
 */
class RedshopbModelOrder extends RedshopbModelAdmin
{
	/**
	 * Context for session
	 *
	 * @var  string
	 */
	protected $context = 'com_redshopb';

	/**
	 * Currency symbol
	 *
	 * @var  string
	 */
	public $currencySymbol = 'DKK';

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);
		RedshopbHelperOrder::loadShippingDetails($item);
		$currency = RedshopbHelperProduct::getCurrency((int) $item->currency_id);

		if (isset($item->currency))
		{
			$this->currencySymbol = $item->currency;
		}
		elseif ($currency)
		{
			$this->currencySymbol = $currency->alpha3;
		}

		$this->getShippingRateName($item);

		$app = Factory::getApplication();
		$app->setUserState('order.customer_id', $item->customer_id);
		$app->setUserState('order.customer_type', $item->customer_type);
		$app->setUserState('order.address_id', $item->delivery_address_id);

		return $item;
	}

	/**
	 * Method to get a single record using possible related data from the web service and optionally adding related data to it
	 *
	 * @param   string  $pk              The pk to be retrieved
	 * @param   bool    $addRelatedData  Add the other related data fields from web service sync
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function getItemWS($pk, $addRelatedData = true)
	{
		$item = parent::getItemWS($pk, $addRelatedData);

		if (!$item)
		{
			return $item;
		}

		RedshopbHelperOrder::loadShippingDetailsStockroom($item);
		RedshopbHelperOrder::loadAuthorEmployeeId($item);

		return $item;
	}

	/**
	 * Method to add the shipping rate name to an order item
	 *
	 * @param   object  $item  order item
	 *
	 * @return null
	 *
	 * @throws Exception
	 */
	private function getShippingRateName($item)
	{
		$item->shipping_code = RedshopbShippingHelper::getShippingRateName($item->shipping_rate_id, false);

		if (empty($item->shipping_code))
		{
			$item->shipping_code = null;
		}

		return null;
	}

	/**
	 * Method to return the name of a shipping rate based on rate id
	 *
	 * @param   array  $shippingRates  list of available rates
	 * @param   int    $rateId         shipping rate record id
	 *
	 * @return string|null
	 */
	private function getRateById($shippingRates, $rateId)
	{
		foreach ($shippingRates as $rate)
		{
			if ($rate->id == $rateId)
			{
				return $rate->name;
			}
		}

		return null;
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
		if (!$loadData && array_key_exists('id', $data))
		{
			$this->setState($this->getName() . '.id', $data['id']);
		}

		return parent::getForm($data, true);
	}

	/**
	 * Here we can change form or data
	 *
	 * @param   Form    $form      A Form object.
	 * @param   mixed   $data      The data expected for the form.
	 * @param   array   $options   Optional array of options for the form creation.
	 * @param   string  $group     The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 */
	protected function changeForm(Form &$form, &$data, &$options = array(), $group = 'content')
	{
		$objectData   = (object) $data;
		$formFieldset = $this->getState('formFieldset', 'order_general');
		$xml          = $form->getXml();

		foreach ($xml->fieldset as $fieldset)
		{
			if ($fieldset['name'] == $formFieldset)
			{
				$xml  = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><form>' . $fieldset->asXML() . '</form>');
				$form = new RedshopbForm('Order', $options);
				$form->load($xml->asXML());
			}
		}

		if (isset($objectData->id)
			&& isset($objectData->shipping_rate_id))
		{
			$customer        = RedshopbEntityCustomer::getInstance($objectData->customer_id, $objectData->customer_type);
			$deliveryAddress = $customer->getDeliveryAddress()->getExtendedData();
			$shippingMethods = RedshopbHelperOrder::getShippingMethods($objectData->company_id, $deliveryAddress, 0, $objectData->currency);
			$currentPlugin   = false;

			foreach ($shippingMethods as $method)
			{
				$rateName = $this->getRateById($method->shippingRates, $objectData->shipping_rate_id);

				if (!is_null($rateName))
				{
					$currentPlugin = explode('!', $method->value);
					$currentPlugin = $currentPlugin[0];

					break;
				}
			}

			if ($currentPlugin)
			{
				RForm::addFormPath(JPATH_SITE . '/plugins/redshipping/' . $currentPlugin);
				RForm::addFormPath(JPATH_SITE . '/plugins/system/' . $currentPlugin);
				$lang      = Factory::getLanguage();
				$extension = 'plg_redshipping_' . $currentPlugin;
				$lang->load(strtolower($extension), JPATH_ADMINISTRATOR, null, false, true)
				|| $lang->load(strtolower($extension), JPATH_PLUGINS . '/redshipping/' . $currentPlugin, null, false, true)
				|| $lang->load(strtolower($extension), JPATH_PLUGINS . '/system/' . $currentPlugin, null, false, true);
				$pluginForm = RForm::getInstance($this->context . '.' . $currentPlugin, $currentPlugin, array(), false, $formFieldset);

				foreach ($form->getXml() as $formFieldSet)
				{
					foreach ($pluginForm->getXml() as $fields)
					{
						$node     = dom_import_simplexml($formFieldSet->field);
						$fragment = $node->ownerDocument->createDocumentFragment();
						$fragment->appendXML($fields->fields->asXML());
						$node->parentNode->insertBefore($fragment);
					}
				}
			}
		}

		parent::changeForm($form, $data, $group);
	}

	/**
	 * Validate incoming data from the web service for creation
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  array
	 */
	public function validateCreateWS($data)
	{
		$data = parent::validateCreateWS($data);

		if (!$data)
		{
			return false;
		}

		$company    = RedshopbEntityCompany::load($data['company_id']);
		$department = ($data['department_id'] != '' ? RedshopbEntityDepartment::load($data['department_id']) : null);
		$user       = ($data['user_id'] != '' ? RedshopbEntityUser::load($data['user_id']) : null);

		// Validating that the user belongs to the department and company
		if ($user)
		{
			if ($user->getItem()->department_id != $data['department_id'])
			{
				Factory::getApplication()->enqueueMessage(
					Text::sprintf('COM_REDSHOPB_USER_NOT_BELONG_DEPARTMENT', $data['user_id'], $data['department_id']), 'error'
				);

				return false;
			}

			// Get all user companies
			$userCompanies = $user->getUserMultiCompanies();
			$companyFound  = false;

			foreach ($userCompanies as $userCompany)
			{
				if ($userCompany->company_id == $data['company_id'])
				{
					$companyFound = true;
					break;
				}
			}

			if (!$companyFound)
			{
				Factory::getApplication()->enqueueMessage(
					Text::sprintf('COM_REDSHOPB_USER_NOT_BELONG_COMPANY', $data['user_id'], $data['company_id']), 'error'
				);

				return false;
			}
		}

		// Validating that the department belongs to the company
		if ($department)
		{
			if ($department->getItem()->company_id != $data['company_id'])
			{
				Factory::getApplication()->enqueueMessage(
					Text::sprintf('COM_REDSHOPB_DEPARTMENT_NOT_BELONG_COMPANY', $data['department_id'], $data['company_id']), 'error'
				);

				return false;
			}
		}

		// Disallowing main company from purchasing
		if ($company->getItem()->type == 'main')
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_COMPANY_MAIN_CANNOT_PURCHASE'), 'error');

			return false;
		}

		// Validates that the given address actually belongs to either the company, department or user placing this order
		$address         = RedshopbEntityAddress::load($data['delivery_address_id']);
		$addressCustomer = $address->getCustomer();
		$addressKey      = $addressCustomer->getType() . '_id';

		if ($addressCustomer->getId() != $data[$addressKey])
		{
			$address = null;
		}

		if (is_null($address))
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_ORDER_WS_INVALID_ADDRESS'), 'error');

			return false;
		}

		$data['shipping_rate_id'] = $this->getShippingRateId($data, $address);

		if (!is_array($data['items']) || !count($data['items']))
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_ORDER_WS_INVALID_ITEMS'), 'error');

			return false;
		}

		foreach ($data['items'] as $i => $item)
		{
			$data['items'][$i] = $this->validateOrderItemWS($item);

			if (!$data['items'][$i])
			{
				return false;
			}
		}

		return $data;
	}

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
		$data = parent::validate($form, $data, $group);

		if (!$data)
		{
			return false;
		}

		$arrayData = (array) $data;

		if (array_key_exists('shipping_details', $arrayData))
		{
			if (array_key_exists('pickup_stockroom_id', $arrayData['shipping_details']))
			{
				if (!RedshopbHelperStockroom::pickUpStockroomAllowed($arrayData['shipping_details']['pickup_stockroom_id'], $data['company_id']))
				{
					Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_STOCKROOM_PICK_UP_NOT_ALLOWED'), 'error');

					return false;
				}
			}
		}

		return $data;
	}

	/**
	 * Method to get a rate id for a item with only a shipping name
	 *
	 * @param   array                  $item             item data
	 * @param   RedshopbEntityAddress  $deliveryAddress  delievery address
	 *
	 * @return integer rate id or 0 if the shipping name is not set
	 */
	private function getShippingRateId($item, $deliveryAddress)
	{
		if (empty($item['shipping_code']))
		{
			return 0;
		}

		$company  = RedshopbEntityCompany::getInstance($item['company_id']);
		$currency = RedshopbHelperProduct::getCurrency((int) $company->getCurrency());

		if ($currency)
		{
			$this->currencySymbol = $currency->alpha3;
		}

		$shippingMethods = RedshopbHelperOrder::getShippingMethods($item['company_id'], $deliveryAddress, 0, $currency->alpha3);

		foreach ($shippingMethods as $method)
		{
			$rateId = $this->getRateIdByName($method->shippingRates, $item['shipping_code']);

			if (!is_null($rateId))
			{
				return $rateId;
			}
		}
	}

	/**
	 * Method to return the name of a shipping rate based on rate id
	 *
	 * @param   array   $shippingRates  list of available rates
	 * @param   string  $name           shipping rate name
	 *
	 * @return string|null
	 */
	private function getRateIdByName($shippingRates, $name)
	{
		foreach ($shippingRates as $rate)
		{
			if ($rate->name == $name)
			{
				return $rate->id;
			}
		}

		return null;
	}

	/**
	 * Method to validate order items for web service
	 *
	 * @param   array  $item  The given order item
	 *
	 * @return false|array Item data on success (with local ids), false on error
	 */
	public function validateOrderItemWS($item)
	{
		if (!isset($item['product_id']) || !isset($item['quantity']))
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_ORDER_WS_PRODUCT_QUANTITY_NOT_INCLUDED'), 'error');

			return false;
		}

		/** @var RedshopbModelProduct $productModel */
		$productModel = RedshopbModel::getFrontInstance('Product');
		$product      = $productModel->getItemFromWSData($item['product_id']);

		if (!$product)
		{
			Factory::getApplication()->enqueueMessage(
				Text::sprintf('COM_REDSHOPB_WEBSERVICE_ID_NOT_FOUND', strtolower(Text::_('COM_REDSHOPB_PRODUCT')), $item['product_id']), 'error'
			);

			return false;
		}

		$item['product_id'] = $product->id;
		$this->getIdByItemValue($item, 'product_item_id', 'Product_Item', 'COM_REDSHOPB_PRICE_PRODUCT_ITEM');
		$this->getIdByItemValue($item, 'collection_id', 'Collection', 'COM_REDSHOPB_COLLECTION');
		$this->getIdByItemValue($item, 'stockroom_id', 'Stockroom', 'COM_REDSHOPB_STOCKROOM_PRODUCT');

		return $item;
	}

	/**
	 * Validate incoming data from the update web service - maps non-incoming data to avoid problems with actual validation
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  array
	 */
	public function validateUpdateWS($data)
	{
		$data = parent::validateUpdateWS($data);

		if (!$data)
		{
			return false;
		}

		// Restricted statuses - they must be executed in UI because they execute special actions
		if ($data['status_code'] == 'refunded'
			|| $data['status_code'] == 'expedited'
			|| $data['status_code'] == 'collected')
		{
			$data['status_code'] = '';
		}

		$statusCodes    = RedshopbEntityOrder::getAllowedStatusCodes();
		$data['status'] = array_search($data['status_code'], $statusCodes);

		if ($data['status'] === false)
		{
			Factory::getApplication()->enqueueMessage(
				Text::_(
					'COM_REDSHOPB_ORDER_WS_INVALID_STATUS_CODE'
				), 'error'
			);

			return false;
		}

		unset($data['status_code']);

		return $data;
	}

	/**
	 * Create a new item from the web service - storing the related sync id
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  false|integer record id (int)
	 */
	public function createWS($data)
	{
		// Global order parameters
		$customerId   = ($data['user_id'] != '' ? $data['user_id'] : ($data['department_id'] != '' ? $data['department_id'] : $data['company_id']));
		$customerType = ($data['user_id'] != '' ? 'employee' : ($data['department_id'] != '' ? 'department' : 'company'));
		$currencyId   = RedshopbEntityCompany::load($data['company_id'])->getCustomerCurrency();
		$app          = Factory::getApplication();
		$app->setUserState('shop.customer_id',  $customerId);
		$app->setUserState('shop.customer_type', $customerType);

		// Make sure cart session don`t have extra items
		RedshopbHelperCart::clearCartFromSession(true);

		// Individual items are added to cart for calculation
		foreach ($data['items'] as $item)
		{
			$productId     = $item['product_id'];
			$productItemId = (isset($item['product_item_id']) ? $item['product_item_id'] : null);
			$quantity      = $item['quantity'];
			$collectionId  = (isset($item['collection_id']) ? $item['collection_id'] : null);
			$stockroomId   = (isset($item['stockroom_id']) ? $item['stockroom_id'] : 0);

			RedshopbHelperCart::addToCartById(
				$productId, $productItemId, null, $quantity, 0, $currencyId, $customerId, $customerType, $collectionId, 0, $stockroomId
			);
		}

		// Model is fetched from cart and stored
		$modelShop       = RedshopbModel::getFrontInstance('Shop');
		$customerOrders  = $modelShop->getCustomerOrders();
		$allowExpedite   = (count($customerOrders) == 1);
		$shippingDate    = null;
		$shippingDetails = null;
		$config          = RedshopbApp::getConfig();

		if ($config->getInt('use_shipping_date', 0)
			&& array_key_exists('shipping_date', $data))
		{
			$shippingDate = $data['shipping_date'];
		}

		if (array_key_exists('shipping_details', $data))
		{
			$shippingDetails = $data['shipping_details'];
		}

		$orderId = (int) RedshopbHelperOrder::storeOrder(
			$customerOrders[0]->regular, $allowExpedite, $data['delivery_address_id'], $data['requisition'],
			$data['comment'], $shippingDate, $shippingDetails, false
		);

		if (!$orderId)
		{
			return false;
		}

		if (!empty($data['id']))
		{
			// If there is sync associated data set for the table, it stores the association in the sync table
			$syncHelper = new RedshopbHelperSync;
			$table      = $this->getTable();
			$wsMap      = $table->get('wsSyncMapPK');
			$syncRef    = $wsMap['erp'][0];
			$syncHelper->recordSyncedId($syncRef, $data['id'], $orderId, '', true, 0, '', false, '', $table, 1);
		}

		return $orderId;
	}

	/**
	 * Validate incoming data from the web service
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  false|array
	 */
	public function validateWS($data)
	{
		$data = parent::validateWS($data);

		if ($data && array_key_exists('shipping_details', $data))
		{
			if (!$this->findRealItemId($data['shipping_details'], 'pickup_stockroom_id', 'Stockroom'))
			{
				return false;
			}
		}

		return $data;
	}

	/**
	 * Update an item from the web service - storing the related sync id
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  mixed record id (int) | false
	 */
	public function updateWS($data)
	{
		$table = $this->getTable();
		$table->load($data['id']);

		$finalData = array(
			'requisition' => $data['requisition'],
			'comment' => $data['comment'],
			'status' => $data['status']
		);

		try
		{
			if (!$table->save($finalData))
			{
				return false;
			}

			$this->updateERPId($data['erp_id'], $data['id']);
		}
		catch (Exception $e)
		{
			return false;
		}

		return $data['id'];
	}

	/**
	 * Method to save the data from WS.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return boolean True on success, false on error.
	 *
	 * @throws ErrorException
	 */
	public function placeOrder($data = array())
	{
		/** @var RedshopbTableOrder $orderTable */
		$orderTable = RedshopbTable::getAdminInstance('Order');

		$row                  = array();
		$row['customer_type'] = 'company';
		$row['customer_id']   = $data['company_id'];

		if (!empty($data['user_id']) && !empty($data['department_id']) &&  !empty($data['company_id']))
		{
			$row['customer_type']       = 'employee';
			$row['customer_id']         = $data['user_id'];
			$row['customer_department'] = $data['department_id'];
			$row['customer_company']    = $data['company_id'];
		}
		elseif (!empty($data['department_id']) &&  !empty($data['company_id']))
		{
			$row['customer_type']       = 'department';
			$row['customer_id']         = $data['department_id'];
			$row['customer_department'] = $data['company_id'];
		}

		if (empty($data['delivery_address_id']))
		{
			throw new ErrorException(Text::_('COM_REDSHOPB_ORDER_DELIVERY_ADDRESS_MISSING'), 400);
		}

		/** @var RedshopbTableAddress $addressTable */
		$addressTable = RedshopbTable::getAdminInstance('Address');

		if (!$addressTable->load($data['delivery_address_id']))
		{
			throw new ErrorException(Text::_('COM_REDSHOPB_ADDRESS_ID_ERROR'), 400);
		}

		if ($row['customer_type'] != $addressTable->customer_type)
		{
			throw new ErrorException(Text::_('COM_REDSHOPB_ORDER_ADDRESS_MATCH_ERROR'), 400);
		}

		$row['delivery_address_id'] = $data['delivery_address_id'];

		if (!$orderTable->save($row))
		{
			return false;
		}

		$orderId = $orderTable->id;
		$items   = $data['items'];

		return (bool) !$this->saveOrderItems($orderId, $items);
	}

	/**
	 * Save order items for given order.
	 *
	 * @param   int    $orderId  Order id.
	 * @param   array  $items    Order items.
	 *
	 * @return array
	 */
	public function saveOrderItems($orderId, $items)
	{
		$db             = $this->getDbo();
		$app    		= Factory::getApplication();
		$orderItems     = $db->setQuery(RedshopbHelperOrder::getOrderItemsQuery($orderId))->loadObjectList();
		$result         = array('msg' => '', 'success' => 1, 'total' => 0.0);
		$customer       = RedshopbHelperOrder::getOrderCustomer($orderId);
		$customer->type = RedshopbHelperShop::getCustomerType($customer->cid, $customer->ctype);

		$keys             = array_keys($items);
		$deleteList       = array();
		$updateList       = array();
		$foundInsertDItem = false;

		foreach ($orderItems as $index => $orderItem)
		{
			if (!in_array($orderItem->id, $keys) && !$orderItem->parent_id)
			{
				$deleteList[] = $orderItem->id;

				// Make sure not insert this order item again.
				unset($orderItems[$index]);
				continue;
			}

			if (isset($items[$orderItem->id]))
			{
				$updateList[] = array('id' => $orderItem->id, 'quantity' => $items[$orderItem->id]);

				// Make sure not insert this order item again.
				unset($orderItems[$index]);
			}
		}

		$db->transactionStart();

		$query = $db->getQuery(true);

		if (!empty($deleteList))
		{
			$query = $db->getQuery(true)
				->select('ot.id')
				->from($db->qn('#__redshopb_order_tax', 'ot'))
				->where($db->qn('ot.product_id') . ' IN (' . implode(',', $deleteList) . ')');

			if ($db->setQuery($query, 0, 1)->loadResult())
			{
				$foundInsertDItem = true;
			}

			$query = $db->getQuery(true)
				->delete($db->qn('#__redshopb_order_item'))
				->where($db->qn('id') . ' IN (' . implode(',', $deleteList) . ')');

			if (!RedshopbHelperOrder::executeDbQueryError($db, $query, $result))
			{
				return $result;
			}
		}

		foreach ($updateList as $item)
		{
			$query->clear()
				->update($db->qn('#__redshopb_order_item'))
				->set($db->qn('quantity') . ' = ' . (int) $item['quantity'])
				->where($db->qn('id') . ' = ' . (int) $item['id']);

			if (!RedshopbHelperOrder::executeDbQueryError($db, $query, $result))
			{
				return $result;
			}
		}

		if ($customer->type == 'customer')
		{
			// Insert new order items if needed
			if (!empty($orderItems))
			{
				$columns = array(
					$db->qn('order_id'),
					$db->qn('product_item_id'),
					$db->qn('product_id'),
					$db->qn('price'),
					$db->qn('quantity'),
					$db->qn('collection_id')
				);

				$query->clear()
					->insert($db->qn('#__redshopb_order_item'))
					->columns($columns);

				foreach ($orderItems as $orderItem)
				{
					$currency   = $this->getOrderItemCurrency($orderItem, $customer);
					$price      = $this->getOrderItemPrice($orderItem, $customer, $currency);
					$totalPrice = $price->price * $orderItem->quantity;
					$values     = array(
						(int) $orderId,
						(int) $orderItem->product_item_id,
						(int) $orderItem->product_id,
						(float) $totalPrice,
						(int) $orderItem->quantity,
						(int) $orderItem->collection_id
					);

					$query->values(implode(',', $values));

					if (!$foundInsertDItem)
					{
						$taxes = RedshopbHelperTax::getTaxRates($customer->cid, $customer->ctype, $orderItem->product_id, true);

						if (!empty($taxes))
						{
							$foundInsertDItem = true;
						}
					}
				}

				if (!RedshopbHelperOrder::executeDbQueryError($db, $query, $result))
				{
					return $result;
				}
			}

			$newTotal = $this->recalculateOrderTotal($orderId, $foundInsertDItem);

			$orderCurrency = RedshopbEntityOrder::getInstance($orderId)->get('currency_id');

			$company = RedshopbHelperCompany::getCompanyByCustomer($customer->cid, $customer->ctype, false);

			// Check employee amount with new total of order
			if (!RedshopbHelperUser::employeePurchase($customer->cid, $newTotal, $orderCurrency, $company, true) && $company->useWallet)
			{
				$db->transactionRollback();
				$result['success'] = 0;
				$result['msg']     = Text::_('COM_REDSHOPB_ORDER_ORDERITEMS_SAVE_ERROR_NOT_ENOUGH_FUNDS');

				return $result;
			}

			if ((int) $company->calculate_fee && (int) $company->currency_id)
			{
				$fee = RedshopbHelperShop::getAdditionalCharges($company->currency_id, 'fee');

				// Check if fee isn't stored yet
				$query->clear()
					->select($db->qn('id'))
					->from($db->qn('#__redshopb_order_item'))
					->where($db->qn('order_id') . ' = ' . $orderId)
					->where($db->qn('product_item_id') . ' = ' . (int) $fee->itemId);
				$feeId     = (int) $db->setQuery($query)->loadResult();
				$feeExists = $feeId > 0;

				// Check if fee exists for given currency
				if (!is_null($fee) && $fee->limit > $newTotal && !$feeExists)
				{
					// Fee doesn't exists, add it for given order
					$columns = array(
						$db->qn('order_id'),
						$db->qn('product_item_id'),
						$db->qn('currency_id'),
						$db->qn('price'),
						$db->qn('quantity')
					);
					$values  = ((int) $orderId) . ',' . ((int) $fee->itemId) . ',' .
						((int) $fee->currency) . ',' . ((float) $fee->price) . ',1';

					$query->clear()
						->insert($db->qn('#__redshopb_order_item'))
						->columns($columns)
						->values($values);

					if (!RedshopbHelperOrder::executeDbQueryError($db, $query, $result))
					{
						return $result;
					}

					$newTotal += (float) $fee->price;
				}
				// Check if total is higher then fee limit and fee is in order
				elseif (!is_null($fee) && $fee->limit < ($newTotal - $fee->price) && $feeExists)
				{
					$query->clear()
						->delete($db->qn('#__redshopb_order_item'))
						->where($db->qn('id') . ' = ' . $feeId);

					if (!RedshopbHelperOrder::executeDbQueryError($db, $query, $result))
					{
						return $result;
					}

					$newTotal -= (float) $fee->price;
				}
			}
		}
		else
		{
			$newTotal = $this->recalculateOrderTotal($orderId, $foundInsertDItem);
		}

		$result['total'] = (float) $newTotal;

		$db->transactionCommit();
		$app->triggerEvent('onRedshopbAfterOrderStore', array($orderId, __METHOD__));

		return $result;
	}

	/**
	 * Recalculate order total price
	 *
	 * @param   int   $orderId             Order id
	 * @param   bool  $foundInsertOrDItem  Found insert or delete order item with tax
	 *
	 * @return  float
	 */
	protected function recalculateOrderTotal($orderId, $foundInsertOrDItem = false)
	{
		$taxAmount = 0;
		$db        = Factory::getDbo();
		$query     = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__redshopb_order'))
			->where('id = ' . (int) $orderId);

		$order = $db->setQuery($query)->loadObject();

		if ($foundInsertOrDItem)
		{
			$query = $db->getQuery(true)
				->delete($db->qn('#__redshopb_order_tax'))
				->where('t.order_id = ' . (int) $orderId);

			$db->setQuery($query)->execute();

			$query = $db->getQuery(true)
				->select('SUM(oi.price * oi.quantity) AS price, product_id, product_name')
				->from($db->qn('#__redshopb_order_item'))
				->where('order_id = ' . (int) $orderId)
				->group('product_id');

			$productList = $db->setQuery($query)->loadObjectList('product_id');
			$itemsTotal  = 0;

			if (!empty($productList))
			{
				// Include product taxes
				foreach ($productList as $productId => $item)
				{
					$taxes       = RedshopbHelperTax::getTaxRates($order->customer_id, $order->customer_type, $productId, true);
					$itemsTotal += $item->final_price;

					if (!empty($taxes))
					{
						foreach ($taxes as $tax)
						{
							$singleTax             = new stdClass;
							$singleTax->name       = Text::sprintf('COM_REDSHOPB_TAX_FROM_PRODUCT', $tax->name, $item->product_name);
							$singleTax->tax_rate   = $tax->tax_rate;
							$singleTax->tax        = ($item->final_price + $order->shipping_price) * $tax->tax_rate;
							$singleTax->product_id = $item->product_id;
							$taxList[]             = $singleTax;
							$taxAmount            += $singleTax->tax;
						}
					}
				}
			}

			// Get global taxes
			$taxes = RedshopbHelperTax::getTaxRates($order->customer_id, $order->customer_type);

			if (!empty($taxes))
			{
				foreach ($taxes as $tax)
				{
					$singleTax           = new stdClass;
					$singleTax->name     = $tax->name;
					$singleTax->tax_rate = $tax->tax_rate;
					$singleTax->tax      = ($itemsTotal + $order->shipping_price) * $tax->tax_rate;
					$taxList[]           = $singleTax;
					$taxAmount          += $singleTax->tax;
				}
			}

			$newTotal   = $taxAmount + $itemsTotal + $order->shipping_price;
			$paymentFee = RedshopbHelperOrder::getPaymentMethodFee($order->customer_company, $newTotal, $order->currency, $order->payment_name);
			$newTotal  += $paymentFee;

			$query = $db->getQuery(true)
				->update($db->qn('#__redshopb_order'))
				->where('id = ' . (int) $orderId)
				->set('total_price = ' . (float) $newTotal);

			$db->setQuery($query)->execute();

			if (!empty($taxList))
			{
				$query = $db->getQuery(true)
					->insert($db->qn('#__redshopb_order_tax'))
					->columns('name, tax_rate, price, order_id, product_id');

				foreach ($taxList as $tax)
				{
					$query->values(
						$db->q($tax->name) . ','
						. (float) $tax->tax_rate . ','
						. (float) $tax->tax . ','
						. (int) $orderId . ','
						. (isset($tax->product_id) ? (int) $tax->product_id : 'NULL')
					);
				}

				$db->setQuery($query)->execute();
			}
		}
		else
		{
			$productTaxes = array();
			$globalTaxes  = array();
			$globalTax    = 0;
			$db           = Factory::getDbo();
			$subQuery     = $db->getQuery(true)
				->select('t.*')
				->from($db->qn('#__redshopb_order_tax', 't'))
				->where('t.order_id = ' . (int) $orderId);

			$taxes = $db->setQuery($subQuery)->loadObjectList();

			if (!empty($taxes))
			{
				foreach ($taxes as $oneTax)
				{
					if ($oneTax->product_id)
					{
						$productTaxes[] = $oneTax;
					}
					else
					{
						$globalTaxes[] = $oneTax;
						$globalTax    += $oneTax->tax_rate;
					}
				}
			}

			$query      = $db->getQuery(true)
				->select('SUM(price * quantity) AS price, product_id')
				->from($db->qn('#__redshopb_order_item'))
				->where('order_id = ' . (int) $orderId)
				->group('product_id');
			$results    = $db->setQuery($query)->loadAssocList('product_id', 'price');
			$itemsTotal = array_sum($results);
			$newTotal   = 0;

			if (!empty($productTaxes))
			{
				foreach ($productTaxes as $productTax)
				{
					if (array_key_exists($productTax->product_id, $results))
					{
						$oneProductTax = ($results[$productTax->product_id] + $order->shipping_price) * $productTax->tax_rate;
						$newTotal     += $oneProductTax;

						// Apply changes for product taxes
						$this->getQueryForTax($oneProductTax, $productTax, $db);
					}

					// Product not found, so remove related current tax
					else
					{
						$query = $db->getQuery(true)
							->delete($db->qn('#__redshopb_order_tax'))
							->where('id = ' . (int) $productTax->id);

						$db->setQuery($query)->execute();
					}
				}
			}

			$newTotal  += ($itemsTotal + $order->shipping_price) * (1 + $globalTax);
			$paymentFee = RedshopbHelperOrder::getPaymentMethodFee($order->customer_company, $newTotal, $order->currency, $order->payment_name);
			$newTotal  += $paymentFee;

			$query = $db->getQuery(true)
				->update($db->qn('#__redshopb_order'))
				->where('id = ' . (int) $orderId)
				->set('total_price = ' . (float) $newTotal);

			$db->setQuery($query)->execute();

			// Apply changes for global taxes
			if (!empty($globalTaxes))
			{
				foreach ($globalTaxes as $oneTax)
				{
					$taxPrice = ($itemsTotal + $order->shipping_price) * $oneTax->tax_rate;
					$this->getQueryForTax($taxPrice, $oneTax, $db);
				}
			}
		}

		return $newTotal;
	}

	/**
	 * Method to get an order item currency
	 *
	 * @param   object  $orderItem  order item from the database
	 * @param   object  $customer   Customer object with id and type.
	 *
	 * @return integer
	 */
	private function getOrderItemCurrency($orderItem, $customer)
	{
		if (!empty($orderItem->collection_id))
		{
			return RedshopbHelperCollection::getCurrency($orderItem->collection_id);
		}

			$customerCompany = RedshopbHelperCompany::getCustomerCompanyByCustomer($customer->cid, $customer->ctype);

			return $customerCompany->currency_id;
	}

	/**
	 * Method to get order item price
	 *
	 * @param   object  $orderItem  order item from the database
	 * @param   object  $customer   Customer object with id and type.
	 * @param   int     $currency   Currency Id
	 *
	 * @return object
	 */
	private function getOrderItemPrice($orderItem, $customer, $currency)
	{
		if (!empty($orderItem->product_item_id))
		{
			return RedshopbHelperPrices::getProductItemPrice($orderItem->product_item_id, $customer->cid, $customer->ctype, $currency);
		}

		return RedshopbHelperPrices::getProductPrice($orderItem->product_id, $customer->cid, $customer->ctype, $currency);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean True on success, false on error.
	 */
	public function save($data)
	{
		if (intval($data['created_by']) === 0)
		{
			unset($data['created_by']);
		}

		if (intval($data['modified_by']) === 0)
		{
			unset($data['modified_by']);
		}

		Factory::getApplication()->triggerEvent('onRedshopbBeforeOrderSave', array(&$data));

		$isNew = (isset($data['id']) && ($data['id'] > 0)) ? false : true;

		$missingAddressInfo = (empty($data['delivery_address_address'])
			|| empty($data['delivery_address_city'])
			|| empty($data['delivery_address_country'])
			|| empty($data['delivery_address_zip']));

		if (!empty($data['delivery_address_id'])
			&& $missingAddressInfo)
		{
			$addressInfo = RedshopbEntityAddress::getInstance($data['delivery_address_id'])->getExtendedData();

			$data['delivery_address_name']         = $addressInfo->name;
			$data['delivery_address_name2']        = $addressInfo->name2;
			$data['delivery_address_address']      = $addressInfo->address;
			$data['delivery_address_address2']     = $addressInfo->address2;
			$data['delivery_address_city']         = $addressInfo->city;
			$data['delivery_address_zip']          = $addressInfo->zip;
			$data['delivery_address_country']      = Text::_($addressInfo->country);
			$data['delivery_address_country_code'] = $addressInfo->country_code;
			$data['delivery_address_state']        = $addressInfo->state_name;
			$data['delivery_address_state_code']   = $addressInfo->state_code;
			$data['delivery_address_type']         = $addressInfo->address_type;
			$data['delivery_address_code']         = $addressInfo->address_code;
		}

		$config = RedshopbApp::getConfig();

		if ($config->getInt('use_shipping_date', 0))
		{
			if (isset($data['shipping_date']) && $data['shipping_date'])
			{
				$shippingDate = new DateTime($data['shipping_date']);

				if (!RedshopbHelperOrder::isShippingDateAvailable($data['shipping_date'], $data['customer_type'], $data['customer_id']))
				{
					$this->setError(
						Text::sprintf(
							'COM_REDSHOPB_SHOP_SHIPPING_DATE_ALLOW_FROM',
							$shippingDate->format(Text::_('DATE_FORMAT_LC4'))
						)
					);

					return false;
				}
			}
			else
			{
				$data['shipping_date'] = null;
			}
		}

		if ($isNew)
		{
			$data['created_by'] = ($data['created_by'] == 0) ? (int) Factory::getUser()->id : $data['created_by'];

			if (!isset($data['delivery_address_id']) || $data['delivery_address_id'] == 0)
			{
				$this->setError(Text::_('COM_REDSHOPB_ORDER_DELIVERY_ADDRESS_MISSING'));

				return false;
			}
		}
		elseif (!isset($data['delivery_address_id']) || $data['delivery_address_id'] == 0)
		{
			if (RedshopbHelperOrder::isLog($data['id']))
			{
				unset($data['delivery_address_id']);
			}
			else
			{
				$this->setError(Text::_('COM_REDSHOPB_ORDER_DELIVERY_ADDRESS_MISSING'));

				return false;
			}
		}

		if (!$isNew && $data['created_by'] == 0)
		{
			$data['created_by'] = null;
		}

		if (!isset($data['id']) || !$data['id'] || !RedshopbHelperOrder::canChangeStatus($data['id']))
		{
			unset($data['status']);
		}

		if (isset($data['status']))
		{
			// Free child orders from canceled collection or expedition
			RedshopbHelperOrder::freeChildOrdersFromCanceledProcess($data['id'], $data['status'], $isNew);
		}

		$data['modified_by'] = (int) Factory::getUser()->id;

		return parent::save($data);
	}

	/**
	 * Refund order credit back to it's customer.
	 *
	 * @param   object  $order  Order for refund.
	 *
	 * @return boolean True on success.
	 */
	public function refund($order)
	{
		$credit   = (float) $order->total_price;
		$currency = $order->currency_id;
		$db       = $this->getDbo();
		$query    = $db->getQuery(true);
		$employee = RedshopbHelperUser::getUser($order->customer_id);

		if (is_null($employee))
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_ORDER_REFUND_CUSTOMER_MISSING'));

			return false;
		}

		$query->update($db->qn('#__redshopb_wallet_money'))
			->set($db->qn('amount') . ' = ' . $db->qn('amount') . ' + ' . (float) $credit)
			->where($db->qn('wallet_id') . ' = ' . (int) $employee->wallet)
			->where($db->qn('currency_id') . ' = ' . (int) $currency);

		return $db->setQuery($query)->execute();
	}

	/**
	 * Method for load all order items into cart.
	 *
	 * @param   int  $orderId  ID of order
	 *
	 * @return  boolean        True on success. False otherwise.
	 */
	public function loadCartFromOrder($orderId = 0)
	{
		if (!$orderId)
		{
			return false;
		}

		$db = $this->getDbo();

		// Clear cart sessions
		RedshopbHelperCart::clearCartFromSession();

		$orderItems = $db->setQuery(RedshopbHelperOrder::getOrderItemsQuery($orderId))->loadObjectList();

		if (empty($orderItems))
		{
			return false;
		}

		$customerId   = Factory::getApplication()->getUserState('shop.customer_id', 0);
		$customerType = Factory::getApplication()->getUserState('shop.customer_type', '');

		$productAccKey = array();

		foreach ($orderItems as $key => $orderItem)
		{
			if (!$orderItem->parent_id)
			{
				continue;
			}

			if (!isset($productAccKey[$orderItem->parent_id]))
			{
				$productAccKey[$orderItem->parent_id] = array();
			}

			$productAccKey[$orderItem->parent_id][] = $orderItem->product_id;

			unset($orderItems[$key]);
		}

		foreach ($orderItems as $orderItem)
		{
			$accessoriesKey = isset($productAccKey[$orderItem->id]) ? $productAccKey[$orderItem->id] : null;
			$accessories    = null;

			if (!empty($accessoriesKey))
			{
				$query = $db->getQuery(true)
					->select($db->qn('id'))
					->from($db->qn('#__redshopb_product_accessory'))
					->where($db->qn('product_id') . ' = ' . $orderItem->product_id)
					->where($db->qn('accessory_product_id') . ' IN (' . implode(',', $accessoriesKey) . ')');
				$db->setQuery($query);
				$accessories = $db->loadColumn();
			}

			RedshopbHelperCart::addToCartById(
				$orderItem->product_id,
				$orderItem->product_item_id,
				$accessories,
				$orderItem->quantity,
				$orderItem->price,
				$orderItem->currency_id,
				$customerId,
				$customerType,
				$orderItem->collection_id,
				0,
				$orderItem->stockroom_id
			);

			unset($productAccKey[$orderItem->id]);
		}

		return true;
	}

	/**
	 * Get id by name in
	 *
	 * @param   array   $item           Array value that is send.
	 * @param   string  $arrayValue     String value that is search for.
	 * @param   string  $instanceValue  String value that is in instance.
	 * @param   string  $labelValue     String value for message..
	 *
	 * @return false|null
	 */
	private function getIdByItemValue(&$item, $arrayValue, $instanceValue, $labelValue)
	{
		if (isset($item[$arrayValue]))
		{
			/** @var RedshopbModelAdmin $itemModel */
			$itemModel = RedshopbModel::getFrontInstance($instanceValue);
			$itemName  = $itemModel->getItemFromWSData($item[$arrayValue]);

			if (!$itemName)
			{
				Factory::getApplication()->enqueueMessage(
					Text::sprintf(
						'COM_REDSHOPB_WEBSERVICE_ID_NOT_FOUND', strtolower(Text::_($labelValue)), $item[$arrayValue]
					), 'error'
				);

				return false;
			}

			$item[$arrayValue] = $itemName->id;
		}
	}

	/**
	 * Get id by name in
	 *
	 * @param   array|float|int  $tax       Array value that is send.
	 * @param   mixed            $taxPrice  Tax value for price.
	 * @param   JDatabaseDriver  $db        Database.
	 *
	 * @return void
	 */
	private function getQueryForTax(&$tax, &$taxPrice, $db)
	{
		if ($tax != $taxPrice->price)
		{
			$query = $db->getQuery(true)
				->update($db->qn('#__redshopb_order_tax'))
				->where('id = ' . (int) $taxPrice->id)
				->set('price = ' . (float) $tax);

			$db->setQuery($query)->execute();
		}
	}
}
