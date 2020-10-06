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
 * Trait for entities with a user
 *
 * @since  2.0
 */
trait RedshopbEntityTraitUser
{
	/**
	 * User of the item
	 *
	 * @var  RedshopbEntityUser
	 */
	protected $user;

	/**
	 * Get the item user
	 *
	 * @return  RedshopbEntityUser
	 */
	public function getUser()
	{
		if (null === $this->user)
		{
			$this->loadUser();
		}

		return $this->user;
	}

	/**
	 * Load owner from DB
	 *
	 * @return  self
	 */
	protected function loadUser()
	{
		$this->user = RedshopbEntityUser::getInstance();

		$item = $this->getItem();

		if (!$item || !$item->user_id)
		{
			return $this;
		}

		$this->user = RedshopbEntityUser::load($item->user_id);

		return $this;
	}
}
