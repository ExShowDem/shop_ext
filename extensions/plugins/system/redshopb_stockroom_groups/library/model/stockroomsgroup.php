<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

JLoader::import('components.com_redshopb.models.stockrooms', JPATH_SITE);

/**
 * Stockrooms Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelStockroomsgroup extends RedshopbModelStockrooms
{
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$query            = parent::getListQuery();
		$db               = $this->getDbo();
		$stockroomGroupId = $this->getState('filter.stockroomGroupId');

		if (is_numeric($stockroomGroupId))
		{
			$query->leftJoin($db->qn('#__redshopb_stockroom_group_stockroom_xref', 'sgx') . ' ON sgx.stockroom_id = s.id')
				->where('sgx.stockroom_group_id = ' . (int) $stockroomGroupId);
		}

		return $query;
	}
}
