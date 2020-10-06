<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
/**
 * Base Entity.
 *
 * @since  1.7
 */
abstract class RedshopbEntity extends RedshopbEntityBase
{
	/**
	 * Option of the component containing the tables. Example: com_content
	 *
	 * @var    string
	 * @since  1.7
	 */
	protected $component = 'com_redshopb';

	/**
	 * Asset of this for this entity
	 *
	 * @var    Table
	 * @since  1.7
	 */
	protected $asset;

	/**
	 * Converts an array of entities into an array of objects
	 *
	 * @param   array  $entities  Array of RedshopbEntity
	 *
	 * @return  array
	 *
	 * @throws  InvalidArgumentException  If an array of RedshopbEntity is not received
	 *
	 * @since   2.0
	 */
	public function entitiesToObjects(array $entities)
	{
		$results = array();

		if (!$entities)
		{
			return $results;
		}

		foreach ($entities as $key => $entity)
		{
			if (!$entity instanceof RedshopbEntity)
			{
				throw new InvalidArgumentException("RedshopbEntityExpected in " . __FUNCTION__);
			}

			$results[$key] = $entity->getItem();
		}

		return $results;
	}

	/**
	 * Get a property with some kind of cleanup that apparently was required
	 *
	 * @param   string  $propertyName  Name of the property to get sanitised
	 *
	 * @return  string
	 *
	 * @since   1.7
	 */
	public function getWebSafeProperty($propertyName)
	{
		$item = $this->getItem();

		if (!$item || !isset($item->{$propertyName}))
		{
			return null;
		}

		$sourceString = $item->{$propertyName};

		setlocale(LC_ALL, 'en_US.UTF8');

		$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $sourceString);
		$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
		$clean = strtolower(trim($clean, '-'));
		$clean = preg_replace("/[\/_|+ -]+/", '-', $clean);

		return urlencode($clean);
	}

	/**
	 * Get the permissions asset
	 *
	 * @return  Table
	 *
	 * @since   1.7
	 */
	public function getAsset()
	{
		if (null === $this->asset)
		{
			$this->loadAsset();
		}

		return $this->asset;
	}

	/**
	 * Load the asset from assets table
	 *
	 * @return  Table
	 *
	 * @throws  RuntimeException  When table is not found or asset cannot be loaded
	 *
	 * @since   1.7
	 */
	protected function loadAsset()
	{
		$assetName = $this->getAssetName();

		$asset = Table::getInstance('Asset');

		if (!$asset instanceof Table || !$asset->load(Array('name' => $assetName)))
		{
			throw new RuntimeException('Cannot load asset for ' . $assetName);
		}

		$this->asset = $asset;

		return $this;
	}
}
