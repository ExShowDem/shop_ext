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
 * Get Category function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  PIM
 * @since       1.0
 */
class PimGetCategory extends PimFunctionBase
{
	/**
	 * Name in the Sync Table
	 *
	 * @var  string
	 */
	public $syncName = 'erp.pim.category';

	/**
	 * @var string
	 */
	public $cronName = 'GetCategory';

	/**
	 * @var string
	 */
	public $tableClassName = 'Category';

	/**
	 * @var string
	 */
	public $settingsFile = 'Structure.xml';

	/**
	 * @var string
	 */
	public $nameFieldWithData = 'Assortment';

	/**
	 * @var boolean
	 */
	public $useTableClassForDeletes = true;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->avoidOverrideWSProperties = array_merge(
			$this->avoidOverrideWSProperties,
			array(
				'alias', 'description', 'template_id', 'product_grid_template_id', 'product_list_template_id'
			)
		);

		parent::__construct();
	}

	/**
	 * Read and store the data.
	 *
	 * @param   SimpleXMLElement  $xml       XML element
	 * @param   Table             $table     Table object
	 * @param   string            $parentId  Parent id
	 *
	 * @return  boolean
	 *
	 * @throws  Exception
	 */
	public function readXmlRecursive($xml, &$table, $parentId = '')
	{
		$attributes = $xml->attributes();
		$remoteId   = (string) $attributes['id'];

		if (isset($this->executed[$remoteId . '_' . $parentId]))
		{
			return false;
		}

		$row       = array();
		$isNew     = true;
		$itemData  = $this->findSyncedId($this->syncName, $remoteId, $parentId, true, $table);
		$hashedKey = RedshopbHelperSync::generateHashKey($xml, 'xml');
		$isSkipped = false;

		if (!$this->isHashChanged($itemData, $hashedKey))
		{
			// Hash key is the same so we will continue on the next item
			$this->skipItemUpdate($itemData);

			$isSkipped = true;
		}

		if (!$isSkipped)
		{
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

					return false;
				}
				else
				{
					$this->deleteSyncedId($this->syncName, $remoteId, $parentId);
				}
			}

			$row['state']      = 1;
			$row['name']       = (string) $xml->ChannelNodeName;
			$row['company_id'] = null;

			// We do not allow 0, it must be 1 which is ROOT element
			if ($isNew)
			{
				$row['parent_id'] = 1;
			}

			$localParentId = $this->findSyncedId($this->syncName, $parentId);

			if ($parentId && $localParentId)
			{
				$row['parent_id'] = $localParentId;
			}

			if (!isset($row['parent_id']))
			{
				$row['parent_id'] = 1;
			}

			if ($table->get('parent_id') != $row['parent_id'] || !$table->get('id'))
			{
				// Reprocesses record, forcing it to alter its ACL
				$table->setLocation($row['parent_id'], 'last-child');
			}

			// Filter fieldset
			$row['filter_fieldset_id'] = null;
			$filterName                = (string) $xml->FieldSet;

			if ($filterName != '')
			{
				if (substr($filterName, 0, 2) === 'CN')
				{
					$filterName = substr($filterName, 2);
				}

				$filterId = $this->findSyncedId('erp.pim.filterFieldset', $filterName);

				if ($filterId)
				{
					$row['filter_fieldset_id'] = $filterId;
				}
				else
				{
					// We will not update Hash key since this item needs to sync again
					$hashedKey = null;
					RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_PIM_FILTER_FIELD_NOT_FOUND', $filterName), 'warning');
				}
			}

			if (!$table->save($row))
			{
				RedshopbHelperSync::addMessage($table->getError(), 'warning');
			}

			$this->saveLocalFields($table->get('id'), $filterName);

			// Save this item ID to synced table
			$this->recordSyncedId(
				$this->syncName, $remoteId, $table->get('id'), $parentId, $isNew,
				0, '', false, '', $table, 1, $hashedKey
			);
		}

		// If we have subcategories then we run recursive call to it
		if (!empty($xml->Assortments))
		{
			$this->processData($xml->Assortments, $remoteId);
		}

		return true;
	}

	/**
	 * Read and store the data.
	 *
	 * @param   SimpleXMLElement[]  $xml       XML element
	 * @param   string              $parentId  Parent id
	 *
	 * @return  boolean
	 */
	public function processData($xml, $parentId = '')
	{
		if (!isset($xml->{$this->nameFieldWithData}))
		{
			$this->goToNextPart = false;

			return false;
		}

		foreach ($xml->{$this->nameFieldWithData} as $item)
		{
			$this->counterTotal++;

			if ($this->processItemsStep > 0 && $this->processItemsCompleted > 0 && $this->processItemsCompleted >= $this->counterTotal)
			{
				$attributes = $item->attributes();

				// If we have subcategories then we run recursive call to it to count all categories
				if (!empty($item->Assortments))
				{
					$this->processData($item->Assortments, (string) $attributes['id']);
				}

				continue;
			}

			if ($this->goToNextPart == true || $this->isExecutionTimeExceeded() || $this->isOverTheStepLimit())
			{
				$this->goToNextPart = true;
				continue;
			}

			$this->preSyncItem($item, $parentId);
			$this->counter++;
		}

		return true;
	}

	/**
	 * Links local fields to the category.
	 *
	 * @param   integer  $categoryId    XML element
	 * @param   string   $fieldsetName  Name of the category's fieldset
	 *
	 * @return  void
	 */
	public function saveLocalFields($categoryId, $fieldsetName)
	{
		if (!$fieldsetName)
		{
			return;
		}

		$fieldsetFields = $this->getSpecificFilterFieldSet($fieldsetName, 'Fieldset.xml');

		if (!$fieldsetFields || empty($fieldsetFields->Field))
		{
			return;
		}

		$xrefTable = RTable::getInstance('Category_Field_Xref', 'RedshopbTable')
			->setOption('lockingMethod', 'Sync');

		foreach ($fieldsetFields->Field as $fieldRow)
		{
			$fieldId   = $this->findSyncedId('erp.pim.field', $fieldRow);
			$fieldData = RedshopbHelperField::getFieldById($fieldId);

			if (!$fieldData->global)
			{
				$row['category_id'] = $categoryId;
				$row['field_id']    = $fieldId;

				$xrefTable->save($row);
			}
		}
	}
}
