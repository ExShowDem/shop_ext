<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Table\Category;
use Joomla\CMS\UCM\UCMType;
use Joomla\CMS\Table\Observer\Tags;
/**
 * Collection Product Xref Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelCollection_Product_Xref extends RedshopbModelAdmin
{
	/**
	 * A protected method to get the where clause for the reorder
	 * This ensures that the row will be moved relative to a row with the same extension
	 *
	 * @param   Category  $table  Current table instance
	 *
	 * @return  array           An array of conditions to add to add to ordering queries.
	 *
	 * @since   1.6
	 */
	protected function getReorderConditions($table)
	{
		return 'collection_id = ' . $this->_db->quote($table->collection_id);
	}

	/**
	 * Saves the manually set order of records.
	 *
	 * @param   array    $pks    An array of primary key ids.
	 * @param   integer  $order  +1 or -1
	 * @param   integer  $id     Collection id
	 *
	 * @return  mixed
	 *
	 * @since   12.2
	 */
	public function saveorder($pks = null, $order = null, $id = null)
	{
		$table          = $this->getTable();
		$tableClassName = get_class($table);
		$contentType    = new UCMType;
		$type           = $contentType->getTypeByTable($tableClassName);
		$tagsObserver   = $table->getObserverOfClass(Tags::class);
		$conditions     = array();

		if (empty($pks))
		{
			return Factory::getApplication()->enqueueMessage(Text::_($this->text_prefix . '_ERROR_NO_ITEMS_SELECTED'), 'warning');
		}

		// Update ordering values
		foreach ($pks as $i => $pk)
		{
			$table->load(array('collection_id' => $id, 'product_id' => (int) $pk));

			// Access checks.
			if (!$this->canEditState($table))
			{
				// Prune items that you can't change.
				unset($pks[$i]);
				Log::add(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), Log::WARNING, 'jerror');
			}
			elseif ($table->ordering != $order[$i])
			{
				$table->ordering = $order[$i];

				if ($type)
				{
					$this->createTagsHelper($tagsObserver, $type, $pk, $type->type_alias, $table);
				}

				if (!$table->store())
				{
					$this->setError($table->getError());

					return false;
				}

				// Remember to reorder within position and client_id
				$condition = $this->getReorderConditions($table);
				$found     = false;

				foreach ($conditions as $cond)
				{
					if ($cond[1] == $condition)
					{
						$found = true;
						break;
					}
				}

				if (!$found)
				{
					$key          = $table->getKeyName();
					$conditions[] = array($table->$key, $condition);
				}
			}
		}

		// Execute reorder for each category.
		foreach ($conditions as $cond)
		{
			$table->load($cond[0]);
			$table->reorder($cond[1]);
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}
}
