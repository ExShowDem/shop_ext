<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\SiteApplication;

/**
 * Product View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewShop extends RedshopbView
{
	/**
	 * Do we have to display a sidebar?
	 *
	 * @var  boolean
	 */
	protected $displaySidebar = false;

	/**
	 * Model state.
	 *
	 * @var  object
	 */
	public $state;

	/**
	 * Filter for shop search.
	 *
	 * @var object
	 */
	public $filterShopName;

	/**
	 * Departments paths.
	 *
	 * @var array
	 */
	public $departmentPath = array();

	/**
	 * Selected company id.
	 *
	 * @var integer
	 */
	public $companyId = 0;

	/**
	 * Selected department id.
	 *
	 * @var integer
	 */
	public $departmentId = 0;

	/**
	 * redSHOPB2B user id.
	 *
	 * @var integer
	 */
	public $rsbUserId = 0;

	/**
	 * Customer type. It can be 'employee', 'department' or 'company'.
	 *
	 * @var string
	 */
	public $customerType = '';

	/**
	 * Customer id. Depends on selected customer type.
	 *
	 * @var integer
	 */
	public $customerId = 0;

	/**
	 * List of companies for display using current logged in user and current selected company.
	 *
	 * @var array|mixed
	 */
	public $companies = array();

	/**
	 * List of all companies for display using current logged in user.
	 *
	 * @var array|mixed
	 */
	public $companiesAll = array();

	/**
	 * List of departments for display using current logged in user.
	 *
	 * @var array|mixed
	 */
	public $departments = array();

	/**
	 * List of employees for display using current logged in user.
	 *
	 * @var array|mixed
	 */
	public $employees = array();

	/**
	 * Filter form.
	 *
	 * @var object
	 */
	public $filterForm;

	/**
	 * List of active filters.
	 *
	 * @var array
	 */
	public $activeFilters = array();

	/**
	 * Search tool options array.
	 *
	 * @var array
	 */
	public $stoolsOptions = array();

	/**
	 * Product static types.
	 *
	 * @var array
	 */
	public $staticTypes = array();

	/**
	 * Product dynamic types.
	 *
	 * @var array
	 */
	public $dynamicTypes = array();

	/**
	 * Checkout fields.
	 *
	 * @var object
	 */
	public $checkoutFields;

	/**
	 * Customer object for checkout display.
	 *
	 * @var object
	 */
	public $customer;

	/**
	 * End customer object for checkout display.
	 *
	 * @var object
	 */
	public $endCustomer;

	/**
	 * Delivery address object for checkout display.
	 *
	 * @var object
	 */
	public $deliveryAddress;

	/**
	 * Delivery address id.
	 *
	 * @var integer
	 */
	public $deliveryAddressId = 0;

	/**
	 * Order comment.
	 *
	 * @var string
	 */
	public $comment;

	/**
	 * Order Payment name.
	 *
	 * @var string
	 */
	public $paymentName;

	/**
	 * Delay Order Payment name.
	 *
	 * @var string
	 */
	public $paymentDelayName;

	/**
	 * Order Payment Title.
	 *
	 * @var string
	 */
	public $paymentTitle;

	/**
	 * Order Payment Title.
	 *
	 * @var string
	 */
	public $paymentDelayTitle;

	/**
	 * Order Shipping rate
	 *
	 * @var string
	 */
	public $shippingRateId;

	/**
	 * @var string
	 */
	public $shippingRateIdDelay;

	/**
	 * @var integer
	 */
	public $stockroomPickupIdDelay;

	/**
	 * Order Shipping Title.
	 *
	 * @var string
	 */
	public $shippingRateTitle;

	/**
	 * @var integer
	 */
	public $stockroomPickupId;

	/**
	 * @var mixed
	 */
	public $stockroomPickupTitle;

	/**
	 * Order requisition.
	 *
	 * @var string
	 */
	public $requisition;

	/**
	 * @var array
	 */
	public $shippingDate = array();

	/**
	 * Product item variables.
	 *
	 * @var array
	 */
	public $dropDownTypes;

	/**
	 * @var mixed
	 */
	public $issetDynamicVariants;

	/**
	 * @var array
	 */
	public $issetItems;

	/**
	 * @var array
	 */
	public $dropDownSelected = array();

	/**
	 * Fields per collection.
	 *
	 * @var array|boolean
	 */
	public $collections = array();

	/**
	 * @var array
	 */
	public $collectionItems = array();

	/**
	 * @var array
	 */
	public $collectionProducts = array();

	/**
	 * @var array
	 */
	public $collectionDDTypes = array();

	/**
	 * @var array
	 */
	public $collectionSTypes = array();

	/**
	 * @var array
	 */
	public $collectionDTypes = array();

	/**
	 * @var array
	 */
	public $collectionIssetItems = array();

	/**
	 * @var array
	 */
	public $collectionPImages = array();

	/**
	 * @var array
	 */
	public $collectionIDVariants = array();

	/**
	 * @var array
	 */
	public $collectionAcc = array();

	/**
	 * @var array
	 */
	public $collectionSStockAs = array();

	/**
	 * @var array
	 */
	public $collectionPrices = array();

	/**
	 * @var array
	 */
	public $collectionDDSelected = array();

	/**
	 * @var integer
	 */
	public $collectionId = 0;

	/**
	 * Product images.
	 *
	 * @var array
	 */
	public $productImages = array();

	/**
	 * Prepared Payment data
	 *
	 * @var object|array
	 */
	public $paymentData;

	/**
	 * Check if order is completed under checkout. Are we ready to display an invoice?
	 *
	 * @var boolean
	 */
	public $orderPlaced = false;

	/**
	 * List of order items from checkout process.
	 *
	 * @var array
	 */
	public $orderItems;

	/**
	 * Products prices array.
	 *
	 * @var array
	 */
	public $prices = array();

	/**
	 * List of product accessories.
	 *
	 * @var array
	 */
	public $accessories = array();

	/**
	 * CSS class selector for stock status.
	 *
	 * @var string
	 */
	public $showStockAs = '';

	/**
	 * Can user impersonate others?
	 *
	 * @var boolean|object
	 */
	public $canImpersonate = false;

	/**
	 * wash and care items
	 *
	 * @var array
	 */
	public $items;

	/**
	 * wash and care product item
	 *
	 * @var array
	 */
	public $washProductItem;

	/**
	 * Current user RS id.
	 *
	 * @var integer
	 */
	public $userRSid = 0;

	/**
	 * Current customer parent company.
	 *
	 * @var string
	 */
	public $customerAt = '';

	/**
	 * Order vendor.
	 *
	 * @var object
	 */
	public $orderVendor;

	/**
	 * Current shop vendor (customer company).
	 *
	 * @var integer
	 */
	public $vendor;

	/**
	 * Current shop vendor (object company for vendor).
	 *
	 * @var object
	 */
	public $vendorCompany;

	/**
	 * Shopper (shop customers)
	 *
	 * @var object|array
	 */
	public $shopCustomers;

	/**
	 * Companies side menu
	 *
	 * @var  RMenu
	 */
	public $menu;

	/**
	 * Aesir E-Commerce User data
	 *
	 * @var stdClass
	 */
	public $user;

	/**
	 * It can manage addresses of the company
	 *
	 * @var boolean
	 */
	protected $companyAddressManage = false;

	/**
	 * It can manage addresses of the department
	 *
	 * @var boolean
	 */
	protected $departmentAManage = false;

	/**
	 * It can manage (create) its own addresses
	 *
	 * @var boolean
	 */
	protected $ownAddressManage = false;

	/**
	 * @var int Current shop view employees count
	 */
	public $employeesCount = 0;

	/**
	 * @var int Current shop view departments count
	 */
	public $departmentsCount = 0;

	/**
	 * @var int Current shop view companies count
	 */
	public $companiesCount = 0;

	/**
	 * @var int Current shop view categories count
	 */
	public $categoriesCount = 0;

	/**
	 * @var int|double Current shop view category products count
	 */
	public $productsCount = 0;

	/**
	 * @var array|int count of employees found in the next level of each company listed
	 */
	public $subEmployeesCount = 0;

	/**
	 * @var array|int count of departments found in the next level of each company listed
	 */
	public $subDepartmentsCount;

	/**
	 * @var array|int count of child companies found in the next level of each company listed
	 */
	public $subCompaniesCount;

	/**
	 * @var array Shop categories
	 */
	public $categories = array();

	/**
	 * @var object Category object for shop display.
	 */
	public $category;

	/**
	 * @var null|int|RedshopbEntityBase
	 */
	public $manufacturer;

	/**
	 * @var RedshopbEntityConfig  Component configuration
	 */
	public $componentConfig;

	/**
	 * @var null|object Product for single display
	 */
	public $product;

	/**
	 * @var boolean  Defines if the logged in user can add products to cart or not (if no order.place permission is given at all)
	 */
	public $placeOrderPermission = true;

	/**
	 * @var array  List of Payment methods available for this user
	 */
	public $paymentMethods = array();

	/**
	 * @var bool  Defines if we are using payments Layout or not
	 */
	public $usingPayments = false;

	/**
	 * @var array  List of Shipping methods available for this user
	 */
	public $shippingMethods = array();

	/**
	 * @var bool  Defines if we are using shipping Layout or not
	 */
	public $usingShipping = false;

	/**
	 * @var object  Tag table record
	 */
	public $tagRecord;

	/**
	 * @var integer  Number of product per page.
	 */
	public $productsPerPage = 0;

	/**
	 * @var array
	 */
	public $customerOrder = array();

	/**
	 * @var object  Company data of vendor company of current customer.
	 */
	public $customerVendor;

	/**
	 * @var object  Company data of manufacturer if viewing product list page.
	 */
	public $productLManufacturer;

	/**
	 * @var int  Max products within collections
	 */
	public $maxPWCollections = 0;

	/**
	 * @var  RModal  Terms and conditions modal for rendering on checkout.
	 */
	public $termsModal = '';

	/**
	 * @var  string  Page heading for SEO purpose.
	 */
	public $pageHeading = '';

	/**
	 * @var  array  Product attributes for render.
	 */
	public $productAttributes;

	/**
	 * @var string
	 */
	public $customerCType = '';

	/**
	 * @var null|object
	 */
	public $orderEmployee;

	/**
	 * @var null|object
	 */
	public $orderDepartment;

	/**
	 * @var null|object
	 */
	public $orderCompany;

	/**
	 * @var  string
	 */
	public $terms = '';

	/**
	 * Function for generating left menu tree.
	 *
	 * @param   int  $userId  Current user id.
	 *
	 * @return RMenu
	 *
	 * @throws  Exception
	 */
	public function generateCompaniesMenu($userId)
	{
		$menu = new RMenu;

		if (!RedshopbApp::getConfig()->getInt('impersonation_company', 1))
		{
			return $menu;
		}

		if ($this->companyId)
		{
			$limit          = RedshopbApp::getConfig()->getInt('company_menu_limit', 50);
			$companiesCount = RedshopbHelperACL::listAvailableCompanies($userId, 'count');
			$showMore       = 0;

			if ($companiesCount > $limit)
			{
				$showMore = $companiesCount - $limit;
			}

			$companies = RedshopbHelperACL::listAvailableCompanies(
				$userId, 'objectList', 0, '', 'redshopb.order.impersonate',
				'', false, false, false, false, 0, $limit
			);

			if ($companies)
			{
				$activeParents = RedshopbEntityCompany::getInstance($this->companyId)->getTree();

				$keyOrdered = array();
				$levels     = array();
				$returnUrl  = Factory::getApplication()->input->getBase64('return', null);

				if ($returnUrl)
				{
					$returnUrl = '&return=' . $returnUrl;
				}

				// Prebuild separate level arrays
				foreach ($companies as $key => $company)
				{
					$keyOrdered[$company->id] = $company->id;

					if (!isset($levels[$company->level]))
					{
						$levels[$company->level] = array();
					}

					if (!isset($levels[$company->level][$company->parent_id]))
					{
						$levels[$company->level][$company->parent_id] = array();
					}

					if ($company->name2)
					{
						$company->name .= '<br /><small>' . $company->name2 . '</small>';
					}

					$companyLink = RedshopbRoute::_(
						'index.php?option=com_redshopb&task=shop.savepath&company_id=' . $company->id . '&department_id=0&rsbuser_id=0' . $returnUrl
					);
					$node        = new RMenuNode($company->id, $company->name, $companyLink);

					// Is active customer
					if (in_array($company->id, $activeParents))
					{
						$node->setActive();
					}

					$levels[$company->level][$company->parent_id][$company->id] = $node;
				}

				if ($showMore > 0)
				{
					$menu->showMore = $showMore;
				}

				foreach ($levels as $level => $items)
				{
					foreach ($items as $parentId => $children)
					{
						foreach ($children as $childId => $node)
						{
							if (isset($levels[$level + 1][$childId]))
							{
								foreach ($levels[$level + 1][$childId] as $node)
								{
									$levels[$level][$parentId][$childId]->addChild($node);
								}
							}
						}
					}
				}

				$firstLevel = reset($levels);

				foreach ($firstLevel as $parentId => $nodes)
				{
					foreach ($nodes as $node)
					{
						$tree = new RMenuTree($node);
						$menu->addTree($tree);
					}
				}
			}
		}

		return $menu;
	}

	/**
	 * Constructor
	 *
	 * @param   array  $config  A named configuration array for object construction.<br/>
	 *                          name: the name (optional) of the view (defaults to the view class name suffix).<br/>
	 *                          charset: the character set to use for display<br/>
	 *                          escape: the name (optional) of the function to use for escaping strings<br/>
	 *                          base_path: the parent path (optional) of the views directory (defaults to the component folder)<br/>
	 *                          template_plath: the path (optional) of the layout directory (defaults to base_path + /views/ + view name<br/>
	 *                          helper_path: the path (optional) of the helper files (defaults to base_path + /helpers/)<br/>
	 *                          layout: the layout (optional) to use to display the view<br/>
	 *
	 * @throws  Exception
	 */
	public function __construct($config = array())
	{
		$app                = Factory::getApplication();
		$this->customerType = $app->getUserState('shop.customer_type', '');
		$this->customerId   = $app->getUserState('shop.customer_id', 0);

		$this->user           = RedshopbHelperCommon::getUser();
		$this->canImpersonate = RedshopbHelperACL::getPermissionInto('impersonate', 'order');

		$this->componentConfig = RedshopbApp::getConfig();
		$this->productsPerPage = $this->componentConfig->get('shop_categories_per_page', 12);
		$this->customerVendor  = RedshopbHelperCompany::getVendorCompanyByCustomer($this->customerId, $this->customerType);

		parent::__construct($config);
	}

	/**
	 * Display method.
	 *
	 * @param   string|null  $tpl  The template name
	 *
	 * @return  void
	 * @throws  Exception
	 */
	public function display($tpl = null)
	{
		Text::script('COM_REDSHOPB_NOTHING_SELECTED');

		/** @var SiteApplication $app */
		$app = Factory::getApplication();

		// Set layout to session.
		$layout = $this->getLayout();

		// Check checkout_mode
		$this->getOnepageLayout($app, $layout);

		$app->setUserState('shop.layout', $layout);

		$this->vendor = RedshopbHelperShop::getVendor();
		$document     = Factory::getDocument();

		$onSaleFilterState = 0;

		// Set SEO data
		$this->setSEOData($layout, $app, $document);

		if ($layout == 'offers')
		{
			$layout = 'default';
			$this->setLayout($layout);
			$app->input->set('layout', $layout);
			$onSaleFilterState = 1;
		}

		$app->setUserState('shop.offers.filter_onsale', $onSaleFilterState);

		$this->collectionId = $app->input->getInt('collectionId', 0);
		RedshopbHelperShop::setUserStates($this);

		// If no available shop (no Vanir customer or main company user) then it redirects to impersonation
		if ($layout != 'default' && RedshopbHelperCompany::getCustomerCompanyByCustomer($this->customerId, $this->customerType)->type == 'main')
		{
			RedshopbHelperShop::unsetVendor();
			$app->redirect('index.php?option=com_redshopb&view=shop');
		}

		$this->checkLayout($layout, $app);

		/** @var RedshopbModelShop $model */
		$model               = $this->getModel('Shop');
		$this->filter_onsale = $model->getState('filter.onsale');
		$this->state         = $model->getState();

		HTMLHelper::script('com_redshopb/redshopb.shop.js', array('framework' => false, 'relative' => true));
		RedshopbHelperCommon::initCartScript();

		/*
		 * Added support for multiple orders when printing receipt (the system will assume they all have the same "top"
		 * information and display the info of the first one, and that only the items vary)
		 */
		$orderDetails      = $this->getOrderDetails($app);
		$this->orderPlaced = count($orderDetails['multipleOrders']) ? true : false;
		$config            = RedshopbEntityConfig::getInstance();

		switch ($layout)
		{
			case 'default':
			case 'categories':
				$this->getDisplayLayout($config, $model, $app, $layout);

				break;

			case 'collections':
				$this->prepareList($model, $app, $layout);

				$this->shopCustomers = array();

				if (!is_null($this->vendor))
				{
					$this->vendorCompany = RedshopbHelperCompany::getCustomerCompanyByCustomer($this->vendor->id, $this->vendor->pType);

					$cartCustomers = RedshopbHelperCart::getCartCustomers();

					if ($cartCustomers)
					{
						foreach ($cartCustomers as $cartCustomer)
						{
							$customerInfo          = explode('.', $cartCustomer);
							$shopCustomer          = RedshopbHelperShop::getCustomerEntity($customerInfo[1], $customerInfo[0]);
							$shopCustomer->type    = $customerInfo[0];
							$shopCustomer->id      = $customerInfo[1];
							$this->shopCustomers[] = $shopCustomer;
						}
					}
				}

				break;

			case 'category':
				// Fall-through is intentional.
				$categoryId = $app->input->getInt('id', 0);

				// Check view permission for current user
				$availableCategories = (string) RedshopbHelperCategory::getCustomerCategories(
					1, false, $this->customerVendor->get('id'), 'comma', 0, 0, false, 99, false, null, false
				);

				$availableCategories = explode(',', $availableCategories);

				if (!in_array($categoryId, $availableCategories))
				{
					$app->redirect(
						RedshopbRoute::_('index.php?option=com_redshopb&view=shop', false),
						Text::_('COM_REDSHOPB_SHOP_ERROR_DO_NOT_HAVE_PERMISSION'),
						'error'
					);
				}

				if ($app->input->getInt('collection_id', 0))
				{
					$this->prepareList($model, $app, $layout);
				}

				$this->prepareCollection($model, $app, $layout, $document);

				$app->setUserState('shop.category', $categoryId);

				break;

			case 'productlist':
			case 'productrecent':
			case 'productfeatured':
			case 'manufacturer':
				$itemKey = $app->getUserState('shop.itemKey', 0);

				// Reset user state cookie
				$app->setUserState('shop.category', 0);
				$id = $app->input->getInt('id', 0);

				if ($itemKey != $layout . '_' . $id)
				{
					$app->setUserState('shop.manufacturer', null);
					$app->setUserState('shop.categoryfilter', null);
					$app->setUserState('shop.tag', null);
					$app->setUserState('shop.campaign_price', null);
					$app->setUserState('shop.price_range', null);
					$app->setUserState('shop.in_stock', null);
					$app->setUserState('shop.productlist.page', null);
					$app->setUserState('shop.filter', null);
					$app->setUserState('mod_filter.search.' . $itemKey, null);
				}

				$this->prepareCollection($model, $app, $layout, $document);
				break;
			case 'product':
				$model->setState('disable_user_states', true);
				$id = $app->input->getInt('id', 0);
				$model->hit($id);
				$this->prepareProduct($model, $app, $document);
				break;
			case 'delivery':
			case 'shipping':
			case 'payment':
			case 'confirm':
			case 'cart':
				$this->prepareCheckOut($model, $app);
				break;

			case 'collection':
				$this->getDisplayLayout($config, $model, $app, $layout);

				break;

			case 'receipt':
			case 'pay':
				// Checking if order is placed and we are ready to show it. Entering receipt layout.
				if ($this->orderPlaced)
				{
					$this->prepareOrder($orderDetails);
				}
				break;
			default:
				// Void default, but I'm not really sure it if this is needed.
				break;
		}

		$user                       = RedshopbHelperCommon::getUser();
		$this->placeOrderPermission = ($user->b2cMode || RedshopbHelperACL::getPermission('place', 'order'));

		RFactory::getDispatcher()->trigger('onAfterRedshopbViewShopDisplay', array(&$this, &$model, &$layout));

		parent::display($tpl);
	}

	/**
	 * Method to check if we are in the right layout for based on the configuration
	 *
	 * @param   string           $layout  The requested layout
	 * @param   CMSApplication   $app     The application
	 *
	 * @return void
	 */
	private function checkLayout($layout, $app)
	{
		if ($layout == 'default'
			&& $this->customerType != ''
			&& $this->customerId != 0)
		{
			if ($this->componentConfig->getString('show_shop_as', 'categories') === 'categories')
			{
				$app->redirect(RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=categories', false));
				$app->close();
			}
			elseif (RedshopbHelperShop::inCollectionMode(RedshopbEntityCompany::getInstance($this->companyId)))
			{
				$app->redirect(RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=collection', false));
				$app->close();
			}
		}
		elseif ($layout == 'collections')
		{
			if (!RedshopbHelperShop::inCollectionMode(RedshopbEntityCompany::getInstance($this->companyId)))
			{
				$app->redirect(RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=collection&mycollections=1', false));
			}
			else
			{
				$app->redirect(RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=collection', false));
			}

			$app->close();
		}

		// If shop is being used as a catalog, then disable all checkout layouts
		$checkOutLayouts = array('cart', 'delivery','shipping', 'payment', 'confirm', 'receipt');

		if (!RedshopbHelperPrices::displayPrices() && in_array($layout, $checkOutLayouts))
		{
			$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_VIEW_DISABLED', $layout), 'error');
			$app->redirect(RedshopbRoute::_('index.php?option=com_redshopb&view=shop', false));
			$app->close();
		}
	}

	/**
	 * Method to prepare the view to display a list views
	 * Executed for 'default' & 'categories' layouts
	 *
	 * @param   RedshopbModelShop  $model   The view model
	 * @param   CMSApplication     $app     The application
	 * @param   string             $layout  The requested layout
	 *
	 * @return void
	 */
	private function prepareList($model, $app, $layout)
	{
		$this->filterShopName = $model->getState('filter.shopname');

		// For default (collections) view, clear the category state of shop to avoid the missing products base on categories
		if ($layout == 'default')
		{
			$app->setUserState('shop.category', 0);
		}

		// Check if customer is selected so we can get products for showing
		if ($this->customerId != 0 && $this->customerType != '')
		{
			$this->departments = RedshopbHelperACL::listAvailableDepartments(
				$this->user->id,
				'objectList',
				$this->filterShopName ? 0 : $this->companyId,
				false,
				0,
				'',
				'redshopb.order.impersonate',
				$this->filterShopName ? $this->filterShopName : ''
			);

			$companyId = RedshopbHelperCompany::getCompanyIdByCustomer($this->customerId, $this->customerType);

			if ($this->customerType == 'company' || $this->customerType == 'department')
			{
				if (RedshopbHelperCompany::checkEmployeeMandatory($companyId))
				{
					$this->customerId   = 0;
					$this->customerType = '';

					$app->redirect(
						RedshopbRoute::_('index.php?option=com_redshopb&view=shop', false),
						Text::_('COM_REDSHOPB_SHOP_CUSTOMER_NOT_ALLOWED'),
						'error'
					);

					return;
				}
			}

			$isInCollectionMode = RedshopbHelperShop::inCollectionMode(
				RedshopbEntityCompany::getInstance(
					RedshopbHelperCompany::getCompanyIdByCustomer($this->customerId, $this->customerType)
				)
			);

			$this->collections = RedshopbHelperCollection::getCustomerCollectionsForShop($this->customerId, $this->customerType, array(), $isInCollectionMode);

			if ($this->componentConfig->get('show_shop_as', 'categories') == 'categories' || $layout)
			{
				$categoriesPerPage = $this->componentConfig->get('category_number_of_categories_per_column', 20)
					* $this->componentConfig->get('categories_number_of_columns_per_page', 2);

				$this->componentConfig->set('shop_categories_per_page', $categoriesPerPage);

				$this->categories = (array) RedshopbHelperCategory::getCustomerCategories(
					1, $this->collections, $this->customerVendor->get('id'), 'objectList', 0, $categoriesPerPage, true, 0, true, null, false
				);

				$this->categoriesCount = (int) RedshopbHelperCategory::getCustomerCategories(
					1, $this->collections, $this->customerVendor->get('id'), 'count', null, $categoriesPerPage, true, 0, true, null, false
				);
			}

			$this->filterForm                   = $model->getForm();
			$this->activeFilters                = $model->getActiveFilters();
			$this->customerCType                = RedshopbHelperShop::getCustomerType($this->customerId, $this->customerType);
			$this->stoolsOptions['searchField'] = 'search_shop_products';
		}
		// Customer is not selected, show all employees, companies and departments
		else
		{
			$companiesPerPage     = $this->componentConfig->get('shop_companies_per_page', 12);
			$departmentsPerPage   = $this->componentConfig->get('shop_departments_per_page', 12);
			$employeesPerPage     = $this->componentConfig->get('shop_employees_per_page', 12);
			$impersonationUser    = $this->componentConfig->get('impersonation_user', 1);
			$impersonationDep     = $this->componentConfig->get('impersonation_department', 1);
			$impersonationCompany = $this->componentConfig->get('impersonation_company', 1);

			$app->setUserState('shop.company_id', $this->companyId);
			$app->setUserState('shop.department_id', $this->departmentId);
			$app->setUserState('shop.employee_id', $this->rsbUserId);

			if ($impersonationUser)
			{
				$this->employeesCount = RedshopbHelperACL::listAvailableEmployees(
					$this->filterShopName ? 0 : $this->companyId,
					$this->filterShopName ? 0 : $this->departmentId,
					'count',
					'',
					$this->filterShopName ? $this->filterShopName : ''
				);

				$this->employees = RedshopbHelperACL::listAvailableEmployees(
					$this->filterShopName ? 0 : $this->companyId,
					$this->filterShopName ? 0 : $this->departmentId,
					'objectList',
					'',
					$this->filterShopName ? $this->filterShopName : '',
					0,
					$employeesPerPage
				);
			}
			else
			{
				$user      = RedshopbEntityUser::loadFromJoomlaUser();
				$companyId = $user->getCompany()->getId();

				if ($companyId == ($this->filterShopName ? 0 : $this->companyId)
					&& $user->get('department_id') == ($this->filterShopName ? 0 : $this->departmentId))
				{
					$this->employeesCount = 1;
					$this->employees      = array($user);

					$this->employees[0]->role = $user->getRole()->getType()->get('name');
				}
			}

			if ($impersonationCompany)
			{
				$this->companiesCount = RedshopbHelperACL::listAvailableCompanies(
					$this->user->id,
					'count',
					$this->filterShopName ? 0 : $this->companyId,
					'',
					'redshopb.order.impersonate',
					$this->filterShopName ? $this->filterShopName : '',
					false,
					false,
					false,
					true
				);

				$this->companies = RedshopbHelperACL::listAvailableCompanies(
					$this->user->id,
					'objectList',
					$this->filterShopName ? 0 : $this->companyId,
					'',
					'redshopb.order.impersonate',
					$this->filterShopName ? $this->filterShopName : '',
					false,
					false,
					false,
					true,
					0,
					$companiesPerPage
				);

				$this->companiesAll = RedshopbHelperACL::listAvailableCompanies(
					$this->user->id,
					'objectList'
				);
			}

			$ids                       = array();
			$this->subCompaniesCount   = 0;
			$this->subDepartmentsCount = 0;
			$this->subEmployeesCount   = 0;

			if ($this->companies)
			{
				foreach ($this->companies as $company)
				{
					$ids[] = $company->id;
				}

				$this->subCompaniesCount = RedshopbHelperCompany::getSubCompaniesCount($ids, true, false);

				if ($impersonationDep)
				{
					$this->subDepartmentsCount = RedshopbHelperCompany::getDepartmentsCount($ids, true, false);
				}

				if ($impersonationUser)
				{
					$this->subEmployeesCount = RedshopbHelperCompany::getEmployeesCount($ids, true, false);
				}
			}

			if ($impersonationDep)
			{
				$this->departmentsCount = RedshopbHelperACL::listAvailableDepartments(
					$this->user->id,
					'count',
					$this->filterShopName ? 0 : $this->companyId,
					false,
					$this->filterShopName ? 0 : $this->departmentId,
					'',
					'redshopb.order.impersonate',
					$this->filterShopName ? $this->filterShopName : ''
				);

				$this->departments = RedshopbHelperACL::listAvailableDepartments(
					$this->user->id,
					'objectList',
					$this->filterShopName ? 0 : $this->companyId,
					false,
					$this->filterShopName ? 0 : $this->departmentId,
					'',
					'redshopb.order.impersonate',
					$this->filterShopName ? $this->filterShopName : '',
					0,
					$departmentsPerPage
				);

				// We will include currently selected Department in the list (if not searching)
				if (!$this->filterShopName)
				{
					$userDepartmentId = RedshopbHelperDepartment::getDepartmentById($this->departmentId);

					if ($userDepartmentId)
					{
						$this->departments = array_merge(array($userDepartmentId), $this->departments);
					}
				}
			}

			// Creating companies menu
			$this->menu = $this->generateCompaniesMenu($this->user->id);
		}
	}

	/**
	 * Method to prepare the view to display collections
	 * Executed for 'category', 'productfeatured', 'productlist' & 'productrecent' layouts
	 *
	 * @param   RedshopbModelShop  $model     The view model
	 * @param   CMSApplication     $app       The application
	 * @param   string             $layout    The requested layout
	 * @param   Document           $document  The document object
	 *
	 * @return  void
	 * @throws  Exception
	 */
	private function prepareCollection($model, $app, $layout, $document)
	{
		if (true === $this->componentConfig->getBool('no_pagination', false))
		{
			$limitBasedOnCategory = $app->getUserState('shop.pagination.limit.category_' . $app->input->get('id'));

			$app->setUserState(
				'shop.productLimit',
				$limitBasedOnCategory > $this->productsPerPage ? $limitBasedOnCategory : $this->productsPerPage
			);
		}

		$this->filterForm      = $model->getForm();
		$this->productsPerPage = $app->input->getInt('product_category_limit', $app->getUserState('shop.productLimit', $this->productsPerPage));
		$productLimit          = (int) $app->input->getInt('product_shop_limit', $app->getUserState('shop.productLimit', $this->productsPerPage));
		$showAsDefault         = $this->componentConfig->get('show_products_as', 'list');
		$sortDirDefault        = 'asc';
		$input                 = Factory::getApplication()->input;
		$usingCollections      = (boolean) $input->get('mycollections', false)
			|| RedshopbHelperShop::inCollectionMode(RedshopbEntityCompany::getInstance($this->companyId));
		$this->collections     = RedshopbHelperCollection::getCustomerCollectionsForShop(
			$this->customerId, $this->customerType, array(), $usingCollections
		);
		$config                = RedshopbEntityConfig::getInstance();
		$sortByDefault         = $config->getDefaultOrderByField($layout);

		$searchString = $app->input->getString('search');

		if (array_key_exists('search', $app->input->getArray()) && $searchString == '')
		{
			return;
		}

		switch ($layout)
		{
			case 'category':
				$categoryId = $app->input->getInt('id', $app->getUserState('shop.category', 0));
				$itemKey    = $layout . '_' . $categoryId;
				$app->setUserState('shop.itemKey', $itemKey);

				// Store filter data from input into session.
				$filtersData = $app->input->get('filter', array(), 'array');

				foreach ($filtersData as $filter => $filterData)
				{
					RedshopbHelperFilter::setFilterDataToSession($filter, $filterData, 'filter.' . $itemKey);
				}

				$app->setUserState('shop.category', $categoryId);

				if ($config->get('ajax_categories', 0) == 1)
				{
					$document = Factory::getDocument();
					$document->addScriptDeclaration('jQuery(document).ready(function(){ redSHOPB.categories.init();});');
				}

				$companyId = RedshopbHelperCompany::getCompanyIdByCustomer($this->customerId, $this->customerType);

				if (!empty($categoryId))
				{
					$categoriesPerPage = (int) $config->get('shop_categories_per_page', 12);
					$this->category    = RedshopbModel::getInstance('Category', 'RedshopbModel')->getItem($categoryId);

					$this->category->subcategories = (array) RedshopbHelperCategory::getCustomerCategories(
						$categoryId, $this->collections, $companyId, 'objectList', 0, $categoriesPerPage, true, 0, false, null, false
					);

					$this->categoriesCount = (int) RedshopbHelperCategory::getCustomerCategories(
						$categoryId, $this->collections, $companyId, 'count', null, null, true, 0, false, null, false
					);
				}
				break;

			case 'manufacturer':
				// Filter by manufacturer id
				$manufacturerId = $app->input->getInt('id', 0);
				$itemKey        = $layout . '_' . $manufacturerId;
				$app->setUserState('shop.itemKey', $itemKey);
				$this->prepareTagFilters();

				if ($manufacturerId)
				{
					$this->manufacturer = RedshopbEntityManufacturer::load($manufacturerId);
					$app->setUserState('shop.manufacturer.' . $itemKey, $manufacturerId);
				}

				break;

			case 'productlist':
				$sortByDefault = 'relevance';
				$this->filterForm->setFieldAttribute('sort_by', 'configLayout', 'productlist', null);
				$itemKey = $layout . '_0';
				$app->setUserState('shop.itemKey', $itemKey);
				$this->prepareTagFilters();

				break;

			case 'productrecent':
				$itemKey = $layout . '_0';
				$app->setUserState('shop.itemKey', $itemKey);
				$this->prepareTagFilters();

				$sortByDefault  = 'recent';
				$sortDirDefault = 'desc';

				$recentProductCount = $app->getParams()->get('recently_count', 36);

				break;
			case 'productfeatured':
				$itemKey = $layout . '_0';
				$app->setUserState('shop.itemKey', $itemKey);
				$this->prepareTagFilters();

				break;

			default:
				$itemKey = $layout . '_0';
				break;
		}

		$showAs  = $app->input->getString('show_as', $app->getUserState('shop.show.' . $layout . '.ProductsAs', $showAsDefault));
		$sortBy  = $app->input->getString('sort_by', $app->getUserState('shop.show.' . $layout . '.SortBy', $sortByDefault));
		$sortDir = $app->input->getString('sort_dir', $app->getUserState('shop.show.' . $layout . '.SortByDir', $sortDirDefault));

		$app->setUserState('shop.show.' . $layout . '.ProductsAs', $showAs);
		$app->setUserState('shop.show.' . $layout . '.SortBy',  $sortBy);
		$app->setUserState('shop.show.' . $layout . '.SortByDir', $sortDir);
		$app->setUserState('shop.productLimit', $productLimit);

		if (!empty($sortBy))
		{
			$this->filterForm->setValue('sort_by', null, $sortBy);
		}

		if (!empty($sortDir))
		{
			$this->filterForm->setValue('sort_dir', null, $sortDir);
		}

		$urlFilter           = $app->input->get('f', null, 'raw');
		$paginationVariables = array();

		if (!empty($searchString))
		{
			$paginationVariables['search'] = $searchString;
		}

		if (!empty($urlFilter))
		{
			$app->setUserState('shop.categoryfilter', null);
			$app->setUserState('shop.tag', null);
			$app->setUserState('shop.attributefilter', null);
			$app->setUserState('shop.campaign_price', null);
			$app->setUserState('shop.price_range', null);
			$app->setUserState('shop.in_stock', null);
			$paginationVariables['f'] = $urlFilter;

			$groupedFields = RedshopbHelperField::getFields('product');
			$allFields     = array();
			$registry      = Factory::getSession()->get('registry');

			// We collect all fields from all scopes if more than one scope is provided
			foreach ($groupedFields as $scopeName => $fieldsInScope)
			{
				foreach ($fieldsInScope as $field)
				{
					$allFields[] = $field;
				}
			}

			if (!empty($allFields))
			{
				foreach ($allFields as $field)
				{
					$registry->set('filter.' . $itemKey . '.' . $field->id, null);
				}
			}

			$explodedUrlFilter = explode(']', $urlFilter);
			$filterAttributes  = array();

			foreach ($explodedUrlFilter as $oneUrlFilter)
			{
				if (empty($oneUrlFilter))
				{
					continue;
				}

				$urlFilterVariables = explode('[', $oneUrlFilter);

				if (empty($urlFilterVariables[0]) || empty($urlFilterVariables[1]))
				{
					continue;
				}

				switch ($urlFilterVariables[0])
				{
					case 'c':
						$app->setUserState(
							'shop.categoryfilter.' . $itemKey,
							ArrayHelper::toInteger(explode(':', $urlFilterVariables[1]))
						);
						break;
					case 'b':
						$app->setUserState(
							'shop.manufacturer.' . $itemKey,
							ArrayHelper::toInteger(explode(':', $urlFilterVariables[1]))
						);
						break;
					case 'cp':
						$app->setUserState(
							'shop.campaign_price.' . $itemKey,
							$urlFilterVariables[1]
						);
						break;
					case 'p':
						$app->setUserState(
							'shop.price_range.' . $itemKey,
							$urlFilterVariables[1]
						);
						break;
					case 't':
						$app->setUserState(
							'shop.tag.' . $itemKey,
							ArrayHelper::toInteger(explode(':', $urlFilterVariables[1]))
						);
						break;
					case 's':
						$app->setUserState(
							'shop.in_stock.' . $itemKey,
							$urlFilterVariables[1]
						);
						break;
					default:
						$urlFilterNamePieces = explode('-', $urlFilterVariables[0]);

						if (count($urlFilterNamePieces) == 1)
						{
							if (is_numeric($urlFilterNamePieces[0]))
							{
								RedshopbHelperFilter::setFilterDataToSession(
									$urlFilterNamePieces[0], explode(':', $urlFilterVariables[1]), 'filter.' . $itemKey
								);
							}
						}
						else
						{
							switch ($urlFilterNamePieces[0])
							{
								case 'a':
									$filterAttributes[$urlFilterNamePieces[1]] = explode(':', $urlFilterVariables[1]);
									break;
							}
						}

						break;
				}
			}

			$app->setUserState('shop.attributefilter.' . $itemKey, $filterAttributes);
		}

		$app->setUserState('shop.pagination.variables.' . $itemKey, $paginationVariables);

		// Get the actual products per collection
		$this->collectionProducts = array();
		$page                     = $app->input->getInt('page', $app->getUserState('shop.productlist.page.' . $itemKey, 1));
		$productSearch            = new RedshopbDatabaseProductsearch;

		if (!empty($app->input->getString('search', '')))
		{
			$productSearch->setTerm($app->input->getString('search'));
		}

		$app->setUserState('shop.productlist.page.' . $itemKey, $page);
		$useCollection = RedshopbHelperShop::inCollectionMode(
			RedshopbEntityCompany::getInstance(
				RedshopbHelperCompany::getCompanyIdByCustomer($this->customerId, $this->customerType)
			)
		);

		if (($this->collections && $useCollection) || $usingCollections)
		{
			foreach ($this->collections as $collectionId)
			{
				$products = $productSearch->getProductForProductListLayout(0, $productLimit, $collectionId);

				$countProducts = 0;

				if ($products && count($products->productData))
				{
					$countProducts = count($products->productData);
				}

				if ($countProducts >= $productLimit)
				{
					$countProducts = $productSearch->getProductCount($collectionId);
				}

				$this->productsCount += $countProducts;

				if ($countProducts > $this->maxPWCollections)
				{
					$this->maxPWCollections = $countProducts;
				}

				if (!empty($products))
				{
					$this->collectionProducts[$collectionId] = $products;
				}
			}
		}
		elseif ($this->collections === false || empty($this->collections) || $useCollection === 0)
		{
			$limit = $this->productsPerPage;

			if ($layout == 'productrecent' && $recentProductCount < $this->productsPerPage)
			{
				$limit = $recentProductCount;
			}

			if ($layout == 'productrecent' && $recentProductCount > 0 && $this->productsCount > $recentProductCount)
			{
				$this->productsCount = $recentProductCount;
			}

			$start = ($page - 1) * $limit;
			$start = ($start < 0) ? 0 : $start;

			$products = $productSearch->getProductForProductListLayout($start, $productLimit, 0);

			$countProducts = 0;

			if ($products && isset($products->productData) && count($products->productData))
			{
				$countProducts = count($products->productData);
			}

			if ($countProducts && $countProducts == $limit)
			{
				$this->productsCount = $productSearch->getProductCount();
			}
			else
			{
				$this->productsCount = $countProducts + $start;
			}

			if (!empty($products))
			{
				$this->collectionProducts[0] = $products;
			}
		}
	}

	/**
	 * Method to prepare tag filtering
	 *
	 * @return void
	 */
	private function prepareTagFilters()
	{
		$app     = Factory::getApplication();
		$itemKey = $app->getUserState('shop.itemKey', 0);

		// Filter by tag ids
		$productTags = $app->input->getString('tag_id', '');

		if (empty($productTags))
		{
			return;
		}

		$productTags = explode(',', $productTags);
		$tagTable    = RedshopbTable::getAdminInstance('Tag');

		if ($tagTable->load($productTags[0]))
		{
			$this->tagRecord = $tagTable;
		}

		$app->setUserState('shop.tag.' . $itemKey, $productTags);
	}

	/**
	 * Method to prepare the view to display a product
	 * Executed for 'prodcut' layout
	 *
	 * @param   RedshopbModelShop  $model     The view model
	 * @param   CMSApplication     $app       The application
	 * @param   Document           $document  The document object
	 *
	 * @return  void
	 * @throws  Exception
	 */
	private function prepareProduct($model, $app, $document)
	{
		/** @var RedshopbModelProduct $pModel */
		$pModel       = RModelAdmin::getInstance('Product', 'RedshopbModel', array('ignore_request' => true));
		$productId    = $app->input->getInt('id', $app->getUserState('shop.showProduct', 0));
		$collectionId = $app->input->getInt('collection', $app->getUserState('shop.showCollection', 0));

		$app->setUserState('shop.showProduct', $productId);
		$app->setUserState('shop.showCollection', $collectionId);

		$this->filterForm = $model->getForm();
		$productId        = $app->getUserState('shop.showProduct');
		$collectionId     = $app->getUserState('shop.showCollection');

		$model->setState('filter.product_id', $productId);
		$model->setState('product_collection', $collectionId);

		$this->productAttributes = $pModel->getAttributes($productId, array(), true, false);

		// Check view permission for current user
		$availableCategories = (string) RedshopbHelperCategory::getCustomerCategories(
			1, false, $this->customerVendor->get('id'), 'comma', 0, 0, false, 99
		);

		$availableCategories = explode(',', $availableCategories);
		$model->setState('filter.product_category', $availableCategories);

		$list = $model->getItems();

		if (empty($list))
		{
			$app->redirect(
				RedshopbRoute::_('index.php?option=com_redshopb&view=shop', false),
				Text::_('COM_REDSHOPB_SHOP_ERROR_DO_NOT_HAVE_PERMISSION'),
				'error'
			);
		}

		/** @var RedshopbModelShop  $model */
		$this->product = $model->prepareItemsForShopView($list, $this->customerId, $this->customerType, $collectionId);

		$product    = RedshopbHelperProduct::loadProduct($productId);
		$categoryId = RedshopbHelperCategory::getUrlCategoryId($product->categories);

		if ($app->getUserState('shop.category', 0) != $categoryId)
		{
			$app->setUserState('shop.category', $categoryId);
		}

		$uri    = Uri::getInstance();
		$domain = $uri->toString(array('scheme', 'host', 'port'));
		$url    = $domain
			. RedshopbRoute::_(
				'index.php?option=com_redshopb&view=shop&layout=product&id=' . $productId
				. '&category_id=' . $product->categories[0] . '&collection=' . $collectionId,
				false
			);
		$document->addHeadLink(htmlspecialchars($url), 'canonical');

		$this->category = RedshopbEntityCategory::load($categoryId)->getItem();
	}

	/**
	 * Method to prepare the view for check out
	 * Executed for 'delivery', 'shipping', 'payment' & 'confirm' layouts
	 *
	 * @param   RedshopbModelShop  $model  The view model
	 * @param   CMSApplication     $app    The application
	 *
	 * @return void
	 */
	private function prepareCheckOut($model, $app)
	{
		PluginHelper::importPlugin('redshipping');
		RFactory::getDispatcher()->trigger('onBeforeAECPrepareCart');
		RFactory::getDispatcher()->trigger('onBeforeAECPrepareCheckout');

		$cart                 = RedshopbHelperCart::getFirstTotalPrice();
		$this->paymentMethods = RedshopbHelperOrder::getPaymentMethods($this->companyId, $cart[key($cart)], key($cart));

		$this->usingPayments    = RedshopbHelperOrder::isPaymentAllowed($this->paymentMethods);
		$this->paymentName      = $app->getUserStateFromRequest('checkout.payment_name', 'payment_name', '', 'string');
		$this->paymentDelayName = $app->getUserStateFromRequest('checkout.payment_delay_name', 'payment_delay_name', '', 'string');

		if ($this->usingPayments)
		{
			// Don't show payments tab, when available just one payment
			if (count($this->paymentMethods) == 1)
			{
				$this->usingPayments = false;
			}

			foreach ($this->paymentMethods as $payment)
			{
				if ($payment->value == $this->paymentName)
				{
					$this->paymentTitle = $payment->text;
				}

				if ($payment->value == $this->paymentDelayName)
				{
					$this->paymentDelayTitle = $payment->text;
				}
			}
		}

		$billingAddressId = 0;

		switch ($this->customerType)
		{
			case 'employee':
				$billingAddressId = RedshopbEntityUser::getInstance($this->customerId)->getBillingAddress()->getExtendedData()->id;
				break;
			case 'department':
				$billingAddressId = RedshopbEntityDepartment::getInstance($this->customerId)->getBillingAddress()->getExtendedData()->id;
				break;
			case  'company':
				$billingAddress   = RedshopbEntityCompany::load($this->customerId);
				$billingAddressId = $billingAddress->get('address_id');
				break;
		}

		if ($app->getUserState("checkout.usebilling") && !$this->user->guest)
		{
			$app->setUserState('checkout.delivery_address_id', $billingAddressId);
		}

		$this->deliveryAddress   = RedshopbEntityUser::loadActive(true)->getAddress(! (bool) $this->user->guest)->getExtendedData();
		$this->deliveryAddressId = $app->getUserState('checkout.delivery_address_id', 0);

		if ($this->deliveryAddressId === 0)
		{
			$this->deliveryAddressId = $this->deliveryAddress->id;
		}

		$this->comment      = $app->getUserStateFromRequest('checkout.comment', 'comment', '', 'string');
		$this->requisition  = $app->getUserStateFromRequest('checkout.requisition', 'requisition', '', 'string');
		$this->shippingDate = $app->getUserStateFromRequest('checkout.shipping_date', 'shipping_date', array(), 'array');
		unset($this->customerAt);

		$customerCompany = RedshopbHelperCompany::getCompanyByCustomer($this->customerId, $this->customerType);

		if ('company' === $this->customerType && !$this->user->guest)
		{
			if (!$app->getUserState("checkout.usebilling") && $app->getUserState('checkout.delivery_address_id', 0) > 0)
			{
				$this->deliveryAddressId = $app->getUserState('checkout.delivery_address_id', 0);
			}
			else
			{
				$this->deliveryAddressId = $customerCompany->addressId;
			}
		}

		if ('department' === $this->customerType && !$this->user->guest)
		{
			if ($app->getUserState('checkout.delivery_address_id', 0) === 0)
			{
				$this->deliveryAddressId = $customerCompany->addressId;
			}
		}

		$app->setUserState('checkout.delivery_address_id', $this->deliveryAddressId);

		// Checking if delivery address is set in checkout process
		if ($this->deliveryAddressId > 0)
		{
			$this->deliveryAddress = RedshopbEntityAddress::getInstance($this->deliveryAddressId)->getExtendedData();
		}

		$this->shippingMethods = RedshopbHelperOrder::getShippingMethods(
			$this->companyId, $this->deliveryAddress, $cart[key($cart)], key($cart)
		);

		$this->usingShipping          = RedshopbHelperOrder::isShippingAllowed($this->shippingMethods);
		$this->shippingRateId         = $app->getUserStateFromRequest('checkout.shipping_rate_id', 'shipping_rate_id', '', 'string');
		$this->shippingRateIdDelay    = $app->getUserStateFromRequest('checkout.shipping_rate_id_delay', 'shipping_rate_id_delay', '', 'string');
		$this->stockroomPickupId      = $app->getUserStateFromRequest('checkout.pickup_stockroom_id', 'pickup_stockroom_id', 0, 'int');
		$this->stockroomPickupIdDelay = $app->getUserStateFromRequest('checkout.pickup_stockroom_id_delay', 'pickup_stockroom_id_delay', 0, 'int');

		if ($this->stockroomPickupId)
		{
			$this->stockroomPickupTitle = RedshopbEntityStockroom::getInstance($this->stockroomPickupId)->get('name');
		}

		$this->shippingRateTitle = RedshopbShippingHelper::getShippingRateName($this->shippingRateId, true, key($cart));

		// Order UI fields
		$this->orderEmployee   = null;
		$this->orderDepartment = null;
		$this->orderCompany    = $customerCompany;
		$this->orderVendor     = RedshopbEntityCompany::getInstance($this->orderCompany->id)->getVendor()->getItem();

		if ($this->orderVendor->id)
		{
			$this->orderVendor->address = RedshopbEntityAddress::getInstance($this->orderVendor->address_id)->getExtendedData();
		}

		// Getting shopping customer
		$this->getShoppingCustomer($this->customerType, $customerCompany);
		$this->getShoppingCustomer($this->customerType, $customerCompany);

		// Getting customer type company where is current customer placed
		$this->customer = RedshopbHelperCompany::getCustomerCompanyByCustomer($this->customerId, $this->customerType);

		if (!is_null($this->endCustomer))
		{
			$this->deliveryAddress->customer = $this->endCustomer->name;
		}

		/** @var Form $form */
		$form = $model->getForm();

		if ($this->user->guest)
		{
			$guestCSDetails = $app->getUserState('com_redshopb.shop.shop.shipping', array(), 'array');

			if (empty($this->deliveryAddressId) && !empty($guestCSDetails))
			{
				foreach ($guestCSDetails AS $name => $value)
				{
					$this->deliveryAddress->{$name} = $value;
				}
			}

			$form->setFieldAttribute('name', 'required', 'true');
			$form->setFieldAttribute('email', 'required', 'true');
			$form->setFieldAttribute('phone', 'required', (bool) RedshopbEntityConfig::getInstance()->get('checkout_guest_phone_required', true));
		}

		$form->bind($this->deliveryAddress);

		// Extra fields sent from another form (like quick order) or a previous view in the process
		$form->setValue('comment', '', $this->comment);
		$form->setValue('requisition', '', $this->requisition);

		$config = RedshopbEntityConfig::getInstance();

		if ($config->get('show_invoice_email_field', 0))
		{
			// Fill the company invoice email
			$form->setValue('invoice_email', '', RedshopbEntityCompany::load($this->companyId)->get('invoice_email', ''));
		}

		$this->checkoutFields = (object) $form->getFieldset('checkout');

		// Checking access
		$this->companyAddressManage = false;
		$this->departmentAManage    = false;
		$this->ownAddressManage     = false;

		if ($this->orderCompany)
		{
			$this->companyAddressManage = RedshopbHelperACL::getPermission('manage', 'address', Array(), false, $this->orderCompany->asset_id);
		}

		if ($this->orderDepartment)
		{
			$this->departmentAManage = RedshopbHelperACL::getPermission('manage', 'address', Array(), false, $this->orderDepartment->asset_id);
		}

		switch ($this->customerType)
		{
			case 'company':
				$this->ownAddressManage = $this->companyAddressManage;
				break;

			case 'department':
				$this->ownAddressManage = $this->departmentAManage;
				break;

			case 'employee':
				$this->ownAddressManage = RedshopbHelperACL::getPermission('manage', 'address', array('edit', 'edit.own'), true);
				break;
		}

		$termsArticle = $config->getString('terms_and_conditions', '');

		if (!empty($termsArticle))
		{
			$tmp = explode('.', $termsArticle);

			if ($tmp[0] == 'content')
			{
				/** @var ContentModelArticle $contentModel */
				$contentModel = RModel::getAdminInstance('Article', array(), 'com_content');
				$article      = $contentModel->getItem($tmp[1]);
				$this->terms  = $article->introtext . $article->fulltext;
			}
			elseif ($tmp[0] == 'aesir' && ComponentHelper::isInstalled('com_reditem'))
			{
				jimport('libraries.reditem.entity.item');
				$itemEntity  = ReditemEntityItem::getInstance($tmp[1]);
				$this->terms = $itemEntity->renderTemplate();
			}
		}
	}

	/**
	 * Method to get order details
	 *
	 * @param   CMSApplication   $app  the application
	 *
	 * @return array
	 */
	private function getOrderDetails($app)
	{
		$orderId          = $app->getUserState('checkout.orderId', 0);
		$multipleOrderIds = '';
		$multipleOrders   = array();

		if ($orderId <= 0)
		{
			$orderId          = $app->input->get('orderId', 0, 'int');
			$multipleOrderIds = $app->input->get('multipleOrderIds', '', 'string');

			if ($multipleOrderIds != '')
			{
				$multipleOrders = explode(',', $multipleOrderIds);

				if (!count($multipleOrders))
				{
					$multipleOrderIds = '';
				}
			}
		}

		if (!count($multipleOrders) && $orderId)
		{
			$multipleOrderIds = $orderId;
			$multipleOrders[] = $orderId;
		}

		return array('multipleOrderIds' => $multipleOrderIds, 'multipleOrders' => $multipleOrders);
	}

	/**
	 * Method to prepare the view to display orders
	 * Executed for 'receipt' & 'pay' layouts
	 *
	 * @param   array  $orderDetails  array('multipleOrderIds' => string, 'multipleOrders' => array)
	 *
	 * @return void
	 */
	private function prepareOrder($orderDetails)
	{
		/** @var RedshopbModelOrder $orderModel */
		$orderModel = RModel::getAdminInstance('Order', array('ignore_request' => true));
		/** @var RedshopbModelOrders $ordersModel */
		$ordersModel = RModel::getAdminInstance('Orders');

		$getAllData          = true;
		$this->customerOrder = array();

		foreach ($orderDetails['multipleOrders'] as $orderId)
		{
			if (!$getAllData)
			{
				$this->customerOrder[$orderId] = $ordersModel->getCustomerOrder($orderId);
				continue;
			}

			$order                   = (object) $orderModel->getItem($orderId)->getProperties();
			$this->paymentMethods    = RedshopbHelperOrder::getPaymentMethods($this->companyId, $order->total_price, $order->currency);
			$this->usingPayments     = RedshopbHelperOrder::isPaymentAllowed($this->paymentMethods);
			$this->paymentData       = RedshopbHelperOrder::preparePaymentData($order);
			$this->paymentName       = $order->payment_name;
			$this->shippingRateId    = $order->shipping_rate_id;
			$this->shippingRateTitle = RedshopbShippingHelper::getShippingRateName($order->shipping_rate_id, true, $order->currency, $order->id);
			$this->paymentTitle      = RedshopbHelperOrder::getPaymentMethodTitle($order->customer_company, $order->payment_name);
			$customerCompany         = RedshopbHelperCompany::getCompanyByCustomer($order->customer_id, $order->customer_type);
			$this->customer          = RedshopbHelperCompany::getCustomerCompanyByCustomer($order->customer_id, $order->customer_type);

			// Order UI fields
			$this->orderEmployee        = null;
			$this->orderDepartment      = null;
			$this->orderCompany         = $customerCompany;
			$this->orderVendor          = RedshopbEntityCompany::getInstance($this->orderCompany->id)->getVendor()->getItem();
			$this->orderVendor->address = RedshopbEntityAddress::getInstance($this->orderVendor->address_id)->getExtendedData();

			$this->stockroomPickupTitle = '';

			if (isset($order->shipping_details['pickup_stockroom_id'])
				&& $order->shipping_details['pickup_stockroom_id'])
			{
				$this->stockroomPickupId    = $order->shipping_details['pickup_stockroom_id'];
				$this->stockroomPickupTitle = RedshopbEntityStockroom::getInstance($order->shipping_details['pickup_stockroom_id'])->get('name');
			}

			$this->customerId = $order->customer_id;

			// Getting shopping customer
			$this->getShoppingCustomer($order->customer_type, $customerCompany);

			$this->deliveryAddressId = (int) $order->delivery_address_id;
			$this->deliveryAddress   = RedshopbEntityAddress::getInstance($this->deliveryAddressId)->getExtendedData();

			if (!is_null($this->endCustomer))
			{
				$this->deliveryAddress->customer = $this->endCustomer->name;
			}

			$this->shippingMethods = RedshopbHelperOrder::getShippingMethods(
				$this->companyId, $this->deliveryAddress, $order->total_price, $order->currency
			);

			$this->usingShipping = RedshopbHelperOrder::isShippingAllowed($this->shippingMethods);
			$this->comment       = $order->comment;
			$this->requisition   = $order->requisition;
			$this->shippingDate  = array($order->customer_type . '_' . $order->customer_id => $order->shipping_date);

			$this->customerOrder[$orderId] = $ordersModel->getCustomerOrder($orderId);
			$getAllData                    = false;
		}

		RFactory::getDispatcher()->trigger('onAfterRedshopbShopPrepareOrder', array(&$orderDetails, &$this));
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		$layout = $this->getLayout();
		$title  = Text::_('COM_REDSHOPB_SHOP_FORM_TITLE');

		if ($this->rsbUserId)
		{
			switch ($layout)
			{
				case 'cart':
					$title .= '<small> : ' . Text::_('COM_REDSHOPB_SHOP_CART_TITLE') . '</small>';
					break;

				case 'delivery':
					$title .= '<small> : ' . Text::_('COM_REDSHOPB_SHOP_DELIVERY_TITLE') . '</small>';
					break;

				case 'shipping':
					$title .= '<small> : ' . Text::_('COM_REDSHOPB_SHOP_SHIPPING_TITLE') . '</small>';
					break;

				case 'payment':
					$title .= '<small> : ' . Text::_('COM_REDSHOPB_SHOP_PAYMENT_TITLE') . '</small>';
					break;

				case 'confirm':
					$title .= '<small> : ' . Text::_('COM_REDSHOPB_SHOP_CONFIRM_TITLE') . '</small>';
					break;

				case 'receipt':
					$title .= '<small> : ' . Text::_('COM_REDSHOPB_SHOP_RECEIPT_TITLE') . '</small>';
					break;

				default:
					$title .= '<small> : ' . Text::_('COM_REDSHOPB_PRODUCT_LIST_TITLE');

					if (!empty($this->companies))
					{
						foreach ($this->companies as $customer)
						{
							if ($customer->id == $this->companyId)
							{
								$title .= ' / ' . Text::_('COM_REDSHOPB_COMPANY_LABEL') . ': ' . $customer->name;

								break;
							}
						}
					}

					if (!empty($this->departments))
					{
						foreach ($this->departments as $department)
						{
							if ($department->id == $this->departmentId)
							{
								$title .= ' / ' . Text::_('COM_REDSHOPB_DEPARTMENT_LABEL') . ': ' . $department->name;

								break;
							}
						}
					}

					$title .= '</small>';

					break;
			}
		}

		return $title;
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$layout  = $this->getLayout();
		$orderId = Factory::getApplication()->getUserState('checkout.orderId', 0);

		if ($orderId <= 0)
		{
			$orderId = Factory::getApplication()->input->get('orderId', 0, 'int');
		}

		$groupOrderEdit = new RToolbarButtonGroup('pull-right');

		switch ($layout)
		{
			case 'cart':
				return $this->getCheckoutCartToolbar($orderId, $groupOrderEdit);

			case 'delivery':
				$group = new RToolbarButtonGroup;
				$back  = RToolbarBuilder::createStandardButton(
					'shop.movetocart',
					Text::_('COM_REDSHOPB_BACK'),
					'btn-danger',
					'icon-circle-arrow-left',
					false
				);

				if ($this->usingShipping)
				{
					$nextStep = 'shop.shipping';
				}
				elseif ($this->usingPayments)
				{
					$nextStep = 'shop.payment';
				}
				else
				{
					$nextStep = 'shop.confirm';
				}

				$sendOrder = RToolbarBuilder::createStandardButton(
					$nextStep,
					Text::_('JNEXT'),
					'btn-success',
					'icon-circle-arrow-right',
					false
				);
				$group->addButton($back)->addButton($sendOrder);

				$toolbar = new RToolbar;
				$toolbar->addGroup($group);

				return $toolbar;

			case 'shipping':
				$group = new RToolbarButtonGroup;
				$back  = RToolbarBuilder::createStandardButton(
					'shop.movetodelivery',
					Text::_('COM_REDSHOPB_BACK'),
					'btn-danger',
					'icon-circle-arrow-left',
					false
				);

				if ($this->usingPayments)
				{
					$nextStep = 'shop.payment';
				}
				else
				{
					$nextStep = 'shop.confirm';
				}

				$sendOrder = RToolbarBuilder::createStandardButton(
					$nextStep,
					Text::_('JNEXT'),
					'btn-success',
					'icon-circle-arrow-right',
					false
				);
				$group->addButton($back)->addButton($sendOrder);

				$toolbar = new RToolbar;
				$toolbar->addGroup($group);

				return $toolbar;

			case 'payment':
				$group = new RToolbarButtonGroup;

				if ($this->usingShipping)
				{
					$previousStep = 'shop.movetoshipping';
				}
				else
				{
					$previousStep = 'shop.movetodelivery';
				}

				$back      = RToolbarBuilder::createStandardButton(
					$previousStep,
					Text::_('COM_REDSHOPB_BACK'),
					'btn-danger',
					'icon-circle-arrow-left',
					false
				);
				$sendOrder = RToolbarBuilder::createStandardButton(
					'shop.confirm',
					Text::_('JNEXT'),
					'btn-success',
					'icon-circle-arrow-right',
					false
				);
				$group->addButton($back)->addButton($sendOrder);

				$toolbar = new RToolbar;
				$toolbar->addGroup($group);

				return $toolbar;

			case 'confirm':
				$group = new RToolbarButtonGroup;

				if ($this->usingPayments)
				{
					$previousStep = 'shop.movetopayment';
				}

				elseif ($this->usingShipping)
				{
					$previousStep = 'shop.movetoshipping';
				}
				else
				{
					$previousStep = 'shop.movetodelivery';
				}

				$back = RToolbarBuilder::createStandardButton(
					$previousStep,
					Text::_('COM_REDSHOPB_BACK'),
					'btn-danger',
					'icon-circle-arrow-left',
					false
				);

				$sendOrder = RToolbarBuilder::createStandardButton(
					'shop.completeorder',
					Text::_('COM_REDSHOPB_SHOP_COMPLETE_ORDER'),
					'btn-success js-complete-order',
					'icon-book',
					false
				);

				$group->addButton($back)->addButton($sendOrder);
				$toolbar = new RToolbar;
				$toolbar->addGroup($group);

				return $toolbar;

			case 'receipt':
				$group    = new RToolbarButtonGroup;
				$toolbar  = new RToolbar;
				$complete = RToolbarBuilder::createStandardButton(
					'shop.finish',
					Text::_('COM_REDSHOPB_HOME'),
					'btn-primary',
					'icon-dashboard',
					false
				);
				$print    = RToolbarBuilder::createStandardButton(
					'shop.printpdf',
					Text::_('COM_REDSHOPB_PRINT_PDF'),
					'btn-success btn-printpdf',
					'icon-print',
					false
				);

				$group->addButton($complete)->addButton($print);
				$toolbar->addGroup($group);

				return $toolbar;

			case 'wash':
				return null;

			default:
				if ($this->customerType != '' && $this->customerId != 0)
				{
					$groupOne  = new RToolbarButtonGroup;
					$groupTwo  = new RToolbarButtonGroup;
					$toolbar   = new RToolbar;
					$showPrint = $this->componentConfig->get('show_products_print', 0) > 0;

					if ($orderId > 0)
					{
						$cancelOrderEdit = RToolbarBuilder::createStandardButton(
							'order.cancelEditOrderItems',
							Text::_('JTOOLBAR_CANCEL'),
							'btn-danger',
							'icon-remove',
							false
						);

						$proceed = RToolbarBuilder::createStandardButton(
							'shop.checkout',
							Text::_('COM_REDSHOPB_SHOP_PROCEED_TO_CART'),
							'btn-success',
							'icon-circle-arrow-right',
							false
						);

						$groupOrderEdit->addButton($cancelOrderEdit)->addButton($proceed);
						$toolbar->addGroup($groupOrderEdit);
					}
					elseif ($this->canImpersonate)
					{
						if (RedshopbEntityConfig::getInstance()->get('vendor_of_companies', 'parent') == 'parent')
						{
							$changeVendor = RToolbarBuilder::createStandardButton(
								'shop.changevendor',
								Text::_('COM_REDSHOPB_SHOP_CHANGE_VENDOR'),
								'btn-danger shop-changevendor',
								'icon-signout',
								false
							);

							$toolbar->addGroup($groupOne->addButton($changeVendor));
						}

						$changeCustomer = RToolbarBuilder::createStandardButton(
							'shop.changecustomer',
							Text::_('COM_REDSHOPB_SHOP_CHANGE_CUSTOMER'),
							'btn-danger shop-changecustomer',
							'icon-signout',
							false
						);

						$toolbar->addGroup($groupTwo->addButton($changeCustomer));
					}

					if ($showPrint && !$orderId)
					{
						$printProducts = RToolbarBuilder::createStandardButton(
							'printProductsList',
							Text::_('COM_REDSHOPB_SHOP_PRINT_PRODUCTS_LIST'),
							'btn-success',
							'icon-print',
							false
						);

						$groupOrderEdit->addButton($printProducts);
						$toolbar->addGroup($groupOrderEdit);
					}

					return $toolbar;
				}
				else
				{
					if (method_exists('RedshopbView', 'getToolbar'))
					{
						return parent::getToolbar();
					}
					else
					{
						return null;
					}
				}
		}
	}

	/**
	 * Method to get the checkout cart toolbar
	 *
	 * @param   integer              $orderId         [description]
	 * @param   RToolbarButtonGroup  $groupOrderEdit  [description]
	 *
	 * @return RToolbar
	 */
	protected function getCheckoutCartToolbar($orderId, $groupOrderEdit)
	{
		$group   = new RToolbarButtonGroup;
		$toolbar = new RToolbar;

		$back = RToolbarBuilder::createStandardButton(
			'shop.backtoshop',
			Text::_('COM_REDSHOPB_BACK'),
			'btn-danger',
			'icon-circle-arrow-left',
			false
		);

		if ($orderId > 0)
		{
			$cancelOrderEdit = RToolbarBuilder::createStandardButton(
				'order.cancelEditOrderItems',
				Text::_('JTOOLBAR_CANCEL'),
				'btn-danger',
				'icon-remove',
				false
			);

			$saveOrder = RToolbarBuilder::createStandardButton(
				'shop.updateorder',
				Text::_('JTOOLBAR_SAVE'),
				'btn-success',
				'icon-save',
				false
			);

			$group->addButton($back);
			$groupOrderEdit->addButton($cancelOrderEdit)->addButton($saveOrder);
			$toolbar->addGroup($group)->addGroup($groupOrderEdit);

			return $toolbar;
		}

		$config       = RedshopbEntityConfig::getInstance();
		$checkoutMode = $config->get('checkout_mode', 'default', 'string');

		// Default checkout mode
		if ($checkoutMode == 'default')
		{
			$checkout = RToolbarBuilder::createStandardButton(
				'shop.cartcheckout',
				Text::_('JNEXT'),
				'btn-success',
				'icon-circle-arrow-right',
				false
			);

			$group->addButton($back)->addButton($checkout);
			$toolbar->addGroup($group);

			return $toolbar;
		}

		$sendOrder = RToolbarBuilder::createStandardButton(
			'shop.completeorder',
			Text::_('COM_REDSHOPB_SHOP_COMPLETE_ORDER'),
			'btn-success js-complete-order',
			'icon-book',
			false
		);

		$group->addButton($back)->addButton($sendOrder);
		$toolbar->addGroup($group);

		return $toolbar;
	}

	/**
	 * Method for setting SEO data for given layout.
	 *
	 * @param   string           $layout    View layout.
	 * @param   CMSApplication   $app       Application object.
	 * @param   Document         $document  Document object.
	 *
	 * @return  void
	 *
	 * @since   1.12.60
	 */
	private function setSEOData($layout, $app, $document)
	{
		$id = $app->input->getInt('id', 0);

		switch ($layout)
		{
			case 'category':
			case 'manufacturer':
			case 'product':
				$config       = RedshopbHelperSeo::getMetaSettings($layout, $id);
				$title        = RedshopbHelperSeo::replaceTags($config['titles'], $layout, $id);
				$headings     = RedshopbHelperSeo::replaceTags($config['headings'], $layout, $id);
				$descriptions = RedshopbHelperSeo::replaceTags($config['description'], $layout, $id);
				$keywords     = RedshopbHelperSeo::replaceTags($config['keywords'], $layout, $id);

				$this->pageHeading = $headings;
				RedshopbHelperSeo::setDocumentMeta($document, $title, $keywords, $descriptions);

				break;
			default:
				break;
		}
	}

	/**
	 * Method for get Shopping Customer.
	 *
	 * @param   string   $customer           View layout.
	 * @param   object   $customerCompany    Application object.
	 *
	 * @return  void
	 */
	private function getShoppingCustomer($customer, $customerCompany)
	{
		switch ($customer)
		{
			case 'employee':
				$this->endCustomer = RedshopbHelperUser::getUser($this->customerId);
				$department        = RedshopbHelperUser::getUserDepartment($this->customerId);

				$this->customerAt = $customerCompany->name;

				if ($customerCompany->type == 'end_customer' && !is_null($department))
				{
					$this->customerAt = $department->name;
				}

				// Filling up order UI fields for employee orders
				$this->orderEmployee   = $this->endCustomer;
				$this->orderDepartment = $department;

				break;

			case 'department':
				$this->endCustomer = RedshopbHelperDepartment::getDepartmentById($this->customerId);
				$this->customerAt  = $customerCompany->name;

				if ($customerCompany->type == 'end_customer')
				{
					$this->customerAt = $customerCompany->name;
				}

				// Filling up order UI fields for department orders
				$this->orderDepartment = $this->endCustomer;

				break;

			case 'company':
				$this->endCustomer = RedshopbHelperCompany::getCompanyById($this->customerId);
				$this->customerAt  = '';

				if ($customerCompany->type == 'end_customer')
				{
					$this->customerAt = $this->endCustomer->name;
				}

				break;
		}
	}

	/**
	 * Method for get display layout.
	 *
	 * @param   RedshopbEntityConfig  $config   View layout.
	 * @param   RedshopbModelShop     $model    Application object.
	 * @param   CMSApplication        $app      Application object.
	 * @param   string                $layout   Application object.
	 *
	 * @return  void
	 */
	private function getDisplayLayout($config, $model, $app, $layout)
	{
		$this->vendor = RedshopbHelperShop::getVendor();

		if ($config->get('ajax_categories', 0) == 1)
		{
			$document = Factory::getDocument();
			$document->addScriptDeclaration('jQuery(document).ready(function(){ redSHOPB.categories.init();});');
		}

		$this->prepareList($model, $app, $layout);

		// Generate breadcrumbs
		if ($layout == 'default')
		{
			$this->shopCustomers = array();

			if (!is_null($this->vendor))
			{
				$this->vendorCompany = RedshopbHelperCompany::getCustomerCompanyByCustomer($this->vendor->id, $this->vendor->pType);

				$cartCustomers = RedshopbHelperCart::getCartCustomers();

				if ($cartCustomers)
				{
					foreach ($cartCustomers as $cartCustomer)
					{
						$customerInfo          = explode('.', $cartCustomer);
						$shopCustomer          = RedshopbHelperShop::getCustomerEntity($customerInfo[1], $customerInfo[0]);
						$shopCustomer->type    = $customerInfo[0];
						$shopCustomer->id      = $customerInfo[1];
						$this->shopCustomers[] = $shopCustomer;
					}
				}
			}
		}
	}

	/**
	 * Method that checks if the config for checkout_mode is set to onepage
	 * and redirects if the user is entering the default checkout_mode
	 *
	 * @param   CMSApplication   $app       The application
	 * @param   string           $layout    View layout.
	 *
	 * @return  void
	 */
	public function getOnepageLayout($app, $layout)
	{
		$config       = RedshopbEntityConfig::getInstance();
		$checkoutmode = $config->getString('checkout_mode');

		if ($checkoutmode == 'onepage'
			&& ($layout == 'shipping' || $layout == 'delivery'
			|| $layout == 'payment' || $layout == 'confirm'))
		{
			$app->redirect(
				RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=cart', false)
			);
		}
	}
}
