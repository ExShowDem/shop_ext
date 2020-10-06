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
use Joomla\Registry\Registry;
use Joomla\CMS\Pagination\Pagination;
/**
 * ManufacturerList View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.6.51
 */
class RedshopbViewManufacturerlist extends RedshopbView
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
	public $searchchar = array();

	/**
	 * @var boolean
	 */
	public $showAlphabetFilter = true;

	/**
	 * @var boolean
	 */
	public $showCategoryFilter = false;

	/**
	 * @var boolean
	 */
	public $showSearchField = false;

	/**
	 * @var boolean
	 */
	public $manufacturerListSidebar = true;

	/**
	 * @var array
	 */
	public $alphabeticalFilters = array();

	/**
	 * @var Registry
	 */
	public $params;

	/**
	 * Customer type. It can be 'employee', 'department' or 'company'.
	 *
	 * @var string
	 */
	public $customerType = '';

	/**
	 * Customer id. Depends on selected customer type.
	 *
	 * employee = user_id
	 * department = department_id
	 * company = company_id
	 *
	 * @var integer
	 */
	public $customerId = 0;

	/**
	 * @var integer
	 */
	public $companyId = 0;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  configuration array
	 */
	public function __construct($config = array())
	{
		$app                = Factory::getApplication();
		$this->customerType = $app->getUserState('shop.customer_type', 'employee');
		$this->customerId   = $app->getUserState('shop.customer_id', 0);
		$this->companyId    = $app->getUserState('shop.company_id', 0);

		return parent::__construct($config);
	}

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$app = Factory::getApplication();

		/** @var RedshopbModelManufacturers $model */
		$model = RedshopbModel::getAdminInstance('Manufacturers');
		$model->setState('filter.state', 1);
		$model->setState('shop.customer_type', $this->customerType);
		$model->setState('shop.customer_id', $this->customerId);
		$model->setState('shop.company_id', $this->companyId);
		$categoryId = $model->getState('filter.category', $model->getState('filter.product_category', 0));
		$app->setUserState('filter.product_category', $categoryId);

		$itemKey = $app->getUserState('shop.itemKey', 0);
		$app->setUserState('shop.manufacturer.' . $itemKey, array());
		$model->setState('filter.only_with_active_product', 1);
		$model->setState('list.ordering', 'm.name');
		$model->setState('list.direction', 'asc');

		$app->getMenu()->setActive($app->input->get('Itemid'));
		$menu = $app->getMenu()->getActive();

		if ($menu)
		{
			$this->params                  = $menu->params;
			$this->showAlphabetFilter      = (boolean) $this->params->get('show_alphabet_filter', 1);
			$this->showSearchField         = (boolean) $this->params->get('search_manufacturer', 0);
			$this->manufacturerListSidebar = (boolean) $this->params->get('manufacturer_list_sidebar', 1);
			$this->showCategoryFilter      = $this->params->get('show_category_filter', 0);
		}

		if ($this->showAlphabetFilter)
		{
			$this->alphabeticalFilters = $model->getFirstCharAvailable();
			$sessionValue              = $app->getUserStateFromRequest('manufacturer.searchchar', 'searchchar', array(), 'array');

			if ($app->input->get('reset_flag', '') == '1')
			{
				$sessionValue = null;
				$app->setUserState('manufacturer.searchchar', $sessionValue);
			}

			$model->setState('filter.search_char', $sessionValue);

			if (!empty($sessionValue))
			{
				$this->searchchar = $sessionValue;
			}
		}

		if ($this->showCategoryFilter)
		{
			$categoryFilterValue = $app->getUserState('manufacturers_list.category', '');
			$listFilters         = $app->input->get('manufacturers_list', array(), 'array');
			$newValue            = isset($listFilters['category']) ? $listFilters['category'] : null;

			if ($newValue !== null)
			{
				$categoryFilterValue = $newValue;
				$app->setUserState('manufacturers_list.category', $newValue);
			}

			if ($app->input->get('reset_flag', '') == '1')
			{
				$categoryFilterValue = null;
				$app->setUserState('manufacturers_list.category', $categoryFilterValue);
			}

			if (!empty($categoryFilterValue))
			{
				$model->setState('manufacturers_list.category', $categoryFilterValue);
			}
		}

		$this->items      = $model->getItems();
		$this->state      = $model->getState();
		$this->pagination = $model->getPagination();

		/** @var RForm filterForm */
		$this->filterForm                   = $model->getForm();
		$this->activeFilters                = $model->getActiveFilters();
		$this->stoolsOptions['searchField'] = 'search_manufacturers';

		if (!empty($categoryFilterValue))
		{
			$this->filterForm->setValue('category', 'manufacturers_list', $categoryFilterValue);
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
		return Text::_('COM_REDSHOPB_MANUFACTURER_LIST_TITLE');
	}

	/**
	 * Method to round up to the nearest percision
	 *
	 * @param   float  $number     the number to be rounded
	 * @param   int    $precision  the decimal procision point to round to
	 *
	 * @return float
	 */
	protected function roundUp($number,$precision = 0)
	{
		$fig = (int) str_pad('1', $precision, '0');

		return (ceil($number * $fig) / $fig);
	}
}
