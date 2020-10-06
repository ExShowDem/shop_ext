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

/**
 * Wash care spec table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableWash_Care_Spec extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_wash_care_spec';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  string
	 */
	public $type_code;

	/**
	 * @var  string
	 */
	public $code;

	/**
	 * @var  string
	 */
	public $image = '';

	/**
	 * @var  integer
	 */
	public $description;

	/**
	 * @var  integer
	 */
	public $state = 1;

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'fengel' => array(
			'fengel.wash_care_spec'
		)
	);

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

		// Received an array of ids?
		if (is_array($pk))
		{
			// Sanitize input.
			$pk = ArrayHelper::toInteger($pk);
			$pk = RHelperArray::quote($pk);
			$pk = implode(',', $pk);
		}

		$pk = (is_null($pk)) ? $this->$k : $pk;

		// If no primary key is given, return false.
		if ($pk === null)
		{
			return false;
		}

		// Former data
		$this->load($pk);

		$success = parent::delete($pk);

		// Delete old if exists
		if ($success && $this->image != '')
		{
			RedshopbHelperThumbnail::deleteImage($this->image, 1, 'wash_care_spec');
		}

		return $success;
	}
}
