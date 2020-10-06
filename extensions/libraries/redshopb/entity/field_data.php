<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
/**
 * Entity for field data
 *
 * @since  2.0
 */
class RedshopbEntityField_Data extends RedshopbEntity
{
	/**
	 * Media internal URL
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $media_internal_url;

	/**
	 * Media external URL
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $media_external_url;

	/**
	 * Media description
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $media_description;

	/**
	 * Field
	 *
	 * @var    RedshopbEntityField
	 * @since  2.4.0
	 */
	protected $field;

	/**
	 * Gets a media data field
	 *
	 * @param   string  $paramName  Name of the parameter: internal_url, external_url, description
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function getMediaData($paramName)
	{
		$mediaParamName = 'media_' . $paramName;

		if (!property_exists($this, $mediaParamName))
		{
			return null;
		}

		if (null === $this->$mediaParamName)
		{
			$this->loadMediaData();
		}

		return $this->$mediaParamName;
	}

	/**
	 * Gets the field
	 *
	 * @return  RedshopbEntityField
	 */
	public function getField()
	{
		if ($this->field == null)
		{
			$this->loadField();
		}

		return $this->field;
	}

	/**
	 * Loads the media data
	 *
	 * @return  self
	 *
	 * @since   2.0
	 */
	protected function loadMediaData()
	{
		if (!$this->hasId())
		{
			return $this;
		}

		$params = new Registry($this->getItem()->params);

		foreach ($params as $paramName => $paramValue)
		{
			$mediaParamName = 'media_' . $paramName;

			if (property_exists($this, $mediaParamName))
			{
				$this->$mediaParamName = $paramValue;
			}
		}

		return $this;
	}

	/**
	 * Loads the field
	 *
	 * @return self
	 */
	protected function loadField()
	{
		if (!$this->hasId())
		{
			return $this;
		}

		$this->field = RedshopbEntityField::load($this->item->field_id);

		return $this;
	}
}
