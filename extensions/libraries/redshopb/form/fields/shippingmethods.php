<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

FormHelper::loadFieldClass('rlist');

/**
 * Shipping methods Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldShippingmethods extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Shippingmethods';

	/**
	 * A static cache.
	 *
	 * @var  array
	 */
	protected $cache = array();

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options = array();

		$items      = $this->getShippingMethods();
		$isShipper  = array();
		$isAutoCalc = array();

		// Build the field options.
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				if ($item->is_shipper)
				{
					$isShipper[] = $item->identifier;
				}

				if ($item->shipping_name === 'product_based_shipping_calculator')
				{
					$isAutoCalc[] = $item->identifier;
				}

				$options[] = HTMLHelper::_(
					'select.option', $item->identifier,
					Text::sprintf('COM_REDSHOPB_SHIPPING_CONFIGURATION_OPTION_TEMPLATE', $item->customerPriceGroupName, $item->data)
				);
			}
		}

		// Filter by is_shippers
		if ($this->getAttribute('filterShippers', 'false') == 'true')
		{
			$this->onchange            = "changeToShipper(this, [" . implode(',', $isShipper) . "], [" . implode(',', $isAutoCalc) . "])";
			$this->element['onchange'] = $this->onchange;
		}

		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Method to get the list of shipping methods.
	 *
	 * @return  array  An array of department names.
	 */
	protected function getShippingMethods()
	{
		if (empty($this->cache))
		{
			$db          = Factory::getDbo();
			$company     = RedshopbHelperACL::isSuperAdmin() ? RedshopbApp::getMainCompany() : RedshopbApp::getUser()->getCompany();
			$priceGroups = RedshopbEntityCompany::getInstance($company->id)->getPriceGroups()->ids();

			foreach ($priceGroups as $key => $priceGroup)
			{
				$priceGroups[$key] = $db->q($priceGroups[$key]);
			}

			$query = $db->getQuery(true)
				->select(
					array (
						$db->qn('sc.id', 'identifier'),
						$db->qn('sc.shipping_name', 'data'),
						$db->qn('sc.params'),
						$db->qn('sc.shipping_name'),
						$db->qn('pg.name', 'customerPriceGroupName')
					)
				)
				->from($db->qn('#__redshopb_shipping_configuration', 'sc'))
				->leftJoin($db->qn('#__redshopb_customer_price_group', 'pg') . ' ON pg.id = sc.owner_name')
				->where('sc.extension_name = ' . $db->q('com_redshopb'))
				->order('pg.name')
				->order('sc.shipping_name');

			if (!empty($priceGroups))
			{
				$query->where('sc.owner_name IN (' . implode(',', $priceGroups) . ')');
			}

			$db->setQuery($query);
			$result = $db->loadObjectList();

			if (is_array($result))
			{
				foreach ($result as $key => $value)
				{
					$registry = new Registry;
					$registry->loadString($value->params);
					$result[$key]->data       = $registry->get('shipping_title', $value->data);
					$result[$key]->is_shipper = $registry->get('is_shipper', 0);
				}

				$this->cache = $result;
			}
		}

		return $this->cache;
	}
}
