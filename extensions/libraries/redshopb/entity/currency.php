<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Currency Entity
 *
 * @since  2.0
 */
class RedshopbEntityCurrency extends RedshopbEntity
{
	/**
	 * Relationships between alpha3 code & ids to cache instances loaded by alpha3
	 *
	 * @var  array
	 */
	protected static $alpha3IdXref = array();

	/**
	 * Override to ensure that we cache alpha3 - id relationships
	 *
	 * @param   integer  $id  Identifier of the active item
	 *
	 * @return  $this
	 */
	public static function load($id = null)
	{
		$instance = parent::load($id);
		$alpha3   = (string) $instance->get('alpha3');
		$alpha3   = trim(strtolower($alpha3));

		if ($alpha3 && !isset(static::$alpha3IdXref[$alpha3]))
		{
			static::$alpha3IdXref[$alpha3] = $instance->id;
		}

		return $instance;
	}

	/**
	 * Load a currency by its alpha3 code
	 *
	 * @param   string  $alpha3  Alpha3 ISO code
	 *
	 * @return  self
	 */
	public static function loadByAlpha3($alpha3)
	{
		$alpha3 = (string) trim(strtolower($alpha3));

		$instance = static::getInstance();

		if (!$alpha3)
		{
			return $instance;
		}

		// Already cached
		if (isset(static::$alpha3IdXref[$alpha3]))
		{
			return static::load(static::$alpha3IdXref[$alpha3]);
		}

		$table = RTable::getAdminInstance('currency', array(), 'com_redshopb');

		if ($table->load(array('alpha3' => $alpha3)))
		{
			$instance->loadFromTable($table);

			static::$alpha3IdXref[$alpha3] = $instance->id;
		}

		return $instance;
	}
}
