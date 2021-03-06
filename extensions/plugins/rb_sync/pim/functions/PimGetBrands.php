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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

require_once __DIR__ . '/base.php';

/**
 * Get Brands function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  PIM
 * @since       1.0
 */
class PimGetBrands extends PimFunctionBase
{
	/**
	 * Name in the Sync Table
	 *
	 * @var  string
	 */
	public $syncName = 'erp.pim.brands';

	/**
	 * @var string
	 */
	public $cronName = 'GetBrands';

	/**
	 * @var string
	 */
	public $tableClassName = 'Manufacturer';

	/**
	 * @var string
	 */
	public $settingsFile = 'Cvl.xml';

	/**
	 * @var string
	 */
	public $nameFieldWithData = 'CVLValue';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->avoidOverrideWSProperties = array_merge(
			$this->avoidOverrideWSProperties,
			array(
				'alias', 'image', 'description', 'featured'
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
	 * @throws  \Exception
	 */
	public function readXmlRecursive($xml, &$table, $parentId = '')
	{
		$db = Factory::getDbo();

		// We use Key instead of $xml->Id because products are connected with string Key
		$remoteId = (string) $xml->Key;

		if (isset($this->executed[$remoteId . '_']))
		{
			return false;
		}

		$row       = array();
		$isNew     = true;
		$itemData  = $this->findSyncedId($this->syncName, $remoteId, '', true, $table);
		$hashedKey = RedshopbHelperSync::generateHashKey($xml, 'xml');

		if (!$this->isHashChanged($itemData, $hashedKey))
		{
			// Hash key is the same so we will continue on the next item
			$this->skipItemUpdate($itemData);

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

				return false;
			}
			else
			{
				$this->deleteSyncedId($this->syncName, $remoteId);
			}
		}

		$row['state']     = 1;
		$row['name']      = (string) $xml->Value;
		$row['parent_id'] = 1;

		if ($table->get('parent_id') != $row['parent_id'] || !$table->get('id'))
		{
			// Reprocesses record, forcing it to alter its ACL
			$table->setLocation($row['parent_id'], 'last-child');
		}

		if (!$table->save($row))
		{
			RedshopbHelperSync::addMessage($table->getError(), 'warning');

			return false;
		}

		// Save this item ID to synced table
		$this->recordSyncedId(
			$this->syncName, $remoteId, $table->get('id'), '', $isNew,
			0, '', false, '', $table, 1, $hashedKey
		);

		return true;
	}

	/**
	 * Read and store the data.
	 *
	 * @param   SimpleXMLElement  $xml       XML element
	 * @param   string            $parentId  Parent id
	 *
	 * @return  boolean
	 */
	public function processData($xml, $parentId = '')
	{
		$xmlGroup = null;

		foreach ($xml->group as $obj)
		{
			if (!empty($obj['id']) && $obj['id'] == 'Brand')
			{
				$xmlGroup = $obj;
				break;
			}
		}

		if (empty($xmlGroup))
		{
			RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_PIM_FAILED_TO_FETCH_ITEMS', $this->settingsFile), 'warning');

			return false;
		}

		if (!isset($xmlGroup->{$this->nameFieldWithData}))
		{
			return true;
		}

		$this->counterTotal = count($xmlGroup->{$this->nameFieldWithData});
		$xmlGroup           = (array) $xmlGroup;

		if ($this->processItemsCompleted > 0)
		{
			$xmlGroup[$this->nameFieldWithData] = array_slice($xmlGroup[$this->nameFieldWithData], $this->processItemsCompleted);
		}

		foreach ($xmlGroup[$this->nameFieldWithData] as $item)
		{
			if ($this->goToNextPart == true || $this->isExecutionTimeExceeded() || $this->isOverTheStepLimit())
			{
				$this->goToNextPart = true;
				break;
			}

			$this->preSyncItem($item, $parentId);
			$this->counter++;
		}

		return true;
	}
}
