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
 * Tax_Group table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableTax_Group extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_tax_group';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  string
	 */
	public $name;

	/**
	 * @var  integer
	 */
	public $state;

	/**
	 * @var  integer|null
	 */
	public $company_id;

	/**
	 * @var array
	 */
	protected $taxes;

	/**
	 * The options.
	 *
	 * @var  array
	 */
	protected $_options = array(
		'tax_relate.store' => true
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
		$this->taxes = null;

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

		if (!empty($this->taxes))
		{
			// Sanitize taxes id
			$this->taxes = array_values(array_unique($this->taxes, SORT_STRING));
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

		if ($this->getOption('tax_relate.store')
			&& !$this->storeTaxXref())
		{
			return false;
		}

		return true;
	}

	/**
	 * Store the tax x references
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function storeTaxXref()
	{
		if (!isset($this->taxes))
		{
			return true;
		}

		// Delete all items
		$db    = $this->_db;
		$query = $db->getQuery(true)
			->delete($db->qn('#__redshopb_tax_group_xref'))
			->where($db->qn('tax_group_id') . ' = ' . (int) $this->id);

		if (!$db->setQuery($query)->execute())
		{
			$this->setError($db->getErrorMsg());

			return false;
		}

		if (!is_array($this->taxes) || count($this->taxes) <= 0)
		{
			return true;
		}

		$query->clear()
			->insert($db->qn('#__redshopb_tax_group_xref'))
			->columns('tax_group_id, tax_id');

		// Store the new items
		foreach ($this->taxes as $taxId)
		{
			$query->values((int) $this->id . ',' . (int) $taxId);
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
		if (!$this->loadTaxXref())
		{
			return false;
		}

		return parent::afterLoad($keys, $reset);
	}

	/**
	 * Load the taxes related to this tax group
	 *
	 * @return  boolean  True on success, false otherwise
	 */
	private function loadTaxXref()
	{
		$db    = $this->_db;
		$query = $db->getQuery(true)
			->select('tax_id')
			->from($db->qn('#__redshopb_tax_group_xref'))
			->where($db->qn('tax_group_id') . ' = ' . (int) $this->id);

		$taxIds = $db->setQuery($query)
			->loadColumn();

		if (!is_array($taxIds))
		{
			$this->taxes = array();
		}
		else
		{
			$this->taxes = $taxIds;
		}

		return true;
	}
}
