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
 * Media Entity.
 *
 * @since  2.0
 */
class RedshopbEntityMedia extends RedshopbEntity
{
	/**
	 * @const  integer
	 * @since  1.0
	 */
	const VIEW_FRONT = 1;

	/**
	 * @const  integer
	 * @since  1.0
	 */
	const VIEW_BACK = 2;

	/**
	 * Get the name of the view
	 *
	 * @param   integer  $viewId  Name
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function getViewName($viewId = null)
	{
		// If no id is received use current instance view
		if (null === $viewId)
		{
			$item = $this->getItem();

			if (!$item)
			{
				return null;
			}

			$viewId = $item->view;
		}

		switch ((int) $viewId)
		{
			case static::VIEW_FRONT:
				return Text::_('COM_REDSHOPB_MEDIA_IMAGE_VIEW_ANGLE_FRONT');

			case static::VIEW_BACK:
				return Text::_('COM_REDSHOPB_MEDIA_IMAGE_VIEW_ANGLE_BACK');

			default:
				return Text::_('COM_REDSHOPB_MEDIA_IMAGE_VIEW_ANGLE_OTHER');
		}
	}

	/**
	 * This entity needs special leftjoin so we cannot use tables to load it
	 *
	 * @param   string  $key       Field name used as key
	 * @param   string  $keyValue  Value used if it's not the $this->id property of the instance
	 *
	 * @return  self
	 *
	 * @since   2.0
	 */
	public function loadItem($key = 'id', $keyValue = null)
	{
		if (!$this->hasId())
		{
			return $this;
		}

		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select('m.*, pav.string_value AS main_attribute_name')
			->from($db->qn('#__redshopb_media', 'm'))
			->leftJoin($db->qn('#__redshopb_product_attribute_value', 'pav') . ' ON pav.id = m.attribute_value_id')
			->where('m.id = ' . (int) $this->id)
			->order($db->qn('m.ordering') . ' ASC')
			->order('pav.ordering');

		$db->setQuery($query, 0, 1);

		$item = $db->loadObject();

		if (!$item)
		{
			return $this;
		}

		$this->item = $item;
		$this->id   = $item->id;
		$class      = get_called_class();

		// Ensure that we cache the item
		if (!isset(static::$instances[$class][$this->id]))
		{
			static::$instances[$class][$this->id] = $this;
		}

		return $this;
	}
}
