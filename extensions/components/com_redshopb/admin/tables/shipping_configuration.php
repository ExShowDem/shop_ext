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
 * Shipping configuration table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.6
 */
class RedshopbTableShipping_Configuration extends RedshopbTable
{
	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  string
	 */
	public $extension_name;

	/**
	 * @var  string
	 */
	public $owner_name;

	/**
	 * @var  string
	 */
	public $shipping_name;

	/**
	 * @var  string
	 */
	public $params;

	/**
	 * @var integer
	 */
	public $state;

	/**
	 * @var  string
	 */
	public $created_date = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	public $created_by = null;

	/**
	 * @var  string
	 */
	public $modified_date = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	public $modified_by = null;

	/**
	 * Constructor
	 *
	 * @param   JDatabase  $db  A database connector object
	 *
	 * @throws  UnexpectedValueException
	 */
	public function __construct(&$db)
	{
		$this->_tableName = 'redshopb_shipping_configuration';
		$this->_tbl_key   = 'id';

		parent::__construct($db);
	}

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
		// Check if client is not already created with this id.
		$client = clone $this;

		$this->extension_name = trim($this->extension_name);
		$this->owner_name     = trim($this->owner_name);
		$this->shipping_name  = trim($this->shipping_name);

		if (empty($this->extension_name))
		{
			$this->setError(Text::_('COM_REDSHOPB_SHIPPING_EXTENSION_NAME_FIELD_CANNOT_BE_EMPTY'));

			return false;
		}

		if (empty($this->shipping_name))
		{
			$this->setError(Text::_('COM_REDSHOPB_SHIPPING_NAME_FIELD_CANNOT_BE_EMPTY'));

			return false;
		}

		$loadParams = array('shipping_name' => $this->shipping_name, 'owner_name' => $this->owner_name, 'extension_name' => $this->extension_name);

		if ($client->load($loadParams) && $client->id != $this->id)
		{
			$this->setError(Text::_('COM_REDSHOPB_SHIPPING_ID_ALREADY_EXISTS'));

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
		if (!parent::store($updateNulls))
		{
			return false;
		}

		return true;
	}
}
