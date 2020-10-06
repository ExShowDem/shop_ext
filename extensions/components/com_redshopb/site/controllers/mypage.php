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

/**
 * My Page Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerMyPage extends RedshopbControllerAdmin
{
	/**
	 * Get collection product items.
	 *
	 * @return void
	 */
	public function ajaxGetCollectionItems()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$user  = RedshopbHelperCommon::getUser();
		$input = $app->input;

		$collectionId = $input->getInt('collectionId', 0);
		$start        = $input->getInt('start', 0);
		$limit        = $input->getInt('limit', 0);
		$onSale       = $input->getInt('onSale', 0);
		$search       = $input->getString('search', '');
		$category     = $input->getInt('category', 0);
		$flatDisplay  = $input->getString('flat_display', '');
		$collection   = $input->getInt('collection', 0);
		$filters      = $input->get('filter', array(), 'array');

		$customerId      = $app->getUserState('customer_id', 0);
		$customerType    = $app->getUserState('shop.customer_type', '');
		$forceCollection = RedshopbHelperShop::inCollectionMode(
			RedshopbEntityCompany::getInstance(
				RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType)
			)
		);

		if ($user->b2cMode)
		{
			$placeOrderPermission = true;
		}
		else
		{
			$placeOrderPermission = RedshopbHelperACL::getPermission('place', 'order');
		}

		echo RedshopbHelperCollection::getShopCollectionProducts(
			$placeOrderPermission, $collectionId, $start, $limit, $onSale, $search, $category, $flatDisplay, $collection, $filters, !$forceCollection
		);

		$app->close();
	}
}
