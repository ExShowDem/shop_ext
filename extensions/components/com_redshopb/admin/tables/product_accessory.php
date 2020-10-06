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
 * Product Accessory table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableProduct_Accessory extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_product_accessory';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  integer
	 */
	public $product_id;

	/**
	 * @var  integer
	 */
	public $accessory_product_id;

	/**
	 * @var  string
	 */
	public $description;

	/**
	 * @var  integer
	 */
	public $collection_id;

	/**
	 * @var  integer
	 */
	public $hide_on_collection;

	/**
	 * @var  decimal
	 */
	public $price;

	/**
	 * @var  string
	 */
	public $selection;

	/**
	 * @var  integer
	 */
	public $state;

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'erp' => array(
			'ws.product_accessory'
		),
		'pim' => array(
			'erp.pim.product_accessory'
		)
	);

	/**
	 * WS sync mapping for other fields of the model table with other model pks
	 *
	 * @var  array
	 */
	protected $wsSyncMapFields = array(
		'product_id' => array(
			'model' => 'Products'
		),
		'accessory_product_id' => array(
			'model' => 'Products'
		),
		'collection_id' => array(
			'model' => 'Collections'
		)
	);

	/**
	 * WS sync map of fields from string to boolean or viceversa
	 *
	 * @var  array
	 */
	protected $wsSyncMapBoolean = array('state', 'hide_on_collection');

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
		if (!parent::check())
		{
			return false;
		}

		// Make sure an product could not become an product accessory of itself
		if ($this->product_id == $this->accessory_product_id)
		{
			$this->setError(Text::_('COM_REDSHOPB_PRODUCT_ACCESSORY_ERROR_PRODUCT_CAN_NOT_BECOME_ACCESSORY_ITSELF'));

			return false;
		}

		// Make sure the selection text is correct.
		$enumSelection = array('require', 'proposed', 'optional');

		if (empty($this->selection) || !in_array($this->selection, $enumSelection))
		{
			$this->setError(Text::_('COM_REDSHOPB_PRODUCT_ACCESSORY_ERROR_SELECTION_INCORRECT'));

			return false;
		}

		return true;
	}
}
