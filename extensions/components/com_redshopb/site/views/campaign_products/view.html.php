<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;

/**
 * Campaign Products View
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Views
 * @since       1.6.25
 */
class RedshopbViewCampaign_Products extends RedshopbView
{
	/**
	 * Do we have to display a sidebar?
	 *
	 * @var  boolean
	 */
	protected $displaySidebar = false;

	/**
	 * @var  array
	 */
	public $items = array();

	/**
	 * @var integer
	 */
	public $customerId = 0;

	/**
	 * @var string
	 */
	public $customerType = '';

	/**
	 * @var integer
	 */
	public $customerCompany = 0;

	/**
	 * @var array
	 */
	public $productsImages = array();

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$app  = Factory::getApplication();
		$user = RedshopbHelperCommon::getUser();

		// Get available categories of current user.
		if ($user->b2cMode)
		{
			$this->customerType    = 'company';
			$this->customerId      = $user->b2cCompany;
			$this->customerCompany = $this->customerId;
		}
		elseif (!RedshopbHelperACL::getPermissionInto('impersonate', 'order'))
		{
			$this->customerType    = 'employee';
			$this->customerId      = RedshopbHelperUser::getUserRSid($user->id);
			$this->customerCompany = RedshopbHelperCompany::getCompanyIdByCustomer($this->customerId, $this->customerType);
		}
		else
		{
			$userCompany           = RedshopbHelperUser::getUserCompanyId($user->id, 'joomla');
			$this->customerCompany = $app->getUserStateFromRequest('list.company_id', 'company_id', $userCompany, 'int');
			$this->customerType    = $app->getUserState('shop.customer_type', '');
			$this->customerId      = $app->getUserState('shop.customer_id', 0);
		}

		$userCollections = RedshopbHelperCollection::getCustomerCollectionsForShop($this->customerId, $this->customerType);
		$userCategories  = (string) RedshopbHelperCategory::getCustomerCategories(1, $userCollections, $this->customerCompany, 'comma', 0, 0);
		$userCategories  = explode(',', $userCategories);

		// Get categories from menu config
		$config          = $app->getParams();
		$limitCategories = (array) $config->get('categories_limit', array());
		$includeSub      = (boolean) $config->get('include_sub_categories', false);

		if (!empty($limitCategories))
		{
			$limitCategories = ArrayHelper::toInteger($limitCategories);

			if ($includeSub)
			{
				$tmpCategories = $limitCategories;

				foreach ($limitCategories as $categoryId)
				{
					$subCategories = RedshopbHelperACL::listAvailableCategories(
						$user->id, $categoryId, 1, $this->customerCompany, $userCollections, 'comma', '', 'redshopb.category.view', 0, 99
					);

					if (empty($subCategories))
					{
						continue;
					}

					$tmpCategories = array_merge($tmpCategories, explode(',', $subCategories));
				}

				$limitCategories = array_unique($tmpCategories);
			}

			$userCategories = array_intersect($userCategories, $limitCategories);
		}

		if (empty($userCategories))
		{
			parent::display($tpl);
		}

		$productsPerPage = RedshopbApp::getConfig()->get('shop_products_per_page', 12);
		$shopModel       = RedshopbModel::getInstance('Shop', 'RedshopbModel');

		// Get products with campaign price base on available categories.
		$this->items = RedshopbHelperCategory::getOnlyCampaignPriceProducts(
			$this->customerId, $this->customerType, $userCategories, 0, 'objectList', 0, $productsPerPage
		);

		if (!empty($this->items))
		{
			$ids = array();

			foreach ($this->items as $item)
			{
				$ids[] = $item->id;
			}

			$this->productsImages = RedshopbModelShop::getProductImages($ids);
		}

		$this->itemsCount = RedshopbHelperCategory::getOnlyCampaignPriceProducts(
			$this->customerId, $this->customerType, $userCategories, 0, 'count'
		);

		parent::display($tpl);
	}
}
