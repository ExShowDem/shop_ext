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
 * Media table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableMedia extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_media';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  string
	 */
	public $name;

	/**
	 * @var  string
	 */
	public $remote_path;

	/**
	 * @var  string
	 */
	public $alt;

	/**
	 * @var  string
	 */
	public $view;

	/**
	 * @var  integer
	 */
	public $product_id;

	/**
	 * @var  integer
	 */
	public $attribute_value_id = null;

	/**
	 * @var  integer
	 */
	public $state = 1;

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
	 * @var  integer
	 */
	public $ordering = 0;

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'pim' => array(
			'erp.pim.media'
		),
		'erp' => array(
			'ws.product_image'
		),
		'b2b' => array(
			'erp.webservice.product_images'
		),
		'fengel' => array(
			'fengel.media'
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
		'attribute_value_id' => array(
			'model' => 'Product_Attribute_Values'
		)
	);

	/**
	 * WS sync map of fields from string to boolean or viceversa
	 *
	 * @var  array
	 */
	protected $wsSyncMapBoolean = array('state');

	/**
	 * Deletes this row in database (or if provided, the row of key $pk)
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 */
	public function delete($pk = null)
	{
		// Initialise variables.
		$k  = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// If no primary key is given, return false.
		if ($pk === null)
		{
			return false;
		}

		$deleteIds = array();

		foreach ((array) $pk as $item)
		{
			if ($this->load($item))
			{
				$mediaName   = $this->name;
				$deleteIds[] = $item;

				// Delete old if exists
				if ($mediaName != '')
				{
					RedshopbHelperThumbnail::deleteImage($mediaName, 1, 'products', $this->remote_path);
				}
			}
		}

		if (!empty($deleteIds))
		{
			if (!parent::delete($deleteIds))
			{
				return false;
			}
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
		if ($this->ordering == 0 && empty($this->id) && $this->product_id > 0)
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);

			$query->select('MAX(' . $db->qn('ordering') . ')')
				->from($db->qn('#__redshopb_media'))
				->where($db->qn('product_id') . ' = ' . (int) $this->product_id);

			$ordering = $db->setQuery($query)->loadResult();

			if (!is_null($ordering))
			{
				$this->ordering = $ordering + 1;
			}
		}

		// Store with $updateNulls = true
		if (!parent::store($updateNulls))
		{
			return false;
		}

		return true;
	}
}
