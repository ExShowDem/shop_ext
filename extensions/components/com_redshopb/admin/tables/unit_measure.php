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
 * Unit measure table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableUnit_Measure extends RedshopbTable
{
	/**
	 * The table name without the prefix
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_unit_measure';

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
	public $alias;

	/**
	 * @var  string
	 */
	public $description;

	/**
	 * @var  integer
	 */
	public $decimal_position;

	/**
	 * @var  string
	 */
	public $decimal_separator;

	/**
	 * @var  string
	 */
	public $thousand_separator;

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'b2b' => array(
			'erp.webservice.units_measure'
		),
		'pim' => array(
			'erp.pim.stockUnits'
		)
	);

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
		if (!$this->beforeCheck())
		{
			return false;
		}

		if ($this->decimal_position < 0)
		{
			$this->decimal_position = 0;
		}

		$item = clone $this;

		if ($item->load(array('alias' => $this->alias)) && $item->id != $this->id)
		{
			$this->setError(Text::sprintf('COM_REDSHOPB_UNIT_MEASURE_ALIAS_ALREADY_TAKEN', $this->alias));

			return false;
		}

		if (!$this->afterCheck())
		{
			return false;
		}

		return true;
	}

	/**
	 * Deletes this row in database (or if provided, the row of key $pk)
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @throws Exception
	 *
	 * @return  boolean  True on success.
	 */
	public function delete($pk = null)
	{
		try
		{
			if (!parent::delete($pk))
			{
				throw new Exception;
			}
		}
		catch (Exception $exception)
		{
			$this->setError(Text::_('COM_REDSHOPB_UNIT_MEASURE_ERROR_DELETE'));

			return false;
		}

		return true;
	}
}
