<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Represents a menu node (link).
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Menu
 * @since       1.0
 */
final class RedshopbHelperMenu extends RMenuNode
{
	/**
	 * The level content.
	 *
	 * @var integer
	 */
	protected $level;

	/**
	 * The type data.
	 *
	 * @var integer
	 */
	protected $typeData;

	/**
	 * Constructor.
	 *
	 * @param   string  $name      The link name.
	 * @param   string  $content   The link content.
	 * @param   string  $target    The link target.
	 * @param   string  $level     The link level.
	 * @param   string  $typeData  The type data.
	 */
	public function __construct($name, $content, $target, $level, $typeData)
	{
		$this->name     = $name;
		$this->content  = $content;
		$this->target   = $target;
		$this->level    = $level;
		$this->typeData = $typeData;
	}

	/**
	 * Get the node level.
	 *
	 * @return  string  The node name.
	 */
	public function getTypeData()
	{
		return $this->typeData;
	}

	/**
	 * Get the node level.
	 *
	 * @return  string  The node name.
	 */
	public function getLevel()
	{
		return $this->level;
	}

	/**
	 * Test if this node has children
	 *
	 * @return  boolean  True if there are children
	 */
	public function hasChildren()
	{
		return (bool) count($this->children);
	}
}
