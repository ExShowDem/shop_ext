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
class RedshopbTableCategory_Field_Xref extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_category_field_xref';

	/**
	 * Name of the primary key fields in the table.
	 *
	 * @var    array
	 * @since  12.2
	 */
	protected $_tbl_keys = array('category_id', 'field_id');

	/**
	 * @var  integer
	 */
	public $category_id;

	/**
	 * @var  integer
	 */
	public $field_id;

	/**
	 * WS sync mapping for other fields of the model table with other model pks
	 *
	 * @var  array
	 */
	protected $wsSyncMapFields = array(
		'field_id' => array(
			'model' => 'Fields'
		),
		'category_id' => array(
			'model' => 'Categories'
		)
	);

	/**
	 * Checks that the object is valid and able to be stored.
	 *
	 * @return  boolean  True if all checks pass.
	 */
	public function check()
	{
		$field = RedshopbHelperField::getFieldById($this->field_id);

		// Ensure that the field is not in the global scope
		if ($field->global)
		{
			$this->setError(Text::_('COM_REDSHOPB_CATEGORY_FIELD_IN_GLOBAL_SCOPE'));

			return false;
		}

		return true;
	}
}
