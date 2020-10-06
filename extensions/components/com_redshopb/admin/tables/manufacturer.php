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
 * Manufacturer table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.6.51
 */
class RedshopbTableManufacturer extends RedshopbTableNested
{
	/**
	 * The table name without the prefix
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_manufacturer';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var  integer
	 */
	public $parent_id;

	/**
	 * @var  string
	 */
	public $alias;

	/**
	 * @var string
	 */
	public $description;

	/**
	 * @var string
	 */
	public $category;

	/**
	 * @var  string
	 */
	public $path = '';

	/**
	 * @var  integer
	 */
	public $state = 1;

	/**
	 * @var  string
	 */
	public $image;

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
	 * Layout params in JSON format.
	 *
	 * @var  integer
	 */
	public $params;

	/**
	 * Sync cascade children deletion
	 *
	 * @var  array
	 */
	protected $syncCascadeChildren = array(
		'Manufacturer' => 'parent_id'
	);

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'pim' => array (
			'erp.pim.brands'
		),
		'erp' => array (
			'ws.manufacturer'
		),
		'b2b' => array(
			'erp.webservice.manufacturers'
		)
	);

	/**
	 * WS sync mapping for other fields of the model table with other model pks
	 *
	 * @var  array
	 */
	protected $wsSyncMapFields = array(
		'parent_id' => array(
			'model' => 'Manufacturers'
		)
	);

	/**
	 * WS sync map of fields from string to boolean or viceversa
	 *
	 * @var  array
	 */
	protected $wsSyncMapBoolean = array('featured', 'state');

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table.  The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.
	 *                            If not set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return  boolean  True on success; false if $pks is empty.
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		// Update children categories state follow this state
		if (!is_array($pks))
		{
			$pks = array($pks);
		}

		$pks = ArrayHelper::toInteger($pks);

		foreach ($pks as $manufacturerId)
		{
			$pks = array_merge($pks, RedshopbEntityManufacturer::load($manufacturerId)->getChildrenIds());
		}

		$pks = array_unique($pks);

		return parent::publish($pks, $state, $userId);
	}
}
