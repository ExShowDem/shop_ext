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
use Joomla\Registry\Registry;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;

require_once __DIR__ . '/base.php';

/**
 * Get Product function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  PIM
 * @since       1.0
 */
class PimGetProduct extends PimFunctionBase
{
	/**
	 * Name in the Sync Table
	 *
	 * @var  string
	 */
	public $syncName = 'erp.pim.product';

	/**
	 * Names used in the Sync Table. Currently used only for clearing Hashed Keys
	 *
	 * @var  string
	 */
	public $allUsedSyncNames = array(
		'Product' => 'erp.pim.product',
		'Field_Data' => 'erp.pim.field_data',
		'Media' => 'erp.pim.media',
		'Product_Description' => 'erp.pim.product_description'
	);

	/**
	 * @var string
	 */
	public $syncFieldDataName = 'erp.pim.field_data';

	/**
	 * @var string
	 */
	public $syncProductDescriptionName = 'erp.pim.product_description';

	/**
	 * @var string
	 */
	public $syncMediaName = 'erp.pim.media';

	/**
	 * @var string
	 */
	public $cronName = 'GetProduct';

	/**
	 * @var array
	 */
	public $fieldsForTranslation = array();

	/**
	 * @var string
	 */
	public $tableClassName = 'Product';

	/**
	 * @var string
	 */
	public $nameFieldWithData = 'Product';

	/**
	 * @var array
	 */
	public $resourceHashedKeys = array();

	/**
	 * Should base methods use database transaction
	 *
	 * @var  boolean
	 */
	public $useTransaction = true;

	/**
	 * List of sync references using temporary file for marking records as executed (to delete the ones that are not in the end)
	 *
	 * @var  array
	 */
	public $syncRefsUsingFile = array('erp.pim.product', 'erp.pim.field_data', 'erp.pim.media', 'erp.pim.product_description');

	/**
	 * Prefix for cached product for field data
	 *
	 * @var  string
	 */
	protected $redisCachedProductFieldsPrefix = 'vanir_sync_product_field_data';

	/**
	 * Prefix for cached field data
	 *
	 * @var  string
	 */
	protected $redisCachedFieldsPrefix = 'vanir_sync_field_data';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->avoidOverrideWSProperties = array_merge(
			$this->avoidOverrideWSProperties,
			array(
				'customer_ids'
			)
		);

