<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Base
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

/**
 * Redshopb Base Table
 *
 * @package     Redshopb
 * @subpackage  Base
 * @since       1.0
 */
class RedshopbTableNestedAsset extends RedshopbTableNested
{
	/**
	 * Overriden to set the right parent ID in asset table
	 *
	 * @param   Table    $table  A Table object (optional) for the asset parent
	 * @param   integer  $id     The id (optional) of the content.
	 *
	 * @return  integer
	 */
	protected function _getAssetParentId(Table $table = null, $id = null)
	{
		return $this->getRedshopbAssetParentId($table, $id);
	}
}
