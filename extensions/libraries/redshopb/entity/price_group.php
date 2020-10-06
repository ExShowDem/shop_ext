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
 * Price_Group Entity.
 *
 * @since  2.0
 */
class RedshopbEntityPrice_Group extends RedshopbEntity
{
	/**
	 * Get the associated table
	 *
	 * @param   string  $name  Main name of the Table. Example: Article for ContentTableArticle
	 *
	 * @return  RTable
	 */
	public function getTable($name = null)
	{
		$name = is_null($name) ? 'Customer_Price_Group' : $name;

		return parent::getTable($name);
	}
}
