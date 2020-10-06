<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
/**
 * Tax Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelTax extends RedshopbModelAdmin
{
	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		if (property_exists($item, 'tax_groups'))
		{
			$item->tax_groups = Joomla\Utilities\ArrayHelper::fromObject($item->tax_groups);
		}

		return $item;
	}

	/**
	 * Called before delete / store / publish
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True on success.
	 */
	protected function additionalACLCheck($record)
	{
		if (!parent::additionalACLCheck($record))
		{
			return false;
		}

		if (!RedshopbHelperACL::isSuperAdmin())
		{
			$table = $this->getTable();
			$user  = Factory::getUser();
			$table->load($record->id);
			$editAllowedCompanies = RedshopbHelperACL::listAvailableCompanies($user->id);

			if (!$table->get('company_id')
				|| !in_array($table->get('company_id'), explode(',', $editAllowedCompanies)))
			{
				return false;
			}
		}

		return true;
	}
}
