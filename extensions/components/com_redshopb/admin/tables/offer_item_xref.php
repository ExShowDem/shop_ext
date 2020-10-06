<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Offer Item Xref table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableOffer_Item_Xref extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_offer_item_xref';

	/**
	 * @var  integer
	 */
	public $offer_id;

	/**
	 * @var  integer
	 */
	public $product_id;

	/**
	 * @var  integer
	 */
	public $product_item_id;

	/**
	 * @var  integer
	 */
	public $quantity;

	/**
	 * @var  integer
	 */
	public $unit_price;

	/**
	 * @var  float
	 */
	public $subtotal;

	/**
	 * @var  float
	 */
	public $discount = 0;

	/**
	 * @var  float
	 */
	public $total;

	/**
	 * @var integer
	 */
	public $id;

	/**
	 * @var string
	 */
	public $discount_type = 'percent';

	/**
	 * @var string
	 */
	public $params = null;

	/**
	 * Method to store a row in the database from the Table instance properties.
	 * If a primary key value is set the row with that primary key value will be
	 * updated with the instance property values.  If no primary key value is set
	 * a new row will be inserted into the database with the properties from the
	 * Table instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = true)
	{
		if ($this->product_item_id == '' || $this->product_item_id == 0)
		{
			$this->product_item_id = null;
		}

		return parent::store($updateNulls);
	}
}
