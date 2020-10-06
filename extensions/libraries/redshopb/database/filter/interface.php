<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Database.Filter
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Filterers interface
 *
 * @since  1.0
 */
interface RedshopbDatabaseFilterInterface
{
	/**
	 * Filter data
	 *
	 * @return  mixed
	 */
	public function filter();
}
