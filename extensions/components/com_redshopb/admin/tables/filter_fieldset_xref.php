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
 * Field Data table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableFilter_Fieldset_Xref extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_filter_fieldset_xref';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  integer
	 */
	public $fieldset_id;

	/**
	 * @var  integer
	 */
	public $field_id;
}
