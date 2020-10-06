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
 * Trait for entities with a department
 *
 * @since  2.0
 */
trait RedshopbEntityTraitDepartment
{
	/**
	 * Department owning this cart
	 *
	 * @var  RedshopbEntityDepartment
	 */
	protected $department;

	/**
	 * Get the department this item belongs to
	 *
	 * @return  RedshopbEntityDepartment
	 */
	public function getDepartment()
	{
		if (null === $this->department)
		{
			$this->loadDepartment();
		}

		return $this->department;
	}

	/**
	 * Load department from DB
	 *
	 * @return  self
	 */
	protected function loadDepartment()
	{
		$this->department = RedshopbEntityDepartment::getInstance();

		$item = $this->getItem();

		if (!$item || !$item->department_id)
		{
			return $this;
		}

		$this->department = RedshopbEntityDepartment::load($item->department_id);

		return $this;
	}
}
