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
/**
 * Quick Order View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewQuick_Order extends RedshopbView
{
	/**
	 * Customer ID
	 *
	 * @var  integer
	 */
	public $customerId;

	/**
	 * Customer type
	 *
	 * @var  string
	 */
	public $customerType;

	/**
	 * Current cart items.
	 *
	 * @var  array
	 */
	public $cartItems;

	/**
	 * Current cart totals
	 *
	 * @var  array
	 */
	public $totals;

	/**
	 * Current cart tax rates
	 *
	 * @var  array
	 */
	public $taxes;

	/**
	 * Can the current user use the quick order?
	 *
	 * @var boolean
	 *
	 * @since 2.4.0
	 */
	public $enabled = true;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		$app = Factory::getApplication();

		if (RedshopbEntityUser::loadActive(true)->getRole()->getType()->get('type') === 'sales')
		{
			$this->enabled = (new RedshopbDatabaseProductsearch(array('useSimpleSearch' => true)))->getProductCount() > 0;
		}

		$this->customerId   = $app->getUserState('shop.customer_id', 0);
		$this->customerType = $app->getUserState('shop.customer_type', '');
		$this->cartItems    = RedshopbHelperCart::getCart($this->customerId, $this->customerType)->get('items', array());
		$this->totals       = RedshopbHelperCart::getCustomerCartTotals($this->customerId, $this->customerType, true);
		$this->taxes        = RedshopbHelperCart::getCustomerCartTaxByName($this->customerId, $this->customerType);

		return parent::display($tpl);
	}
}
