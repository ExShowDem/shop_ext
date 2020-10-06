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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Date\Date;

/**
 * A Wallet helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperWallet
{
	/**
	 * Get the money amount for specific currency. If currency is not set
	 * amount for all currencies are returned.
	 *
	 * @param   integer  $walletId    The wallet id
	 * @param   integer  $currencyId  The currency id
	 * @param   bool     $hideZeros   To hide currencies with value 0 or not
	 *
	 * @return  float|object list  The amount of money available in the wallet, in the given currency
	 */
	public static function getMoneyAmount($walletId, $currencyId = 0, $hideZeros = false)
	{
		if ($walletId)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select(
				array(
					$db->qn('wm.amount', 'amount'),
					$db->qn('c.alpha3', 'alpha'),
					$db->qn('c.name', 'currency'),
					$db->qn('c.symbol', 'symbol')
				)
			)
				->from($db->qn('#__redshopb_wallet_money', 'wm'))
				->innerJoin($db->qn('#__redshopb_currency', 'c') . ' ON ' . $db->qn('wm.currency_id') . ' = ' . $db->qn('c.id'))
				->where($db->qn('wm.wallet_id') . ' = ' . (int) $walletId);

			if ($hideZeros)
			{
				$query->where($db->qn('wm.amount') . ' <> 0');
			}

			if ($currencyId != 0)
			{
				$query->where($db->qn('wm.currency_id') . ' = ' . $db->q($currencyId));

				$db->setQuery($query);

				return (float) $db->loadResult();
			}
			else
			{
				$db->setQuery($query);

				return $db->loadAssocList();
			}
		}
		else
		{
			return 0.0;
		}
	}

	/**
	 * Get the wallet id of the given user. If $userId is not set,
	 * wallet of currently logged in user is returned.
	 * This function also checks if wallet is usable (not expired & company use wallets).
	 *
	 * @param   int     $userId     The user id.
	 * @param   string  $userType   User type : redshopb/joomla.
	 * @param   bool    $checkTime  Use check time period
	 *
	 * @return  integer|null  The wallet id or null if no wallet
	 */
	public static function getUserWalletId($userId = 0, $userType = 'redshopb', $checkTime = true)
	{
		$db   = Factory::getDbo();
		$now  = Date::getInstance()->toSql();
		$user = RedshopbHelperUser::getUser($userId, $userType);

		if (is_null($user->wallet) || $user->wallet == 0)
		{
			// Wallet doesn't exists, lets create new one for this user
			$walletTable = RedshopbTable::getAdminInstance('Wallet');

			if ($walletTable->save(array()))
			{
				$walletId = $walletTable->id;

				// Check if we managed to create new wallet
				if ($walletId)
				{
					// Update user record
					$query = $db->getQuery(true)
						->update($db->qn('#__redshopb_user'))
						->set($db->qn('wallet_id') . ' = ' . (int) $walletId)
						->where($db->qn('id') . ' = ' . (int) $user->id);
					$db->setQuery($query);

					if ($db->execute())
					{
						return $walletId;
					}
				}
			}
		}

		$query = $db->getQuery(true)
			->select(
				array(
					$db->qn('u.wallet_id', 'id'),
					$db->qn('w.start_date', 'start'),
					$db->qn('w.end_date', 'end')
				)
			)
			->from($db->qn('#__redshopb_user', 'u'))
			->innerJoin($db->qn('#__redshopb_wallet', 'w') . ' ON u.wallet_id = w.id')
			->where('u.id = ' . $db->quote($user->id));

		$db->setQuery($query);

		$wallet = $db->loadObject();

		if (!is_null($wallet))
		{
			if (($now >= $wallet->start && ($wallet->end == '0000-00-00 00:00:00' || $now <= $wallet->end) || !$checkTime))
			{
				return $wallet->id;
			}

			Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_USER_WALLET_NOT_ACTIVE'), 'warning');
		}

		return 0;
	}

	/**
	 * Get user wallet with all credit and expiration.
	 *
	 * @param   int     $userId     The user id.
	 * @param   string  $userType   User type : redshopb/joomla.
	 * @param   bool    $hideZeros  To hide currencies with value 0 or not
	 *
	 * @return object Wallet object.
	 */
	public static function getUserWallet($userId = 0, $userType = 'redshopb', $hideZeros = false)
	{
		$db   = Factory::getDbo();
		$user = RedshopbHelperUser::getUser($userId, $userType);

		// Check if user exists
		if (is_null($user))
		{
			return null;
		}

		$walletId = $user->wallet;

		if (is_null($walletId) || $walletId == 0)
		{
			// Wallet doesn't exists, lets create new one for this user
			$walletTable = RedshopbTable::getAdminInstance('Wallet');

			if ($walletTable->save(array()))
			{
				$walletId = $walletTable->id;

				// Check if we managed to create new wallet
				if ($walletId)
				{
					// Update user record
					$query = $db->getQuery(true)
						->update($db->qn('#__redshopb_user'))
						->set($db->qn('wallet_id') . ' = ' . (int) $walletId)
						->where($db->qn('id') . ' = ' . (int) $user->id);
					$db->setQuery($query)->execute();
				}
			}
		}

		$query  = $db->getQuery(true)
			->select(
				array(
					$db->qn('id'),
					$db->qn('start_date', 'start'),
					$db->qn('end_date', 'end'),
				)
			)
			->from($db->qn('#__redshopb_wallet'))
			->where($db->qn('id') . ' = ' . (int) $walletId);
		$wallet = $db->setQuery($query)->loadObject();

		if (is_null($wallet))
		{
			return null;
		}

		$wallet->credit = self::getMoneyAmount($wallet->id, 0, $hideZeros);

		return $wallet;
	}

	/**
	 * Check if there is an any record of given currency in wallet.
	 *
	 * @param   int  $walletId  Wallet id.
	 * @param   int  $currency  Currency id.
	 *
	 * @return boolean
	 */
	public static function currencyExists($walletId, $currency)
	{
		if ((int) $walletId > 0 && (int) $currency > 0)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*')
				->from($db->qn('#__redshopb_wallet_money', 'wm'))
				->where($db->qn('wm.wallet_id') . ' = ' . (int) $walletId)
				->where($db->qn('wm.currency_id') . ' = ' . (int) $currency);

			$db->setQuery($query);
			$result = $db->loadObject();

			if (!empty($result))
			{
				return true;
			}

			return false;
		}
		else
		{
			return false;
		}
	}
}
