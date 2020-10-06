<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Database.Filter
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Base database filterer
 *
 * @since  1.0
 */
abstract class RedshopbDatabaseFilterBase
{
	/**
	 * Type of filter
	 *
	 * @var  string
	 */
	protected $type;

	/**
	 * Data to filter
	 *
	 * @var  mixed
	 */
	protected $data;

	/**
	 * Filtered data
	 *
	 * @var  mixed
	 */
	protected $filteredData;

	/**
	 * Constructor
	 *
	 * @param   mixed   $data  Data to filter
	 * @param   string  $type  Filter to apply
	 */
	public function __construct($data, $type = null)
	{
		$this->data = $data;

		if (null !== $type)
		{
			$this->type = $type;
		}
	}

	/**
	 * Get the method to use to filter the data
	 *
	 * @return  mixed
	 */
	protected function getFilterMethod()
	{
		if (null === $this->type)
		{
			return null;
		}

		$method = 'filter' . ucfirst(strtolower($this->type));

		if (!method_exists($this, $method))
		{
			return null;
		}

		return $method;
	}

	/**
	 * Base filter method
	 *
	 * @return  mixed
	 */
	public function filter()
	{
		if (null === $this->data)
		{
			return null;
		}

		$method = $this->getFilterMethod();

		$this->filteredData = $method ? $this->$method() : $this->data;

		return $this->filteredData;
	}
}
