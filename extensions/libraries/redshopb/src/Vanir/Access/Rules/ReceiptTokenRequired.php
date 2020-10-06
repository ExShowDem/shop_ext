<?php
/**
 * @package     Vanir.Library
 * @subpackage  Access
 *
 * @copyright   Copyright (C) 2012 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

namespace Vanir\Access\Rules;


defined('_JEXEC') or die;

/**
 * Class ReceiptTokenRequired
 *
 * This will validate tokens used when accessing the receipt layout
 *
 * @package  Vanir\Access\Rules
 * @since    2.0
 */
class ReceiptTokenRequired extends Token
{
	/**
	 * Validates the token in the query string
	 *
	 * @param   \RedshopbEntityUser  $user   The user asking for permission
	 * @param   \JInput              $input  Input variables to be used by the rules
	 *
	 * @return   boolean
	 */
	protected function matchToken(\RedshopbEntityUser $user,\JInput $input)
	{
		$tokens = $this->getTokens($input);

		if (empty($tokens))
		{
			return false;
		}

		return $this->verify($this->getOrderIds($input), $tokens);
	}

	/**
	 * Converts the token string into an array
	 *
	 * @param   \JInput $input Input variables
	 *
	 * @return array
	 */
	private function getTokens(\JInput $input)
	{
		if (null !== $input->get('token', null))
		{
			return explode(',', $input->getString('token'));
		}

		return array();
	}

	/**
	 * Converts the order id(s) into an array
	 *
	 * @param   \JInput $input Input variables
	 *
	 * @return array
	 */
	private function getOrderIds(\JInput $input)
	{
		if (null !== $input->get('orderId', null))
		{
			return array($input->getInt('orderId'));
		}

		if (null !== $input->get('multipleOrderIds', null))
		{
			return explode(',', $input->getString('multipleOrderIds'));
		}

		return array();
	}

	/**
	 * Combines the order id(s) with the tokens and verifies them
	 *
	 * @param   array  $ids    List of order ids
	 * @param   array  $tokens List of tokens
	 *
	 * @return boolean
	 */
	private function verify(array $ids, array $tokens)
	{
		$data = array_combine($ids, $tokens);

		if (false === $data)
		{
			return false;
		}

		foreach ($data as $orderId => $token)
		{
			$order = \RedshopbEntityOrder::load($orderId);

			if (false === $order->verifyToken($token))
			{
				return false;
			}
		}

		return true;
	}
}
