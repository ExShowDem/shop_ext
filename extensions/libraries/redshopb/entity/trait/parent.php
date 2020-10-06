<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity.Trait
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Trait for entities with a parent
 *
 * @since  2.0
 */
trait RedshopbEntityTraitParent
{
	/**
	 * User of the item
	 *
	 * @var  RedshopbEntity
	 */
	protected $parent;

	/**
	 * Get the parent item
	 *
	 * @return  RedshopbEntity
	 */
	public function getParent()
	{
		if (null === $this->parent)
		{
			$this->loadParent();
		}

		return $this->parent;
	}

	/**
	 * Get item parent id
	 *
	 * @return  integer  0 on fail. Parent identifier otherwise
	 */
	public function getParentId()
	{
		$item = $this->getItem();

		if (!$item || !$item->parent_id)
		{
			return 0;
		}

		return $item->parent_id;
	}

	/**
	 * Load owner from DB
	 *
	 * @return  self
	 */
	protected function loadParent()
	{
		$this->parent = static::getInstance();

		$parentId = $this->getParentId();

		if (!$parentId)
		{
			return $this;
		}

		$this->parent = static::load($parentId);

		return $this;
	}

	/**
	 * Method to get category parents (ordered in tree order)
	 *
	 * @param   RedshopbEntityTraitParent  $root  Root element.
	 * @param   array                      $tree  Tree structure.
	 *
	 * @return  array  Array of parent categories.
	 *
	 * @since   1.12.60
	 */
	public function getParents($root = null, &$tree = array())
	{
		if ($root == null)
		{
			$root = $this;
		}

		$parent = $root->getParent();

		if (!$parent->getId() !== $root->getId() && $parent->get('level', 0) != 0)
		{
			$tree[] = $parent;
			$this->getParents($parent, $tree);
		}

		return $tree;
	}
}
