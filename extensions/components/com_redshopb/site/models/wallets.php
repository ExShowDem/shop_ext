<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Date\Date;
/**
 * Wallets Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelWallets extends RedshopbModelList
{
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$now   = Date::getInstance()->toSql();
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select(
				$db->quoteName(
					array('w.id', 'wm.currency_id', 'wm.amount', 'w.start_date', 'w.end_date')
				)
			)
			->select($db->quoteName('u.id', 'user_id'))
			->from($db->qn('#__redshopb_wallet', 'w'))
			->join('LEFT', $db->quoteName('#__redshopb_user', 'u') . ' ON (' . $db->quoteName('w.id') . ' = ' . $db->quoteName('u.wallet_id') . ')')
			->join('LEFT', $db->quoteName('#__redshopb_wallet_money', 'wm') . ' ON (' . $db->quoteName('w.id')
				. ' = ' . $db->quoteName('wm.wallet_id') . ')'
			)
			->where(
				$db->quoteName('w.start_date') . ' <=  ' . $db->quote($now) . ' AND (' .
				$db->quoteName('w.end_date') . ' = ' . $db->quote('0000-00-00 00:00:00') . '  OR '
				. $db->quoteName('w.end_date') . ' >=  ' . $db->quote($now) . ')'
			);

		$currencyId = $this->getState('filter.currency_id');

		if (is_numeric($currencyId))
		{
			$query->where('wm.currency_id = ' . (int) $currencyId);
		}

		$userId = $this->getState('filter.user_id');

		if (is_numeric($userId))
		{
			$query->where('u.id = ' . (int) $userId);
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 'w.id';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}
}
