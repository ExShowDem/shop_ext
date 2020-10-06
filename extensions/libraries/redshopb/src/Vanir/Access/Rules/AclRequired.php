<?php
/**
 * @package     Vanir.Library
 * @subpackage  Access
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

namespace Vanir\Access\Rules;

use RInflector, RFactory;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Input\Input;
/**
 * Class AclRequired
 *
 * @package  Vanir\Access\Rules
 * @since    2.0
 */
class AclRequired extends Rule
{
	/**
	 * List of view => acl rule relations
	 *
	 * @var array
	 */
	protected $viewACL = array();

	/**
	 * List of permissions granted to the logged in user
	 *
	 * @var array
	 *
	 * @todo convert this to permissions of the user passed to the check function
	 */
	protected $viewPermissions = array();

	/**
	 * Method to grant or deny ACL permission to the user
	 *
	 * @param   \RedshopbEntityUser  $user   the user asking for permission
	 * @param   Input                $input  input variables to be used by the rules
	 *
	 * @return boolean
	 */
	protected function check($user, $input)
	{
		$view           = $input->getCmd('view');
		$pluralizedView = RInflector::pluralize($view);

		if ($this->checkAcl($view) || $this->checkAcl($pluralizedView))
		{
			return true;
		}

		$this->specificationContainer
			->addMessage(Text::sprintf('COM_REDSHOPB_ACL_NOT_HAVE_PERMISSION', $view), 'warning')
			->setRedirect();

		return false;
	}

	/**
	 * Method to check permissons based on view name
	 *
	 * @param   string  $viewName  view name to check
	 *
	 * @return boolean
	 */
	private function checkAcl($viewName)
	{
		$viewAcl = $this->getViewAcl();

		if (!array_key_exists($viewName, $viewAcl))
		{
			return true;
		}

		$viewPermissions = $this->getViewPermissions();

		if (!isset($viewPermissions[$viewAcl[$viewName]]) || $viewPermissions[$viewAcl[$viewName]])
		{
			return true;
		}

		return false;
	}

	/**
	 * Method to get an array of view acl relationships
	 *
	 * in the format of [$viewName => $permissionName]
	 *
	 * @return array
	 */
	private function getViewAcl()
	{
		if (!empty($this->viewACL))
		{
			return $this->viewACL;
		}

		$viewACL = array(
			'offers' => 'product',
			'users' => 'user',
			'addresses' => 'address',
			'companies' => 'company',
			'departments' => 'department',
			'manufacturers' => 'product',
			'collections' => 'mainwarehouse',
			'products' => 'product',
			'stockrooms' => 'company',
			'all_discounts' => 'product',
			'discount_debtor_groups' => 'product',
			'product_discount_groups' => 'product',
			'all_prices' => 'product',
			'price_debtor_groups' => 'product',
			'categories' => 'category',
			'orders' => 'order',
			'return_orders' => 'order',
			'layouts' => 'layout',
			'tags' => 'tag',
			'wash_care_specs' => 'mainwarehouse',
			'fields' => 'mainwarehouse',
			'filter_fieldsets' => 'product',
			'newsletter_lists' => 'mainwarehouse',
			'newsletters' => 'mainwarehouse',
			'shipping_rates' => 'product',
			'reports' => 'product',
			'templates' => 'product',
			'unit_measures' => 'product',
			'mypage' => 'order',
			'words' => 'mainwarehouse',
			'taxes' => 'product',
			'tax_groups' => 'product',
			'currencies' => 'product',
			'states' => 'product',
			'countries' => 'product');

		RFactory::getDispatcher()->trigger('onAfterComRedshopbGetViewsACL', array(&$viewACL));

		$this->viewACL = $viewACL;

		return $this->viewACL;
	}

	/**
	 * Method to get an array of view permissions
	 *
	 * @return array
	 */
	private function getViewPermissions()
	{
		if (!empty($this->viewPermissions))
		{
			return $this->viewPermissions;
		}

		$this->viewPermissions = \RedshopbHelperACL::getViewPermissions();

		return $this->viewPermissions;
	}
}
