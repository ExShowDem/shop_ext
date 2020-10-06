<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Upgrade
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

/**
 * Custom upgrade of Redshop b2b.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Upgrade
 * @since       1.6
 */
class Com_RedshopbUpdateScript_1_6_35
{
	/**
	 * Performs the upgrade for this version
	 *
	 * @return  true
	 */
	public function execute()
	{
		jimport('redshopb.table.table');
		jimport('redshopb.table.nested');
		jimport('redshopb.table.nested.asset');
		jimport('redshopb.table.webservices');
		jimport('redshopb.helper.acl');
		jimport('redshopb.helper.role');
		jimport('redshopb.model.admin');

		$this->mainCompanyUpdate();

		return true;
	}

	/**
	 * check for main type company and update if not present
	 *
	 * @return boolean
	 */
	public function mainCompanyUpdate()
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);

		$query->select(count('*'))
			->from($db->qn('#__redshopb_company'))
			->where($db->qn('type') . ' = ' . $db->q('main'));
		$db->setQuery($query);

		$companyCount = $db->loadResult();

		// Check if there is no main company
		if ($companyCount == 0)
		{
			$db = Factory::getDbo();

			$companyTable = RedshopbTable::getAdminInstance('Company', array(), 'com_redshopb');

			$companyTable->name            = 'Main Company';
			$companyTable->type            = 'main';
			$companyTable->level           = 1;
			$companyTable->path            = 'main';
			$companyTable->alias           = 'main';
			$companyTable->state           = 1;
			$companyTable->parent_id       = 1;
			$companyTable->customer_number = 'main';

			if (!$companyTable->store())
			{
				Log::add(Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR'), Log::WARNING, 'jerror');

				return false;
			}

			$companyId                   = $companyTable->id;
			$addressTable                = RedshopbTable::getAdminInstance('Address', array(), 'com_redshopb');
			$addressTable->customer_type = 'company';
			$addressTable->customer_id   = $companyId;
			$addressTable->type          = 2;

			if (!$addressTable->store())
			{
				Log::add(Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR'), Log::WARNING, 'jerror');

				return false;
			}

			$query = $db->getQuery(true);
			$query->update($db->qn('#__redshopb_company'))
				->set($db->qn('address_id') . ' = ' . (int) $addressTable->id)
				->where($db->qn('id') . ' = ' . $companyId);
			$db->setQuery($query);
			$db->execute();

			// Get the asset id and id of the root
			$query = $db->getQuery(true)
				->select(array('asset_id', 'id'))
				->from('#__redshopb_company')
				->where($db->qn('parent_id') . ' IS NULL or ' . $db->qn('parent_id') . ' = 0')
				->order($db->qn(array('lft', 'id')));
			$db->setQuery($query);
			$rootResult = $db->loadObjectList();

			$rootCompanyAssetID = $rootResult[0]->asset_id;

			$nestedClass = (stripos(get_parent_class($companyTable), 'nested') === false ? false : true);

			// Make an insert into asset table
			$asset            = Table::getInstance('Asset');
			$asset->name      = 'com_redshopb.company.' . $companyId;
			$asset->parent_id = $rootCompanyAssetID;
			$asset->rules     = '{}';
			$asset->title     = 'MAIN COMPANY';
			$asset->setLocation($rootCompanyAssetID, 'last-child');
			$asset->store();
			$assetId = $asset->id;

			$query = $db->getQuery(true)
				->update($db->qn('#__redshopb_company'))
				->set($db->qn('asset_id') . ' = ' . $assetId)
				->where($db->qn('id') . ' = ' . $companyId);

			$db->setQuery($query);
			$db->execute();

			// Rebuild the nested companies
			if ($nestedClass)
			{
				$tableObject->rebuild($rootResult[0]->id);
			}
		}
	}
}
