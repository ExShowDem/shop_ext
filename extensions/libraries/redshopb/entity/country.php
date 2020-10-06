<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
/**
 * Country Entity.
 *
 * @since  2.0
 *
 * @property string $name
 * @property string $alpha2
 */
class RedshopbEntityCountry extends RedshopbEntity
{
	/**
	 * Relationships between name & ids to cache instances loaded by name
	 *
	 * @var  array
	 */
	protected static $nameIdXref = array();

	/**
	 * Override to ensure that we cache name - id relationships
	 *
	 * @param   integer  $id  Identifier of the active item
	 *
	 * @return  $this
	 */
	public static function load($id = null)
	{
		$instance = parent::load($id);
		$name     = (string) $instance->get('name');
		$name     = trim(mb_strtolower($name));

		if ($name && !isset(static::$nameIdXref[$name]))
		{
			static::$nameIdXref[$name] = $instance->id;
		}

		return $instance;
	}

	/**
	 * Try to load a country from its name
	 *
	 * @param   string  $name  Name of the country
	 *
	 * @return  self
	 */
	public static function loadFromName($name)
	{
		$name = trim(mb_strtolower($name));

		$instance = static::getInstance();

		if (!$name)
		{
			return $instance;
		}

		// Already cached
		if (isset(static::$nameIdXref[$name]))
		{
			return static::load(static::$nameIdXref[$name]);
		}

		$db = RedshopbApp::getDbo();

		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__redshopb_country'))
			->where('LOWER(' . ($db->qn('name')) . ') = ' . $db->q($name));
		$db->setQuery($query);

		$country = $db->loadObject();

		if ($country)
		{
			$instance = static::getInstance($country->id)->bind($country);
		}

		static::$nameIdXref[$name] = $country ? $instance->id : 0;

		return $instance;
	}

	/**
	 * Returns list of translated country names.
	 *
	 * @return  array  List of countries
	 *
	 * @since   1.12.72
	 */
	public static function getTranslatedList()
	{
		$db = RedshopbApp::getDbo();

		$query = $db->getQuery(true);
		$query->select(
			array (
				$db->qn('id'),
				'CONCAT(\'COM_REDSHOPB_COUNTRY_\', UPPER(' . $db->qn('alpha3') . ')) as ' . $db->qn('string')
			)
		)
			->from($db->qn('#__redshopb_country'))
			->order($db->qn('id') . ' ASC');

		$strings = $db->setQuery($query)->loadAssocList('id', 'string');
		$result  = array();

		foreach ($strings as $id => $string)
		{
			$result[$id] = Text::_($string);
		}

		return $result;
	}
}
