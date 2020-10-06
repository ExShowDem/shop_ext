<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Input\Input;

/**
 * My favorite list Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerMyfavoritelist extends RedshopbControllerForm
{
	/**
	 * @var  string
	 */
	protected $text_prefix = 'COM_REDSHOPB_MYFAVORITELIST';

	/**
	 * Checkout
	 *
	 * @return boolean
	 *
	 * @since 1.13.0
	 */
	public function checkout()
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$input          = Factory::getApplication()->input;
		$favoriteListId = $input->getInt('id', 0);

		if (!$favoriteListId)
		{
			$this->setMessage(Text::_('COM_REDSHOPB_MYFAVORITELIST_LIST_NOT_FOUND', 'warning'));
			$this->setRedirect(
				$this->getRedirectToItemRoute($this->getRedirectToItemAppend($favoriteListId))
			);

			return false;
		}

		$model = $this->getModel();
		$table = $model->getTable();

		if (!$table->load($favoriteListId))
		{
			$this->setMessage(Text::_('COM_REDSHOPB_MYFAVORITELIST_LIST_NOT_FOUND'), 'warning');
			$this->setRedirect(
				$this->getRedirectToItemRoute($this->getRedirectToItemAppend($favoriteListId))
			);

			return false;
		}

		$products     = $model->getProducts($favoriteListId, true);
		$productItems = $model->getProductItems($favoriteListId, true);

		if (!$products && !$productItems)
		{
			$this->setMessage(Text::_('COM_REDSHOPB_MYFAVORITELIST_LIST_EMPTY'), 'warning');
			$this->setRedirect(
				$this->getRedirectToItemRoute($this->getRedirectToItemAppend($favoriteListId))
			);

			return false;
		}

		if (RedshopbApp::getConfig()->getBool('clean_cart_when_add_from_favourite_list', true))
		{
			// Clear cart sessions
			RedshopbHelperCart::clearCartFromSession();
		}

		$app          = Factory::getApplication();
		$customerId   = $app->getUserState('shop.customer_id', 0);
		$customerType = $app->getUserState('shop.customer_type', '');
		$collections  = $app->input->get('collection_id', array(), 'array');
		$quantities   = $app->input->get('quantity', array(), 'array');
		$currency     = RedshopbHelperPrices::getCurrency($customerId, $customerType);

		$this->addProductsToCart($products, $collections, $quantities, $customerId, $customerType, $currency);
		$this->addProductItemsToCart($productItems, $collections, $quantities, $customerId, $customerType, $currency);

		$myFavListRedirect = RedshopbEntityConfig::getInstance()->getInt('my_favorite_list_checkout', '1');

		if ($myFavListRedirect == 0)
		{
			$this->setMessage(Text::_('COM_REDSHOPB_MYFAVORITELIST_LIST_ADD_TO_CART_SUCESSFULLY'));
			$this->setRedirect(
				$this->getRedirectToItemRoute($this->getRedirectToItemAppend($favoriteListId))
			);
		}
		else
		{
			$this->setMessage(Text::_('COM_REDSHOPB_MYFAVORITELIST_LIST_CHECKOUT_SUCESSFULLY'));
			$this->setRedirect(
				RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=cart', false)
			);
		}

		return true;
	}

	/**
	 * Add products to cart
	 *
	 * @param   array               $products       A list of products to be added to cart.
	 * @param   array               $collections    Collection IDs.
	 * @param   array               $quantities     Quantities.
	 * @param   integer             $customerId     Customer's ID.
	 * @param   string              $customerType   Customer's type.
	 * @param   integer|string      $currency       Customer's currency either in number code form or alpha3 form.
	 *
	 * @return  void
	 */
	protected function addProductsToCart($products, $collections, $quantities, $customerId, $customerType, $currency)
	{
		foreach ($products as $product)
		{
			$collectionId = isset($collections[$product->id][0]) ? $collections[$product->id][0] : null;
			$quantity     = isset($quantities[$product->id][0]) ? $quantities[$product->id][0] : $product->quantity;
			$productPrice = RedshopbHelperPrices::getProductPrice(
				$product->id,
				$customerId,
				$customerType,
				$currency,
				$collectionId ? array($collectionId) : array(),
				'',
				0,
				$quantity
			);

			if (!$collectionId && !empty($productPrice->wid))
			{
				$collectionId = $productPrice->wid;
			}

			RedshopbHelperCart::addToCartById(
				$product->id,
				null,
				null,
				$quantity,
				$productPrice ? $productPrice->price : 0.0,
				$productPrice ? $productPrice->currency : $currency,
				$customerId,
				$customerType,
				$collectionId
			);
		}
	}

	/**
	 * Add product items to cart
	 *
	 * @param   array               $productItems   A list of product items to be added to cart.
	 * @param   array               $collections    Collection IDs.
	 * @param   array               $quantities     Quantities.
	 * @param   integer             $customerId     Customer's ID.
	 * @param   string              $customerType   Customer's type.
	 * @param   integer|string      $currency       Customer's currency either in number code form or alpha3 form.
	 *
	 * @return  void
	 */
	protected function addProductItemsToCart($productItems, $collections, $quantities, $customerId, $customerType, $currency)
	{
		foreach ($productItems as $productItem)
		{
			$collectionId = isset($collections[$productItem->product_id][$productItem->id]) ? $collections[$productItem->product_id][$productItem->id] : null;
			$quantity     = isset($quantities[$productItem->product_id][$productItem->id]) ? $quantities[$productItem->product_id][$productItem->id] : $productItem->quantity;
			$productPrice = RedshopbHelperPrices::getProductItemPrice(
				$productItem->id,
				$customerId,
				$customerType,
				$currency,
				$collectionId ? array($collectionId) : array(),
				'',
				0,
				$quantity
			);

			if (!$collectionId && !empty($productPrice->wid))
			{
				$collectionId = $productPrice->wid;
			}

			RedshopbHelperCart::addToCartById(
				$productItem->product_id,
				$productItem->id,
				null,
				$quantity,
				$productPrice ? $productPrice->price : 0.0,
				$productPrice ? $productPrice->currency : $currency,
				$customerId,
				$customerType,
				$collectionId
			);
		}
	}

	/**
	 * Generate PDF
	 *
	 * @return boolean
	 *
	 * @since 1.13.0
	 */
	public function toPdf()
	{
		$app            = Factory::getApplication();
		$input          = $app->input;
		$favoriteListId = $input->getInt('id', 0);

		try
		{
			/** @var RedshopbModelMyfavoriteproducts $myFavoriteProductsModel */
			$myFavoriteProductsModel = RedshopbModel::getFrontInstance('Myfavoriteproducts', array('ignore_request' => true));

			$myFavoriteProductsModel->setState('filter.favorite_list_id_exclude', false);
			$myFavoriteProductsModel->setState('filter.favorite_list_id', $favoriteListId);
			$myFavoriteProductsModel->setState('disable_user_states', true);
			$myFavoriteProductsModel->setState('filter.include_available_collections', true);

			if ($myFavoriteProductsModel->removeNotAvailableProducts())
			{
				$app->enqueueMessage(Text::_('COM_REDSHOPB_MYFAVORITELIST_WARNING_SOME_PRODUCTS_ARE_NOT_AVAILABLE'), 'warning');

				$this->setRedirect($this->getRedirectToItemRoute($this->getRedirectToItemAppend($favoriteListId)));

				return false;
			}

			$products		  = $myFavoriteProductsModel->getItems();
			$productsAdjusted = array();

			$ids			 = array();
			$prices			 = array();
			$customerId		 = $app->getUserState('shop.customer_id', 0);
			$customerType	 = $app->getUserState('shop.customer_type', 'employee');
			$defaultCurrency = RedshopbHelperPrices::getCurrency($customerId, $customerType);

			foreach ($products as $product)
			{
				$ids[$product->id] = $product->quantity;
			}

			if (!empty($ids))
			{
				$prices				   = RedshopbHelperPrices::getProductsPrice(
					$ids,
					$customerId,
					$customerType,
					$currency		   = null,
					$collections	   = array(),
					$date			   = '',
					$endCustomer	   = 0,
					$quantity		   = null,
					$forceCollection   = false,
					$itemsWithQuantity = true
				);
			}

			$grandTotalPrice = 0;

			foreach ($products as $key => $product)
			{
				$productsAdjusted[$key]['product_id'] = $product->id;
				$productsAdjusted[$key]['name']       = $product->name;
				$productsAdjusted[$key]['sku']        = $product->sku;
				$productsAdjusted[$key]['attr_name']  = $product->attr_name;
				$productsAdjusted[$key]['quantity']   = $product->quantity;

				if (empty($prices[$product->id]))
				{
					$prices[$product->id]->price	   = 0;
					$prices[$product->id]->currency_id = $defaultCurrency;
				}

				$productsAdjusted[$key]['product_price'] = RedshopbHelperProduct::getProductFormattedPrice(
					$prices[$product->id]->price,
					$prices[$product->id]->currency_id
				);

				$rowTotal = (float) $prices[$product->id]->price * (float) $product->quantity;

				$productsAdjusted[$key]['total_price'] = RedshopbHelperProduct::getProductFormattedPrice(
					$rowTotal,
					$prices[$product->id]->currency_id
				);

				$grandTotalPrice += $rowTotal;

				$productsAdjusted[$key]['product_image'] = RedshopbHelperProduct::getProductImageThumbPath($product->id);
			}

			/** @var RedshopbModelMyfavoritelist $myFavoriteListModel */
			$myFavoriteListModel = $this->getModel();
			$table 				 = $myFavoriteListModel->getTable();

			if (!$table->load($favoriteListId))
			{
				$this->setMessage(Text::_('COM_REDSHOPB_MYFAVORITELIST_LIST_NOT_FOUND'), 'warning');

				$this->setRedirect($this->getRedirectToItemRoute($this->getRedirectToItemAppend($favoriteListId)));

				return false;
			}

			$myFavoriteListName  = $table->get('name');
			$myFavoriteListAlias = $table->get('alias');

			$arrayForPdf = array(
				'favourite_list_id'   => $favoriteListId,
				'favourite_list_name' => $myFavoriteListName,
				'products' 			  => $productsAdjusted,
				'grand_total_price'   => RedshopbHelperProduct::getProductFormattedPrice($grandTotalPrice, $defaultCurrency)
			);

			$productTableLayoutOptions = array(
				'favourite_list' => $arrayForPdf
			);

			JLoader::import('mpdf', JPATH_SITE . '/libraries/mpdf');

			ob_start();

			$options = [
				'format' => 'A4',
				'defaultFontSize' => 0,
				'headerMargin' => 15 * Mpdf\Mpdf::SCALE,
				'footerMargin' => 40 * Mpdf\Mpdf::SCALE,
				'showFooterImage' => false,
				'showHeaderImage' => false
			];

			$mpdf = RedshopbHelperMpdf::getInstance('', '', $options);

			$HTMLstart  = RedshopbLayoutHelper::render('myfavoritelists.producttablepdfstart', $productTableLayoutOptions);
			$HTMLheader = RedshopbLayoutHelper::render('myfavoritelists.producttablepdfheader', $productTableLayoutOptions);
			$HTMLend	= RedshopbLayoutHelper::render('myfavoritelists.producttablepdfend', $productTableLayoutOptions);
			$HTMLOutput = RedshopbLayoutHelper::render('myfavoritelists.producttablepdf', $productTableLayoutOptions);

			$footer = array (
				'odd' => array (
					'C' => array (
						'content' => Text::_('COM_REDSHOPB_MYFAVORITELIST_PAGE') . ' {PAGENO} / {nb}',
						'font-size' => 10,
						'font-style' => '',
						'font-family' => 'sans-serif',
						'color' => '#000000'
					),
					'line' => 0,
				),
				'even' => array ()
			);

			ob_clean();

			$filename = $myFavoriteListAlias . '-' . $favoriteListId . '.pdf';

			$mpdf->setAutoTopMargin	   = 'stretch';
			$mpdf->setAutoBottomMargin = 'stretch';
			$mpdf->SetHTMLHeader($HTMLheader);
			$mpdf->SetFooter($footer);
			$mpdf->WriteHTML($HTMLstart);
			$mpdf->WriteHTML($HTMLOutput);
			$mpdf->WriteHTML($HTMLend);

			$mpdf->Output($filename, 'D');

			$app->close();

			return true;
		}
		catch (Exception $exception)
		{
			$app->enqueueMessage($exception->getMessage(), 'warning');

			$this->setRedirect($this->getRedirectToItemRoute($this->getRedirectToItemAppend($favoriteListId)));

			return false;
		}
	}

	/**
	 * Method to save a record.
	 *
	 * @param   string $key    The name of the primary key of the URL variable.
	 * @param   string $urlVar The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since  1.13.0
	 */
	public function save($key = null, $urlVar = null)
	{
		if (!parent::save($key, $urlVar))
		{
			return false;
		}

		$quantities = $this->input->get('quantity', array(), 'array');

		if (!empty($quantities))
		{
			$model    = $this->getModel();
			$table    = $model->getTable('Favoritelist_Product_Xref');
			$recordId = $this->input->getInt('id');

			foreach ($quantities as $productId => $quantity)
			{
				if ($table->load(array('favoritelist_id' => $recordId, 'product_id' => $productId)))
				{
					if (!$table->save(array('quantity' => $quantity)))
					{
						$this->setError($table->getError());

						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Get Product List via AJAX
	 *
	 * @return void
	 */
	public function ajaxGetProductList()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$returnObject = $this->getReturnObject();
		$app          = Factory::getApplication();

		/** @var Input $input */
		$input     = $app->input;
		$favListId = $input->get('id', 0);

		/** @var RedshopbModelShop $shopModel */
		$shopModel = RModel::getFrontInstance('Shop');
		$context   = $shopModel->get('context');

		$listLimit = $app->getUserStateFromRequest(
			$context . '.com_redshopb_myfavoritelists_shop_limit',
			'com_redshopb_myfavoritelists_shop_limit',
			10,
			'int'
		);

		$listStart = $app->getUserStateFromRequest(
			$context . '.com_redshopb_myfavoritelists_shop_start',
			'com_redshopb_myfavoritelists_shop_start',
			0,
			'int'
		);

		$shopModel->getState();

		$shopModel->setState('list.limit', $listLimit);
		$shopModel->setState('list.start', $listStart);

		$includedIds     = array();
		$includedRecords = $this->getFavoriteProductList($favListId);

		foreach ($includedRecords AS $record)
		{
			$includedIds[] = $record->id;
		}

		$shopModel->setState('filter.excluded_products', $includedIds);
		$model = $this->getModel();
		$item  = $model->getItem($favListId);

		$layoutOptions = array
		(
			'shopState'  => $shopModel->getState(),
			'favId'      => $favListId,
			'item'       => $item,
			'products'   => $shopModel->getItems(),
			'pagination' => $shopModel->getPagination(),
		);

		$needChangeImpersonation = false;

		if ($app->getUserState('shop.customer_type') != 'employee'
			|| $app->getUserState('shop.customer_id') != $item->user_id
		)
		{
			$needChangeImpersonation = true;
		}

		if ($needChangeImpersonation)
		{
			$action             = RedshopbRoute::_('index.php?option=com_redshopb&view=myfavoritelist&layout=edit&id=' . $item->id, false);
			$returnObject->html = RedshopbLayoutHelper::render(
				'myfavoritelists.changeimpersonation',
				array(
					'return'        => base64_encode($action), 'userId' => $item->user_id,
					'displayHeader' => true
				)
			);
		}
		else
		{
			$returnObject->html = RedshopbLayoutHelper::render('myfavoritelists.ajaxaddproduct', $layoutOptions);
		}

		$returnObject->options = $layoutOptions;
		echo json_encode($returnObject);

		$app->close();
	}

	/**
	 * Get favorite product list.
	 *
	 * @param   integer   $favId   The favorite identifier
	 *
	 * @return mixed
	 */
	private function getFavoriteProductList($favId)
	{
		/** @var RedshopbModelMyfavoriteproducts $myFavoriteProductsModel */
		$myFavoriteProductsModel = RedshopbModel::getFrontInstance('Myfavoriteproducts', array('ignore_request' => true));
		$myFavoriteProductsModel->setState('filter.favorite_list_id_exclude', false);
		$myFavoriteProductsModel->setState('filter.favorite_list_id', $favId);
		$myFavoriteProductsModel->setState('disable_user_states', true);
		$myFavoriteProductsModel->setState('filter.include_available_collections', true);
		$products = $myFavoriteProductsModel->getItems();

		return $products;
	}

	/**
	 * Get return object
	 *
	 * @return stdClass
	 */
	private function getReturnObject()
	{
		$returnObject              = new stdClass;
		$returnObject->message     = '';
		$returnObject->messageType = '';

		return $returnObject;
	}

	/**
	 * Add product via AJAX
	 *
	 * @return void
	 */
	public function ajaxAddProduct()
	{
		RedshopbHelperAjax::validateAjaxRequest();
		$app          = Factory::getApplication();
		$input        = $app->input;
		$returnObject = $this->getReturnObject();

		$favId = $input->getInt('id', $input->getInt('fav_id', 0));

		if (empty($favId))
		{
			$returnObject->message     = Text::_('COM_REDSHOPB_MYFAVORITELIST_ERROR_INVALID_LIST_ID');
			$returnObject->messageType = "alert-error";
			header('HTTP/1.1 400 Bad Request');
			echo json_encode($returnObject);
			$app->close();
		}

		$productId = $input->getInt('product_id', 0);

		if (empty($productId))
		{
			$returnObject->message     = Text::_('COM_REDSHOPB_MYFAVORITELIST_ERROR_INVALID_PRODUCT_ID');
			$returnObject->messageType = "alert-error";

			header('HTTP/1.1 400 Bad Request');
			echo json_encode($returnObject);
			$app->close();
		}

		/** @var RedshopbModelMyfavoritelist $model */
		$model = $this->getModel('Myfavoritelist');

		$productItemId = $input->getInt('product_item_id', 0);

		if ($productItemId === 0)
		{
			if (!$model->addProduct($favId, $productId))
			{
				$returnObject->message     = Text::_('COM_REDSHOPB_MYFAVORITELIST_PRODUCT_ADDED_ERROR');
				$returnObject->messageType = "alert-error";

				header('HTTP/1.1 500 Internal Server Error');
				echo json_encode($returnObject);
				$app->close();
			}
		}
		else
		{
			$customerType  = $app->getUserState('shop.customer_type', '');
			$customerId    = $app->getUserState('shop.customer_id', 0);
			$useCollection = RedshopbHelperShop::inCollectionMode(
				RedshopbEntityCompany::getInstance(
					RedshopbHelperCompany::getCompanyIdByCustomer(
						$customerId, $customerType
					)
				)
			);

			if ($useCollection)
			{
				// Get the collection IDs that the current user is allowed to see
				$userVisibleCollections = RedshopbHelperCollection::getCustomerCollectionsForShop($customerId, $customerType);

				// Get the products of those collection(s)
				$db                    = Factory::getDbo();
				$collectionFilterQuery = $db->getQuery(true)
					->select('DISTINCT product_item_id')
					->from($db->qn('#__redshopb_collection_product_item_xref'))
					->where($db->qn('collection_id') . ' IN (' . implode(',', $userVisibleCollections) . ')');

				$collectionPermittedProductItemIds = $db->setQuery($collectionFilterQuery)->loadColumn();

				if (!in_array($productItemId, $collectionPermittedProductItemIds))
				{
					$returnObject->message     = Text::_('COM_REDSHOPB_MYFAVORITELIST_CANT_ADD_PRODUCT_ITEM_NOT_IN_YOUR_COLLECTIONS');
					$returnObject->messageType = "alert-error";

					header('HTTP/1.1 500 Internal Server Error');
					echo json_encode($returnObject);
					$app->close();
				}
			}

			if (!$model->addProductItem($favId, $productItemId))
			{
				$returnObject->message     = Text::_('COM_REDSHOPB_MYFAVORITELIST_PRODUCT_ITEM_ADDED_ERROR');
				$returnObject->messageType = "alert-error";

				header('HTTP/1.1 500 Internal Server Error');
				echo json_encode($returnObject);
				$app->close();
			}
		}

		$returnObject->message     = Text::_('COM_REDSHOPB_MYFAVORITELIST_PRODUCT_ADDED_SUCESSFULY');
		$returnObject->messageType = "alert-success";

		$returnObject->html = $this->getProductTableHtml($favId);

		echo json_encode($returnObject);
		$app->close();
	}

	/**
	 * Get product Html table
	 *
	 * @param   integer $favId  The favourite list identifier
	 *
	 * @return string
	 */
	private function getProductTableHtml($favId)
	{
		$input  = Factory::getApplication()->input;
		$model  = $this->getModel();
		$layout = $input->getCmd('layout', 'edit');
		$action = RedshopbRoute::_('index.php?option=com_redshopb&view=myfavoritelist&layout=' . $layout . '&id=' . $favId, false);

		$productTableLayoutOptions = array(
			'action'         => $action,
			'products'       => $this->getFavoriteProductList($favId),
			'item'           => $model->getItem($favId),
			'quantities'     => $input->get('quantity', array(), 'array'),
			'collection_ids' => $input->get('collection_id', array(), 'array'),
			'cart_prefix'    => $input->getCmd('cart_prefix', 'myFavoriteList')
		);

		return RedshopbLayoutHelper::render('myfavoritelists.producttable', $productTableLayoutOptions);
	}

	/**
	 * Method to update the producttable when quantity changes
	 *
	 * @return void
	 */
	public function ajaxUpdateProductTable()
	{
		RedshopbHelperAjax::validateAjaxRequest();
		$app          = Factory::getApplication();
		$input        = $app->input;
		$returnObject = $this->getReturnObject();
		$favId        = $input->getInt('id', $input->getInt('fav_id', 0));

		if (empty($favId))
		{
			$returnObject->message     = Text::_('COM_REDSHOPB_MYFAVORITELIST_ERROR_INVALID_LIST_ID');
			$returnObject->messageType = "alert-error";
			header('HTTP/1.1 400 Bad Request');
			echo json_encode($returnObject);
			$app->close();
		}

		$quantities = $input->get('quantity', array(), 'array');

		foreach ($quantities as $productId => $quantityInfo)
		{
			foreach ($quantityInfo as $productItemId => $quantity)
			{
				if ($productItemId == 0)
				{
					$favProdTable = RedshopbTable::getAdminInstance('Favoritelist_Product_Xref');

					$entryToUpdate['product_id']      = (int) $productId;
					$entryToUpdate['favoritelist_id'] = (int) $favId;
					$entryToUpdate['quantity']        = $quantity;

					if (!$favProdTable->save($entryToUpdate))
					{
						$returnObject->message     = Text::_('COM_REDSHOPB_MYFAVORITELIST_PRODUCT_UPDATE_ERROR');
						$returnObject->messageType = "alert-error";
						header('HTTP/1.1 400 Bad Request');
						echo json_encode($returnObject);
						$app->close();
					}
				}
				else
				{
					$favProdItemTable = RedshopbTable::getAdminInstance('Favoritelist_Product_Item_Xref');

					$entryToUpdate['product_item_id'] = (int) $productItemId;
					$entryToUpdate['favoritelist_id'] = (int) $favId;
					$entryToUpdate['quantity']        = $quantity;

					if (!$favProdItemTable->save($entryToUpdate))
					{
						$returnObject->message     = Text::_('COM_REDSHOPB_MYFAVORITELIST_PRODUCT_ITEM_UPDATE_ERROR');
						$returnObject->messageType = "alert-error";
						header('HTTP/1.1 400 Bad Request');
						echo json_encode($returnObject);
						$app->close();
					}
				}
			}
		}

		$returnObject->html = $this->getProductTableHtml($favId);

		echo json_encode($returnObject);
		$app->close();
	}

	/**
	 * Method for add a product to favorite list.
	 *
	 * @return  boolean
	 */
	public function addProduct()
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$input          = Factory::getApplication()->input;
		$jForm          = $input->get('jform', array(), 'array');
		$favoriteListId = $input->getInt('favid', 0);

		if (isset($jForm['redshopb_favlist']) && $jForm['redshopb_favlist'] && $favoriteListId)
		{
			$model = $this->getModel('Myfavoritelist');

			if ($model->addProduct($favoriteListId, $jForm['redshopb_favlist']))
			{
				$this->setMessage(Text::_('COM_REDSHOPB_MYFAVORITELIST_PRODUCT_ADDED_SUCESSFULY'));
			}
			else
			{
				$this->setMessage(Text::_('COM_REDSHOPB_MYFAVORITELIST_PRODUCT_ADDED_ERROR'), 'error');
			}

			$this->setRedirect(
				$this->getRedirectToItemRoute($this->getRedirectToItemAppend($favoriteListId))
			);
		}
		else
		{
			$this->setMessage(Text::_('COM_REDSHOPB_MYFAVORITELIST_NO_PRODUCT_SELECT_ERROR'), 'error');
		}

		if ($favoriteListId)
		{
			$this->setRedirect(
				$this->getRedirectToItemRoute($this->getRedirectToItemAppend($favoriteListId))
			);
		}
		else
		{
			$this->setRedirect(
				$this->getRedirectToListRoute()
			);
		}

		return true;
	}

	/**
	 * Method for requesting offer for  favorite list.
	 *
	 * @return  boolean
	 */
	public function requestOffer()
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$redshopbConfig = RedshopbEntityConfig::getInstance();
		$enableOffer    = $redshopbConfig->getInt('enable_offer', 1);
		$input          = Factory::getApplication()->input;
		$fav            = $this->getFavData();
		$favoriteListId = $input->getInt('id', 0);

		if (!$enableOffer)
		{
			$this->setMessage(Text::_('COM_REDSHOPB_OFFER_DISABLED'), 'error');

			$this->setRedirect(
				$this->getRedirectToItemRoute($this->getRedirectToItemAppend($favoriteListId))
			);

			return false;
		}

		if (empty($fav))
		{
			$this->setMessage(Text::_('COM_REDSHOPB_ERROR_NOPRODUCT_FAVORITELIST'), 'error');
			$this->setRedirect(
				$this->getRedirectToItemRoute($this->getRedirectToItemAppend($favoriteListId))
			);

			return false;
		}

		/** @var RedshopbModelOffer $model */
		$model = RedshopbModel::getAutoInstance('Offer');

		if (!$model->requestOfferForFavList($favoriteListId, $fav))
		{
			$this->setMessage($model->getError(), 'error');

			$this->setRedirect(
				$this->getRedirectToItemRoute($this->getRedirectToItemAppend($favoriteListId))
			);

			return false;
		}

		$this->setRedirect(
			RedshopbRoute::_('index.php?option=com_redshopb&view=myoffers', false),
			Text::_('COM_REDSHOPB_OFFER_REQUEST_PLACED')
		);

		return true;
	}

	/**
	 * Temporary method to convert the new favorites form format to legacy data format
	 * This should be removed when RedshopbModelOffer::requestOfferForFavList method has been updated to use
	 * input arrays as the source for the list I.E. <input name="quantity[$product->id]/> when submitting forms
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	private function getFavData()
	{
		$favData               = array();
		$favData['product_id'] = array();

		$input                    = Factory::getApplication()->input;
		$quantities               = $input->get('quantity', array(), 'array');
		$favData['collection_id'] = $input->get('collection_id', array(), 'array');

		foreach ($quantities AS $productId => $quantity)
		{
			$favData['product_id'][] = (int) $productId;
			$favData['quantity'][]   = (int) $quantity;
		}

		return $favData;
	}

	/**
	 * Method for remove a reference of a product from favorite list.
	 *
	 * @return  void
	 */
	public function ajaxRemoveProductInList()
	{
		RedshopbHelperAjax::validateAjaxRequest();
		$app   = Factory::getApplication();
		$input = $app->input;

		$returnObject = $this->getReturnObject();

		$favId         = $input->getInt('id', $input->getInt('fav_id', 0));
		$productId     = $input->getInt('product_id');
		$productItemId = $input->getInt('product_item_id', 0);

		if (empty($favId))
		{
			$returnObject->message     = Text::_('COM_REDSHOPB_MYFAVORITELIST_ERROR_INVALID_LIST_ID');
			$returnObject->messageType = 'alert-error';

			header('HTTP/1.1 400 Bad Request');
			echo json_encode($returnObject);
			$app->close();
		}

		if (empty($productId))
		{
			$returnObject->message     = Text::_('COM_REDSHOPB_MYFAVORITELIST_ERROR_INVALID_PRODUCT_ID');
			$returnObject->messageType = "alert-error";

			header('HTTP/1.1 400 Bad Request');
			echo json_encode($returnObject);
			$app->close();
		}

		/** @var RedshopbModelMyfavoritelist $model */
		$model = $this->getModel('Myfavoritelist');

		if ($productItemId === 0)
		{
			if (!$model->removeSingleProduct($favId, $productId))
			{
				$returnObject->message     = Text::_('COM_REDSHOPB_MYFAVORITELIST_PRODUCT_REMOVE_ERROR');
				$returnObject->messageType = "alert-error";

				header('HTTP/1.1 500 Internal Server Error');
				echo json_encode($returnObject);
				$app->close();
			}
		}
		else
		{
			if (!$model->removeSingleProductItem($favId, $productItemId))
			{
				$returnObject->message     = Text::_('COM_REDSHOPB_MYFAVORITELIST_PRODUCT_REMOVE_ERROR');
				$returnObject->messageType = "alert-error";

				header('HTTP/1.1 500 Internal Server Error');
				echo json_encode($returnObject);
				$app->close();
			}
		}

		$returnObject->message     = Text::_('COM_REDSHOPB_MYFAVORITELIST_REMOVED_SUCCESSFULLY');
		$returnObject->messageType = 'alert-success';

		$returnObject->html = $this->getProductTableHtml($favId);

		echo json_encode($returnObject);
		$app->close();
	}

	/**
	 * Get price condition
	 *
	 * @return array
	 */
	protected function getPriceCondition()
	{
		$app              = Factory::getApplication();
		$input            = $app->input;
		$productId        = $input->getInt('productId', $input->getInt('product_id', 0));
		$quantities       = $input->get('quantity', array(), 'array');
		$myFavoriteListId = $input->getInt('favId', $input->getInt('id', 0));
		$collectionId     = $input->get('collection_id', array(), 'array');

		$myFavoriteListModel        = $this->getModel('Myfavoritelist');
		$prices                     = $myFavoriteListModel->getProductPrice(
			$myFavoriteListId, $productId, $collectionId[$productId], $quantities[$productId]
		);
		$prices['currencyTotal']    = $input->getFloat('total', 0) + ($prices['price'] * $quantities[$productId]);
		$prices['myFavoriteListId'] = $myFavoriteListId;
		$prices['productId']        = $productId;

		return $prices;
	}

	/**
	 * Get ajax price condition
	 *
	 * @return  void
	 */
	public function ajaxPriceCondition()
	{
		RedshopbHelperAjax::validateAjaxRequest();
		$app                                      = Factory::getApplication();
		$priceCondition                           = $this->getPriceCondition();
		$priceCondition['priceFormatted']         = RedshopbHelperProduct::getProductFormattedPrice(
			$priceCondition['price'],
			$priceCondition['currency']
		);
		$priceCondition['finalPriceFormatted']    = RedshopbHelperProduct::getProductFormattedPrice(
			$priceCondition['subtotal'],
			$priceCondition['currency']
		);
		$priceCondition['currencyTotalFormatted'] = RedshopbHelperProduct::getProductFormattedPrice(
			$priceCondition['currencyTotal'],
			$priceCondition['currency']
		);

		echo json_encode($priceCondition);

		$app->close();
	}

	/**
	 * Method to render the attributes for a product
	 *
	 * @return  void
	 */
	public function ajaxGetAttributeInputs()
	{
		$app            = Factory::getApplication();
		$productId      = $app->input->getInt('productId', 0);
		$responseObject = new RedshopbAjaxResponse;

		if (empty($productId))
		{
			header('HTTP/1.1 400 Bad Request');
			$responseObject->setMessage('COM_REDSHOPB_SHOP_DELIVERY_NOT_SET', true);
			$responseObject->setMessageType('alert-error');

			echo json_encode($responseObject);
			$app->close();
		}

		$productEntity = RedshopbEntityProduct::getInstance($productId);
		$attributes    = $productEntity->getAttributes();

		$responseObject->setBody(RedshopbLayoutHelper::render('myfavoritelists.product_attributes', array('product' => $productEntity, 'attributes' => $attributes)));

		echo json_encode($responseObject);
		$app->close();
	}

	/**
	 * Ajax method to get the product item based on selected attribute values
	 *
	 * @return  void
	 */
	public function ajaxGetProductItem()
	{
		$app             = Factory::getApplication();
		$attributeValues = $app->input->get('attributeValues', array());
		$result          = new stdClass;

		$productItem           = RedshopbEntityProduct_Item::getInstanceByAttributeValues($attributeValues);
		$productItemId         = $productItem->get('id');
		$result->productItemId = $productItemId;

		echo json_encode($result);
		$app->close();
	}
}
