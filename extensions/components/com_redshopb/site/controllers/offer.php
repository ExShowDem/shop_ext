<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;

/**
 * Order Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerOffer extends RedshopbControllerForm
{
	/**
	 * Method to send an offer.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 */
	public function send($key = null, $urlVar = null)
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$model = $this->getModel();

		// Determine the name of the primary key for the data.
		if (empty($key))
		{
			$table = $model->getTable();
			$key   = $table->getKeyName();
		}

		// To avoid data collisions the urlVar may be different from the primary key.
		if (empty($urlVar))
		{
			$urlVar = $key;
		}

		$recordId = $this->input->getInt($urlVar);

		// Redirect back to the edit screen.
		$this->setRedirect(
			$this->getRedirectToItemRoute($this->getRedirectToItemAppend($recordId, $urlVar))
		);

		if (!RedshopbHelperEmail::sendOfferEmail($recordId))
		{
			$this->setMessage(Text::_('COM_REDSHOPB_OFFER_SENT_FAIL'), 'error');

			return false;
		}

		return true;
	}

	/**
	 * Ajax call to get products tab content.
	 *
	 * @return  void
	 */
	public function ajaxproducts()
	{
		RedshopbHelperAjax::validateAjaxRequest();
		$app   = Factory::getApplication();
		$input = $app->input;

		$offerId = $input->getInt('offer_id');

		if ($offerId)
		{
			$config = array('context' => 'com_redshopb.not_offer.products');
			/** @var RedshopbModelProducts $model */
			$model      = RModelAdmin::getInstance('Products', 'RedshopbModel', $config);
			$offerModel = $this->getModel('Offer');
			$offerTable = $offerModel->getTable();
			$offerTable->load($offerId);
			$collectionId = $offerTable->get('collection_id', 0);

			$formName = 'productsForm';
			$this->setModelStatesCommon($model, $offerTable, $collectionId);

			$pagination = $model->getPagination();
			$pagination->set('formName', $formName);

			$filterForm = $model->getForm();
			$this->setFiltersCommon($filterForm, $offerTable);
			$items = $model->getItems();

			$ids = array();

			foreach ($items as $item)
			{
				$ids[$item->id] = $item->id;
			}

			$currency             = RedshopbHelperPrices::getCurrency(
				$offerTable->get('customer_id'), $offerTable->get('customer_type'), $collectionId
			);
			$productItemsUnsorted = RedshopbHelperProduct::getSKUCollections($ids, 'objectList', true, $collectionId);
			$productsWithItems    = array();
			$productsWithoutItems = $ids;
			$productPrices        = array();
			$productItemPrices    = array();
			$productItems         = array();
			$defaultProductItems  = array();

			$this->prepareAttrMenuData($productPrices, $productItemPrices, $productItems, $defaultProductItems,
				$productItemsUnsorted, $productsWithItems, $productsWithoutItems,
				$ids, $offerTable, $currency, $collectionId
			);

			echo RedshopbLayoutHelper::render('offer.products', array(
					'state' => $model->getState(),
					'items' => $items,
					'pagination' => $pagination,
					'offerId' => $offerId,
					'activeFilters' => $model->getActiveFilters(),
					'filter_form' => $filterForm,
					'formName' => $formName,
					'showToolbar' => false,
					'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=offer&model=products'),
					'return' => base64_encode('index.php?option=com_redshopb&view=offer&layout=edit&tab=products&id=' . $offerId),
					'button' => 1,
					'context' => $model->get('context'),
					'productPrices' => $productPrices,
					'productItemPrices' => $productItemPrices,
					'currency' => $currency,
					'productItems' => $productItems,
					'defaultProductItems' => $defaultProductItems,
					'offerInfo' => $offerTable
				)
			);
		}

		$app->close();
	}

	/**
	 * Refresh offer item table after quantities has been altered
	 *
	 * @return  void
	 */
	public function ajaxRefreshProducts()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app             = Factory::getApplication();
		$input           = $app->input->post;
		$contents        = $input->get('content', array(), 'array');
		$changedDiscount = $input->get('changed_discount', array(), 'array');
		$formName        = $input->get('form_name');
		$offerId         = (int) $input->get('offer_id');
		$tally           = array();
		$offerModel      = $this->getModel('Offer');
		$offerTable      = $offerModel->getTable();
		$offerTable->load($offerId);
		$collectionId = $offerTable->get('collection_id', 0);
		$customerType = $offerTable->get('customer_type', '');
		$customerId   = $offerTable->get('customer_id', 0);
		$currency     = RedshopbHelperPrices::getCurrency($customerId, $customerType, $collectionId);

		$this->groupOfferItems($contents, $changedDiscount, $tally);
		$this->recalculateQuantityPrices($tally, $currency, $customerId, $customerType, $collectionId, $formName);

		$lookupTable = array(
			'productsForm' => array(
				'context'   => 'com_redshopb.not_offer.products',
				'buttonSet' => 1,
				'tab'       => 'products',
			),
			'productsOfferForm' => array(
				'context'   => 'com_redshopb.offer.products',
				'buttonSet' => 2,
				'tab'       => 'offerproducts',
			),
		);

		$config = array('context' => $lookupTable[$formName]['context']);
		/** @var RedshopbModelProducts $model */
		$model = RModelAdmin::getInstance('Products', 'RedshopbModel', $config);
		$this->setModelStatesCommon($model, $offerTable, $collectionId);

		if (strcmp($formName, 'productsOfferForm') === 0)
		{
			$model->setState('list.offer', $offerId);
			$model->setState('list.offerInclude', true);
		}

		$pagination = $model->getPagination();
		$pagination->set('formName', $formName);

		$filterForm = $model->getForm();
		$this->setFiltersCommon($filterForm, $offerTable);

		// Prepare presentation
		$ajaxResponse = new RedshopbAjaxResponse;

		$return = 'index.php?option=com_redshopb&view=offer&layout=edit&tab=' . $lookupTable[$formName]['tab']
			. '&id=' . $offerId;
		$return = base64_encode($return);

		$layoutData = array(
			'state'         => $model->getState(),
			'items'         => $tally['remade'],
			'pagination'    => $pagination,
			'offerId'       => $offerId,
			'activeFilters' => $model->getActiveFilters(),
			'filter_form'   => $filterForm,
			'formName'      => $formName,
			'showToolbar'   => false,
			'action'        => RedshopbRoute::_('index.php?option=com_redshopb&view=offer&model=products'),
			'return'        => $return,
			'button'        => $lookupTable[$formName]['buttonSet'],
			'context'       => $model->get('context'),
			'currency'      => $currency
		);

		// Set up per-row attribute drop menu of Products tab
		if (strcmp($formName, 'productsForm') === 0)
		{
			$items = $model->getItems();
			$ids   = array();

			foreach ($items as $item)
			{
				$ids[$item->id] = $item->id;
			}

			$productItemsUnsorted = RedshopbHelperProduct::getSKUCollections(
				$ids,
				'objectList',
				true,
				$collectionId
			);
			$productsWithItems    = array();
			$productsWithoutItems = $ids;
			$productPrices        = array();
			$productItemPrices    = array();
			$productItems         = array();
			$defaultProductItems  = array();

			$this->prepareAttrMenuData($productPrices, $productItemPrices, $productItems,
				$defaultProductItems, $productItemsUnsorted, $productsWithItems, $productsWithoutItems,
				$ids, $offerTable, $currency, $collectionId
			);

			$layoutData['productPrices']       = $productPrices;
			$layoutData['productItemPrices']   = $productItemPrices;
			$layoutData['productItems']        = $productItems;
			$layoutData['defaultProductItems'] = $defaultProductItems;
		}

		$ajaxResponse->setBody(RedshopbLayoutHelper::render('offer.products', $layoutData));
		$ajaxResponse->setData(array('tabName' => $lookupTable[$formName]['tab']));

		echo json_encode($ajaxResponse);

		$app->close();
	}

	/**
	 * Group together price-quantity ranges
	 *
	 * @param   Array  $contents          Info about offer products' quantities.
	 * @param   Array  $changedDiscount   Info about offer products' discounts.
	 * @param   Array  $tally             The result array.
	 *
	 * @return  void
	 */
	protected function groupOfferItems(&$contents, &$changedDiscount, &$tally)
	{
		foreach ($contents as $content)
		{
			$content     = (object) $content;
			$combinedIds = $content->productId . '_' . $content->productItemId;

			if (!isset($tally['grouped'][$combinedIds]['quantity']))
			{
				$tally['grouped'][$combinedIds]['quantity'] = (float) $content->quantity;
			}
			else
			{
				$tally['grouped'][$combinedIds]['quantity'] += (float) $content->quantity;
			}

			if (!empty($changedDiscount)
				&& strcmp($changedDiscount[0]['productId'], $content->productId) === 0
				&& strcmp($changedDiscount[0]['productItemId'], $content->productItemId) === 0)
			{
				$tally['grouped'][$combinedIds]['discount']     = $changedDiscount[0]['discount'];
				$tally['grouped'][$combinedIds]['discountType'] = $changedDiscount[0]['discountType'];
			}
			else
			{
				$tally['grouped'][$combinedIds]['discount']     = $content->discount;
				$tally['grouped'][$combinedIds]['discountType'] = $content->discountType;
			}

			if (isset($content->selectedProductItemId))
			{
				$tally['grouped'][$combinedIds]['selectedProductItemId'] = $content->selectedProductItemId;
			}
		}
	}

	/**
	 * Re-calculate quantity-ranges' prices
	 *
	 * @param   Array    $tally             The result array.
	 * @param   mixed    $currency          Currency code or symbol.
	 * @param   integer  $customerId        Buyer's ID.
	 * @param   string   $customerType      Company, department or employee.
	 * @param   integer  $collectionId      Collection ID.
	 * @param   string   $formName          Name of the form.
	 *
	 * @return  void
	 */
	protected function recalculateQuantityPrices(&$tally, $currency, $customerId, $customerType, $collectionId, $formName)
	{
		foreach ($tally['grouped'] as $keys => $grouped)
		{
			$ids           = explode('_', $keys);
			$productId     = (int) $ids[0];
			$productItemId = (int) $ids[1];

			// Check if there are quantity chunks for split / existence of price multiplications
			$newQuantities = RedshopbHelperCart::splitQuantityMultiplications(
				$productId,
				$productItemId,
				$grouped['quantity'],
				$currency,
				$customerId,
				$customerType,
				$collectionId
			);

			// Re-calculate quantities-prices
			foreach ($newQuantities as $newQuantity)
			{
				$newPrice = RedshopbHelperCart::updateItemPrice(
					array(
						'collectionId' => $collectionId,
						'productId'    => $productId,
						'productItem'  => $productItemId,
						'currency'     => $currency
					),
					$newQuantity,
					$customerId,
					$customerType
				);

				$subtotal = $newPrice['price'] * $newQuantity;
				$total    = RedshopbHelperOffers::calculateDiscount(
					$grouped['discountType'],
					$grouped['discount'],
					$subtotal
				);

				$productInfo                     = RedshopbHelperProduct::loadProduct($productId);
				$refreshedRow                    = new stdClass;
				$refreshedRow->id                = $productId;
				$refreshedRow->name              = $productInfo->name;
				$refreshedRow->sku               = $productInfo->sku;
				$refreshedRow->unit_measure_code = $productInfo->unit_measure_id;
				$refreshedRow->discount_type     = $grouped['discountType'];
				$refreshedRow->discount          = (float) $grouped['discount'];
				$refreshedRow->quantity          = $newQuantity;
				$refreshedRow->unit_price        = $newPrice['price'];
				$refreshedRow->subtotal          = $subtotal;
				$refreshedRow->total             = $total;

				if ($productItemId !== 0 && strcmp($formName, 'productsOfferForm') === 0)
				{
					$productItemInfo = RedshopbHelperProduct_Item::loadProductItem($productItemId);
					$attrName        = RedshopbHelperProduct_Item::getDescriptiveAttributeNames($productItemInfo->id);

					$refreshedRow->product_item_id  = $productItemInfo->id;
					$refreshedRow->product_item_sku = $productItemInfo->sku . '-' . $attrName;
				}

				if (isset($grouped['selectedProductItemId']))
				{
					$refreshedRow->selectedProductItemId = $grouped['selectedProductItemId'];
				}

				$tally['remade'][] = $refreshedRow;
			}
		}
	}

	/**
	 * Set common model states
	 *
	 * @param   RedshopbModel  $model         A reference to the products model.
	 * @param   RedshopbTable  $offerTable    A reference to $offerTable.
	 * @param   integer        $collectionId  Collection ID.
	 *
	 * @return  void
	 */
	protected function setModelStatesCommon(&$model, &$offerTable, $collectionId)
	{
		$model->getState();
		$model->setState('list.product_state', '1');
		$model->setState('list.company_id', $offerTable->get('vendor_id', 0));
		$model->setState('list.allow_mainwarehouse_products', true);
		$model->setState('list.disallow_freight_fee_products', true);
		$model->setState('list.allow_parent_companies_products', true);
		$model->setState('filter.include_categories', true);
		$model->setState('filter.include_tags', true);
		$model->setState('include_objects', true);
		$model->setState('list.product_discontinued', 0);
		$model->setState('list.include_categories', true);
		$model->setState('list.include_tags', true);
		$model->setState('filter.product_collection', $collectionId);
	}

	/**
	 * Set common filters
	 *
	 * @param   Object         $filterForm    A reference to $filterForm.
	 * @param   RedshopbTable  $offerTable    A reference to $offerTable.
	 *
	 * @return  void
	 */
	protected function setFiltersCommon(&$filterForm, &$offerTable)
	{
		$filterForm->setFieldAttribute('product_state', 'default', '1', 'filter');
		$filterForm->setFieldAttribute('product_state', 'disabled', 'true', 'filter');
		$filterForm->setFieldAttribute('product_discontinued', 'disabled', 'true', 'filter');
		$filterForm->setFieldAttribute('product_discontinued', 'default', '0', 'filter');
		$filterForm->setFieldAttribute('product_company', 'type', 'companyvendor', 'filter');
		$filterForm->setFieldAttribute('product_company', 'company_id', $offerTable->get('vendor_id', 0), 'filter');
	}

	/**
	 * Prepare per-row attribute drop-menus' data on offer view's products tab
	 *
	 * @param   Array          $productPrices            A reference to $productPrices.
	 * @param   Array          $productItemPrices        A reference to $productItemPrices.
	 * @param   Array          $productItems             A reference to $productItems.
	 * @param   Array          $defaultProductItems      A reference to $defaultProductItems.
	 * @param   Array          $productItemsUnsorted     A reference to $productItemsUnsorted.
	 * @param   Array          $productsWithItems        A reference to $productsWithItems.
	 * @param   Array          $productsWithoutItems     A reference to $productsWithoutItems.
	 * @param   Array          $ids                      A reference to $ids.
	 * @param   RedshopbTable  $offerTable               A reference to $offerTable.
	 * @param   mixed          $currency                 A reference to the currency ID, either code number or alpha3.
	 * @param   integer        $collectionId             A reference to the Collection ID.
	 *
	 * @return  void
	 */
	protected function prepareAttrMenuData(&$productPrices, &$productItemPrices, &$productItems, &$defaultProductItems,
		&$productItemsUnsorted, &$productsWithItems, &$productsWithoutItems,
		&$ids, &$offerTable, &$currency, &$collectionId
	)
	{
		if (count($productItemsUnsorted))
		{
			foreach ($productItemsUnsorted as $productItem)
			{
				$productsWithItems[$productItem->product_id] = $productItem->product_id;
				unset($productsWithoutItems[$productItem->product_id]);

				if (!isset($productItems[$productItem->product_id]))
				{
					$productItems[$productItem->product_id] = array();
				}

				if (!isset($productItems[$productItem->product_id][$productItem->pi_id]))
				{
					$productItems[$productItem->product_id][$productItem->pi_id] = array();
				}

				$productItems[$productItem->product_id][$productItem->pi_id] = $productItem;

				$attrName = RedshopbHelperProduct_Item::getDescriptiveAttributeNames($productItem->pi_id);

				$productItem->sku .= '-' . $attrName;

				if (!isset($defaultProductItems[$productItem->product_id]))
				{
					$defaultProductItems[$productItem->product_id] = $productItem->pi_id;
				}
			}

			$productItemPrices = RedshopbHelperPrices::getProductItemsPrice(
				$defaultProductItems, $ids, $offerTable->get('customerId'), $offerTable->get('customerType'),
				$currency, array($collectionId), '', 0, 0, true
			);
		}

		if (count($productsWithoutItems))
		{
			$productPrices = RedshopbHelperPrices::getProductsPrice(
				$ids, $offerTable->get('customerId'), $offerTable->get('customerType'), $currency,
				array($collectionId), '', 0, 0, true
			);
		}
	}

	/**
	 * Ajax call to get products tab content.
	 *
	 * @return  void
	 */
	public function ajaxofferproducts()
	{
		RedshopbHelperAjax::validateAjaxRequest();
		$app   = Factory::getApplication();
		$input = $app->input;

		$offerId = $input->getInt('offer_id');

		if ($offerId)
		{
			$offerModel = $this->getModel('Offer');
			$offerTable = $offerModel->getTable();
			$offerTable->load($offerId);
			$collectionId = $offerTable->get('collection_id', 0);
			$config       = array('context' => 'com_redshopb.offer.products');
			/** @var RedshopbModelProducts $model */
			$model    = RModelAdmin::getInstance('Products', 'RedshopbModel', $config);
			$formName = 'productsOfferForm';
			$this->setModelStatesCommon($model, $offerTable, $collectionId);

			$model->setState('list.offer', $offerId);
			$model->setState('list.offerInclude', true);
			$model->setState('list.forOfferItemsView', true);

			$pagination = $model->getPagination();
			$pagination->set('formName', $formName);

			$filterForm = $model->getForm();
			$this->setFiltersCommon($filterForm, $offerTable);
			$currency = RedshopbHelperPrices::getCurrency($offerTable->get('customer_id'), $offerTable->get('customer_type'), $collectionId);

			echo RedshopbLayoutHelper::render('offer.products', array(
					'state' => $model->getState(),
					'items' => $model->getItems(),
					'pagination' => $pagination,
					'offerId' => $offerId,
					'activeFilters' => $model->getActiveFilters(),
					'filter_form' => $filterForm,
					'formName' => $formName,
					'showToolbar' => false,
					'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=offer&model=products'),
					'return' => base64_encode('index.php?option=com_redshopb&view=offer&layout=edit&tab=offerproducts&id=' . $offerId),
					'button'	 => 2,
					'context' => $model->get('context'),
					'currency' => $currency,
					'offerInfo' => $offerTable
				)
			);
		}

		$app->close();
	}

	/**
	 * Get ajax price condition
	 *
	 * @return  void
	 */
	public function ajaxPriceCondition()
	{
		RedshopbHelperAjax::validateAjaxRequest();
		$app                          = Factory::getApplication();
		$priceCondition               = $this->getPriceCondition();
		$priceCondition['price']      = RedshopbHelperProduct::getProductFormattedPrice($priceCondition['price'], $priceCondition['currency']);
		$priceCondition['finalPrice'] = RedshopbHelperProduct::getProductFormattedPrice($priceCondition['finalPrice'], $priceCondition['currency']);

		echo json_encode($priceCondition);

		$app->close();
	}

	/**
	 * Get price condition
	 *
	 * @return array
	 */
	protected function getPriceCondition()
	{
		$app           = Factory::getApplication();
		$input         = $app->input;
		$productId     = $input->getInt('productId', 0);
		$discount      = $input->getFloat('discount', 0);
		$typeDiscount  = $input->getCmd('typeDiscount', 'percent');
		$quantity      = $input->getFloat('quantity', 0);
		$productItemId = $input->getInt('productItemId', 0);
		$offerId       = $input->getInt('offer_id', 0);
		$offerModel    = $this->getModel('Offer');
		$offerTable    = $offerModel->getTable();
		$offerTable->load($offerId);
		$prices               = $offerModel->getProductPrice($offerId, $productItemId, $productId, $quantity);
		$prices['finalPrice'] = $offerModel->calculateDiscount($prices['subtotal'], $typeDiscount, $discount);

		return $prices;
	}

	/**
	 * Method for adding a  product to offer list.
	 *
	 * @return  void
	 */
	public function ajaxAddOfferItem()
	{
		RedshopbHelperAjax::validateAjaxRequest();
		$app           = Factory::getApplication();
		$input         = $app->input;
		$productId     = $input->getInt('productId', 0);
		$discount      = $input->getFloat('discount', 0);
		$typeDiscount  = $input->getCmd('typeDiscount', 'percent');
		$productItemId = $input->getInt('productItemId', 0);
		$offerId       = $input->getInt('offer_id', 0);
		$return        = array('statusResult' => 0);
		$rowCount      = RedshopbHelperOffers::getOfferProductItemsCount($offerId, $productId, $productItemId);
		$model         = $this->getModel('Offer');
		$offerTable    = $model->getTable('Offer');
		$offerTable->load($offerId);

		if (!$productId || !$offerId || $rowCount > 0)
		{
			echo json_encode($return);
			$app->close();
		}

		$priceCondition        = $this->getPriceCondition();
		$data                  = array();
		$data['offer_id']      = $offerId;
		$data['product_id']    = $productId;
		$data['unit_price']    = $priceCondition['price'];
		$data['quantity']      = $priceCondition['quantity'];
		$data['discount_type'] = $typeDiscount;

		if ($productItemId)
		{
			$data['product_item_id'] = $productItemId;
		}

		$data['discount'] = $discount;
		$data['subtotal'] = $priceCondition['subtotal'];
		$data['total']    = $priceCondition['finalPrice'];

		RFactory::getDispatcher()->trigger('onRedshopbOfferItemStore', array(&$data, $input->get('customText', '', 'raw')));

		$table = $model->getTable('Offer_Item_Xref');

		if ($table->save($data))
		{
			if ($model->recalculateOfferTotal($offerId))
			{
				$currency                 = RedshopbHelperPrices::getCurrency(
					$offerTable->get('customer_id'), $offerTable->get('customer_type'), $offerTable->get('collection_id')
				);
				$globalDiscount           = $input->getFloat('globalDiscount', 0);
				$globalTypeDiscount       = $input->getCmd('globalTypeDiscount', 'percent');
				$offerTotal               = $model->getOfferTotal($offerId, $globalTypeDiscount, $globalDiscount);
				$return['globalSubTotal'] = RedshopbHelperProduct::getProductFormattedPrice($offerTotal['subtotal'], $currency);
				$return['globalTotal']    = RedshopbHelperProduct::getProductFormattedPrice($offerTotal['total'], $currency);
				$return['statusResult']   = 1;
			}
		}

		echo json_encode($return);
		$app->close();
	}

	/**
	 * Ajax Get Global Total
	 *
	 * @return  void
	 */
	public function ajaxGetGlobalTotal()
	{
		RedshopbHelperAjax::validateAjaxRequest();
		$app                = Factory::getApplication();
		$input              = $app->input;
		$model              = $this->getModel('Offer');
		$offerId            = $input->getInt('offer_id', 0);
		$globalDiscount     = $input->getFloat('globalDiscount', 0);
		$globalTypeDiscount = $input->getCmd('globalTypeDiscount', 'percent');
		$return             = array();
		$offerTotal         = $model->getOfferTotal($offerId, $globalTypeDiscount, $globalDiscount);
		$offerTable         = $model->getTable();
		$offerTable->load($offerId);
		$currency                 = RedshopbHelperPrices::getCurrency(
			$offerTable->get('customer_id'), $offerTable->get('customer_type'), $offerTable->get('collection_id')
		);
		$return['globalSubTotal'] = RedshopbHelperProduct::getProductFormattedPrice($offerTotal['subtotal'], $currency);
		$return['globalTotal']    = RedshopbHelperProduct::getProductFormattedPrice($offerTotal['total'], $currency);
		$return['statusResult']   = 1;
		echo json_encode($return);
		$app->close();
	}

	/**
	 * Ajax Update Offer Items
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 */
	public function ajaxApplyOfferItems()
	{
		RedshopbHelperAjax::validateAjaxRequest();
		$app        = Factory::getApplication();
		$input      = $app->input->post;
		$offerId    = $input->get('offer_id');
		$offerModel = $this->getModel('Offer');
		$offerTable = $offerModel->getTable();
		$offerTable->load($offerId);
		$collectionId = $offerTable->get('collection_id', 0);
		$customerType = $offerTable->get('customer_type', '');
		$customerId   = $offerTable->get('customer_id', 0);
		$currencyCode = (int) RedshopbHelperPrices::getCurrency($customerId, $customerType, $collectionId);
		$contents     = $input->get('form_content', array(), 'array');
		$return       = array();

		$db = Factory::getDbo();

		$db->transactionStart();

		try
		{
			$query      = $db->getQuery(true);
			$deleteFrom = $db->qn('#__redshopb_offer_item_xref');

			if (is_array($deleteFrom))
			{
				throw new Exception(Text::_('COM_REDSHOPB_OFFER_ITEMS_UPDATE_UNSUCCESSFUL'));
			}

			$query->delete($deleteFrom);
			$query->where($db->qn('offer_id') . ' = ' . $db->q($offerId));

			if (!$db->setQuery($query)->execute())
			{
				throw new Exception(Text::_('COM_REDSHOPB_OFFER_ITEMS_UPDATE_UNSUCCESSFUL'));
			}

			$model    = $this->getModel('Offer');
			$subtotal = 0;

			foreach ($contents as $content)
			{
				$unitPrice  = RedshopbHelperPrices::unformatCurrency($content['productPrice'], $currencyCode);
				$finalPrice = RedshopbHelperPrices::unformatCurrency($content['productFinalPrice'], $currencyCode);

				$subtotal += $finalPrice;

				if ($finalPrice < 0)
				{
					throw new Exception(Text::_('COM_REDSHOPB_OFFER_ITEMS_UPDATE_UNSUCCESSFUL_NEGATIVE_PRICE'));
				}

				$offerItemsData                  = array();
				$offerItemsData['unit_price']    = $unitPrice;
				$offerItemsData['quantity']      = $content['newQuantity'];
				$offerItemsData['discount_type'] = $content['typeDiscount'];
				$offerItemsData['discount']      = $content['discount'];
				$offerItemsData['subtotal']      = $unitPrice * (float) $content['newQuantity'];
				$offerItemsData['total']         = $finalPrice;
				$offerItemsData['product_id']    = $content['productId'];
				$offerItemsData['offer_id']      = $offerId;

				if (!empty($content['customText']))
				{
					RFactory::getDispatcher()->trigger('onRedshopbOfferItemStore', array(&$offerItemsData, $content['customText']));
				}

				if ($content['productItemId'] != 0)
				{
					$offerItemsData['product_item_id'] = $content['productItemId'];
				}

				$offerItemsTable = $model->getTable('Offer_Item_Xref');

				if (!$offerItemsTable->save($offerItemsData))
				{
					throw new Exception(Text::_('COM_REDSHOPB_OFFER_ITEMS_UPDATE_UNSUCCESSFUL'));
				}
			}

			$offerTable = $model->getTable('Offer');
			$offerTable->load($offerId);
			$total = RedshopbHelperOffers::calculateDiscount(
				$offerTable->discount_type,
				$offerTable->discount,
				$subtotal
			);

			$offerData             = array();
			$offerData['id']       = $offerId;
			$offerData['subtotal'] = $subtotal;
			$offerData['total']    = $total;

			if (!$offerTable->save($offerData))
			{
				throw new Exception(Text::_('COM_REDSHOPB_OFFER_ITEMS_UPDATE_UNSUCCESSFUL'));
			}

			$db->transactionCommit();

			$return['statusResult'] = 0;

			echo json_encode($return);
			$app->close();
		}
		catch (Exception $e)
		{
			$db->transactionRollback();

			$return['statusResult'] = 1;
			$return['errorMessage'] = $e->getMessage();

			echo json_encode($return);
			$app->close();

			return;
		}
	}
}
