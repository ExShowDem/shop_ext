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
 * Trait for entities with a company
 *
 * @since  2.0
 */
trait RedshopbEntityTraitCompany
{
	/**
	 * Company of the item
	 *
	 * @var  RedshopbEntityCompany
	 */
	protected $company;

	/**
	 * Get the company this item belongs to
	 *
	 * @return  RedshopbEntityCompany
	 */
	public function getCompany()
	{
		if (null === $this->company)
		{
			$this->loadCompany();
		}

		return $this->company;
	}

	/**
	 * Load company from DB
	 *
	 * @return  self
	 */
	protected function loadCompany()
	{
		$this->company = RedshopbEntityCompany::getInstance();

		$item = $this->getItem();

		if (!$item || !$item->company_id)
		{
			return $this;
		}

		$this->company = RedshopbEntityCompany::load($item->company_id);

		return $this;
	}
}
