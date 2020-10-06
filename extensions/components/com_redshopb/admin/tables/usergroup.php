<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\Usergroup;

jimport('joomla.database.usergroup');

/**
 * Usergroup table class.
 *
 * @since  1.9.7
 */
class RedshopbTableUsergroup extends Usergroup
{
	/**
	 * The options.
	 *
	 * @var  array
	 */
	protected $_options = array();

	/**
	 * Set a table option value.
	 *
	 * @param   string  $key  The key
	 * @param   mixed   $val  The default value
	 *
	 * @return  Table
	 */
	public function setOption($key, $val)
	{
		$this->_options[$key] = $val;

		return $this;
	}

	/**
	 * Get a table option value.
	 *
	 * @param   string  $key      The key
	 * @param   mixed   $default  The default value
	 *
	 * @return  mixed  The value or the default value
	 */
	public function getOption($key, $default = null)
	{
		if (isset($this->_options[$key]))
		{
			return $this->_options[$key];
		}

		return $default;
	}

	/**
	 * Method to recursively rebuild the nested set tree.
	 *
	 * @param   integer  $parentId          The root of the tree to rebuild.
	 * @param   integer  $left              The left id to start with in building the tree.
	 * @param   integer  $maxExecutionTime  Maximum execution time
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.9.7
	 */
	public function rebuild($parentId = 0, $left = 0, $maxExecutionTime = 0)
	{
		if ($this->getOption('disableReorder', false)
			|| ($maxExecutionTime && $maxExecutionTime <= microtime(1)))
		{
			return false;
		}

		// Get the database object
		$db             = $this->_db;
		static $groups  = null;
		static $parents = array();

		if ($groups === null || !$parentId)
		{
			$query   = $db->getQuery(true)
				->select('id, lft, rgt, parent_id')
				->from($db->qn($this->_tbl))
				->order('title');
			$groups  = $db->setQuery($query)
				->loadObjectList('id');
			$parents = array();

			if (!empty($groups))
			{
				foreach ($groups as $key => $group)
				{
					if (!array_key_exists($group->parent_id, $parents))
					{
						$parents[$group->parent_id] = array();
					}

					$parents[$group->parent_id][] = $key;
					unset($groups[$key]->id, $groups[$key]->parent_id);
				}
			}
		}

		// The right value of this node is the left value + 1
		$right = $left + 1;
		$this->setOption('rebuildIteration', $this->getOption('rebuildIteration', 0) + 1);

		// Get all children of this node
		if (array_key_exists($parentId, $parents))
		{
			// Execute this function recursively over all children
			$len = count($parents[$parentId]);

			for ($i = 0; $i < $len; $i++)
			{
				// $right is the current right value, which is incremented on recursion return
				$right = $this->rebuild($parents[$parentId][$i], $right, $maxExecutionTime);

				// If there is an update failure, return false to break out of the recursion
				if ($right === false)
				{
					return false;
				}
			}
		}

		if (array_key_exists((int) $parentId, $groups))
		{
			$parent = &$groups[(int) $parentId];

			if ($parent->lft != (int) $left || $parent->rgt != (int) $right)
			{
				$this->setOption('notMatchRebuildIteration', $this->getOption('notMatchRebuildIteration', 0) + 1);
				$parent->lft = (int) $left;
				$parent->rgt = (int) $right;
				$query       = $db->getQuery(true)
					->update($db->qn($this->_tbl))
					->set('lft = ' . (int) $left)
					->set('rgt = ' . (int) $right)
					->where('id = ' . (int) $parentId);

				// If there is an update failure, return false to break out of the recursion
				try
				{
					// We've got the left value, and now that we've processed
					// the children of this node we also know the right value
					$db->setQuery($query)->execute();
				}
				catch (JDatabaseExceptionExecuting $e)
				{
					return false;
				}
			}
		}

		// Return the right value of this node + 1
		return $right + 1;
	}
}
