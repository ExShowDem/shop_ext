<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Pagination\Pagination;
/**
 * Recently Purchased Products view
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.6.60
 */
class RedshopbViewRecent_Purchased_Products extends RedshopbView
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
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$model = $this->getModel('Recent_Purchased_Products');

		$this->items         = $model->getItems();
		$this->state         = $model->getState();
		$this->pagination    = $model->getPagination();
		$this->filterForm    = $model->getForm();
		$this->activeFilters = $model->getActiveFilters();

		$values = new stdClass;
		RedshopbHelperShop::setUserStates($values);

		if (!empty($this->items))
		{
			$sortProducts = array();

			foreach ($this->items as $product)
			{
				$sortProducts[$product->id] = $product;
			}

			/** @var RedshopbModelShop $shopModel */
			$shopModel = RModelAdmin::getInstance('Shop', 'RedshopbModel', array('ignore_request' => true));
			RedshopbHelperProduct::setProduct($sortProducts);
			$preparedItems              = $shopModel->prepareItemsForShopView($sortProducts, $values->customerId, $values->customerType, 0, true);
			$preparedItems->productData = $sortProducts;
			$this->items                = $preparedItems;
		}

		parent::display($tpl);
	}
}
