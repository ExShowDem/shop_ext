<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Helpers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Tax helper.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Helpers
 * @since       1.6
 */
class RedshopbHelperTax
{
	/**
	 * Get tax customer info
	 *
	 * @param   int     $customerId    Customer id
	 * @param   string  $customerType  Customer type
	 *
	 * @return array
	 */
	public static function getTaxCustomerInfo($customerId = 0, $customerType = '')
	{
		$app = Factory::getApplication();

		if ($customerId == 0)
		{
			$customerId = $app->getUserState('shop.customer_id',  0);
		}

		if ($customerType == '')
		{
			$customerType = $app->getUserState('shop.customer_type', '');
		}

		$stateId   = null;
		$countryId = null;

		if (!$customerType || !$customerId)
		{
			return array(
				'country_id' => $countryId,
				'state_id' => $stateId
			);
		}

		$companyId      = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);
		$vendorEntity   = RedshopbEntityCompany::getInstance($companyId)->getVendor();
		$config         = RedshopbApp::getConfig();
		$taxBasedOn     = $vendorEntity->get('tax_based_on');
		$calculateVatOn = $vendorEntity->get('calculate_vat_on');

		if (!$taxBasedOn)
		{
			$taxBasedOn = $config->get('tax_based_on', 'vendor');
		}

		if (!$calculateVatOn)
		{
			$calculateVatOn = $config->get('calculate_vat_on', 'payment');
		}

		switch ($customerType)
		{
			case 'company':
				$customerEntity = RedshopbEntityCompany::getInstance($customerId);
				break;
			case 'department':
				$customerEntity = RedshopbEntityDepartment::getInstance($customerId);
				break;
			case 'employee':
			default:
				$customerEntity = RedshopbEntityUser::getInstance($customerId);
				break;
		}

		switch ($taxBasedOn)
		{
			case 'customer':
			case 'eu-mode';
				self::getCountryState($customerEntity, $countryId, $stateId, $calculateVatOn);

				if (!$countryId)
				{
					if ($customerType == 'employee')
					{
						$departmentId = RedshopbHelperDepartment::getDepartmentIdByCustomer($customerId, $customerType);

						if ($departmentId)
						{
							$customerEntity = RedshopbEntityDepartment::getInstance($departmentId);
							self::getCountryState($customerEntity, $countryId, $stateId, $calculateVatOn);
						}
					}

					if (!$countryId && ($customerType == 'employee' || $customerType == 'department'))
					{
						$companyId = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);

						if ($companyId)
						{
							self::getCountryState($customerEntity, $countryId, $stateId, $calculateVatOn);
						}
					}
				}

				break;
			case 'vendor':
			default:
				switch ($calculateVatOn)
				{
					case 'shipping':
						$vendorShippingAddressEntity = $vendorEntity->getDefaultShippingAddress();

						if ($vendorShippingAddressEntity->isLoaded())
						{
							$countryId = $vendorShippingAddressEntity->get('country_id');
							$stateId   = $vendorShippingAddressEntity->get('state_id');

							// If found address - break, otherwise use payment address
							break;
						}

					case 'payment':
					default:
						$vendorPaymentAddressEntity = $vendorEntity->getAddress();

						if ($vendorPaymentAddressEntity->isLoaded())
						{
							$countryId = $vendorPaymentAddressEntity->get('country_id');
							$stateId   = $vendorPaymentAddressEntity->get('state_id');
						}

						break;
				}

