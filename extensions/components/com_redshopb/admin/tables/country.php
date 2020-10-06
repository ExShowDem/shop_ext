<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Type table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableCountry extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_country';

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
	public $alpha2;

	/**
	 * @var  string
	 */
	public $alpha3;

	/**
	 * @var  integer
	 */
	public $numeric;

	/**
	 * @var int|null
	 */
	public $company_id;

	/**
	 * Method to store a node in the database table.
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = true)
	{
		if (strcmp(Text::_('COM_REDSHOPB_COUNTRY_' . strtoupper($this->alpha3)), 'COM_REDSHOPB_COUNTRY_' . strtoupper($this->alpha3)) !== 0)
		{
			if (empty($this->name) || strpos($this->name, 'COM_REDSHOPB_COUNTRY_') === false)
			{
				$this->name = 'COM_REDSHOPB_COUNTRY_' . strtoupper($this->alpha3);
			}
		}

		return parent::store($updateNulls);
	}

	/**
	 * Called after check().
	 *
	 * @return  boolean  True if all checks pass.
	 */
	public function afterCheck()
	{
		if (empty($this->company_id))
		{
			$this->company_id = null;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('id')
			->from($db->qn($this->getTableName()))
			->where('alpha2 LIKE ' . $db->q($this->alpha2))
			->where('company_id' . (is_null($this->company_id) ? ' IS NULL' : ' = ' . $db->q($this->company_id)))
			->where('id != ' . (int) $this->id);

		if ($db->setQuery($query, 0, 1)->loadResult())
		{
			$this->setError(Text::sprintf('COM_REDSHOPB_COUNTRY_APLHA_ALREADY_TAKEN', 2, $this->alpha2));

			return false;
		}

		$query->clear('where')
			->where('alpha3 LIKE ' . $db->q($this->alpha3))
			->where('company_id' . (is_null($this->company_id) ? ' IS NULL' : ' = ' . $db->q($this->company_id)))
			->where('id != ' . (int) $this->id);

		if ($db->setQuery($query, 0, 1)->loadResult())
		{
			$this->setError(Text::sprintf('COM_REDSHOPB_COUNTRY_APLHA_ALREADY_TAKEN', 3, $this->alpha3));

			return false;
		}

		$query->clear('where')
			->where('name LIKE ' . $db->q($this->name))
			->where('company_id' . (is_null($this->company_id) ? ' IS NULL' : ' = ' . $db->q($this->company_id)))
			->where('id != ' . (int) $this->id);

		if ($db->setQuery($query, 0, 1)->loadResult())
		{
			$this->setError(Text::sprintf('COM_REDSHOPB_COUNTRY_NAME_ALREADY_TAKEN', $this->name));

			return false;
		}

		$query->clear('where')
			->where($db->qn('numeric') . ' = ' . $db->q($this->numeric))
			->where('company_id' . (is_null($this->company_id) ? ' IS NULL' : ' = ' . $db->q($this->company_id)))
			->where('id != ' . (int) $this->id);

		if ($db->setQuery($query, 0, 1)->loadResult())
		{
			$this->setError(Text::sprintf('COM_REDSHOPB_COUNTRY_NUMERIC_ALREADY_TAKEN', $this->numeric));

			return false;
		}

		return parent::afterCheck();
	}

	/**
	 * Method to load a row from the database by primary key and bind the fields
	 * to the Table instance properties.
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
	 *                           set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found.
	 */
	public function load($keys = null, $reset = true)
	{
		if (parent::load($keys, $reset))
		{
			$this->name = Text::_($this->name);

			return true;
		}

		return false;
	}
}
