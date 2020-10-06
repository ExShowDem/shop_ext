<?php
/**
 * @package     Aesir\Commerce\Libraries\Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * A CartObject helper.
 *
 * @since 1.13.2
 */
class RedshopbHelperCart_Object extends Registry
{
	/**
	 * Constructor
	 *
	 * @param   mixed  $data  The data to bind to the new RedshopbHelperCart_Object object.
	 *
	 * @since   2.4.0
	 */
	public function __construct($data = null)
	{
		parent::__construct($data);

		$this->def('items', array());
		$this->def('offers', array());
	}

	/**
	 * Adds an item to the cart
	 *
	 * @param   string   $hash   Unique item hash for identification
	 * @param   array    $item   The item itself
	 *
	 * @return  self
	 */
	public function addItem($hash, array $item)
	{
		$item['hash'] = $hash;

		if (false === array_key_exists('params', $item))
		{
			$item['params'] = new Registry;
		}

		$this->append('items', $item);

		return $this;
	}

	/**
	 * Removes an item from the cart
	 *
	 * @param   string   $hash   Unique item hash for identification
	 *
	 * @return  self
	 */
	public function removeItem($hash)
	{
		$items = $this->get('items', array());

		foreach ($items as $key => $item)
		{
			if ($item['hash'] === $hash)
			{
				unset($items[$key]);
			}
		}

		$this->set('items', $items);

		return $this;
	}

	/**
	 * Get a single item based on its hash
	 *
	 * @param   string   $hash   Unique item hash for identification
	 *
	 * @return   array|null
	 */
	public function getItem($hash)
	{
		$items = $this->get('items', array());

		foreach ($items as $item)
		{
			if ($item['hash'] === $hash)
			{
				return $item;
			}
		}

		return null;
	}

	/**
	 * Get the total quantity of an item, including items split by {@see RedshopbHelperCart::splitQuantityMultiplications()}
	 *
	 * @param   string   $hash   Unique item hash for identification
	 *
	 * @return   integer|float
	 */
	public function getItemQuantity($hash)
	{
		$items = $this->get('items', array());

		$quantities = array();

		foreach ($items as $item)
		{
			if ($item['hash'] === $hash)
			{
				$quantities[] = $item['quantity'];
			}
		}

		return array_sum($quantities);
	}

	/**
	 * Changes the item parameters
	 *
	 * @param   string   $hash     Unique item hash for identification
	 * @param   array    $params   Array of parameters
	 *
	 * @return   self
	 */
	public function changeItemParams($hash, array $params)
	{
		$items = $this->get('items', array());

		foreach ($items as &$item)
		{
			if ($item['hash'] === $hash)
			{
				$item['params']->loadArray($params);
			}
		}

		$this->set('items', $items);

		return $this;
	}

	/**
	 * Generates a unique item hash used for identification
	 *
	 * @param   array   $item   Item to generate hash from
	 *
	 * @return  string
	 */
	public function generateItemHash(array $item)
	{
		$dispatcher = RFactory::getDispatcher();

		$dispatcher->trigger('onAECCartObjectGenerateHashBeforeUnset', array(&$item));

		unset(
			$item['accessories'],
			$item['keyAccessories'],
			$item['price_without_discount'],
			$item['price'],
			$item['currency'],
			$item['price_multiple'],
			$item['price_multiple_of'],
			$item['quantity'],
			$item['subtotal'],
			$item['total_tax'],
			$item['taxes'],
			$item['subtotal_with_tax'],
			$item['params']
		);

		$dispatcher->trigger('onAECCartObjectGenerateHashAfterUnset', array(&$item));

		return md5(serialize($item));
	}

	/**
	 * Checks if an item already exists in the cart
	 *
	 * @param   string   $hash   Unique item hash for identification
	 *
	 * @return  boolean
	 */
	public function itemExists($hash)
	{
		return $this->exists('items') ? in_array($hash, $this->extract('items')->flatten()) : false;
	}

	/**
	 * Adds an offer to the cart
	 *
	 * @param   integer   $offerId   Offer id
	 * @param   array     $offer     The offer data
	 *
	 * @return  self
	 */
	public function addOffer($offerId, array $offer)
	{
		$this->set("offers.{$offerId}", $offer);

		return $this;
	}

	/**
	 * Removes an offer from the cart
	 *
	 * @param   integer   $offerId   Offer id
	 *
	 * @return  self
	 */
	public function removeOffer($offerId)
	{
		$offers = $this->get('offers', array());

		unset($offers[$offerId]);

		$this->set('offers', $offers);

		return $this;
	}

	/**
	 * Checks if an offer already exists in the cart
	 *
	 * @param   integer   $offerId   Offer id
	 *
	 * @return  boolean
	 */
	public function offerExists($offerId)
	{
		return $this->exists("offers.{$offerId}");
	}
}
