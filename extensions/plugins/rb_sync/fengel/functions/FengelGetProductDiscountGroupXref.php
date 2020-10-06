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
 * Get Product Discount Group Xref function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelGetProductDiscountGroupXref extends FengelFunctionBase
{
	/**
	 * @var string
	 */
	public $syncName = 'fengel.product_discount_group';

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
			$xml = $this->client->Red_GetItemDiscGroupConn();

			if (!is_object($xml))
			{
				throw new Exception(Text::_('PLG_RB_SYNC_FENGEL_FAILED_TO_FETCH_ITEMS'));
			}

			$db->transactionStart();
			$productGroupIds = array();
			$productIds      = array();
			$no              = 'No.';

			// Fix flag from all old items as not synced
			$query = $db->getQuery(true)
				->update($db->qn('#__redshopb_sync'))
				->set('execute_sync = 1')
				->where('reference = ' . $db->q($this->syncName));

			$db->setQuery($query)->execute();

			foreach ($xml->ItemDiscGroupConn as $obj)
			{
				$productGroupIds[] = $db->q((string) $obj->Code);

				foreach ($obj->Items->Item as $product)
				{
					$productIds[] = $db->q((string) $product->$no);
				}
			}

			$productGroupIds = implode(',', $productGroupIds);
			$query->clear()
				->select(array('s1.local_id', 's1.remote_key'))
				->from($db->qn('#__redshopb_sync', 's1'))
				->where('s1.reference = ' . $db->q($this->syncName))
				->where('s1.remote_key IN (' . $productGroupIds . ')');
			$db->setQuery($query);
			$productGroupResults = $db->loadObjectList('remote_key');

			$subQuery = $db->getQuery(true)
				->select('GROUP_CONCAT(pdgx.discount_group_id SEPARATOR ' . $db->q(',') . ')')
				->from($db->qn('#__redshopb_product_discount_group_xref', 'pdgx'))
				->where('pdgx.product_id = s1.local_id');

			$productIds = implode(',', $productIds);
			$query->clear()
				->select(array('s1.local_id', 's1.remote_key', '(' . $subQuery . ') AS discount_group_ids'))
				->from($db->qn('#__redshopb_sync', 's1'))
				->where('s1.reference = ' . $db->q('fengel.product'))
				->where('s1.remote_key IN (' . $productIds . ')');
			$db->setQuery($query);
			$productResults = $db->loadObjectList('remote_key');

			foreach ($xml->ItemDiscGroupConn as $obj)
			{
				$discountGroupCode = (string) $obj->Code;

				if (isset($productGroupResults[$discountGroupCode]))
				{
					foreach ($obj->Items->Item as $product)
					{
						$productNo = (string) $product->$no;
						$isNew     = false;

						if (isset($productResults[$productNo]))
						{
							if ($productResults[$productNo]->discount_group_ids)
							{
								$productGroupIds = explode(',', $productResults[$productNo]->discount_group_ids);

								if (array_search($productGroupResults[$discountGroupCode]->local_id, $productGroupIds) === false)
								{
									$isNew = true;
									$query->clear()
										->insert($db->qn('#__redshopb_product_discount_group_xref'))
										->columns('discount_group_id, product_id')
										->values($productGroupResults[$discountGroupCode]->local_id . ', ' . $productResults[$productNo]->local_id);

									$db->setQuery($query)->execute();
								}
							}
							else
							{
								$isNew = true;
								$query->clear()
									->insert($db->qn('#__redshopb_product_discount_group_xref'))
									->columns('discount_group_id, product_id')
									->values($productGroupResults[$discountGroupCode]->local_id . ', ' . $productResults[$productNo]->local_id);

								$db->setQuery($query)->execute();
							}

							if ($isNew == false)
							{
								$query->clear()
									->select('local_id')
									->from('#__redshopb_sync')
									->where('remote_key = ' . $db->q($discountGroupCode))
									->where('remote_parent_key = ' . $db->q($productNo))
									->where('reference = ' . $db->q($this->syncName));
								$db->setQuery($query);

								if (!$db->loadResult())
								{
									$isNew = true;
								}
							}

							$this->recordSyncedId(
								$this->syncName, $discountGroupCode,
								$productGroupResults[$discountGroupCode]->local_id . '_' . $productResults[$productNo]->local_id,
								$productNo, $isNew
							);
						}
					}
				}
			}

			$query->clear()
				->select('local_id')
				->from($db->qn('#__redshopb_sync'))
				->where('reference = ' . $db->q($this->syncName))
				->where('execute_sync = 1');

			$results = $db->setQuery($query)->loadColumn();

			if ($results)
			{
				$query->clear()
					->delete($db->qn('#__redshopb_product_discount_group_xref'));

				foreach ($results as $result)
				{
					$data = explode('_', $result);
					$query->where('discount_group_id = ' . (int) $data[0] . ' AND product_id = ' . (int) $data[1], 'OR');
				}

				$db->setQuery($query)->execute();

				$query->clear()
					->delete($db->qn('#__redshopb_sync'))
					->where('reference = ' . $db->q($this->syncName))
					->where('execute_sync = 1');

				$db->setQuery($query)->execute();
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
