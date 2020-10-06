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
class RedshopbTableState extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_state';

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
	 * @var  integer|null
	 */
	public $country_id;

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
			->where('country_id = ' . $db->q($this->country_id))
			->where('company_id' . (is_null($this->company_id) ? ' IS NULL' : ' = ' . $db->q($this->company_id)))
			->where('id != ' . (int) $this->id);

		if ($db->setQuery($query, 0, 1)->loadResult())
		{
			$this->setError(Text::sprintf('COM_REDSHOPB_STATE_APLHA_ALREADY_TAKEN', 2, $this->alpha2));

			return false;
		}

		$query->clear('where')
			->where('alpha3 LIKE ' . $db->q($this->alpha3))
			->where('country_id = ' . $db->q($this->country_id))
			->where('company_id' . (is_null($this->company_id) ? ' IS NULL' : ' = ' . $db->q($this->company_id)))
			->where('id != ' . (int) $this->id);

		if ($db->setQuery($query, 0, 1)->loadResult())
		{
			$this->setError(Text::sprintf('COM_REDSHOPB_STATE_APLHA_ALREADY_TAKEN', 3, $this->alpha3));

			return false;
		}

		return parent::afterCheck();
	}
}