		parent::__construct();
	}

	/**
	 * Read and store the data.
	 *
	 * @param   SimpleXMLElement  $obj       XML element
	 * @param   Table             $table     Table object
	 * @param   string            $parentId  Parent id
	 *
	 * @return  boolean
	 *
	 * @throws  Exception
	 */
	public function readXmlRecursive($obj, &$table, $parentId = '')
	{
		$db         = Factory::getDbo();
		$attributes = $obj->attributes();
		$remoteId   = (string) $attributes['id'];

		$row = array();
		$table->setOption('category_relate.store', true)
			->setOption('tag_relate.store', true)
			->setOption('storeNulls', false)
			->setOption('fields_relate.store', true)
			->setOption('deleteMissingFields', false);
		$isNew                    = true;
		$tagIds                   = array();
		$categoryIds              = array();
		$this->resourceHashedKeys = $this->getResourceHashedKeys($obj);
		$itemData                 = $this->findSyncedId($this->syncName, $remoteId, '', true, $table);
		$hashedKey                = RedshopbHelperSync::generateHashKey($obj, 'xml');
		$hashedKey                = RedshopbHelperSync::generateHashKey($hashedKey . implode('', $this->resourceHashedKeys), 'string');

		if (!$this->isHashChanged($itemData, $hashedKey))
		{
			// Hash key is the same so we will continue on the next item
			$this->skipItemUpdate($itemData);
			$this->skipResourcesUpdate($obj, $itemData->local_id);

			// Returning false so we do not go to the next step of inserting translations
			return false;
		}

		if ($itemData)
		{
			if (!$itemData->deleted && $table->load($itemData->local_id))
			{
				$isNew = false;
			}
			// If item not exists, then user delete it, so lets skip it
			elseif ($itemData->deleted)
			{
				$this->skipItemUpdate($itemData);
				$this->skipResourcesUpdate($obj, $itemData->local_id);

				return false;
			}
			else
			{
				$this->deleteSyncedId($this->syncName, $remoteId);
			}
		}

		$row['state'] = 1;

		// If product is deleted it is notified from this attribute
		if ($attributes['action'] == 'deleted')
		{
			$row['state'] = 0;

			// This row is already marked as deleted so we do not have to update it again
			if (!$isNew && $table->state == 0)
			{
				$this->skipItemUpdate($itemData);
				$this->skipResourcesUpdate($obj, $table->get('id'));

				return false;
			}
		}

		// Set Department Code Tag
		if (!empty($obj->AfdKode))
		{
			$departmentCode = (string) $obj->AfdKode;

			$tagId = $this->findSyncedId('erp.pim.departmentCode', $departmentCode);

			if ($tagId)
			{
				$tagIds[] = $tagId;
			}
			else
			{
				// We will not update Hash key since this item needs to sync again
				$hashedKey = null;
				RedshopbHelperSync::addMessage(
					Text::sprintf('PLG_RB_SYNC_PIM_TAG_NOT_FOUND', $departmentCode, 'departmentCode (AfdKode)', $remoteId), 'warning'
				);
			}
		}

		// Set Brand Tag
		if (!empty($obj->Brand))
		{
			$brand = (string) $obj->Brand;

			$manufacturerId = $this->findSyncedId('erp.pim.brands', $brand);

			if ($manufacturerId)
			{
				$row['manufacturer_id'] = $manufacturerId;
			}
			else
			{
				// We will not update Hash key since this item needs to sync again
				$hashedKey = null;
				RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_PIM_TAG_NOT_FOUND', $brand, 'brands (Brand)', $remoteId), 'warning');
			}
		}

		// Set Product type Tag
		if (!empty($obj->ProdType))
		{
			$productType = (string) $obj->ProdType;

			$tagId = $this->findSyncedId('erp.pim.productType', $productType);

			if ($tagId)
			{
				$tagIds[] = $tagId;
			}
			else
			{
				// We will not update Hash key since this item needs to sync again
				$hashedKey = null;
				RedshopbHelperSync::addMessage(
					Text::sprintf('PLG_RB_SYNC_PIM_TAG_NOT_FOUND', $productType, 'productType (ProdType)', $remoteId),
					'warning'
				);
			}
		}

		// Set Stock unit Tag
		if (!empty($obj->StockUnit))
		{
			$stockUnit   = (string) $obj->StockUnit;
			$stockUnitId = $this->findSyncedId('erp.pim.stockUnits', $stockUnit);

			if ($stockUnitId)
			{
				$row['unit_measure_id'] = $stockUnitId;
			}
			else
			{
				// We will not update Hash key since this item needs to sync again
				$hashedKey = null;
				RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_PIM_STOCK_UNIT_NOT_FOUND', $stockUnit, $remoteId), 'warning');
			}
		}

		// Set Categories
		if (isset($obj->Assortments))
		{
			foreach ($obj->Assortments->Assortment as $categoryRow)
			{
				$category = $categoryRow->attributes();

				if (isset($category['id']))
				{
					$categoryId = $this->findSyncedId('erp.pim.category', (string) $category['id']);

					if ($categoryId)
					{
						if (empty($row['category_id']))
						{
							$row['category_id'] = $categoryId;
						}

						$categoryIds[] = $categoryId;
					}
					else
					{
						// We will not update Hash key since this item needs to sync again
						$hashedKey = null;
						RedshopbHelperSync::addMessage(
							Text::sprintf('PLG_RB_SYNC_PIM_CATEGORY_NOT_FOUND', (string) $category['id'], $remoteId),
							'warning'
						);
					}
				}
			}
		}

		$row['name']   = (string) $obj->ProductName;
		$row['sku']    = (string) $obj->ProductDJItemNo;
		$row['fields'] = array();

		// Bind the fields to the product
		$fieldsToBind = array('ProductDescr2s', 'ProductDescr3s');

		foreach ($fieldsToBind as $fieldToBind)
		{
			$field = RedshopbHelperField::getFieldByName($fieldToBind, 'product');

			if ($field)
			{
				$value = $this->bindValueFromField($obj, $field);

				if ($value)
				{
					$row['fields']['scope_field_' . $field->id] = $value;
				}
			}
			else
			{
				// We will not update Hash key since this item needs to sync again
				$hashedKey = null;
				RedshopbHelperSync::addMessage(Text::sprintf('COM_REDSHOPB_SYNC_FIELD_MISSING', $fieldToBind, $remoteId), 'warning');
			}
		}

		// List of fields we want to load from the XML item
		$productFieldsToBind = RedshopbHelperField::getFields('product');
		$item                = null;

		if ($obj->Items->Item)
		{
			$item = $obj->Items->Item[0];
		}

		if ($item)
		{
			// Set Filter FieldSet
			if (isset($item->FieldSet))
			{
				$fieldSet = (string) $item->FieldSet;

				if (!empty($fieldSet))
				{
					$filterFieldsetId = $this->findSyncedId('erp.pim.filterFieldset', $fieldSet);

					if ($filterFieldsetId)
					{
						$row['filter_fieldset_id'] = $filterFieldsetId;
					}
					else
					{
						// We will not update Hash key since this item needs to sync again
						$hashedKey = null;
						RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_PIM_FILTER_FIELDSET_NOT_FOUND', $fieldSet, $remoteId), 'warning');
					}
				}
				else
				{
					$row['filter_fieldset_id'] = '';
				}
			}

			$fieldsToBind       = array();
			$fieldsValuesToBind = array();

			foreach ($productFieldsToBind['product'] as $productField)
			{
				$productField->syncName = $this->getSyncedFieldId($productField->id);

				if (in_array($productField->syncName, $this->reservedFieldsInProduct))
				{
					continue;
				}

				if ($productField->value_type == 'field_value')
				{
					$fieldsValuesToBind[$productField->syncName != '' ? $productField->syncName : $productField->name] = $productField;
				}
				else
				{
					// "Fixes" field name using the original name (not translation) when available
					$productField->name                = ($productField->syncName != '' ? $productField->syncName : $productField->name);
					$fieldsToBind[$productField->name] = $productField;
				}
			}

			foreach ($fieldsToBind as $fieldToBind)
			{
				$value = $this->bindValueFromField($item, $fieldToBind);

				if (!is_null($value))
				{
					$row['fields']['scope_field_' . $fieldToBind->id] = $value;
				}
			}

			foreach ($fieldsValuesToBind as $fieldNameToBind => $fieldToBind)
			{
				// Bind Field Value
				if (isset($item->{$fieldNameToBind}))
				{
					$itemValue    = trim((string) $item->{$fieldNameToBind});
					$cvlFieldName = $fieldNameToBind;

					if (!empty($fieldToBind->field_value_xref_id))
					{
						$cvlField = RedshopbHelperField::getFieldById($fieldToBind->field_value_xref_id);

						if ($cvlField)
						{
							$cvlFieldName = $cvlField->name;
						}
					}

					if (isset($item->{$fieldNameToBind}['cvl']))
					{
						$xmlCvlFieldName = trim((string) $item->{$fieldNameToBind}['cvl']) . '_CVL';

						// We check if we need to update Field Value reference
						if ($xmlCvlFieldName != $cvlFieldName)
						{
							$newFieldValueXref = RedshopbHelperField::getFieldByName($xmlCvlFieldName);

							if ($newFieldValueXref)
							{
								$fieldRow = array(
									'id'                  => $fieldToBind->id,
									'field_value_xref_id' => $newFieldValueXref->id
								);
								$this->updateRedshopbField($fieldRow);
							}

							$cvlFieldName = $xmlCvlFieldName;
						}
					}

					$fieldValueId = $this->findSyncedId('erp.pim.field.value', $itemValue, $cvlFieldName);

					if ($fieldValueId)
					{
						$row['fields']['scope_field_' . $fieldToBind->id] = $fieldValueId;
					}
					elseif ($itemValue == '')
					{
						$row['fields']['scope_field_' . $fieldToBind->id] = null;
					}
					else
					{
						// We will not update Hash key since this item needs to sync again
						$hashedKey = null;
						RedshopbHelperSync::addMessage(
							Text::sprintf('PLG_RB_SYNC_PIM_FIELD_NOT_FOUND', $cvlFieldName, $itemValue, $remoteId, $cvlFieldName), 'warning'
						);
					}
				}
			}

			// Get item sku
			if (isset($item->DJItemNo))
			{
				$row['sku'] = (string) $item->DJItemNo;
			}

			// Get item name
			if (isset($item->ItemName))
			{
				$row['name'] = (string) $item->ItemName;
			}
		}

		// We place all tags in tag_id and they will be saved properly
		if (count($tagIds) > 0)
		{
			$row['tag_id'] = $tagIds;
		}

		// We place all categories in categories and they will be saved properly
		if (count($categoryIds) > 0)
		{
			$row['categories'] = $categoryIds;
		}

		$fields = $row['fields'];
		unset($row['fields']);

		if (!$table->save($row))
		{
			RedshopbHelperSync::addMessage($table->getError(), 'warning');

			if (!$isNew)
			{
				$this->skipResourcesUpdate($obj, $table->get('id'));
			}

			return false;
		}

		if (!$this->storeScopeFieldData('product', $table->get('id'), $remoteId, $fields))
		{
			$this->skipResourcesUpdate($obj, $table->get('id'));

			return false;
		}

		// If state is deleted then we do not have to update descriptions or resources
		if ($table->get('state') == 0)
		{
			// Save this item ID to synced table
			$this->recordSyncedId(
				$this->syncName, $remoteId, $table->get('id'), '', $isNew, 0, '', false, '', $table, 1, $hashedKey
			);

			$this->skipResourcesUpdate($obj, $table->get('id'));

			return true;
		}

		$changedProperties                 = $table->get('changedProperties', array());
		$changedProperties['retail_price'] = (float) 0;

		// Sales Price (guided or retail price)
		if (isset($item->SalesPriceString))
		{
			// Their price is coming in , as a decimal separator
			$retailPrice = (float) str_replace(',', '.', str_replace('.', '', trim((string) $item->SalesPriceString)));

			if ($retailPrice != '0.00')
			{
				$changedProperties['retail_price'] = (float) $retailPrice;
				$currencyId                        = RedshopbApp::getDefaultCurrency()->getId();
				$priceTable                        = RedshopbTable::getAdminInstance('Product_Price');
				$fallbackPrice                     = RedshopbEntityProduct::getInstance($table->get('id'))->getFallbackPrice();
				$priceTable->setOption('forceWebserviceUpdate', true)
					->setOption('lockingMethod', 'Sync');

				if (!is_null($fallbackPrice))
				{
					$priceTable->load($fallbackPrice->id);
				}

				$priceValues = array(
					'type_id'       => $table->get('id'),
					'type'          => 'product',
					'sales_type'    => 'all_customers',
					'sales_code'    => null,
					'currency_id'   => $currencyId,
					'starting_date' => '0000-00-00 00:00:00',
					'ending_date'   => '0000-00-00 00:00:00',
					'country_id'    => null,
					'quantity_min'  => null,
					'quantity_max'  => null,
					'retail_price'  => (float) $retailPrice
				);

				if (!$priceTable->save($priceValues))
				{
					RedshopbHelperSync::addMessage($priceTable->getError(), 'warning');
					$this->skipResourcesUpdate($obj, $table->get('id'));

					return false;
				}
			}
		}

		$changedProperties['description'] = '';

		// Save Description to a separately table
		if (isset($obj->LevLangVaretxts->LevLangVaretxt))
		{
			$productDescriptionTable = RTable::getAdminInstance('Product_Description')
				->setOption('forceWebserviceUpdate', true)
				->setOption('lockingMethod', 'Sync');

			foreach ($obj->LevLangVaretxts->LevLangVaretxt as $description)
			{
				$descriptionAttributes = $description->attributes();
				$descriptionRow        = array();

				// We only save Danish for now
				if (!empty($descriptionAttributes['locale']) && $descriptionAttributes['locale'] == 'da')
				{
					$isNewProductDescription    = true;
					$itemDataProductDescription = $this->findSyncedId(
						$this->syncProductDescriptionName, $remoteId, '', true, $productDescriptionTable
					);
					$hashedKey                  = RedshopbHelperSync::generateHashKey((string) $description, 'string');

					if (!$this->isHashChanged($itemDataProductDescription, $hashedKey))
					{
						// Hash key is the same so we will continue on the next item
						$this->skipItemUpdate($itemDataProductDescription);

						continue;
					}

					if ($itemDataProductDescription)
					{
						if ($productDescriptionTable->load($itemDataProductDescription->local_id))
						{
							$isNewProductDescription = false;
						}
						else
						{
							$this->deleteSyncedId($this->syncProductDescriptionName, $remoteId);
						}
					}

					$descriptionRow['description']    = (string) $description;
					$descriptionRow['product_id']     = $table->get('id');
					$changedProperties['description'] = (string) $description;

					if (!$productDescriptionTable->save($descriptionRow))
					{
						throw new Exception($productDescriptionTable->getError());
					}

					$this->recordSyncedId(
						$this->syncProductDescriptionName, $remoteId, $productDescriptionTable->get('id'), '',
						$isNewProductDescription, 0, '', false, '', $productDescriptionTable, 1, $hashedKey
					);
				}
			}
		}

		$table->set('changedProperties', $changedProperties);

		// Save this item ID to synced table
		$this->recordSyncedId($this->syncName, $remoteId, $table->get('id'), '', $isNew, 0, '', false, '', $table, 1, null);

		// Save Resources to a separately table
		if (isset($item->Resources->Resource))
		{
			$localFolder = $this->params->get('localFolder', '');

			foreach ($item->Resources->Resource as $resource)
			{
				if (!empty($resource->ResourceType))
				{
					$resourceType = (string) $resource->ResourceType;

					switch ($resourceType)
					{
						case 'Picture':
							$mediaTable                = RTable::getInstance('Media', 'RedshopbTable')
								->setOption('forceWebserviceUpdate', true)
								->setOption('lockingMethod', 'Sync');
							$resourceRow               = array();
							$resourceSerialize         = array();
							$isNewMedia                = true;
							$resourceAttributes        = $resource->attributes();
							$resourceId                = (string) $resourceAttributes['id'];
							$resourceRow['alt']        = (string) $resource->ResourceDescription;
							$resourceRow['product_id'] = $table->get('id');
							$remoteMediaKey            = $resourceId . '_' . $table->get('id');
							$resourceFileName          = (string) $resource->ResourceFilename;
							$resourceFileName          = $resourceId . '.' . JFile::getExt($resourceFileName);
							$mediaData                 = $this->findSyncedId($this->syncMediaName, $remoteMediaKey, '', true, $mediaTable);

							$hashedKeyMedia = isset($this->resourceHashedKeys[$resourceId]) ?
								$this->resourceHashedKeys[$resourceId] : RedshopbHelperSync::generateHashKey($resource, 'xml');
							$isModified     = $this->isResourceChanged($mediaData, $resourceFileName, $resourceSerialize);

							if (!$isModified && !$this->isHashChanged($mediaData, $hashedKeyMedia))
							{
								// Hash key is the same so we will continue on the next item
								$this->skipItemUpdate($mediaData);

								continue;
							}

							if ($mediaData)
							{
								if (!$mediaData->deleted && $mediaTable->load($mediaData->local_id))
								{
									$isNewMedia = false;
								}
								// If item not exists, then user delete it, so lets skip it
								elseif ($mediaData->deleted)
								{
									$this->skipItemUpdate($mediaData);

									continue;
								}
								else
								{
									$this->deleteSyncedId($this->syncMediaName, $remoteMediaKey);
								}
							}

							if ($isNewMedia || $isModified)
							{
								// If a local_id is not set, then the original image should exist but it will be deleted right after it's been used
								$resourceFilePath = (string) $this->client->localFolder . '/Pics/' . $resourceFileName;

								if (JFile::exists($resourceFilePath))
								{
									if (!$mediaTable->save($resourceRow))
									{
										throw new Exception($mediaTable->getError());
									}

									$resourceRow['id']          = $mediaTable->get('id');
									$resourceRow['name']        = $resourceFileName;
									$resourceRow['remote_path'] = 'media/com_redshopb/' . $localFolder . '/Pics';
								}
							}
							elseif (!$mediaTable->get('name'))
							{
								$hashedKey = null;
								continue;
							}

							if (($isNewMedia || $isModified) && (!isset($resourceRow['name']) || !$resourceRow['name']))
							{
								$this->skipItemUpdate($mediaData);
								continue;
							}

							if (!$mediaTable->save($resourceRow))
							{
								throw new Exception($mediaTable->getError());
							}

							// If the file is modified we delete only generate thumbnails
							if ($isModified)
							{
								RedshopbHelperThumbnail::deleteImage($mediaTable->name, 0, 'products', $mediaTable->remote_path);
							}

							$this->recordSyncedId(
								$this->syncMediaName, $remoteMediaKey, $mediaTable->id, $resourceFileName,
								$isNewMedia, 0, serialize($resourceSerialize), false, '', $mediaTable, 1, $hashedKeyMedia
							);
							break;

						case 'Instructions':
						case 'Securitysheet':
						case 'Video':
						default:
							$fieldDataTable     = RTable::getInstance('Field_Data', 'RedshopbTable')
								->setOption('forceWebserviceUpdate', true)
								->setOption('lockingMethod', 'Sync');
							$params             = array();
							$resourceRow        = array();
							$resourceSerialize  = array();
							$isNewResource      = true;
							$resourceAttributes = $resource->attributes();
							$resourceId         = (string) $resourceAttributes['id'];
							$remoteResourceId   = $remoteId . '_' . $resourceId;
							$resourceData       = $this->findSyncedId(
								$this->syncFieldDataName, $remoteResourceId, strtolower($resourceType), true, $fieldDataTable
							);
							$hashedKeyMedia     = isset($this->resourceHashedKeys[$resourceId]) ?
								$this->resourceHashedKeys[$resourceId] : RedshopbHelperSync::generateHashKey($resource, 'xml');

							if (!$this->isHashChanged($resourceData, $hashedKeyMedia))
							{
								// Hash key is the same so we will continue on the next item
								$this->skipItemUpdate($resourceData);

								continue;
							}

							if ($resourceData)
							{
								if (!$resourceData->deleted && $fieldDataTable->load($resourceData->local_id))
								{
									$isNewResource     = false;
									$resourceRow['id'] = $resourceData->local_id;
									$params            = json_decode($fieldDataTable->params, true);
								}
								// If item not exists, then user delete it, so lets skip it
								elseif ($resourceData->deleted)
								{
									$this->skipItemUpdate($resourceData);

									continue;
								}
								else
								{
									$this->deleteSyncedId($this->syncFieldDataName, $remoteResourceId, strtolower($resourceType));
								}
							}

							// Find the resource field
							$field = RedshopbHelperField::getFieldByName($resourceType, 'product');

							// That is a new resource type field so we will create it
							if (!$field)
							{
								$typeName = $resourceType == 'Video' ? 'Videos' : 'Documents';
								$typeId   = RedshopbHelperField::getType($typeName)->id;
								$field    = $this->createFieldValueField($resourceType, 'product', $typeId, true);
							}

							$resourceRow['item_id']  = $table->get('id');
							$resourceRow['field_id'] = $field->id;

							// Deep link is placed inside of the resource description. Only way we know for sure is it Filename is not present
							$params['external_url'] = empty($resource->Filename) ? trim((string) $resource->ResourceDescription) : null;
							$params['title']        = (string) $resource->ResourceDescription;
							$resourceFileName       = '';

							// If it is not deep link then we need to copy that file
							if (empty($params['external_url']))
							{
								// Get Field Type since we need it to get file from proper folder
								$fieldType        = RedshopbHelperField::getType($field->field_type_name);
								$resourceFileName = trim((string) $resource->Filename);
								$resourceFileName = $resourceId . '.' . JFile::getExt($resourceFileName);
								$isModified       = $this->isResourceChanged($resourceData, $resourceFileName, $resourceSerialize);

								if ($isNewResource || $isModified)
								{
									// If a local_id is not set, then the original file should exist but it will be deleted right after it's been used
									$resourceFilePath = (string) $this->client->localFolder . '/Pics/' . $resourceFileName;

									if (JFile::exists($resourceFilePath))
									{
										if (!$fieldDataTable->save($resourceRow))
										{
											throw new Exception($fieldDataTable->getError());
										}

										$params['internal_url'] = RedshopbHelperMedia::savingMedia(
											$resourceFilePath,
											$resourceFilePath,
											$fieldDataTable->get('id'),
											false,
											'products',
											$fieldType->alias
										);

										if (!$params['internal_url'])
										{
											$hashedKey = null;

											if ($isNewResource)
											{
												$fieldDataTable->delete($fieldDataTable->get('id'));
												$this->deleteSyncedId($this->syncFieldDataName, $remoteResourceId, strtolower($resourceType));
												continue;
											}

											$this->skipItemUpdate($resourceData);

											continue;
										}
									}
								}
							}

							// If resource name is not set we will use resource Type as the resource name
							$value                           = isset($resource->ResourceName) && (string) $resource->ResourceName != '' ?
										trim((string) $resource->ResourceName) : $resourceType;
							$resourceRow['params']           = $params;
							$resourceRow[$field->value_type] = $value;

							if (!$fieldDataTable->save($resourceRow))
							{
								throw new Exception($fieldDataTable->getError());
							}

							$this->recordSyncedId(
								$this->syncFieldDataName, $remoteResourceId, $fieldDataTable->id, strtolower($resourceType), $isNewResource,
								0, serialize($resourceSerialize), false, '', $fieldDataTable, 1, $hashedKeyMedia
							);
							break;
					}
				}
			}
		}

		// Translation fields
		if (!empty($this->fieldsForTranslation))
		{
			// Save field Translations
			// @todo implement translation logic after they define it *bump*

			// Reset the container for the next product
			$this->fieldsForTranslation = array();
		}

		// We have a hashed key stored without any problems like image missing, category missing or any unwanted behavior
		if ($hashedKey)
		{
			// Save this item ID to synced table
			$this->recordSyncedId(
				$this->syncName, $remoteId, $table->get('id'), '', false, 0, '', false, '', $table, 1, $hashedKey
			);
		}

		return true;
	}

	/**
	 * Read and store the data.
	 *
	 * @param   SimpleXMLElement  $xml    Xml element
	 * @param   object            $field  Parameters of the plugin
	 *
	 * @return  boolean
	 *
	 * @throws  \Exception
	 */
	public function bindValueFromField($xml, $field)
	{
		$value           = null;
		$fieldNamePlural = $field->name . 's';

		// If we are binding string then we might have translation
		if (($field->value_type == 'string_value' || $field->value_type == 'text_value')
			&& isset($xml->{$fieldNamePlural})
			&& isset($xml->{$fieldNamePlural}->{$field->name}))
		{
			foreach ($xml->{$fieldNamePlural}->{$field->name} as $child)
			{
				// This is their default value
				if ((string) $child['locale'] == 'da')
				{
					$value = trim((string) $child);
				}
				else
				{
					// This is for translation table
					$this->fieldsForTranslation[$field->id] = (string) $child;
				}
			}
		}
		elseif (in_array($field->value_type, array('float_value', 'int_value')))
		{
			$value = str_replace(
				array(',', ' '),
				array('.', ''),
				(string) $xml->{$field->name}
			);

			$value = $field->value_type == 'float_value' ? (float) $value : (int) $value;
		}
		// If we are here then this field does not have plural structure
		elseif (isset($xml->{$field->name}))
		{
			$value = trim((string) $xml->{$field->name});
		}

		return $value;
	}

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
		$this->setDefaultCronParams($webserviceData, $params);

		try
		{
			$db->unlockTables();

			$tmpFileSourceList = false;
			$cronExecuted      = $this->isCronExecuted($this->cronName);

			// Check to see if we are already run Sync which is not finished (so we can have multiple parts if webservice is too big)
			if ($cronExecuted)
			{
				$tmpFileSourceList = $this->getTmpFile('filesourcelist');
				$problemFound      = false;

				if (!$tmpFileSourceList)
				{
					$problemFound = true;
				}

				if ($this->redisDB->getIndex()
					&& !in_array($this->redisDB->getKey('sync_started', $this->redisExecutionPrefix),  array(1, 2)))
				{
					self::addMessage(Text::_('PLG_RB_SYNC_PIM_LOST_REDIS_DATA'), 'warning');
					$problemFound = true;
				}

				if ($this->params->get('cacheDriver', '') != ($this->redisDB->getIndex() ? 'redis' : 'mysql'))
				{
					self::addMessage(Text::_('PLG_RB_SYNC_PIM_WRONG_CACHE_DRIVER'), 'warning');
					$problemFound = true;
				}

				if ($problemFound)
				{
					// Forces a restart of the process, from scratch
					$cronExecuted = false;
					$this->setCronAsExecuted($this->cronName, false);

					if ($tmpFileSourceList)
					{
						JFile::delete($tmpFileSourceList);
					}
				}
			}

			// Process is not being executed or is in need to restart
			if (!$cronExecuted)
			{
				if ($this->redisDB->getIndex())
				{
					if (!in_array($this->redisDB->getKey('sync_started', $this->redisExecutionPrefix), array(2)))
					{
						RedshopbHelperSync::addMessage(Text::_('COM_REDSHOPB_SYNC_USING_REDIS'));

						// Initializes the Redis database for new processing
						$this->redisDB->flushDB();

						// Preparing before sync
						$this->redisDB->setKey('sync_started', 2, $this->redisExecutionPrefix);
					}

					$cacheDriver = 'redis';
				}
				else
				{
					$cacheDriver = 'mysql';
				}

				$this->params->set('cacheDriver', $cacheDriver);

				// We set them with a flag so we can delete the ones which are not present in the latest sync
				$counter = $this->setSyncRowsAsExecuted($this->syncName, null, true);

				if ($this->goToNextPart)
				{
					if (!$this->redisDB->getIndex())
					{
						RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_PIM_RESET_DB_SYNC_COUNTERS', $this->syncName, $counter));
					}

					goto endOfProcessLabel;
				}

				$counter = $this->setSyncRowsAsExecuted($this->syncFieldDataName, null, true);

				if ($this->goToNextPart)
				{
					if (!$this->redisDB->getIndex())
					{
						RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_PIM_RESET_DB_SYNC_COUNTERS', $this->syncFieldDataName, $counter));
					}

					goto endOfProcessLabel;
				}

				$counter = $this->setSyncRowsAsExecuted($this->syncMediaName, null, true);

				if ($this->goToNextPart)
				{
					if (!$this->redisDB->getIndex())
					{
						RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_PIM_RESET_DB_SYNC_COUNTERS', $this->syncMediaName, $counter));
					}

					goto endOfProcessLabel;
				}

				$counter = $this->setSyncRowsAsExecuted($this->syncProductDescriptionName, null, true);

				if ($this->goToNextPart)
				{
					if (!$this->redisDB->getIndex())
					{
						RedshopbHelperSync::addMessage(
							Text::sprintf('PLG_RB_SYNC_PIM_RESET_DB_SYNC_COUNTERS', $this->syncProductDescriptionName, $counter)
						);
					}

					goto endOfProcessLabel;
				}

				$tmpFileSourceList = $this->getTmpFile('filesourcelist', '', true);

				if (!$tmpFileSourceList)
				{
					goto endOfProcessLabel;
				}

				// Preparing before sync passed, then mark value as sync part
				$this->redisDB->setKey('sync_started', 1, $this->redisExecutionPrefix);

				// We set cron as executed so we can have multipart process if the process takes too long
				$this->setCronAsExecuted($this->cronName);

				$this->processItemsCompleted = 0;

				// List all source files
				$this->client->listAndStoreSourceFiles(
					$tmpFileSourceList,
					array(
						$this->client->localFolder . '/Product'
					)
				);
			}

			// It needs a temporary file to proceed
			if (!$tmpFileSourceList)
			{
				return false;
			}

			$db->unlockTables();

			// Set webservice parameters
			$this->params->set('processItemsCompleted', $this->processItemsCompleted);
			$this->params->set('processItemsStep', $this->processItemsStep);
			$this->params->set('groupLoadedFiles', true);
			$this->params->set('localFolder', $params->get('localFolder'));

			// Reads files and total from temp file
			$arrayListedFiles   = json_decode(file_get_contents($tmpFileSourceList), true);
			$numberOfFiles      = $arrayListedFiles['totalFiles'];
			$listedFiles        = $arrayListedFiles['files'];
			$countItemsLeft     = 0;
			$this->counterTotal = $numberOfFiles;

			if ($listedFiles)
			{
				if (is_null($this->params))
				{
					$this->params = new Registry;
				}

				$processItemsStep = ($this->params->get('groupLoadedFiles', false) ? $this->params->get('processItemsStep', 0) : 1);

				foreach ($listedFiles as $folder => $files)
				{
					if (!$files || !count($files))
					{
						unset($listedFiles[$folder]);

						continue;
					}

					foreach ($files as $fileId => $file)
					{
						// Breaks when the limit is reached
						if (!$processItemsStep || $this->goToNextPart)
						{
							$this->goToNextPart = true;
							break(2);
						}

						$path = $folder . '/' . $file;

						if (!JFile::exists($path) || !is_readable($path))
						{
							continue;
						}

						$fileContent = file_get_contents($path);

						if (!is_string($fileContent) || empty($fileContent))
						{
							continue;
						}

						// Check if loaded file is really XML properly formatted file
						try
						{
							$xmlTest = new SimpleXMLElement($fileContent);
						}
						catch (Exception $e)
						{
							continue;
						}

						// Remove first xml tag from all loaded files
						if (strpos(trim($fileContent), '<?xml') !== false)
						{
							$fileContent = substr($fileContent, (strpos($fileContent, '>') + 1));
						}

						$xmlString = '<?xml version="1.0" encoding="utf-8"?><group>' . $fileContent . '</group>';

						$xml = new SimpleXMLElement($xmlString);

						if (!is_object($xml))
						{
							throw new Exception(Text::sprintf('PLG_RB_SYNC_PIM_FAILED_TO_FETCH_ITEMS', $path));
						}

						unset($xmlTest);
						unset($fileContent);

						// Start sync with the new XML data
						$this->processData($xml);

						$processItemsStep --;
						unset($listedFiles[$folder][$fileId]);
					}

					unset($listedFiles[$folder]);
				}

				foreach ($listedFiles as $folder => $files)
				{
					$listedFiles[$folder] = array_values($files);
					$countItemsLeft      += count($files);
				}
			}

			$this->counter = $numberOfFiles - $countItemsLeft;

			if ($this->counter < $this->counterTotal)
			{
				$this->goToNextPart = true;
			}

			// Updates listed files into the temp file
			$arrayListedFiles['files'] = $listedFiles;

			JFile::write($tmpFileSourceList, json_encode($arrayListedFiles));

			// Sets number of executed items in Cron
			RedshopbHelperSync::setProgressCounters($this->cronTable, $this->counter, $numberOfFiles);

			if ($this->isExecutionTimeExceeded())
			{
				$this->goToNextPart = true;
			}

			if (!$this->goToNextPart)
			{
				// Remove product field data that were not present in the XML data
				$this->deleteRowsNotPresentInRemote(
					$this->syncFieldDataName, 'Field_Data', array(), true
				);

				if ($this->isExecutionTimeExceeded())
				{
					$this->goToNextPart = true;
				}
			}

			if (!$this->goToNextPart)
			{
				// Remove product media that were not present in the XML data
				$this->deleteRowsNotPresentInRemote($this->syncMediaName, 'Media', array(), true);

				if ($this->isExecutionTimeExceeded())
				{
					$this->goToNextPart = true;
				}
			}

			if (!$this->goToNextPart)
			{
				// Remove product media that were not present in the XML data
				$this->deleteRowsNotPresentInRemote($this->syncProductDescriptionName, 'Product_Description', array(), true);

				if ($this->isExecutionTimeExceeded())
				{
					$this->goToNextPart = true;
				}
			}

			if (!$this->goToNextPart)
			{
				// Remove products that were not present in the XML data
				$this->deleteRowsNotPresentInRemote($this->syncName, $this->tableClassName, array(), true);

				if ($this->isExecutionTimeExceeded())
				{
					$this->goToNextPart = true;
				}
			}

			if (!$this->goToNextPart)
			{
				// We are setting cron as finished (no more parts)
				$this->setCronAsFinished($this->cronName);

				// Cleans up temporary files
				$this->cleanupTmpFile('filesourcelist', '');

				if ($this->redisDB->getIndex())
				{
					// Cleans up Redis database
					$this->redisDB->flushDB();
				}
			}

			endOfProcessLabel:

			$db->unlockTables();
		}
		catch (Exception $e)
		{
			// We might be using nested tables so just in case we will unlock the table object if the store function have failed
			$db->unlockTables();
			RedshopbHelperSync::addMessage($e->getMessage(), 'error');

			return false;
		}

		return $this->outputResult();
	}

	/**
	 * Generic store for scope fields
	 *
	 * @param   string $scope    Fields scope
	 * @param   int    $itemId   ID of item
	 * @param   string $remoteId PIM ID of item
	 * @param   array  $fields   Array of fields to save
	 *
	 * @return  boolean  True on success, false otherwise
	 * @throws Exception
	 */
	public function storeScopeFieldData($scope, $itemId, $remoteId, $fields = array())
	{
		if (!isset($fields) || empty($fields))
		{
			return true;
		}

		$db = Factory::getDbo();

		// Get current Product Fields
		$query = $db->getQuery(true)
			->select('fd.*, f.name')
			->from($db->qn('#__redshopb_field_data', 'fd'))
			->leftJoin($db->qn('#__redshopb_field', 'f') . ' ON ' . $db->qn('f.id') . ' = ' . $db->qn('fd.field_id'))
			->where($db->qn('fd.item_id') . ' = ' . $db->q($itemId));

		$currentProductFields = $db->setQuery($query)->loadObjectList();

		// Get scope fields with value type definition
		$scopeFields = RedshopbHelperField::getFields($scope);
		$scopeFields = $scopeFields[$scope];

		$fieldsContainer       = array();
		$removeFieldsContainer = array();

		if (is_array($fields) && count($fields) > 0)
		{
			foreach ($fields as $fieldName => $fieldValue)
			{
				// When sending in form we replace prefix
				$fieldId = str_replace('scope_field_', '', $fieldName);

				if (!empty($scopeFields[$fieldId]))
				{
					$fieldItem = array(
						'id'       => 0,
						'item_id'  => $itemId,
						'field_id' => $fieldId,
						'state'    => '1'
					);

					// It received field is array then we are dealing with multiple values or values with parameters
					if (is_array($fieldValue))
					{
						// If this is single field data we change it to array so we can process it
						if (isset($fieldValue['params']))
						{
							$fieldValue = array($fieldValue);
						}

						// We need to save each of this fields to a separate field data row
						foreach ($fieldValue as $fieldDataId => $multipleFieldData)
						{
							$newField = $fieldItem;

							// We will check if this field have param key which differentiate it from the rest of the fields
							if (!isset($multipleFieldData['params']))
							{
								$newField[$scopeFields[$fieldId]->value_type] = $multipleFieldData;
								$newField['id']                               = RedshopbHelperField::checkForExistingFieldItem(
									$currentProductFields, $scopeFields, $newField, true
								);
							}
							else
							{
								$newField[$scopeFields[$fieldId]->value_type] = $multipleFieldData['name'];
								$newField['params']                           = $multipleFieldData['params'];
								$newField['state']                            = $multipleFieldData['state'];
								$newField['id']                               = is_numeric($fieldDataId) ? (int) $fieldDataId : 0;
								$newField['file']                             = null;

								// We reduce number of fields that are not set at all in the database by removing them
								if ($newField['id'] && $newField[$scopeFields[$fieldId]->value_type] == '')
								{
									$removeFieldsContainer[] = $newField['id'];

									continue;
								}
								elseif ($newField[$scopeFields[$fieldId]->value_type] == '')
								{
									continue;
								}
							}

							$fieldsContainer[] = $newField;
						}

						continue;
					}

					$fieldItem[$scopeFields[$fieldId]->value_type] = $fieldValue;

					if ($currentProductFields)
					{
						$fieldItem['id'] = RedshopbHelperField::checkForExistingFieldItem($currentProductFields, $scopeFields, $fieldItem, false);
					}

					if ($fieldItem[$scopeFields[$fieldId]->value_type] == '')
					{
						// We reduce number of fields that are not set at all in the database by removing them
						if ($fieldItem['id'])
						{
							$removeFieldsContainer[] = $fieldItem['id'];
						}

						continue;
					}
					elseif (in_array($scopeFields[$fieldId]->value_type, array('float_value', 'int_value'))
						&& !is_numeric($fieldItem[$scopeFields[$fieldId]->value_type])
					)
					{
						$fieldItem[$scopeFields[$fieldId]->value_type] = str_replace(
							array(',', ' '),
							array('.', ''),
							$fieldItem[$scopeFields[$fieldId]->value_type]
						);

						if (!is_numeric($fieldItem[$scopeFields[$fieldId]->value_type]))
						{
							continue;
						}
					}

					$fieldsContainer[] = $fieldItem;
				}
			}
		}

		// Store the new items
		foreach ($fieldsContainer as $field)
		{
			/** @var RedshopbTableField_Data $xrefTable */
			$xrefTable = RedshopbTable::getAdminInstance('Field_Data')->setOption('lockingMethod', 'Sync');
			$xrefTable->setOption('storeNulls', true)
				->setOption('forceWebserviceUpdate', true)
				->setOption('lockingMethod', 'Sync');
			$isNew      = true;
			$hashedKey  = null;
			$pimFieldId = $this->findSyncedLocalId('erp.pim.field', $field['field_id']);

			if (!$pimFieldId)
			{
				RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_PIM_CANT_FIND_FIELD_PIM_ID', $field['field_id']), 'error');
				continue;
			}

			if ($field['id'])
			{
				$itemData  = $this->findSyncedId($this->syncFieldDataName, $remoteId . '_' . $pimFieldId, '', true, $xrefTable);
				$hashedKey = RedshopbHelperSync::generateHashKey($field, 'array');

				if (!$this->isHashChanged($itemData, $hashedKey))
				{
					// Hash key is the same so we will continue on the next item
					$this->skipItemUpdate($itemData);

					continue;
				}

				if ($itemData)
				{
					if ($xrefTable->load($field['id']))
					{
						$isNew = false;
					}
					else
					{
						$this->deleteSyncedId($this->syncFieldDataName, $remoteId . '_' . $pimFieldId);
					}
				}
				else
				{
					// Enrich sync record from original table
					if ($xrefTable->load($field['id']))
					{
						$xrefTable->setChangedProperties();

						try
						{
							$this->recordSyncedId(
								$this->syncFieldDataName, $remoteId . '_' . $pimFieldId, $xrefTable->get('id'), '',
								true, 0, '', false, '', $xrefTable, 1, null
							);
						}
						catch (Exception $e)
						{
							RedshopbHelperSync::addMessage($e->getMessage(), 'warning');

							return false;
						}

						$isNew = false;
					}
				}
			}

			if (!$xrefTable->save($field))
			{
				RedshopbHelperSync::addMessage($xrefTable->getError(), 'warning');

				return false;
			}

			if (empty($hashedKey))
			{
				$field['id'] = $xrefTable->get('id');
				$hashedKey   = RedshopbHelperSync::generateHashKey($field, 'array');
			}

			try
			{
				$this->recordSyncedId(
					$this->syncFieldDataName, $remoteId . '_' . $pimFieldId, $xrefTable->get('id'), '',
					$isNew, 0, '', false, '', $xrefTable, 1, $hashedKey
				);
			}
			catch (Exception $e)
			{
				RedshopbHelperSync::addMessage($e->getMessage(), 'warning');

				return false;
			}
		}

		return true;
	}

	/**
	 * Read and store the data.
	 *
	 * @param   SimpleXMLElement  $xml       XML element
	 * @param   string            $parentId  Parent id
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 */
	public function processData($xml, $parentId = '')
	{
		if (!isset($xml->{$this->nameFieldWithData}))
		{
			$this->goToNextPart = false;

			return false;
		}

		$db = Factory::getDbo();

		foreach ($xml->{$this->nameFieldWithData} as $i => $item)
		{
			if ($this->goToNextPart == true || $this->isExecutionTimeExceeded() || $this->isOverTheStepLimit())
			{
				$this->goToNextPart = true;
				break;
			}

			try
			{
				// We are doing transaction for each row separately because of large resource loads
				$db->transactionStart();
				$this->counter++;
				$this->preSyncItem($item, $parentId);
				$db->transactionCommit();

				// We might be using nested tables so just in case we will unlock the table object if the store function have failed
				$db->unlockTables();
			}
			catch (Exception $e)
			{
				$db->transactionRollback();

				throw $e;
			}
		}

		return true;
	}

	/**
	 * Read and store the data.
	 *
	 * @param   SimpleXMLElement  $obj  XML element
	 *
	 * @return  array
	 */
	public function getResourceHashedKeys($obj)
	{
		$resourceHashedKeys = array();
		$item               = null;

		if ($obj->Items->Item)
		{
			$item = $obj->Items->Item[0];
		}

		if (isset($item->Resources->Resource))
		{
			foreach ($item->Resources->Resource as $resource)
			{
				if (!empty($resource->ResourceType))
				{
					$resourceType     = (string) $resource->ResourceType;
					$resourceFileName = $resourceType == 'Picture' ? (string) $resource->ResourceFilename : (string) $resource->Filename;
					$resourceFileName = trim($resourceFileName);

					$resourceAttributes              = $resource->attributes();
					$resourceId                      = (string) $resourceAttributes['id'];
					$resourceHashedKeys[$resourceId] = RedshopbHelperSync::generateHashKey($resource, 'xml');

					// File name is actually an ID of the resource
					$resourceFileName = $resourceId . '.' . JFile::getExt($resourceFileName);
					$resourceFilePath = (string) $this->client->localFolder . '/Pics/' . $resourceFileName;

					if (JFile::exists($resourceFilePath))
					{
						$fileTimeStamp                    = filemtime($resourceFilePath);
						$resourceHashedKeys[$resourceId] .= RedshopbHelperSync::generateHashKey($fileTimeStamp, 'string');
					}
				}
			}
		}

		return $resourceHashedKeys;
	}

	/**
	 * Set all existing rows as executed in sync table (or file, depending on the parameters)
	 *
	 * @param   string  $reference        Reference name
	 * @param   bool    $remoteParentKey  Is one product then bind it to a specific remote parent
	 * @param   bool    $usePartialSet    Use partial sets
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 */
	public function setSyncRowsAsExecuted($reference, $remoteParentKey = null, $usePartialSet = false)
	{
		$parent = parent::setSyncRowsAsExecuted($reference, $remoteParentKey, $usePartialSet);

		if (!$parent)
		{
			return false;
		}

		// Only continues processing for field data and if Redis DB is present, to create cache of fields
		if ($reference != $this->syncFieldDataName || !$this->redisDB->getIndex())
		{
			return $parent;
		}

		// Caches all current custom data rows in Redis for faster future access
		$db        = Factory::getDbo();
		$productId = $this->redisDB->getKey('field_data_product', $this->redisExecutionPrefix);

		$productQuery = $db->getQuery(true)
			->select('id')
			->from($db->qn('#__redshopb_product'))
			->order('id ASC');

		if ($productId)
		{
			$productQuery->where('id > ' . (int) $productId);
		}

		$currentProduct = $db->setQuery($productQuery, 0, 1)->loadResult();

		while ($currentProduct)
		{
			$query = $db->getQuery(true)
				->select(
					array(
						$db->qn('fd.id', 'field_data_id'),
						$db->qn('f.name', 'field_name')
					)
				)
				->from($db->qn('#__redshopb_field_data', 'fd'))
				->join('inner', $db->qn('#__redshopb_field', 'f') . ' ON ' . $db->qn('f.id') . ' = ' . $db->qn('fd.field_id'))
				->where($db->qn('f.scope') . ' = ' . $db->q('product'))
				->where('fd.item_id = ' . (int) $currentProduct)
				->order(
					array(
						$db->qn('f.name'),
						$db->qn('fd.id')
					)
				);

			$fieldDataRows = $db->setQuery($query)
				->loadObjectList();

			$fieldName          = '';
			$currentDataProduct = array();
			$currentDataField   = array();

			if (!empty($fieldDataRows))
			{
				// Saves one array for product/field relations and one for product-field/data relations, to store in Redis for faster access to records
				foreach ($fieldDataRows as $fieldDataRow)
				{
					if (!$this->findSyncedLocalId($reference, $fieldDataRow->field_data_id))
					{
						continue;
					}

					if ($fieldName != $fieldDataRow->field_name)
					{
						if (!empty($currentDataField))
						{
							$this->redisDB->setKey(
								$currentProduct . '_' . $fieldName, json_encode($currentDataField), $this->redisCachedFieldsPrefix
							);
						}

						$currentDataField = array();
					}

					$fieldName = $fieldDataRow->field_name;

					$currentDataProduct[] = $fieldDataRow->field_name;
					$currentDataField[]   = $fieldDataRow->field_data_id;
				}
			}

			if (!empty($currentDataProduct))
			{
				$this->redisDB->setKey($currentProduct, json_encode($currentDataProduct), $this->redisCachedProductFieldsPrefix);
			}

			if (!empty($currentDataField))
			{
				$this->redisDB->setKey($currentProduct . '_' . $fieldName, json_encode($currentDataField), $this->redisCachedFieldsPrefix);
			}

			if ($this->isExecutionTimeExceeded())
			{
				$this->goToNextPart = true;
				$this->redisDB->setKey('field_data_product', $currentProduct, $this->redisExecutionPrefix);

				$productQuery->clear()
					->select('COUNT(id)')
					->from($db->qn('#__redshopb_product'))
					->where('id > ' . (int) $currentProduct);

				$left = (int) $db->setQuery($productQuery)->loadResult();

				RedshopbHelperSync::addMessage(
					Text::sprintf('PLG_RB_SYNC_PIM_RESET_SYNC_COUNTERS', $reference, $left), 'notice'
				);

				break;
			}
			else
			{
				$productQuery->clear('where')
					->where('id > ' . (int) $currentProduct);
				$currentProduct = $db->setQuery($productQuery)->loadResult();
			}
		}

		if (!$this->goToNextPart)
		{
			$this->redisDB->setKey('field_data_product', 0, $this->redisExecutionPrefix);
		}

		return true;
	}

	/**
	 * Skips updating resources because of unchanged product data
	 *
	 * @param   SimpleXMLElement  $obj      XML element
	 * @param   int               $localId  Local ID
	 *
	 * @return  void
	 */
	public function skipResourcesUpdate($obj, $localId)
	{
		$item       = null;
		$attributes = $obj->attributes();
		$remoteId   = (string) $attributes['id'];

		if ($obj->Items->Item)
		{
			$item = $obj->Items->Item[0];
		}

		if (!$item)
		{
			return;
		}

		if (empty($localId))
		{
			$localId = $this->findSyncedId($this->syncName, $remoteId, '');
		}

		// Skip Product Description
		$productDescriptionData = $this->findSyncedId($this->syncProductDescriptionName, $remoteId, '', true);

		if ($productDescriptionData)
		{
			$this->skipItemUpdate($productDescriptionData);
		}

		// Pictures and other resources like documents, etc
		if (isset($item->Resources->Resource))
		{
			foreach ($item->Resources->Resource as $resource)
			{
				if (!empty($resource->ResourceType))
				{
					$resourceType       = (string) $resource->ResourceType;
					$resourceAttributes = $resource->attributes();
					$resourceId         = (string) $resourceAttributes['id'];

					if ($resourceType == 'Picture')
					{
						$resourceFileName = (string) $resource->ResourceFilename;

						// File name is actually an ID of the resource
						$resourceFileName = $resourceId . '.' . JFile::getExt($resourceFileName);
						$remoteMediaKey   = $resourceId . '_' . $localId;
						$resourceData     = $this->findSyncedId($this->syncMediaName, $remoteMediaKey, $resourceFileName, true);
					}
					else
					{
						$remoteResourceId = $remoteId . '_' . $resourceId;
						$resourceData     = $this->findSyncedId($this->syncFieldDataName, $remoteResourceId, strtolower($resourceType), true);
					}

					if ($resourceData)
					{
						$this->skipItemUpdate($resourceData);
					}
				}
			}
		}

		$sync            = new stdClass;
		$sync->reference = $this->syncFieldDataName;

		$children = $item->children();

		$redisProductFields = array();
		$redisFieldDataKeys = array();

		// For Redis support it caches all the data fields of the product in a php array
		if ($this->redisDB->getIndex())
		{
			$redisProductFields = $this->redisDB->getKey($localId, $this->redisCachedProductFieldsPrefix);

			if ($redisProductFields)
			{
				$redisProductFields = json_decode($redisProductFields);
			}

			if (!is_array($redisProductFields) || empty($redisProductFields))
			{
				return;
			}
		}

		// Skipping product field data
		if ($children && !empty($children))
		{
			$fieldDataRows = array();

			if (!$this->redisDB->getIndex())
			{
				$db    = Factory::getDbo();
				$query = $db->getQuery(true)
					->select(
						array(
							$db->qn('fd.id', 'field_data_id'),
							$db->qn('f.name', 'field_name')
						)
					)
					->from($db->qn('#__redshopb_field_data', 'fd'))
					->leftJoin($db->qn('#__redshopb_field', 'f') . ' ON ' . $db->qn('f.id') . ' = ' . $db->qn('fd.field_id'))
					->where($db->qn('f.scope') . ' = ' . $db->q('product'))
					->where('fd.item_id = ' . (int) $localId)
					->order(
						array(
							$db->qn('f.name'),
							$db->qn('fd.id')
						)
					);

				$fieldRows = $db->setQuery($query)
					->loadObjectList();

				if (!empty($fieldRows))
				{
					foreach ($fieldRows as $fieldRow)
					{
						if (!array_key_exists($fieldRow->field_name, $fieldDataRows))
						{
							$fieldDataRows[$fieldRow->field_name] = array();
						}

						$fieldDataRows[$fieldRow->field_name][] = $fieldRow;
					}
				}
			}

			foreach ($children as $child)
			{
				$fieldName = $child->getName();

				// No redis support
				if (!$this->redisDB->getIndex())
				{
					if (!array_key_exists($fieldName, $fieldDataRows))
					{
						if (substr($fieldName, -1) == 's')
						{
							$fieldName = substr($fieldName, 0, strlen($fieldName) - 1);

							if (!array_key_exists($fieldName, $fieldDataRows))
							{
								continue;
							}
						}
						else
						{
							continue;
						}
					}

					// Marks each field data of this product as executed
					foreach ($fieldDataRows[$fieldName] as $fieldData)
					{
						$sync->local_id = $fieldData->field_data_id;
						$this->skipItemUpdate($sync);
					}

					continue;
				}

				// Redis support.  First it discards the unset field data for this product
				if (!in_array($fieldName, $redisProductFields))
				{
					if (substr($fieldName, -1) == 's')
					{
						$fieldName = substr($fieldName, 0, strlen($fieldName) - 1);

						if (!in_array($fieldName, $redisProductFields))
						{
							continue;
						}
					}
				}

				$redisFieldDataKeys[] = $localId . '_' . $fieldName;
			}
		}

		if (!empty($redisFieldDataKeys))
		{
			$dataFieldIdArrays = $this->redisDB->getKeys($redisFieldDataKeys, $this->redisCachedFieldsPrefix);

			foreach ($dataFieldIdArrays as $dataFieldIdArray)
			{
				if (!$dataFieldIdArray)
				{
					continue;
				}

				$dataFieldIds = json_decode($dataFieldIdArray);

				if ($dataFieldIds && is_array($dataFieldIds) && !empty($dataFieldIds))
				{
					foreach ($dataFieldIds as $dataFieldId)
					{
						$sync->local_id = $dataFieldId;
						$this->skipItemUpdate($sync);
					}
				}
			}
		}
	}
}
