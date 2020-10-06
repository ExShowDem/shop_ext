<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Sync View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewSync extends RedshopbViewAdmin
{
	/**
	 * Do we have to display a sidebar ?
	 *
	 * @var  boolean
	 */
	protected $displaySidebar = false;

	/**
	 * @var  array
	 */
	public $items;

	/**
	 * @var  object
	 */
	public $state;

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		/** @var RedshopbModelSync $model */
		$model = $this->getModel('Sync');

		$this->items = $model->getItems();
		$this->state = $model->getState();

		// Preprocess the list of items to find ordering divisions.
		foreach ($this->items as &$item)
		{
			$this->ordering[$item->parent_id][] = $item->id;
		}

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return Text::_('COM_REDSHOPB_SYNC_TITLE');
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$app        = Factory::getApplication();
		$editSync   = $app->getUserState('list.change_sync', 0);
		$firstGroup = new RToolbarButtonGroup;
		$toolbar    = new RToolbar;

		if ($editSync)
		{
			$secondGroup = new RToolbarButtonGroup;
			$new         = RToolbarBuilder::createNewButton('syncedit.add');
			$firstGroup->addButton($new);

			$edit = RToolbarBuilder::createEditButton('syncedit.edit');
			$firstGroup->addButton($edit);

			$delete = RToolbarBuilder::createStandardButton('sync.delete', 'JTOOLBAR_DELETE', 'btn-danger', 'icon-trash');
			$secondGroup->addButton($delete);

			$rightGroup = new RToolbarButtonGroup('pull-right');
			$switcher   = RToolbarBuilder::createStandardButton(
				'sync.syncWebservices', 'COM_REDSHOPB_SYNC_SWITCH_TO_SYNCHRONIZE', '', 'icon-refresh', false
			);
			$rightGroup->addButton($switcher);

			$toolbar->addGroup($firstGroup)
				->addGroup($secondGroup)
				->addGroup($rightGroup);
		}
		else
		{
			$rightGroup = new RToolbarButtonGroup('pull-right');

			if (RedshopbHelperACL::getPermission('manage', 'sync', array('sync'), true))
			{
				$syncSelected = RToolbarBuilder::createStandardButton('sync.selected', 'COM_REDSHOPB_SYNC_SELECTED_ITEMS', 'btn-success', 'icon-ok');
				$firstGroup->addButton($syncSelected);

				$clearHashedKeys = RToolbarBuilder::createStandardButton(
					'sync.clearHashedKeys', 'COM_REDSHOPB_SYNC_CLEAR_HASHED_KEYS', 'btn-danger', 'icon-trash'
				);
				$firstGroup->addButton($clearHashedKeys);

				$clearAllHashedKeys = RToolbarBuilder::createStandardButton(
					'sync.clearAllHashedKeys', 'COM_REDSHOPB_SYNC_CLEAR_ALL_HASHED_KEYS', 'btn-danger', 'icon-trash'
				);
				$rightGroup->addButton($clearAllHashedKeys);
			}

			$switcher = RToolbarBuilder::createStandardButton(
				'sync.editSync', 'COM_REDSHOPB_SYNC_SWITCH_TO_EDIT', '', 'icon-refresh', false
			);
			$rightGroup->addButton($switcher);
			$toolbar->addGroup($firstGroup)
				->addGroup($rightGroup);
		}

		return $toolbar;
	}
}
