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
 * Get Fee Setup function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelGetFeeSetup extends FengelFunctionBase
{
	/**
	 * @var string
	 */
	public $syncName = 'fengel.fee';

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
			$xml = $this->client->GetFeeSetup();

			if (!is_object($xml))
			{
				throw new Exception(Text::_('PLG_RB_SYNC_FENGEL_FAILED_TO_FETCH_ITEMS'));
			}

			$db->transactionStart();

			$query = $db->getQuery(true)
				->select(
					array (
						$db->qn('id'),
						$db->qn('alpha3')
					)
				)
				->from($db->qn('#__redshopb_currency'));
			$db->setQuery($query);
			$currencies = $db->loadAssocList('alpha3', 'id');

			// Fix flag from all old items as not synced
			$query = $db->getQuery(true)
				->update($db->qn('#__redshopb_sync'))
				->set('execute_sync = 1')
				->where('reference = ' . $db->q($this->syncName));

			$db->setQuery($query)->execute();

			$feeProducts = array();

			foreach ($xml->FeeSetup as $obj)
			{
				$productId = $this->findSyncedId('fengel.product', (string) $obj->FeeItemNo);

				if (!$productId)
				{
					RedshopbHelperSync::addMessage(
						Text::sprintf('PLG_RB_SYNC_FENGEL_PRODUCT_NOT_FOUND', (string) $obj->FeeItemNo), 'warning'
					);
					continue;
				}

				$feeLimit  = str_replace('.', '', trim((string) $obj->FeeLimit));
				$feeLimit  = (float) str_replace(',', '.', $feeLimit);
				$feeAmount = str_replace('.', '', trim((string) $obj->FeeAmount));
				$feeAmount = (float) str_replace(',', '.', $feeAmount);

				$row = array(
					'fee_limit' => $feeLimit,
					'fee_amount' => $feeAmount,
					'product_id' => $productId
				);

				if ((string) $obj->Currency != '' || isset($currencies[(string) $obj->Currency]))
				{
					$row['currency_id'] = $currencies[(string) $obj->Currency];
				}

				$table = RTable::getInstance('Fee', 'RedshopbTable')
					->setOption('forceWebserviceUpdate', true)
					->setOption('lockingMethod', 'Sync');
				$isNew = true;
				$id    = $this->findSyncedId($this->syncName, (string) $obj->Currency, (string) $obj->FeeItemNo);

				if ($id)
				{
					if ($table->load($id))
					{
						$isNew = false;
					}
					else
					{
						$this->deleteSyncedId($this->syncName, (string) $obj->Currency, (string) $obj->FeeItemNo);
					}
				}

				if (!$table->save($row))
				{
					throw new Exception($table->getError());
				}

				$this->recordSyncedId($this->syncName, (string) $obj->Currency, $table->id, (string) $obj->FeeItemNo, $isNew);

				$feeProducts[] = $productId;
			}

			$feeProducts = array_unique($feeProducts);

			foreach ($feeProducts as $fee)
			{
				// Adding dummy product item to the system
				$itemQuery = $db->getQuery(true);
				$itemQuery->select($db->qn('id'))
					->from($db->qn('#__redshopb_product_item'))
					->where($db->qn('product_id') . ' = ' . (int) $fee);
				$db->setQuery($itemQuery);

				if (is_null($db->loadResult()))
				{
					$itemTable = RedshopbTable::getAdminInstance('Product_Item')
						->setOption('lockingMethod', 'Sync');

					$itemTable->reset();
					$itemTable->id = null;

					// Create the item
					if (!$itemTable->save(
						array (
							'product_id' => $fee
						)
					))
					{
						RedshopbHelperSync::addMessage($itemTable->getError(), 'warning');
					}
				}
			}

			$subQuery = $db->getQuery(true)
				->select('local_id')
				->from($db->qn('#__redshopb_sync'))
				->where('reference = ' . $db->q($this->syncName))
				->where('execute_sync = 1');
			$query->clear()
				->delete($db->qn('#__redshopb_fee'))
				->where('id IN (' . $subQuery . ')');

			$db->setQuery($query)->execute();

			$query->clear()
				->delete($db->qn('#__redshopb_sync'))
				->where('reference = ' . $db->q($this->syncName))
				->where('execute_sync = 1');

			$db->setQuery($query)->execute();

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
