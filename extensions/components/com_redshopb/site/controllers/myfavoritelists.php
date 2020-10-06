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
use Joomla\CMS\Document\HtmlDocument;

/**
 * Favoritelists Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.6
 */
class RedshopbControllerMyFavoriteLists extends RedshopbControllerAdmin
{
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

		$input           = Factory::getApplication()->input;
		$favoriteListIds = $input->get('cid', array(), 'array');

		if (empty($favoriteListIds))
		{
			$this->setMessage(Text::_('COM_REDSHOPB_MYFAVORITELIST_LIST_NOT_FOUND', 'warning'));
			$this->setRedirect($this->getRedirectToListRoute());

			return false;
		}

		$favoriteListId = reset($favoriteListIds);
		$model          = $this->getModel('Myfavoritelist');
		$table          = $model->getTable();

		if (!$table->load($favoriteListId))
		{
			$this->setMessage(Text::_('COM_REDSHOPB_MYFAVORITELIST_LIST_NOT_FOUND'), 'warning');
			$this->setRedirect($this->getRedirectToListRoute());

			return false;
		}

		$products     = $model->getProducts($favoriteListId, true);
		$productItems = $model->getProductItems($favoriteListId, true);

		if (!$products && !$productItems)
		{
			$this->setMessage(Text::_('COM_REDSHOPB_MYFAVORITELIST_LIST_EMPTY'), 'warning');
			$this->setRedirect($this->getRedirectToListRoute());

			return false;
		}

		// Clear cart sessions
		if (RedshopbApp::getConfig()->getBool('clean_cart_when_add_from_favourite_list', true))
		{
			// Clear cart sessions
			RedshopbHelperCart::clearCartFromSession();
		}

		$app          = Factory::getApplication();
		$customerId   = $app->getUserState('shop.customer_id', 0);
		$customerType = $app->getUserState('shop.customer_type', '');
		$currency     = RedshopbHelperPrices::getCurrency($customerId, $customerType);

		foreach ($products as $oneProduct)
		{
			$productPrice = RedshopbHelperPrices::getProductPrice(
				$oneProduct->id, $customerId, $customerType, $currency, array(), '', 0, $oneProduct->quantity
			);
			$collectionId = null;

			if (!empty($productPrice->wid))
			{
				$collectionId = $productPrice->wid;
			}

			RedshopbHelperCart::addToCartById(
				$oneProduct->id,
				null,
				null,
				$oneProduct->quantity,
				$productPrice ? $productPrice->price : 0.0,
				$productPrice ? $productPrice->currency : '',
				$customerId,
				$customerType,
				$collectionId
			);
		}

		foreach ($productItems as $oneProductItem)
		{
			$productPrice = RedshopbHelperPrices::getProductItemPrice(
				$oneProductItem->id, $customerId, $customerType, 'DKK', array(), '', 0, $oneProductItem->quantity
			);
			$collectionId = null;

			if (!empty($productPrice->wid))
			{
				$collectionId = $productPrice->wid;
			}

			RedshopbHelperCart::addToCartById(
				$oneProductItem->product_id,
				$oneProductItem->id,
				null,
				$oneProductItem->quantity,
				$productPrice ? $productPrice->price : 0.0,
				$productPrice ? $productPrice->currency : '',
				$customerId,
				$customerType,
				$collectionId
			);
		}

		$myFavListRedirect = RedshopbEntityConfig::getInstance()->getInt('my_favorite_list_checkout', '1');

		if ($myFavListRedirect == 0)
		{
			$this->setMessage(Text::_('COM_REDSHOPB_MYFAVORITELIST_LIST_ADD_TO_CART_SUCESSFULLY'));
			$this->setRedirect(
				RedshopbRoute::_('index.php?option=com_redshopb&view=myfavoritelists', false)
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
	 * Get favorite lists via AJAX
	 *
	 * @return void
	 */
	public function ajaxLists()
	{
		RedshopbHelperAjax::validateAjaxRequest();
		$layoutOptions = $this->getListLayoutOptions('myfavoritelists');

		$returnObject       = $this->getReturnObject();
		$returnObject->html = RedshopbLayoutHelper::render('myfavoritelists.lists', $layoutOptions);

		/** @var HtmlDocument $document */
		$document            = Factory::getDocument();
		$returnObject->html .= '<script type="text/javascript">'
			. $document->getHeadData()['script']['text/javascript']
			. '</script>';

		echo json_encode($returnObject);

		$app = Factory::getApplication();
		$app->close();
	}

	/**
	 * Method to get favoritelist layout layout options
	 *
	 * @param   string  $formName    form name
	 * @param   int     $showShared  toggle shared lists
	 *
	 * @return array
	 */
	private function getListLayoutOptions($formName, $showShared = 0)
	{
		/** @var RedshopbModelMyfavoritelists $model */
		$model = RModelAdmin::getInstance('Myfavoritelists', 'RedshopbModel');
		$model->getState();
		$model->setState('filter.showshared', $showShared);

		$model->set('filterFormName', 'filter_' . $formName);
		$model->set('limitField', $formName . '_limit');

		$layoutOptions = array(
			'state' => $model->getState(),
			'listOrder' => $model->getState('list.ordering'),
			'listDirn' => $model->getState('list.direction'),
			'items' => $model->getItems(),
			'pagination' => $model->getPagination(),
			'filter_form'   => $model->getForm(),
			'activeFilters' => $model->getActiveFilters(),
			'formName' => $formName,
			'action' => 'index.php?option=com_redshopb&view=myfavoritelists',
			'itemLink' => 'index.php?option=com_redshopb&task=myfavoritelist.edit',
			'isManage' => true,
			'showToolbar' => true
		);

		return $layoutOptions;
	}

	/**
	 * Get Shared lists via AJAX
	 *
	 * @return void
	 */
	public function ajaxGetSharedList()
	{
		RedshopbHelperAjax::validateAjaxRequest();
		$layoutOptions                = $this->getListLayoutOptions('sharedfavoritelists', 1);
		$layoutOptions['showToolbar'] = false;
		$layoutOptions['itemLink']    = 'index.php?option=com_redshopb&view=myfavoritelist&layout=item';

		$returnObject       = $this->getReturnObject();
		$returnObject->html = RedshopbLayoutHelper::render('myfavoritelists.lists', $layoutOptions);

		/** @var HTMLDocument $document */
		$document            = Factory::getDocument();
		$returnObject->html .= '<script type="text/javascript">'
			. $document->getHeadData()['script']['text/javascript']
			. '</script>';

		echo json_encode($returnObject);

		$app = Factory::getApplication();
		$app->close();
	}

	/**
	 * Delete method for My Favorite Lists
	 *
	 * @return void
	 *
	 * @since   1.1
	 */
	public function delete()
	{
		$id = $this->input->get('id', 0, 'int');

		if (!$this->getModel('MyFavoriteList')->deleteOwnList($id))
		{
			Factory::getApplication()->enqueueMessage(
				Text::_('COM_REDSHOPB_MYFAVORITELIST_ERROR_FAVORITELIST_DELETE'),
				'error'
			);
		}
		else
		{
			Factory::getApplication()->enqueueMessage(
				Text::_('COM_REDSHOPB_MYFAVORITELIST_VIEW_FAVORITELIST_DELETED_SUCESSFULY')
			);
		}

		$this->setRedirect($this->getRedirectToListRoute());
	}
}
