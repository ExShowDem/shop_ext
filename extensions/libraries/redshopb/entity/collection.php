<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Collection Entity.
 *
 * @since  2.0
 */
class RedshopbEntityCollection extends RedshopbEntity
{
	use RedshopbEntityTraitProducts
	{
		searchProducts as traitSearchProducts;
	}

	/**
	 * Search on this collection products
	 *
	 * @param   array  $modelState  State for the Products model
	 *
	 * @return  RedshopbEntitiesCollection
	 *
	 * @since   2.0
	 */
	public function searchProducts($modelState = array())
	{
		// Force collection filter
		$modelState['filter.product_collection'] = $this->id;

		return $this->traitSearchProducts($modelState);
	}
}
