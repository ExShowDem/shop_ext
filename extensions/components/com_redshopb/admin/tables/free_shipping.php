<?php
/**
 * @package     Redshopb.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Free_Shipping table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableFree_Shipping extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_free_shipping_threshold_purchases';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var integer
	 */
	public $product_discount_group_id;

	/**
	 * @var integer
	 */
	public $category_id;

	/**
	 * @var  float
	 */
	public $threshold_expenditure;

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
	 * @var  integer
	 */
	public $checked_out = null;

	/**
	 * @var  string
	 */
	public $checked_out_time = '0000-00-00 00:00:00';

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
		// Sanitize the product_discount_group_id
		if (empty($this->product_discount_group_id))
		{
			$this->product_discount_group_id = null;
		}

		// Sanitize the category_id
		if (empty($this->category_id))
		{
			$this->category_id = null;
		}

		if (is_null($this->product_discount_group_id) && is_null($this->category_id))
		{
			$this->setError(Text::_('COM_REDSHOPB_FREE_SHIPPING_MUST_CHOOSE_GROUP'));

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
		return parent::store($updateNulls);
	}

	/**
	 * Method to remove an entry in the 'free_shipping_threshold_purchases' database table.
	 *
	 * @param   array    $cids  Array of table IDs.
	 *
	 * @return  boolean  True on success.
	 */
	public function remove($cids)
	{
		return parent::delete($cids);
	}
}
