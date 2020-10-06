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
 * Webservice Permission Item Reference table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.6
 */
class RedshopbTableWebservice_Permission_Item_Xref extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_webservice_permission_item_xref';

	/**
	 * Name of the primary key fields in the table.
	 *
	 * @var    array
	 * @since  12.2
	 */
	protected $_tbl_keys = array('item_id', 'webservice_permission_id');

	/**
	 * @var  integer
	 */
	public $item_id;

	/**
	 * @var  string
	 */
	public $scope = 'product';

	/**
	 * @var  integer
	 */
	public $webservice_permission_id;

	/**
	 * @var  array
	 */
	public $webservice_permission_ids;

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
		if (empty($this->item_id))
		{
			$this->setError(Text::_('COM_REDSHOPB_WEBSERVICE_PERMISSION_ITEM_REQUIRED'));

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
	public function storeXref($updateNulls = false)
	{
		if (!isset($this->webservice_permission_ids))
		{
			return true;
		}

		// Delete all items
		$db    = $this->_db;
		$query = $db->getQuery(true)
			->delete('#__redshopb_webservice_permission_item_xref')
			->where('item_id = ' . $db->q($this->item_id))
			->where('scope = ' . $db->q($this->scope));

		$db->setQuery($query);

		if (!$db->execute())
		{
			return false;
		}

		// Store new permissions if they exist
		if (is_array($this->webservice_permission_ids) && count($this->webservice_permission_ids) > 0)
		{
			// Store the new items
			foreach ($this->webservice_permission_ids as $webservicePermissionId)
			{
				if (!$this->save(
					array(
						'item_id' => $this->item_id,
						'scope' => $this->scope,
						'webservice_permission_id' => $webservicePermissionId
					)
				))
				{
					$this->setError($this->getError());

					return false;
				}
			}
		}

		return true;
	}
}
