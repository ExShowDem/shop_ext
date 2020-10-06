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
use Joomla\Registry\Registry;
/**
 * Field Data table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableField_Data extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_field_data';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  integer
	 */
	public $field_id;

	/**
	 * @var  integer
	 */
	public $item_id;

	/**
	 * @var  integer
	 */
	public $subitem_id;

	/**
	 * @var  integer
	 */
	public $state = 1;

	/**
	 * @var  string
	 */
	public $params;

	/**
	 * @var  string
	 */
	public $string_value = null;

	/**
	 * @var  integer
	 */
	public $int_value = null;

	/**
	 * @var  float
	 */
	public $float_value = null;

	/**
	 * @var  string
	 */
	public $text_value = null;

	/**
	 * @var  integer
	 */
	public $field_value = null;

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'pim' => array(
			'erp.pim.field_data'
		),
		'erp' => array(
			'ws.field_data'
		),
		'b2b' => array(
			'erp.webservice.field_data'
		)
	);

	/**
	 * WS sync mapping for other fields of the model table with other model pks
	 *
	 * @var  array
	 */
	protected $wsSyncMapFields = array(
		'field_id' => array(
			'model' => 'Fields'
		),
		'product_id' => array(
			'model' => 'Products',
			'alias' => 'p.id'
		),
		'field_value_id' => array(
			'model' => 'Field_Values',
			'alias' => 'fv.id'
		),
	);

	/**
	 * WS sync map of fields from string to boolean or viceversa
	 *
	 * @var  array
	 */
	protected $wsSyncMapBoolean = array('state');

	/**
	 * Called before store().
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	protected function beforeStore($updateNulls = false)
	{
		$result = parent::beforeStore($updateNulls);

		// We will check if we already have that same exact row and pull ID for it so we don't duplicate them
		if ($this->id)
		{
			return $result;
		}

		$keys = array(
			'field_id' => $this->field_id,
			'item_id' => $this->item_id,
			'subitem_id' => $this->subitem_id,
			'field_value' => $this->field_value,
			'string_value' => $this->string_value,
			'int_value' => $this->int_value,
			'float_value' => $this->float_value,
			'text_value' => $this->text_value);

		$table = clone $this;

		if (!$table->load($keys) || !$table || empty($table->id))
		{
			return $result;
		}

		if (!$this->params || $this->params == '{}' || $this->params == $table->params)
		{
			$this->id = $table->id;
		}

		return $result;
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
		$isUnsetIntValue = (!is_numeric($this->int_value) && !is_null($this->int_value));

		if ($isUnsetIntValue)
		{
			$this->int_value = null;
		}

		$isUnsetFloatValue = (!is_numeric($this->float_value) && !is_null($this->float_value));

		if ($isUnsetFloatValue)
		{
			$this->float_value = null;
		}

		if (!parent::store($updateNulls))
		{
			return false;
		}

		// We check to see if we have uploaded files
		if (!isset($this->_tempFile) || !is_file($this->_tempFile['tmp_name']))
		{
			return true;
		}

		if (!RedshopbHelperThumbnail::checkFileError($this->_tempFile['name'], $this->_tempFile['error']))
		{
			return false;
		}

		$field    = RedshopbEntityField::load($this->field_id);
		$type     = $field->getType();
		$params   = new Registry($this->params);
		$oldMedia = $params->get('internal_url');

		$section = RInflector::pluralize($field->scope);
		$media   = $type->alias;

		$internalUrl = RedshopbHelperMedia::savingMedia(
			$this->_tempFile['tmp_name'],
			$this->_tempFile['name'],
			$this->id,
			false,
			$section,
			$media
		);

		if (!$internalUrl)
		{
			return false;
		}

		if (!is_object($this->params))
		{
			$this->params = new Registry($this->params);
		}

		$this->params->set('internal_url', $internalUrl);
		$this->params = $this->params->toString();

		// Clean up temp data
		JFile::delete($this->_tempFile['tmp_name']);

		// Clean up old media
		if (!empty($oldMedia) && $oldMedia != $internalUrl)
		{
			RedshopbHelperMedia::deleteMedia($oldMedia, 1, $section, $media);
		}

		return parent::store(false);
	}

	/**
	 * Method to bind an associative array or object to the Table instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the Table instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws  InvalidArgumentException
	 */
	public function bind($src, $ignore = array())
	{
		if (!$this->webserviceBind($src, $ignore))
		{
			return false;
		}

		$return = parent::bind($src, $ignore);

		if (isset($src['file']))
		{
			$this->_tempFile = $src['file'];
		}

		return $return;
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
		// Initialise variables.
		$k = $this->_tbl_key;

		if (!is_array($pk))
		{
			$pk = explode(',', $pk);
		}

		// Sanitize input.
		$pk = ArrayHelper::toInteger($pk);

		$pk = (is_null($pk)) ? (array) $this->$k : $pk;

		// If no primary key is given, return false.
		if ($pk === null)
		{
			return false;
		}

		$deleteIds = array();

		// We are deleting it one by one since we need to delete media files
		foreach ($pk as $item)
		{
			if (!$this->load($item))
			{
				continue;
			}

			$deleteIds[] = $item;

			if (empty($this->params))
			{
				continue;
			}

			$params    = new Registry($this->params);
			$mediaName = $params->get('internal_url', '');

			// Delete file if exists
			if ($mediaName != '')
			{
				$field = RedshopbHelperField::getFieldById($this->field_id);
				$type  = RedshopbHelperField::getTypeById($field->type_id);
				RedshopbHelperMedia::deleteMedia($mediaName, 1, RInflector::pluralize($field->scope), $type->alias);
			}
		}

		if (!empty($deleteIds) && !parent::delete($deleteIds))
		{
			return false;
		}

		return true;
	}
}
