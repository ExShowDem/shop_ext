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

/**
 * Custom upgrade of Redshop b2b.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Upgrade
 * @since       1.9
 */
class WebserviceUpdateScript_1_12_31
{
	/**
	 * Performs the upgrade for this version
	 *
	 * @return  boolean
	 */
	public function execute()
	{
		$app                      = Factory::getApplication();
		$idsWithReference         = array();
		$productIdsWithDuplicates = array();

		// Ensure that we only delete duplicates of product descriptions synced via the webservice sync
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('pd.id, pd.product_id, pd.description, pd.description_intro, s.metadata')
			->from($db->qn('#__redshopb_product_descriptions', 'pd'))
			->innerjoin($db->qn('#__redshopb_sync', 's') . ' ON pd.id = s.local_id')
			->where('s.reference = "erp.webservice.product_descriptions"');

		$db->setQuery($query);
		$results = $db->loadObjectList();

		foreach ($results as $result)
		{
			$idsWithReference[]         = $result->id;
			$productIdsWithDuplicates[] = $result->product_id;
		}

		$productIdsWithDuplicates = array_unique($productIdsWithDuplicates);

		// Delete duplicate Product description data
		$query = $db->getQuery(true)
			->delete($db->qn('#__redshopb_product_descriptions'))
			->where($db->qn('product_id') . ' IN (' . implode(',', $productIdsWithDuplicates) . ')')
			->where($db->qn('id') . ' NOT IN (' . implode(',', $idsWithReference) . ')');

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		$deletedRows = $db->getAffectedRows();
		$app->enqueueMessage(sprintf('Number of duplicate product descriptions removed: %s', $deletedRows), 'message');

		return true;
	}
}
