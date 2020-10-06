<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Webservice Permission table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.6
 */
class RedshopbTableWebservice_Permission extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_webservice_permission';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  string
	 */
	public $scope;

	/**
	 * @var  string
	 */
	public $name;

	/**
	 * @var  string
	 */
	public $description;

	/**
	 * @var  integer
	 */
	public $manual;

	/**
	 * @var  integer
	 */
	public $state;

	/**
	 * @var  array
	 */
	protected $webservice_permission_item_ids;

	/**
	 * Checks that the object is valid and able to be stored.
	 *
	 * This method checks that the parent_id is non-zero and exists in the database.
	 * Note that the root node (parent_id = 0) cannot be manipulated with this class.
	 *
	 * @return  boolean  True if all checks pass.
	 */
	public function check()
	{
		if (empty($this->name))
		{
			$this->setError(Text::_('COM_REDSHOPB_WEBSERVICE_PERMISSION_NAME_REQUIRED'));

			return false;
		}

		if (empty($this->scope))
		{
			$this->setError(Text::_('COM_REDSHOPB_WEBSERVICE_PERMISSION_SCOPE_REQUIRED'));

			return false;
		}

		return true;
	}

	/**
	 * Method to store a node in the database table.
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = true)
	{
		if ($this->getOption('storeNulls', false))
		{
			$updateNulls = true;
		}

		if (!parent::store($updateNulls))
		{
			return false;
		}

		if (!$this->storeXref())
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to store a node in the database table.
	 *
	 * @return  boolean  True on success.
	 */
	public function storeXref()
	{
		if (empty($this->manual))
		{
			return true;
		}

		// Delete all items
		$db    = $this->_db;
		$query = $db->getQuery(true)
			->delete('#__redshopb_webservice_permission_item_xref')
			->where('webservice_permission_id = ' . $db->q($this->id));

		$db->setQuery($query);

		if (!$db->execute())
		{
			return false;
		}

		/** @var RedshopbTableWebservice_Permission_Item_Xref $xrefTable */
		$xrefTable = RedshopbTable::getAdminInstance('Webservice_Permission_Item_Xref');

		// Store new permissions if they exist
		if (is_array($this->webservice_permission_item_ids) && count($this->webservice_permission_item_ids) > 0)
		{
			// Store the new items
			foreach ($this->webservice_permission_item_ids as $webservicePermissionItemId)
			{
				if (!$xrefTable->save(
					array(
						'item_id' => $webservicePermissionItemId,
						'scope' => $this->scope,
						'webservice_permission_id' => $this->id
					)
				))
				{
					$this->setError($xrefTable->getError());

					return false;
				}
			}
		}

		return true;
	}
}
