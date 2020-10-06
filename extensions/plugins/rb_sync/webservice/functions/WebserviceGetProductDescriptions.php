<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;

require_once __DIR__ . '/base.php';

/**
 * Get Product Descriptions function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Webservice
 * @since       1.0
 */
class WebserviceGetProductDescriptions extends WebserviceFunctionBase
{
	/**
	 * Name in the Sync Table
	 *
	 * @var  string
	 */
	public $syncName = 'erp.webservice.product_descriptions';

	/**
	 * Names used in the Sync Table. Currently used only for clearing Hashed Keys
	 *
	 * @var  string
	 */
	public $allUsedSyncNames = array(
		'Product_Description' => 'erp.webservice.product_descriptions'
	);

	/**
	 * Url for sync list of categories
	 *
	 * @var  string
	 */
	public $readListUrl = 'index.php?webserviceClient=site&webserviceVersion=1.1.0&option=redshopb'
							. '&view=product_description&api=hal&list[ordering]=product_id&list[direction]=ASC'
							. '&task=read';

	/**
	 * List of categories with parent has not synced yet.
	 *
	 * @var  array
	 */
	public $syncParentCategories = array();

	/**
	 * @var string
	 */
	public $cronName = 'ProductDescriptions';

	/**
	 * @var string
	 */
	public $tableClassName = 'Product_Description';

	/**
	 * @var string
	 */
	public $postFilterKey = 'product_id_array';

	/**
	 * @var string
	 */
	public $productModelKey = 'remote_key';

	/**
	 * @var boolean
	 */
	public $deleteItemsNotPresentInRemote = false;

	/**
	 * @var boolean
	 */
	public $processStoreOtherIds = false;

	/**
	 * Constructor.
	 *
	 */
	public function __construct()
	{
		parent::__construct();

		// Sets the enrichment base and reference to use them later when syncing
		$this->setEnrichmentBase();
	}

	/**
	 * Method for synchronize an single category
	 *
	 * @param   object  $item   Item object data
	 * @param   Table   $table  Table object
	 *
	 * @return  boolean
	 *
	 * @throws  \Exception
	 */
	public function synchronizeItem($item, $table)
	{
		$remoteId = (int) $item->id;

		if (isset($this->executed[$remoteId . '_']))
		{
			return true;
		}

		$productIds = $this->getProductsByRelatedId($item->product_id);

		if (empty($productIds))
		{
			return true;
		}

		$row       = array();
		$hashedKey = RedshopbHelperSync::generateHashKey($item, 'object');

		$row['main_attribute_value_id'] = (string) $item->main_attribute_value_id;
		$row['description_intro']       = (string) $item->description_intro;
		$row['description']             = (string) $item->description;

		foreach ($productIds as $productId)
		{
			// If product does not exist anymore then we skip it
			if (!RedshopbEntityProduct::getInstance((int) $productId)->isValid())
			{
				continue;
			}

			$table                               = RTable::getInstance($this->tableClassName, 'RedshopbTable')
				->setOption('forceWebserviceUpdate', true)
				->setOption('lockingMethod', 'Sync');
			$productDescriptionRow               = $row;
			$productDescriptionRow['product_id'] = $productId;
			$isNew                               = true;
			$unSerialize                         = array();
			/** @var object $itemData */
			$itemData = $this->findSyncedId($this->syncName, $remoteId, $productId, true, $table);

			if (!$this->isHashChanged($itemData, $hashedKey))
			{
				// Hash key is the same so we will continue on the next item
				$this->skipItemUpdate($itemData);

				continue;
			}

			if ($itemData)
			{
				if (!$itemData->deleted && $table->load($itemData->local_id))
				{
					$unSerialize = RedshopbHelperSync::mbUnserialize($itemData->serialize);
					$isNew       = false;
				}
				// If item not exists, then user delete it, so lets skip it
				elseif ($itemData->deleted)
				{
					$this->skipItemUpdate($itemData);

					continue;
				}
				else
				{
					$this->deleteSyncedId($this->syncName, $remoteId, $productId);
				}
			}

			if (($itemData && $isNew) || !$itemData)
			{
				// Get product descriptions and check if they have wildcard
				$db         = Factory::getDbo();
				$query      = $db->getQuery(true)
					->select('s.remote_key')
					->from($db->qn('#__redshopb_product_descriptions', 'pd'))
					->leftJoin($db->qn('#__redshopb_sync', 's') . ' ON s.local_id = pd.id AND s.reference = ' . $db->q($this->enrichmentSyncRef))
					->where('pd.product_id = ' . $db->q($productId))
					->where('s.remote_key IS NOT NULL');
				$remoteKeys = $db->setQuery($query)->loadColumn();

				if (!empty($remoteKeys))
				{
					foreach ($remoteKeys as $remoteKey)
					{
						// We stop with enrichment for this item all together if the wildcard is found
						if (strpos($remoteKey, '*') !== false)
						{
							continue 2;
						}
					}
				}
			}

			$this->setUserInformation($productDescriptionRow, $isNew);

			if (!$table->save($productDescriptionRow))
			{
				RedshopbHelperSync::addMessage($table->getError(), 'warning');
				$this->skipItemUpdate($itemData);

				continue;
			}

			$serialize = serialize($unSerialize);

			// Save this item ID to synced table
			$this->recordSyncedId(
				$this->syncName, $remoteId, $table->get('id'), $productId, $isNew, 0, $serialize,
				false, '', $table, 1, $hashedKey
			);

			$this->storeOtherIds($item, $table);
		}

		return true;
	}
}
