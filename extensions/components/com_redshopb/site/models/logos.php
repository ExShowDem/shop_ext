<?php
/**
 * @package    Redshopb.Backend
 *
 * @copyright  Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Orders Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Logos
 * @since       1.0
 */
class RedshopbModelLogos extends RedshopbModelList
{
	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   12.2
	 */
	public function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__redshopb_logos'));

		$types = $this->getState('type');

		if ($types)
		{
			foreach ($types as $type)
			{
				$query->where($db->qn('type') . '=' . $db->q($type), 'OR');
			}
		}

		return $query;
	}
}
