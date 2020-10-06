<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Role Entity
 *
 * @since  2.0
 */
final class RedshopbEntityRole extends RedshopbEntity
{
	use RedshopbEntityTraitCompany;

	/**
	 * Role type
	 *
	 * @var  RedshopbEntityRole_Type
	 */
	protected $type;

	/**
	 * Allow to set type
	 *
	 * @param   RedshopbEntityRole_Type  $type  Role type
	 *
	 * @return  self
	 */
	public function setType(RedshopbEntityRole_Type $type)
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * Get the user role
	 *
	 * @return  RedshopbEntityRole_Type
	 *
	 * @since   2.0
	 */
	public function getType()
	{
		if (null === $this->type)
		{
			if ($this->loadItem())
			{
				$this->type = RedshopbEntityRole_Type::getInstance($this->get('role_type_id'))->loadItem();
			}
		}

		return $this->type;
	}
}
