<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;

require_once __DIR__ . '/base.php';

/**
 * Get Products function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Webservice
 * @since       1.0
 */
class WebserviceGetProducts extends WebserviceFunctionBase
{
	/**
	 * Name in the Sync Table
	 *
	 * @var  string
	 */
	public $syncName = 'erp.webservice.products';

	/**
	 * Names used in the Sync Table. Currently used only for clearing Hashed Keys
	 *
	 * @var  string
	 */
	public $allUsedSyncNames = array('Product' => 'erp.webservice.products');

	/**
	 * @var string
	 */
	public $cronName = 'GetProducts';

	/**
	 * @var string
	 */
	public $tableClassName = 'Product';

	/**
	 * @var boolean
	 */
	public $deleteItemsNotPresentInRemote = false;

	/**
	 * @var string
	 */
	public $postFilterKey = 'sku_array';

	/**
	 * Should base methods use database transaction
	 *
	 * @var  boolean
	 */
	public $useTransaction = false;

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
		$this->readListUrl                 = 'index.php?webserviceClient=site&webserviceVersion=1.1.0&option=redshopb&view=product&task=read'
			. '&api=hal&list[ordering]=sku&list[direction]=ASC&filter[include_categories]=true&filter[include_tags]=true';
		$this->avoidOverrideWSProperties[] = 'related_sku';
	}

	/**
	 * Method for synchronize an single product
	 *
	 * @param   object  $product  Object of product
	 * @param   Table   $table    Table object
	 *
	 * @return  boolean
	 *
	 * @throws  \Exception
	 */
	public function synchronizeItem($product, $table)
	{
		$productRemoteId = (int) $product->id;

		if (isset($this->executed[$productRemoteId . '_']))
		{
			return false;
		}

		$row = array();

		$hashedKey  = RedshopbHelperSync::generateHashKey($product, 'object');
		$productIds = $this->getProductsByRelatedSku($product->sku);

		$row['modified_by']        = Factory::getUser()->id;
		$row['modified_date']      = Factory::getDate()->toSql();
		$row['name']               = (string) $product->name;
		$row['alias']              = (string) $product->alias;
		$row['discontinued']       = (int) $product->discontinued;
		$row['service']            = (int) $product->service;
		$row['featured']           = (int) $product->featured;
		$row['stock_upper_level']  = (int) $product->stock_upper_level;
		$row['stock_lower_level']  = (int) $product->stock_lower_level;
		$row['state']              = (int) $product->state;
		$row['filter_fieldset_id'] = $this->findSyncedId('erp.webservice.filterfieldset', (string) $product->filter_fieldset_id);
		$row['manufacturer_id']    = $this->findSyncedId('erp.webservice.manufacturers', (string) $product->manufacturer_id);
		$row['template_id']        = $this->getUnitId((string) $product->template_code, '#__redshopb_template');
		$row['unit_measure_id']    = $this->getUnitId((string) $product->unit_measure_code, '#__redshopb_unit_measure');
		$newCategories             = array();

		if (!empty($product->categories))
		{
			foreach ($product->categories as $remoteCategory)
			{
				$categoryId = $this->findSyncedId('erp.webservice.categories', $remoteCategory);

				if ($categoryId)
				{
					$newCategories[] = $categoryId;
				}
				else
				{
					// Something went wrong so we should re-sync it
					$hashedKey = null;
				}
			}
		}

		$categoryId = $this->findSyncedId('erp.webservice.categories', $product->category_id);

		if ($categoryId && in_array($categoryId, $newCategories))
		{
			$row['category_id'] = $categoryId;
		}
		elseif (count($newCategories))
		{
			$row['category_id'] = reset($newCategories);
		}

		$newTags = array();

		if (!empty($product->tags))
		{
			foreach ($product->tags as $remoteTag)
			{
				$tagId = $this->findSyncedId('erp.webservice.tags', $remoteTag);

				if ($tagId)
				{
					$newTags[] = $tagId;
				}
				else
				{
					// Something went wrong so we should re-sync it
					$hashedKey = null;
				}
			}
		}

		foreach ($productIds as $productId)
		{
			$table = RTable::getInstance($this->tableClassName, 'RedshopbTable')
				->setOption('forceWebserviceUpdate', true)
				->setOption('lockingMethod', 'Sync')
				->setOption('fields_relate.store', false)
				->setOption('deleteMissingFields', false);

			/** @var object $productSyncData */
			$productSyncData = $this->findSyncedId($this->syncName, $productRemoteId, $productId, true, $table);
			$isNewProduct    = true;

			if (!$this->isHashChanged($productSyncData, $hashedKey))
			{
				// Hash key is the same so we will continue on the next item
				$this->skipItemUpdate($productSyncData);

				continue;
			}

			if ($productSyncData)
			{
				$isNewProduct = false;
			}

			$tableRow = $row;

			if (!$table->load($productId))
			{
				$hashedKey = null;
				RedshopbHelperSync::addMessage(
					Text::sprintf('PLG_RB_SYNC_WEBSERVICE_ERROR_PRODUCT_WITH_RELATED_SKU_NOTFOUND', $product->sku),
					'warning'
				);

				continue;
			}

			// Prepare categories for this product
			$oldCategories = $table->get('categories', array());

			if (count(array_diff($oldCategories, $newCategories))
				|| count(array_diff($newCategories, $oldCategories)))
			{
				// Enable flag on table for update categories reference
				$table->setOption('category_relate.store', true);
				$tableRow['categories'] = $newCategories;
			}

			// Prepare tags for this product
			$oldTags = $table->get('tag_id', array());

			if (count(array_diff($oldTags, $newTags))
				|| count(array_diff($newTags, $oldTags)))
			{
				// Enable flag on table for update tags reference
				$table->setOption('tag_relate.store', true);
				$tableRow['tag_id'] = $newTags;
			}

			if (!$table->save($tableRow))
			{
				RedshopbHelperSync::addMessage($table->getError(), 'warning');
				$this->skipItemUpdate($productSyncData);
				$hashedKey = null;

				continue;
			}

			// Save this item ID to synced table
			$this->recordSyncedId(
				$this->syncName, $productRemoteId, $table->get('id'), $table->get('id'), $isNewProduct, 0, '',
				false, '', $table, 1, $hashedKey
			);

			$this->storeOtherIds($product, $table);

			$changedProperties                 = $table->get('changedProperties', array());
			$changedProperties['retail_price'] = (float) $product->retail_price;
			$table->set('changedProperties', $changedProperties);

			$currencyId    = RedshopbApp::getDefaultCurrency()->getId();
			$priceTable    = RedshopbTable::getAdminInstance('Product_Price');
			$fallbackPrice = RedshopbEntityProduct::getInstance($table->get('id'))->getFallbackPrice();
			$priceTable->setOption('forceWebserviceUpdate', true)
				->setOption('lockingMethod', 'Sync');

			$priceValues = array(
				'type_id' => $table->get('id'),
				'type' => 'product',
				'sales_type' => 'all_customers',
				'sales_code' => null,
				'currency_id' => $currencyId,
				'starting_date' => '0000-00-00 00:00:00',
				'ending_date' => '0000-00-00 00:00:00',
				'country_id' => null,
				'retail_price' => (float) $product->retail_price
			);

			if (!is_null($fallbackPrice))
			{
				$priceTable->load($fallbackPrice->id);
			}
			else
			{
				$priceTable->reset();
				$priceValues['id'] = 0;
			}

			if (!$priceTable->save($priceValues))
			{
				RedshopbHelperSync::addMessage($priceTable->getError(), 'warning');
				$hashedKey = null;

				continue;
			}
		}

		return true;
	}

	/**
	 * Read and store the data.
	 *
	 * @param   array  $items  Array of categories object
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 */
	public function processData($items)
	{
		if (empty($items))
		{
			return false;
		}

		$db = Factory::getDbo();

		foreach ($items as $key => $item)
		{
			if ($this->isExecutionTimeExceeded())
			{
				$this->goToNextPart = true;
				break;
			}

			try
			{
				// We are doing transaction for each row separately because of large resource loads
				$db->transactionStart();
				$this->preSyncItem($item);
				$db->transactionCommit();
				$db->unlockTables();
			}
			catch (Exception $e)
			{
				$db->transactionRollback();
				$db->unlockTables();

				throw $e;
			}
		}

		return true;
	}
}
