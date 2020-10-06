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
 * Currency table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableCurrency extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_currency';

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
	public $alpha3;

	/**
	 * @var  integer
	 */
	public $numeric;

	/**
	 * @var  string
	 */
	public $symbol;

	/**
	 * @var  integer
	 */
	public $symbol_position;

	/**
	 * @var  integer
	 */
	public $decimals;

	/**
	 * @var  integer
	 */
	public $state;

	/**
	 * @var  integer
	 */
	public $blank_space;

	/**
	 * @var  string
	 */
	public $decimal_separator;

	/**
	 * @var  string
	 */
	public $thousands_separator;

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
	 * Method to perform sanity checks on the Table instance properties to ensure
	 * they are safe to store in the database.  Child classes should override this
	 * method to make sure the data they are storing in the database is safe and
	 * as expected before storage.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 */
	public function check()
	{
		// Take the first 3 chars
		$this->numeric = substr($this->numeric, 0, 3);
		$this->alpha3  = substr($this->alpha3, 0, 3);

		// Check a currency is not already existing with the same name
		$currency = clone $this;

		if ($currency->load(array('name' => $this->name))
			&& $currency->id != $this->id)
		{
			$this->setError(Text::_('COM_REDSHOPB_CURRENCY_NAME_ALREADY_EXISTING'));

			return false;
		}

		// Check a currency is not already existing with the same numeric value
		if ($currency->load(array('numeric' => $this->numeric))
			&& $currency->id != $this->id)
		{
			$this->setError(Text::_('COM_REDSHOPB_CURRENCY_NUMERIC_ALREADY_EXISTING'));

			return false;
		}

		// Check a currency is not already existing with the same alpha3 value
		if ($currency->load(array('alpha3' => $this->alpha3))
			&& $currency->id != $this->id)
		{
			$this->setError(Text::_('COM_REDSHOPB_CURRENCY_ALPHA3_ALREADY_EXISTING'));

			return false;
		}

		$this->name = trim($this->name);

		if (empty($this->name))
		{
			$this->setError(Text::_('COM_REDSHOPB_NAME_CANNOT_BE_EMPTY'));

			return false;
		}

		return true;
	}
}
