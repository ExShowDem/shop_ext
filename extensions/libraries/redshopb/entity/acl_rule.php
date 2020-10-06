<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
/**
 * ACL Rule Entity.
 *
 * @since  2.0
 */
class RedshopbEntityAcl_Rule extends RedshopbEntity
{
	/**
	 * Check if current user is allowed by this rule.
	 *
	 * @param   integer  $assetId  Asset identifier from #__assets table
	 *
	 * @return  boolean
	 */
	public function allow($assetId)
	{
		$item = $this->getItem();

		if (!$item || !$item->joomla_asset_id)
		{
			return false;
		}

		$user = Factory::getUser();

		if ($user->authorise('core.admin'))
		{
			return true;
		}

		$rUser = RedshopbApp::getUser();

		if (!$rUser->isLoaded())
		{
			return false;
		}

		$roleId = $rUser->getRole()->id;

		if (!$roleId || $roleId !== $item->role_id)
		{
			return false;
		}

		return (bool) $item->granted;
	}
}
