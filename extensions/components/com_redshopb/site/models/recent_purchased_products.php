<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Recently Purchased Products Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.6.60
 */
class RedshopbModelRecent_Purchased_Products extends RedshopbModelList
{
	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'recent_purchased_product_limit';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitstartField = 'auto';

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$select = array(
			$db->qn('oi.product_id', 'id'),
			$db->qn('oi.product_name', 'name'),
			$db->qn('oi.product_sku', 'sku'),
			$db->qn('oi.product_item_id', 'product_item_id'),
			$db->qn('oi.product_item_sku', 'product_item_sku'),
			$db->qn('oi.price', 'price'),
			$db->qn('oi.price_without_discount', 'retail_price'),
			$db->qn('oi.currency_id', 'currency'),
			$db->qn('o.created_date', 'order_date'),
			$db->qn('p.unit_measure_id', 'unit_measure_id'),
			$db->qn('p.category_id', 'category_id'),
			$db->qn('p.manufacturer_id', 'manufacturer_id')
		);

		// Volume pricing
		$volumePriceQuery = $db->getQuery(true);
		$volumePriceQuery->select('vp.id')
			->from($db->qn('#__redshopb_product_price', 'vp'))
			->where($db->qn('vp.type') . ' = ' . $db->q('product'))
			->where($db->qn('vp.type_id') . ' = ' . $db->qn('p.id'))
			->where('(' . $db->qn('vp.quantity_min') . ' IS NOT NULL OR ' . $db->qn('vp.quantity_max') . ' IS NOT NULL)');

		$query->select('(' . $volumePriceQuery . ' LIMIT 0, 1) AS hasVolumePricing');

		$query->select($select)
			->from($db->qn('#__redshopb_order_item', 'oi'))
			->join('inner', $db->qn('#__redshopb_order', 'o') . ' ON ' . $db->qn('o.id') . ' = ' . $db->qn('oi.order_id'))
			->join('left', $db->qn('#__redshopb_product', 'p') . ' ON ' . $db->qn('p.id') . ' = ' . $db->qn('oi.product_id'))
			->where($db->qn('p.state') . ' = 1')
			->where('(p.publish_date = ' . $db->q($db->getNullDate()) . ' OR p.publish_date <= STR_TO_DATE('
				. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where('(p.unpublish_date = ' . $db->q($db->getNullDate()) . ' OR p.unpublish_date >= STR_TO_DATE('
				. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where($db->qn('p.discontinued') . ' = 0')
			->where($db->qn('p.service') . ' = 0');

		// Exclude products set up as fee or freight
		$query2 = $db->getQuery(true)
			->select($db->qn('freight_product_id'))
			->from($db->qn('#__redshopb_company'))
			->where($db->qn('deleted') . ' = 0')
			->where($db->qn('freight_product_id') . ' IS NOT NULL');
		$query3 = $db->getQuery(true)
			->select($db->qn('product_id'))
			->from($db->qn('#__redshopb_fee'))
			->where($db->qn('product_id') . ' IS NOT NULL');

		$query->where($db->qn('p.id') . ' NOT IN (' . $query2->__toString() . ')');
		$query->where($db->qn('p.id') . ' NOT IN (' . $query3->__toString() . ')');

		$user      = RedshopbHelperCommon::getUser();
		$rsbUserId = RedshopbHelperUser::getUserRSid($user->id);

		if ($rsbUserId != 0 && $rsbUserId != '')
		{
			$query->select('COUNT(' . $db->qn('flpx.id') . ') AS ' . $db->qn('favoritelists'))
				->leftJoin(
					$db->qn('#__redshopb_favoritelist_product_xref', 'flpx') . ' ON ' . $db->qn('flpx.product_id') . ' = ' . $db->qn('oi.product_id')
				)
				->leftJoin(
					$db->qn('#__redshopb_favoritelist', 'fl') .
					' ON ' . $db->qn('fl.id') . ' = ' . $db->qn('flpx.favoritelist_id') . ' AND ' . $db->qn('fl.user_id') . ' = ' . (int) $rsbUserId
				);
		}

		// Limit companies based on allowed permissions (main warehouse or allowed companies' categories)
		if ($user->b2cMode)
		{
			$availableCompanies = RedshopbEntityCompany::getInstance($user->b2cCompany)->getTree(true, true);
			$query->where(
				'(' . $db->qn('p.company_id') . ' IN(' . implode(',', $availableCompanies) . ') OR ' . $db->qn('p.company_id') . ' IS NULL)'
			);
		}
		else
		{
			$isFromMainCompany = RedshopbHelperUser::isFromMainCompany($rsbUserId, 'employee');

			// This list not available for main company users and fro super admins
			if ($isFromMainCompany)
			{
				$query->where('0 = 1');
			}
			else
			{
				$companies = RedshopbHelperACL::listAvailableCompanies($user->id, 'comma', 0, '', 'redshopb.company.view', '', true);
				$companies = explode(',', $companies);

				if (!empty($companies))
				{
					// Exclude current company of user
					$userCompany = RedshopbHelperUser::getUserCompany();

					if ($userCompany)
					{
						$query->where($db->qn('o.customer_company') . ' = ' . (int) $userCompany->id);

						$key = array_search($userCompany->id, $companies);

						if ($key !== false)
						{
							unset($companies[$key]);
						}
					}
				}

				if (empty($companies))
				{
					$companies[] = 0;
				}

				$query->where(
					'(' . $db->qn('p.company_id') . ' IN ('
					. implode(',', $companies) . ') OR '
					. $db->qn('p.company_id') . ' IS NULL)'
				);
			}
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 'order_date';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));
		$query->group('oi.product_sku');

		return $query;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 */
	protected function populateState($ordering = 'order_date', $direction = 'desc')
	{
		$app   = Factory::getApplication();
		$limit = $app->getUserStateFromRequest('global.list.' . $this->limitField, $this->limitField, null, 'uint');

		if (is_null($limit))
		{
			$redshopbConfig = RedshopbApp::getConfig();

			if ($redshopbConfig->get('recent_purchased_list_limit') > 0)
			{
				$app->setUserState('global.list.' . $this->limitField, $redshopbConfig->get('recent_purchased_list_limit'));
			}
		}

		parent::populateState($ordering, $direction);
	}
}
