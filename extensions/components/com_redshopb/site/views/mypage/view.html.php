<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Pagination\Pagination;
/**
 * My Page View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewMyPage extends RedshopbView
{
	/**
	 * @var  array
	 */
	public $items;

	/**
	 * @var  object
	 */
	public $state;

	/**
	 * @var  Pagination
	 */
	public $pagination;

	/**
	 * @var  Form
	 */
	public $filterForm;

	/**
	 * @var array
	 */
	public $activeFilters;

	/**
	 * @var array
	 */
	public $stoolsOptions = array();

	/**
	 * @var array
	 */
	public $categories = array();

	/**
	 * @var integer
	 */
	public $categoriesCount;

	/**
	 * @var boolean
	 */
	public $collectionMode = false;

	/**
	 * Do we have to display a sidebar?
	 *
	 * @var  boolean
	 */
	protected $displaySidebar = false;

	/**
	 * @var  array
	 */
	protected $collections = array();

	/**
	 * @var object
	 */
	public $recentlyPurchPag;

	/**
	 * @var mixed
	 */
	public $recentlyPurchProds;

	/**
	 * @var mixed
	 */
	public $productState;

	/**
	 * @var object
	 */
	public $productPagination;

	/**
	 * @var array
	 */
	public $mostPurchasedProds;

	/**
	 * @var boolean
	 */
	public $isFromMainCompany = false;

	/**
	 * @var null
	 */
	public $rsbUser = null;

	/**
	 * @var  string
	 */
	public $customerType = '';

	/**
	 * @var  integer
	 */
	public $customerId = 0;

	/**
	 * @var  integer
	 */
	public $rsbUserId = null;

	/**
	 * @var  string
	 */
	public $impersonating = '';

	/**
	 * @var boolean
	 *
	 * @since 2.4.0
	 */
	public $enableQuickOrder = true;

	/**
	 * Display method.
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$layout      = $this->getLayout();
		$userCompany = RedshopbHelperUser::getUserCompany(Factory::getUser()->id, 'joomla');

		$values = new stdClass;
		RedshopbHelperShop::setUserStates($values);

		$this->customerId   = $values->customerId;
		$this->customerType = $values->customerType;
		$this->rsbUserId    = $values->rsbUserId;

		$vanirUser   = RedshopbHelperUser::getUser();
		$vanirUserId = 0;

		if (!is_null($vanirUser))
		{
			$vanirUserId = $vanirUser->id;
		}

		// Get impersonating entity
		if (!empty($this->customerType) && ($this->customerType != 'employee' || $this->rsbUserId != $vanirUserId))
		{
			$this->impersonating = Text::_('COM_REDSHOPB_' . $this->customerType) . ' ' .
				RedshopbHelperShop::getCustomerEntity($this->customerId, $this->customerType)->name;
		}

		if ($this->rsbUserId)
		{
			$this->isFromMainCompany = RedshopbHelperUser::isFromMainCompany($this->rsbUserId, 'employee');
		}

		if (empty($this->impersonating) && RedshopbEntityUser::loadActive(true)->getRole()->getType()->get('type') === 'sales')
		{
			$this->enableQuickOrder = (new RedshopbDatabaseProductsearch(array('useSimpleSearch' => true)))->getProductCount() > 0;
		}

		switch ($layout)
		{
			case 'default':
			case 'orders':
				$productModel       = RedshopbModel::getAutoInstance('Products');
				$this->productState = $productModel->getState();
				$this->productState->set('filter.include_categories', false);
				$this->productState->set('filter.include_tags', false);

				$model         = RedshopbModel::getAutoInstance('Orders');
				$mainCompanyId = RedshopbHelperCompany::getMain()->id;

				if ($values->companyId == $mainCompanyId)
				{
					$model->setState('filter.customer_company', null);
				}
				elseif ($values->companyId)
				{
					$model->setState('filter.customer_company', $values->companyId);
				}

				if ($values->departmentId)
				{
					$model->setState('filter.customer_department', $values->departmentId);
				}

				if ($values->userRSid == $values->rsbUserId && $values->canImpersonate == 1 && $values->superUser != 1)
				{
					$model->setState('filter.user_id', null);
				}
				elseif ($values->rsbUserId)
				{
					$model->setState('filter.user_id', $values->rsbUserId);
				}

				$this->state = $model->getState();
				$this->productState->set('list.limit', 8);

				$this->pagination        = $model->getPagination();
				$this->items             = $model->getItems();
				$this->productPagination = $productModel->getPagination();

				$this->filterForm                   = $model->getForm();
				$this->activeFilters                = $model->getActiveFilters();
				$this->stoolsOptions['searchField'] = 'search_orders';
				$this->filterForm->removeField('s_company', 'filter');
				$this->filterForm->removeField('customer_type', 'filter');
				$this->filterForm->removeField('order_status', 'filter');

				$this->loadRecentlyPurchasedProducts();
				$this->loadMostPurchasedProducts();
				break;

			case 'collections':

				if (!RedshopbHelperShop::inCollectionMode(
					RedshopbEntityCompany::getInstance(
						RedshopbHelperCompany::getCompanyIdByCustomer($values->customerId, $values->customerType)
					)
				)
				)
				{
					$this->collectionMode = true;
					$this->collections    = RedshopbHelperCollection::getUserCollections(true);
				}
				else
				{
					$this->collections     = RedshopbHelperCollection::getCustomerCollectionsForShop($values->customerId, $values->customerType);
					$categoriesPerPage     = RedshopbApp::getConfig()->get('shop_categories_per_page', 12);
					$this->categories      = (array) RedshopbHelperCategory::getCustomerCategories(
						1, $this->collections, $userCompany->id, 'objectList', 0, $categoriesPerPage
					);
					$this->categoriesCount = (int) RedshopbHelperCategory::getCustomerCategories(1, $this->collections, $userCompany->id, 'count');
				}

				break;

			default:

				break;
		}

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return Text::_('COM_REDSHOPB_MYPAGE');
	}

	/**
	 *  Method to load a list of recently purchased products
	 *
	 * @return void
	 */
	protected function loadRecentlyPurchasedProducts()
	{
		// For recently purchases products
		/** @var RedshopbModelRecent_Purchased_Products $recentlyPurchasesModel */
		$recentlyPurchModel = RedshopbModel::getInstance('Recent_Purchased_Products', 'RedshopbModel');
		$recentlyPurchProds = $recentlyPurchModel->getItems();

		$userStateValues = new stdClass;
		RedshopbHelperShop::setUserStates($userStateValues);

		if (empty($recentlyPurchProds) || empty($userStateValues->customerId))
		{
			$this->recentlyPurchProds = array();
			$this->recentlyPurchPag   = null;

			return;
		}

		$this->recentlyPurchPag = $recentlyPurchModel->getPagination();

		$sortProducts = array();

		foreach ($recentlyPurchProds as $product)
		{
			$sortProducts[$product->id] = $product;
		}

		/** @var RedshopbModelShop $shopModel */
		$shopModel = RModelAdmin::getInstance('Shop', 'RedshopbModel', array('ignore_request' => true));
		RedshopbHelperProduct::setProduct($sortProducts);
		$preparedItems              = $shopModel->prepareItemsForShopView(
			$sortProducts,
			$userStateValues->customerId,
			$userStateValues->customerType,
			0,
			true
		);
		$preparedItems->productData = $sortProducts;
		$this->recentlyPurchProds   = $preparedItems;
	}

	/**
	 *  Method to load a list of most purchased products
	 *
	 * @return void
	 */
	protected function loadMostPurchasedProducts()
	{
		// For most purchased products
		$redshopbConfig = RedshopbApp::getConfig();

		if ($redshopbConfig->get('recent_purchased_list_limit') > 0)
		{
			$limit = $redshopbConfig->get('recent_purchased_list_limit');
		}
		else
		{
			$limit = Factory::getApplication()->get('list_limit');
		}

		$mostPurchasedProds = RedshopbHelperProduct::getMostPurchased($limit);
		$userStateValues    = new stdClass;
		RedshopbHelperShop::setUserStates($userStateValues);

		if (empty($mostPurchasedProds) || empty($userStateValues->customerId))
		{
			$this->mostPurchasedProds = array();

			return;
		}

		$sortProducts = array();

		foreach ($mostPurchasedProds as $product)
		{
			$sortProducts[$product->id] = $product;
		}

		/** @var RedshopbModelShop $shopModel */
		$shopModel = RModelAdmin::getInstance('Shop', 'RedshopbModel', array('ignore_request' => true));
		RedshopbHelperProduct::setProduct($sortProducts);
		$preparedItems              = $shopModel->prepareItemsForShopView(
			$sortProducts,
			$userStateValues->customerId,
			$userStateValues->customerType,
			0,
			true
		);
		$preparedItems->productData = $sortProducts;
		$this->mostPurchasedProds   = $preparedItems;

	}
}
