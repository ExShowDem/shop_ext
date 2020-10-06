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
 * Manufacturer Entity
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Entity
 * @since       1.0
 */
final class RedshopbEntityManufacturer extends RedshopbEntity
{
	/**
	 * Child Manufacturers
	 *
	 * @var    array
	 * @since  1.7
	 */
	protected $children;

	/**
	 * Get the child manufacturers
	 *
	 * @return  array
	 *
	 * @since   1.7
	 */
	public function getChildren()
	{
		if (null === $this->children)
		{
			$this->loadChildren();
		}

		return $this->children;
	}

	/**
	 * Get the children manufacturers ids
	 *
	 * @return  array
	 *
	 * @since   1.7
	 */
	public function getChildrenIds()
	{
		$children = $this->getChildren();

		$ids = array();

		foreach ($children as $manufacturer)
		{
			$ids[] = $manufacturer->id;
		}

		return $ids;
	}

	/**
	 * Load child manufacturers from DB
	 *
	 * @return  array
	 *
	 * @since   1.7
	 */
	protected function loadChildren()
	{
		$this->children = array();

		if (!$this->hasId())
		{
			return $this;
		}

		$model = RedshopbModel::getFrontInstance('manufacturers');

		$state = array(
			'filter.parent_id' => $this->id
		);

		$children = $model->search($state);

		foreach ($children as $child)
		{
			$this->children[$child->id] = static::getInstance($child->id)->bind($child);
		}

		return $this;
	}
}
