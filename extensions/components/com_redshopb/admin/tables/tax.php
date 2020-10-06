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
 * Tax table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableTax extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_tax';

	/**
	 * Tax id.
	 *
	 * @var  integer
	 */
	public $id;

	/**
	 * Tax name.
	 *
	 * @var  string
	 */
	public $name;

	/**
	 * Tax rate.
	 *
	 * @var  decimal
	 */
	public $tax_rate;

	/**
	 * @var integer
	 */
	public $country_id;

	/**
	 * @var interger
	 */
	public $state_id;

	/**
	 * @var boolean
	 */
	public $is_eu_country;

	/**
	 * @var integer
	 */
	public $company_id;

	/**
	 * Tax state.
	 *
	 * @var  integer
	 */
	public $state = 1;

	/**
	 * Tax checked out date.
	 *
	 * @var  integer
	 */
	public $checked_out_time = '0000-00-00 00:00:00';

	/**
	 * Tax checked out by user id.
	 *
	 * @var  integer
	 */
	public $checked_out;

	/**
	 * Tax created date.
	 *
	 * @var  integer
	 */
	public $created_date = '0000-00-00 00:00:00';

	/**
	 * Tax created by user id.
	 *
	 * @var  integer
	 */
	public $created_by;

	/**
	 * Tax date modified.
	 *
	 * @var  integer
	 */
	public $modified_date = '0000-00-00 00:00:00';

	/**
	 * Tax modified by user id.
	 *
	 * @var  integer
	 */
	public $modified_by;

	/**
	 * @var array
	 */
	protected $tax_groups;

	/**
	 * The options.
	 *
	 * @var  array
	 */
	protected $_options = array(
		'tax_group_relate.store' => true
	);

	/**
	 * Method to reset class properties to the defaults set in the class
	 * definition. It will ignore the primary key as well as any private class
	 * properties (except $_errors).
	 *
	 * @return  void
	 */
	public function reset()
	{
		$this->tax_groups = null;

		parent::reset();
	}

	/**
	 * Called after check().
	 *
	 * @return  boolean  True if all checks pass.
	 */
	protected function afterCheck()
	{
		if (!$this->company_id)
		{
			$this->company_id = null;
		}

		if (!$this->country_id)
		{
			$this->country_id = null;
		}

		if (!$this->state_id)
		{
			$this->state_id = null;
		}

		if (!empty($this->tax_groups))
		{
			// Sanitize tax groups id
			$this->tax_groups = array_values(array_unique($this->tax_groups, SORT_STRING));
		}

		return parent::afterCheck();
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
		if (!parent::store($updateNulls))
		{
			return false;
		}

		if ($this->getOption('tax_group_relate.store')
			&& !$this->storeTaxGroupXref())
		{
			return false;
		}

		return true;
	}

	/**
	 * Store the tax group x references
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function storeTaxGroupXref()
	{
		if (!isset($this->tax_groups))
		{
			return true;
		}

		// Delete all items
		$db    = $this->_db;
		$query = $db->getQuery(true)
			->delete($db->qn('#__redshopb_tax_group_xref'))
			->where($db->qn('tax_id') . ' = ' . (int) $this->id);

		if (!$db->setQuery($query)->execute())
		{
			$this->setError($db->getErrorMsg());

			return false;
		}

		if (!is_array($this->tax_groups) || count($this->tax_groups) <= 0)
		{
			return true;
		}

		$query->clear()
			->insert($db->qn('#__redshopb_tax_group_xref'))
			->columns('tax_id, tax_group_id');

		// Store the new items
		foreach ($this->tax_groups as $taxGroupId)
		{
			$query->values((int) $this->id . ',' . (int) $taxGroupId);
		}

		if (!$db->setQuery($query)->execute())
		{
			$this->setError($db->getErrorMsg());

			return false;
		}

		return true;
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
		if (!$this->loadTaxGroupXref())
		{
			return false;
		}

		return parent::afterLoad($keys, $reset);
	}

	/**
	 * Load the tax group related to this tax
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function loadTaxGroupXref()
	{
		$db    = $this->_db;
		$query = $db->getQuery(true)
			->select('tax_group_id')
			->from($db->qn('#__redshopb_tax_group_xref'))
			->where($db->qn('tax_id') . ' = ' . (int) $this->id);

		$taxIds = $db->setQuery($query)
			->loadColumn();

		if (!is_array($taxIds))
		{
			$this->tax_groups = array();
		}
		else
		{
			$this->tax_groups = $taxIds;
		}

		return true;
	}
}
