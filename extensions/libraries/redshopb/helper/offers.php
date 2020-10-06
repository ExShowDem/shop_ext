<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
/**
 * A offer helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperOffers
{
	/**
	 * Get the product count in offer
	 *
	 * @param   int  $offerId    offer id
	 * @param   int  $productId  product id
	 *
	 * @return  integer  count of rows
	 */
	public static function getOfferItemsCount($offerId, $productId = 0)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->qn('#__redshopb_offer_item_xref'))
			->where('offer_id = ' . (int) $offerId);

		if ($productId > 0)
		{
			$query->where('product_id = ' . (int) $productId);
		}

		return $db->setQuery($query, 0, 1)->loadResult();
	}

	/**
	 * Get the product total cost
	 *
	 * @param   int  $offerId  offer id
	 *
	 * @return  integer  total cost of products
	 */
	public static function getTotalCost($offerId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('SUM(total)')
			->from($db->qn('#__redshopb_offer_item_xref'))
			->where('offer_id = ' . (int) $offerId);

		return $db->setQuery($query, 0, 1)->loadResult();
	}

	/**
	 * Get the name of the offer
	 *
	 * @param   int  $offerId  offer id
	 *
	 * @return  string name
	 */
	public static function getOfferName($offerId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('name')
			->from($db->qn('#__redshopb_offer'))
			->where('id = ' . (int) $offerId);

		return $db->setQuery($query, 0, 1)->loadResult();
	}

	/**
	 * Get the customer type of offer
	 *
	 * @param   int  $offerId  offer id
	 *
	 * @return  string name
	 */
	public static function getOfferCustomerType($offerId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(
				array(
					'CASE customer_type WHEN ' . $db->q('company') . ' THEN company_id '
					. 'WHEN ' . $db->q('department') . ' THEN department_id '
					. 'WHEN ' . $db->q('employee') . ' THEN user_id '
					. 'ELSE NULL END AS customer_id',
					'customer_type'
				)
			)
			->from($db->qn('#__redshopb_offer'))
			->where('id = ' . (int) $offerId);

		return $db->setQuery($query, 0, 1)->loadAssoc();
	}

	/**
	 * Get the product item count in offer
	 *
	 * @param   int  $offerId        offer id
	 * @param   int  $productId      product id
	 * @param   int  $productItemId  product Item id
	 *
	 * @return  integer  count of rows
	 */
	public static function getOfferProductItemsCount($offerId, $productId, $productItemId)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->qn('#__redshopb_offer_item_xref'))
			->where('offer_id = ' . (int) $offerId)
			->where('product_id = ' . (int) $productId);

		if ($productItemId)
		{
			$query->where('product_item_id = ' . (int) $productItemId);
		}
		else
		{
			$query->where('product_item_id IS NULL');
		}

		return $db->setQuery($query, 0, 1)->loadResult();
	}

	/**
	 * Change offer status
	 *
	 * @param   int     $offerId  Offer id
	 * @param   string  $status   Status
	 *
	 * @return  void
	 */
	public static function changeOfferStatus($offerId, $status)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->update($db->qn('#__redshopb_offer'))
			->set('status = ' . $db->q($status))
			->where('id = ' . (int) $offerId);
		$db->setQuery($query)->execute();
	}

	/**
	 * Get color badge for offer status
	 *
	 * @param   string  $status  Status name
	 *
	 * @return  string
	 */
	public static function getColorForStatus($status)
	{
		$html = '<span class="badge ';

		switch ($status)
		{
			case 'requested':
				break;
			case 'sent':
				$html .= 'badge-success';
				break;
			case 'accepted':
				$html .= 'badge-warning';
				break;
			case 'rejected':
				$html .= 'badge-important';
				break;
			case 'ordered':
				$html .= 'badge-info';
				break;
			default:
			case 'created':
				$html .= 'badge-inverse';
				break;
		}

		return  $html . '">' . $status . '</span>';
	}

	/**
	 * Check can use for current user
	 *
	 * @param   Table   $table         Offer table object
	 * @param   integer $joomlaUserId  Joomla user id
	 *
	 * @return  boolean
	 */
	public static function canUse($table, $joomlaUserId = 0)
	{
		if ($table->get('state', 0) != 1)
		{
			return false;
		}

		if (!$joomlaUserId)
		{
			$joomlaUserId = Factory::getUser()->id;
		}

		$userRole = RedshopbHelperUser::getUserRole($joomlaUserId);

		if ($userRole == 'superadmin')
		{
			return true;
		}

		$canImpersonate = RedshopbHelperACL::getPermissionInto('impersonate', 'order', 0, 'redshopb', $joomlaUserId);
		$customerType   = $table->get('customer_type');

		if ($canImpersonate)
		{
			switch ($customerType)
			{
				case 'company':
					$companiesCount = RedshopbHelperACL::listAvailableCompanies($joomlaUserId, 'comma', 0, '', 'redshopb.order.impersonate');
					$companiesCount = $companiesCount ? $companiesCount : 0;
					$companiesCount = explode(',', $companiesCount);

					if (!in_array($table->get('company_id'), $companiesCount))
					{
						return false;
					}
					break;
				case 'department':
					$departmentsCount = RedshopbHelperACL::listAvailableDepartments(
						$joomlaUserId, 'comma', 0, false, 0, '', 'redshopb.order.impersonate'
					);
					$departmentsCount = $departmentsCount ? $departmentsCount : 0;
					$departmentsCount = explode(',', $departmentsCount);

					if (!in_array($table->get('department_id'), $departmentsCount))
					{
						return false;
					}
					break;
				case 'employee':
					$employeeCount = RedshopbHelperACL::listAvailableEmployees(0, 0, 'comma', '', '', 0, 0, 'redshopb.order.impersonate');
					$employeeCount = $employeeCount ? $employeeCount : 0;
					$employeeCount = explode(',', $employeeCount);

					if (!in_array($table->get('user_id'), $employeeCount))
					{
						return false;
					}
					break;
			}
		}
		else
		{
			$user = RedshopbHelperUser::getUser($joomlaUserId, 'joomla');

			switch ($customerType)
			{
				case 'company':
					if ($table->get('company_id') != $user->company || $userRole != 'admin')
					{
						return false;
					}
					break;
				case 'department':
					if ($table->get('department_id') != $user->department || $userRole != 'hod')
					{
						return false;
					}
					break;
				case 'employee':
					if ($table->get('user_id') != $user->id)
					{
						return false;
					}
					break;
			}
		}

		return true;
	}

	/**
	 * Calculate offer discount
	 *
	 * @param   string   $discountType         Total or percent
	 * @param   float    $discount             Discount amount
	 * @param   float    $undiscountedPrice    Price before discount
	 *
	 * @return  float    $discountedPrice      Price after discount
	 */
	public static function calculateDiscount($discountType, $discount, $undiscountedPrice)
	{
		$discountedPrice = $undiscountedPrice;

		switch ($discountType)
		{
			case 'percent':
				$discountedPrice = $undiscountedPrice * ( (100 - $discount) / 100 );
				break;
			case 'total':
				$discountedPrice = $undiscountedPrice - $discount;
				break;
		}

		return $discountedPrice;
	}
}
