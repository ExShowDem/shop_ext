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
 * Country Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelCountry extends RedshopbModelAdmin
{
	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'product';

	/**
	 * Code field reference
	 *
	 * @var  string
	 */
	protected $codeField = 'alpha2';

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
