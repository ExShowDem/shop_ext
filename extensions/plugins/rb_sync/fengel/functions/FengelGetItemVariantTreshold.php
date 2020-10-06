<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;

require_once __DIR__ . '/base.php';

/**
 * Get Item Variant Treshold function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelGetItemVariantTreshold extends FengelFunctionBase
{
	/**
	 * Read and store the data.
	 *
	 * @param   RTable     $webserviceData   Webservice object
	 * @param   Registry   $params           Parameters of the plugin
	 *
	 * @return  boolean
	 *
	 * @throws  Exception
	 */
	public function read(&$webserviceData, $params)
	{
		$db = Factory::getDbo();

		try
		{
			$xml = $this->client->Red_GetItemVariantTreshold();

			if (!is_object($xml))
			{
				throw new Exception(Text::_('PLG_RB_SYNC_FENGEL_FAILED_TO_FETCH_ITEMS'));
			}

			$db->transactionStart();
			$query = $db->getQuery(true);
			$rows  = array();

			// Check exists products in sync
			foreach ($xml->ItemVariantTreshold as $obj)
			{
				$rows[(string) $obj->No] = $db->q((string) $obj->No);
			}

			$remoteKeys = implode(',', $rows);

			$query->clear()
				->select(array('local_id', 'remote_key'))
				->from($db->qn('#__redshopb_sync'))
				->where('reference = ' . $db->q('fengel.product'))
				->where('remote_key IN (' . $remoteKeys . ')');
			$db->setQuery($query);
			$results = $db->loadObjectList('remote_key');

			$where1 = array();

			// Check exists items in sync and select items values
			foreach ($xml->ItemVariantTreshold as $obj)
			{
				if (!isset($results[(string) $obj->No]))
				{
					RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_FENGEL_PRODUCT_NOT_FOUND', (string) $obj->No), 'warning');
					continue;
				}

				$no     = (string) $obj->No;
				$where2 = array();

				foreach ($obj->Keys->Key as $key)
				{
					$where2[] = $db->q((string) $key->Value);
				}

				if (count($where2) > 0)
				{
					$where1[] = 's1.remote_parent_key = ' . $db->q($no) . ' AND s1.remote_key IN (' . implode(', ', $where2) . ')';
				}
			}

			if ($where1)
			{
				$query->clear()
					->select(
						array(
							's1.local_id', 's1.remote_key',
							$db->qn('s2.remote_key', 'remote_product_key'),
							'pi.lower_level', 'pi.upper_level'
						)
					)
					->from($db->qn('#__redshopb_sync', 's1'))
					->leftJoin(
						$db->qn('#__redshopb_sync', 's2')
						. ' ON s2.remote_key = s1.remote_parent_key AND s2.reference = ' . $db->q('fengel.product')
					)
					->leftJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON s1.local_id = pi.id')
					->where('s1.reference = ' . $db->q('fengel.item_related'))
					->where('(' . implode(' OR ', $where1) . ')');
				$db->setQuery($query);
				$results = $db->loadObjectList();
				$items   = array();

				if ($results)
				{
					foreach ($results as $result)
					{
						if (!isset($items[$result->remote_product_key]))
						{
							$items[$result->remote_product_key] = array();
						}

						if (!isset($items[$result->remote_product_key][$result->remote_key]))
						{
							$items[$result->remote_product_key][$result->remote_key] = array();
						}

						$items[$result->remote_product_key][$result->remote_key] = $result;
					}
				}
			}

			foreach ($xml->ItemVariantTreshold as $obj)
			{
				if (!isset($items[(string) $obj->No]))
				{
					continue;
				}

				$no = (string) $obj->No;

				foreach ($obj->Keys->Key as $key)
				{
					$value      = (string) $key->Value;
					$upperLevel = (int) trim((string) $key->Upper);
					$lowerLevel = (int) trim((string) $key->Lower);

					if (!isset($items[$no][$value]))
					{
						continue;
					}

					if ($items[$no][$value]->upper_level != $upperLevel || $items[$no][$value]->lower_level != $lowerLevel)
					{
						$query->clear()
							->update($db->qn('#__redshopb_product_item'))
							->set('lower_level = ' . $lowerLevel)
							->set('upper_level = ' . $upperLevel)
							->where('id = ' . (int) $items[$no][$value]->local_id);

						$db->setQuery($query)->execute();
					}
				}
			}

			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$db->transactionRollback();
			RedshopbHelperSync::addMessage($e->getMessage(), 'error');

			return false;
		}

		RedshopbHelperSync::addMessage(Text::_('PLG_RB_SYNC_FENGEL_SYNCHRONIZE_SUCCESS'), 'success');

		return true;
	}
}
