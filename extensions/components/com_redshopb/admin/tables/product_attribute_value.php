<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
/**
 * Product_Attribute_Value table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableProduct_Attribute_Value extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_product_attribute_value';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  integer
	 */
	public $product_attribute_id;

	/**
	 * @var string
	 */
	public $sku;

	/**
	 * @var integer
	 */
	public $state = 1;

	/**
	 * @var boolean
	 */
	public $selected;

	/**
	 * The displayed valued.
	 * The real value is string_value, float_value or int_value.
	 *
	 * @var  string
	 */
	protected $value;

	/**
	 * @var  string
	 */
	public $string_value;

	/**
	 * @var  float
	 */
	public $float_value;

	/**
	 * @var  integer
	 */
	public $int_value;

	/**
	 * @var  integer
	 */
	public $ordering = 0;

	/**
	 * @var  string
	 */
	public $text_value;

	/**
	 * @var  string
	 */
	public $image;

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'erp' => array(
			'ws.product_attribute_value'
		),
		'fengel' => array(
			'fengel.attribute'
		)
	);

	/**
	 * Called before store().
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	protected function beforeStore($updateNulls = false)
	{
		$table = RedshopbTable::getAdminInstance('Product_Attribute');
		$table->load(array('id' => $this->product_attribute_id));

		if ($this->getOption('forceWebserviceUpdate', false) == false
			&& $this->isFromWebservice('#__redshopb_product', null, $table->product_id))
		{
			$this->setError(Text::_('COM_REDSHOPB_ERROR_ITEM_RELATED_TO_WEBSERVICE'));
			Log::add(Text::sprintf('COM_REDSHOPB_LOGS_ERROR_ITEM_RELATED_TO_WEBSERVICE', $this->getKeysString(), $this->_tbl), Log::ERROR, 'CRUD');

			return false;
		}

		// If the selected enabled for this value, it disables any other selected found in the same attribute
		if ($this->selected)
		{
			$attributeValue = clone $this;

			$src = array(
				'product_attribute_id' => $this->product_attribute_id,
				'selected' => $this->selected
			);

			if ($attributeValue->load($src)
				&& $attributeValue->id != $this->id)
			{
				$attributeValue->save(array('selected' => false));
			}
		}

		return parent::beforeStore($updateNulls);
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
		// Sanitize variables
		$this->selected = (int) $this->selected;

		// Make sure there is no duplicate value for the same attribute
		$attributeValue = clone $this;

		// Check there is no other attribute value with the same SKU
		if ($attributeValue->load(array('product_attribute_id' => $this->product_attribute_id, 'sku' => $this->sku))
			&& $attributeValue->id != $this->id)
		{
			$this->setError(Text::sprintf('COM_REDSHOPB_ATTRIBUTE_VALUE_SKU_ALREADY_TAKEN', $this->sku));

			return false;
		}

		// Set ordering
		if ($this->state < 0)
		{
			// Set ordering to 0 if state is archived or trashed
			$this->ordering = 0;
		}
		elseif (empty($this->ordering) && empty($this->id))
		{
			// Set ordering to last if ordering was 0
			$this->ordering = self::getNextOrder(
				$this->_db->qn('product_attribute_id')
				. '='
				. $this->_db->q($this->product_attribute_id)
				. ' AND '
				. $this->_db->qn('state')
				. ' >= 0'
			);
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
		// Set the value depending on the attribute type
		$typeId             = RedshopbHelperProduct_Attribute::getType($this->product_attribute_id);
		$valueType          = RedshopbEntityType::getInstance($typeId)->get('value_type', 'string_value');
		$valueType          = ($valueType == 'field_value') ? 'string_value' : $valueType;
		$this->{$valueType} = $this->value;

		return parent::store($updateNulls);
	}

	/**
	 * Called after load().
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
	 *                           set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found.
	 */
	protected function afterLoad($keys = null, $reset = true)
	{
		// Set the value depending on the attribute type
		$typeId      = RedshopbHelperProduct_Attribute::getType($this->product_attribute_id);
		$this->value = RedshopbEntityType::getFieldValue($typeId, $this);

		return parent::afterLoad($keys, $reset);
	}

	/**
	 * Deletes this row in database (or if provided, the row of key $pk)
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 */
	public function delete($pk = null)
	{
		// Delete the orphaned items
		if ($this->doDeleteOrphanProductItems($pk))
		{
			return parent::delete($pk);
		}

		return false;
	}

	/**
	 * Delete the orphan product items having the value id
	 *
	 * @param   mixed  $pk  The product value id that were delete
	 *
	 * @return  boolean  true on success, false otherwise.
	 */
	private function doDeleteOrphanProductItems($pk)
	{
		if (!is_array($pk))
		{
			$pk = array($pk);
		}

		$pk = ArrayHelper::toInteger($pk);

		$query = 'DELETE i.* FROM #__redshopb_product_item AS i
		INNER JOIN #__redshopb_product_item_attribute_value_xref AS map ON map.product_item_id = i.id'
			. ' WHERE map.product_attribute_value_id IN (' . implode(',', $pk) . ')';

		$db = $this->_db;

		$db->setQuery($query);

		return $db->execute();
	}
}