				break;
		}

		if (!$countryId)
		{
			$countryId = $config->get('default_country_id', 59);
		}

		return array(
			'country_id' => $countryId,
			'state_id' => $stateId
		);
	}

	/**
	 * Get Country and State
	 *
	 * @param   object      $customerEntity  Customer entity
	 * @param   integer     $countryId       Country id
	 * @param   integer     $stateId         State id
	 * @param   string      $calculateVatOn  Flag based on
	 *
	 * @return  void
	 */
	public static function getCountryState($customerEntity, &$countryId, &$stateId, $calculateVatOn)
	{
		switch ($calculateVatOn)
		{
			case 'shipping':
				$shippingAddress = $customerEntity->getDefaultShippingAddress();

				if ($shippingAddress->isLoaded())
				{
					$countryId = $shippingAddress->get('country_id');
					$stateId   = $shippingAddress->get('state_id');

					// If found address - break, otherwise use payment address
					break;
				}

			case 'payment':
			default:

				$paymentAddress = $customerEntity->getAddress();

				if ($paymentAddress->isLoaded())
				{
					$countryId = $paymentAddress->get('country_id');
					$stateId   = $paymentAddress->get('state_id');
				}
				break;
		}
	}

	/**
	 * Get product tax rates
	 *
	 * @param   array   $productIds    Product ids
	 * @param   int     $customerId    Customer id
	 * @param   string  $customerType  Customer type
	 *
	 * @return  array
	 */
	public static function getProductsTaxRates($productIds, $customerId = 0, $customerType = '')
	{
		if (empty($productIds))
		{
			return array();
		}

		$app = Factory::getApplication();

		if ($customerId == 0)
		{
			$customerId = $app->getUserState('shop.customer_id',  0);
		}

		if ($customerType == '')
		{
			$customerType = $app->getUserState('shop.customer_type', '');
		}

		if (!$customerId || !$customerType)
		{
			return array();
		}

		$companyId         = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);
		$config            = RedshopbApp::getConfig();
		$company           = RedshopbEntityCompany::getInstance($companyId);
		$vendorEntity      = $company->getVendor();
		$customerTaxExempt = $vendorEntity->get('customer_tax_exempt', 0);

		// Inherit from global configuration
		if ($customerTaxExempt == 0)
		{
			$customerTaxExempt = $config->get('customer_tax_exempt_global', 0);
		}

		// Tax exempt allowed and company has tax exemption
		if ($customerTaxExempt == 1 && $company->get('tax_exempt'))
		{
			return array();
		}

		static $productsTaxes = array();
		$idsForSelect         = array();
		$foundIds             = array();

		$db               = Factory::getDbo();
		$customerTaxInfo  = self::getTaxCustomerInfo($customerId, $customerType);
		$customerTaxGroup = $vendorEntity->get('tax_group_id');

		foreach ($productIds as $productId)
		{
			if (array_key_exists($productId, $productsTaxes))
			{
				$foundIds[$productId] = $productsTaxes[$productId];
			}
			else
			{
				$idsForSelect[] = (int) $productId;
			}
		}

		$user               = Factory::getUser();
		$availableCompanies = RedshopbHelperACL::listAvailableCompaniesAndParents($user->id);
		$or                 = array($db->qn('t.company_id') . ' IS NULL');

		if (!empty($availableCompanies))
		{
			$or[] = $db->qn('t.company_id') . ' IN (' . $availableCompanies . ')';
		}

		if (!array_key_exists(0, $productsTaxes) && $customerTaxGroup)
		{
			$query = $db->getQuery(true)
				->select('t.id, t.name, t.tax_rate, t.is_eu_country, tg.id AS tax_group_id')
				->from($db->qn('#__redshopb_tax', 't'))
				->leftJoin($db->qn('#__redshopb_tax_group_xref', 'tgx') . ' ON tgx.tax_id = t.id')
				->leftJoin($db->qn('#__redshopb_tax_group', 'tg') . ' ON tg.id = tgx.tax_group_id')
				->where('t.country_id = ' . (int) $customerTaxInfo['country_id'])
				->where('(t.state_id = ' . (int) $customerTaxInfo['state_id'] . ' OR t.state_id IS NULL)')
				->where('t.state = 1')
				->where('tg.state = 1')
				->where('tg.id = ' . (int) $customerTaxGroup)
				->where('(' . implode(' OR ', $or) . ')')
				->order('t.tax_rate ASC');

			$productsTaxes[0] = $db->setQuery($query)->loadObjectList();
		}

		if (array_key_exists(0, $productsTaxes) && !empty($productsTaxes[0]))
		{
			$foundIds[0] = $productsTaxes[0];
		}

		if (count($idsForSelect) > 0)
		{
			$query       = $db->getQuery(true)
				->select('t.id, t.name, t.tax_rate, t.is_eu_country, p.id AS product_id, tg.id AS tax_group_id')
				->from($db->qn('#__redshopb_tax', 't'))
				->leftJoin($db->qn('#__redshopb_tax_group_xref', 'tgx') . ' ON tgx.tax_id = t.id')
				->leftJoin($db->qn('#__redshopb_tax_group', 'tg') . ' ON tg.id = tgx.tax_group_id')
				->leftJoin($db->qn('#__redshopb_product', 'p') . ' ON p.tax_group_id = tg.id')
				->where('t.country_id = ' . (int) $customerTaxInfo['country_id'])
				->where('(t.state_id = ' . (int) $customerTaxInfo['state_id'] . ' OR t.state_id IS NULL)')
				->where('t.state = 1')
				->where('tg.state = 1')
				->where('p.id IN (' . implode(',', $idsForSelect) . ')')
				->where('(' . implode(' OR ', $or) . ')')
				->order('t.tax_rate ASC');
			$results     = $db->setQuery($query)->loadObjectList();
			$selectedIds = array();

			if (!empty($results))
			{
				foreach ($results as $result)
				{
					$productId = $result->product_id;
					unset($result->product_id);

					if (!isset($productsTaxes[$productId]))
					{
						$productsTaxes[$productId] = array();
					}

					$productsTaxes[$productId][] = $result;
					$foundIds[$productId]        = $result;
					$selectedIds[$productId]     = true;
				}
			}

			foreach ($idsForSelect as $oneId)
			{
				if (!isset($selectedIds[$oneId]))
				{
					$productsTaxes[$oneId] = array();
					$foundIds[$oneId]      = array();
				}
			}
		}

		return $foundIds;
	}

	/**
	 * Get tax rates
	 *
	 * @param   integer     $customerId            Customer id
	 * @param   string      $customerType          Customer type
	 * @param   integer     $productId             Product id
	 * @param   boolean     $forceProductTaxRates  Force just product tax rates
	 *
	 * @return false|array
	 */
	public static function getTaxRates($customerId = 0, $customerType = '', $productId = 0, $forceProductTaxRates = false)
	{
		$results = self::getProductsTaxRates(array($productId), $customerId, $customerType);

		if (empty($results))
		{
			return false;
		}

		$return = array();

		if (array_key_exists($productId, $results) && !empty($results[$productId]))
		{
			$return = $results[$productId];

			if (!$forceProductTaxRates)
			{
				return $return;
			}
		}

		if (!$forceProductTaxRates && array_key_exists(0, $results) && !empty($results[0]))
		{
			$return = array_merge($return, $results[0]);
		}

		return $return;
	}

	/**
	 * Get product tax
	 *
	 * @param   int     $productId     Product id
	 * @param   int     $customerId    Customer id
	 * @param   string  $customerType  Customer type
	 *
	 * @return float
	 */
	public static function getProductTax($productId = 0, $customerId = 0, $customerType = '')
	{
		$taxesRateData = self::getTaxRates($customerId, $customerType, $productId);

		if (!is_array($taxesRateData))
		{
			$taxesRateData = array($taxesRateData);
		}

		$taxRate = 0;

		if (!empty($taxesRateData))
		{
			foreach ($taxesRateData as $taxRateData)
			{
				$taxRate += $taxRateData->tax_rate;
			}
		}

		return $taxRate;
	}
}
