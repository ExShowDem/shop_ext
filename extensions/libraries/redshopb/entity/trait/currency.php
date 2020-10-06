<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity.Trait
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Trait for entities with a currency
 *
 * @since  2.0
 */
trait RedshopbEntityTraitCurrency
{
	/**
	 * Currency of the item
	 *
	 * @var  RedshopbEntityCurrency
	 */
	protected $currency;

	/**
	 * Get the item currency
	 *
	 * @return  RedshopbEntityCurrency
	 */
	public function getCurrency()
	{
		if (null === $this->currency)
		{
			$this->loadCurrency();
		}

		return $this->currency;
	}

	/**
	 * Load currency from DB
	 *
	 * @return  self
	 */
	protected function loadCurrency()
	{
		$this->currency = RedshopbEntityCurrency::getInstance();

		$item = $this->getItem();

		if (!$item || !$item->currency_id)
		{
			return $this;
		}

		$this->currency = RedshopbEntityCurrency::load($item->currency_id);

		return $this;
	}
}
