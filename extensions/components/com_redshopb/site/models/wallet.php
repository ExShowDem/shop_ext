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
 * Wallet Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelWallet extends RedshopbModelAdmin
{
	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   12.2
	 */
	public function save($data)
	{
		$userModel = RModelAdmin::getInstance('User', 'RedshopbModel');

		if (!$userModel->saveWallet($data['user_id'], $data['currency_id'], $data['amount'], array()))
		{
			return false;
		}

		return true;
	}

	/**
	 * List one Wallet for webservices
	 *
	 * @param   array  $keyData  Array of primary keys (currency id, user id)
	 *
	 * @return  object item
	 */
	public function getWalletItem($keyData)
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
			->join('LEFT', $db->quoteName('#__redshopb_wallet_money', 'wm') . ' ON (' . $db->quoteName('w.id') .
				' = ' . $db->quoteName('wm.wallet_id') . ')'
			)
			->where(
				$db->quoteName('u.id') . ' = ' . $keyData['user_id']
				. ' AND ' . $db->quoteName('wm.currency_id') . ' = ' . $keyData['currency_id']
				. ' AND ' . $db->quoteName('w.start_date') . ' <=  ' . $db->quote($now) . ' AND (' .
				$db->quoteName('w.end_date') . ' =  ' . $db->quote('0000-00-00 00:00:00') . '  OR '
				. $db->quoteName('w.end_date') . ' >=  ' . $db->quote($now) . ')'
			);

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Delete a wallet data
	 *
	 * @param   array  $keyData  Array of primary keys (currency id, user id)
	 *
	 * @return  boolean  True on success, False on error.
	 */
	public function deleteWallet($keyData)
	{
		$walletTable = RedshopbTable::getAdminInstance('Wallet');
		$walletId    = RedshopbHelperWallet::getUserWalletId($keyData['user_id'], 'redshopb', false);

		if (!$walletTable->delete($walletId))
		{
			return false;
		}

		return true;
	}
}
