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
 * Product Complimentary table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableProduct_Complimentary extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_product_complimentary';

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
	public $complimentary_product_id;

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
		'pim' => array(
			'erp.pim.product_complimentary'
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
		'complimentary_product_id' => array(
			'model' => 'Products'
		)
	);

	/**
	 * WS sync map of fields from string to boolean
	 *
	 * @var  array
	 */
	protected $wsSyncMapBoolean = array('state');

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

		// Make sure an product could not become an product complimentary of itself
		if ($this->product_id == $this->complimentary_product_id)
		{
			$this->setError(Text::_('COM_REDSHOPB_PRODUCT_COMPLIMENTARY_ERROR_PRODUCT_CAN_NOT_BECOME_COMPLIMENTARY_ITSELF'));

			return false;
		}

		return true;
	}
}
