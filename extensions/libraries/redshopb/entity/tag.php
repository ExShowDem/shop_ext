<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Tag Entity.
 *
 * @since  2.0
 */
class RedshopbEntityTag extends RedshopbEntity
{
	/**
	 * Child Tags
	 *
	 * @var    array
	 * @since  1.7
	 */
	protected $children;

	/**
	 * Get the child tags
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
	 * Get the children tags ids
	 *
	 * @return  array
	 *
	 * @since   1.7
	 */
	public function getChildrenIds()
	{
		$children = $this->getChildren();

		$ids = array();

		foreach ($children as $tag)
		{
			$ids[] = $tag->id;
		}

		return $ids;
	}

	/**
	 * Load child tags from DB
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

		$model = RedshopbModel::getFrontInstance('tags');

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
