<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Database.Filter
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;

/**
 * Array database filterer
 *
 * @since  1.0
 */
class RedshopbDatabaseFilterArray extends RedshopbDatabaseFilterBase implements RedshopbDatabaseFilterInterface
{
	/**
	 * Default filter
	 *
	 * @var  string
	 */
	protected $type = 'integer';

	/**
	 * Filtered data
	 *
	 * @var  array
	 */
	protected $filteredData = array();

	/**
	 * Sanitise an array of integers
	 *
	 * @return  array
	 */
	public function filterInteger()
	{
		if (empty($this->data) || !is_array($this->data))
		{
			return $this->filteredData;
		}

		$this->filteredData = ArrayHelper::toInteger($this->data);

		return $this->filteredData;
	}

	/**
	 * Sanitise an string of strings
	 *
	 * @return  array
	 */
	public function filterString()
	{
		if (empty($this->data) || !is_array($this->data))
		{
			return $this->filteredData;
		}

		$db = Factory::getDbo();

		$this->filteredData = array_map(array($db, 'quote'), $this->data);

		return $this->filteredData;
	}
}
