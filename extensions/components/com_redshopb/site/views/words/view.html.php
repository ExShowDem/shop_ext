<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Pagination\Pagination;
/**
 * Words View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewWords extends RedshopbView
{
	/**
	 * @var  array
	 */
	public $items;

	/**
	 * @var  object
	 */
	public $state;

	/**
	 * @var  Pagination
	 */
	public $pagination;

	/**
	 * @var  Form
	 */
	public $filterForm;

	/**
	 * @var array
	 */
	public $activeFilters;

	/**
	 * @var array
	 */
	public $stoolsOptions = array();

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$model = $this->getModel('words');
		$model->setState('filter.scope', null);
		$this->state                        = $model->getState();
		$this->items                        = $model->getItems();
		$this->pagination                   = $model->getPagination();
		$this->filterForm                   = $model->getForm();
		$this->activeFilters                = $model->getActiveFilters();
		$this->stoolsOptions['searchField'] = 'search_words';

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return Text::_('COM_REDSHOPB_WORD_LIST_TITLE');
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$firstGroup  = new RToolbarButtonGroup;
		$secondGroup = new RToolbarButtonGroup;

		// Add / edit
		if (RedshopbHelperACL::getPermission('manage', 'word', array('create'), true))
		{
			$new = RToolbarBuilder::createNewButton('word.add');
			$firstGroup->addButton($new);
		}

		if (RedshopbHelperACL::getPermission('manage', 'word', array('edit'), true))
		{
			$edit = RToolbarBuilder::createEditButton('word.edit');
			$firstGroup->addButton($edit);
		}

		if (RedshopbHelperACL::getPermission('manage', 'word', array('edit.state'), true))
		{
			$share   = new RToolbarButtonStandard('COM_REDSHOPB_WORD_SHARE', 'words.share', '', 'icon-plus');
			$unShare = new RToolbarButtonStandard('COM_REDSHOPB_WORD_UNSHARE', 'words.unshare', '', 'icon-minus');

			$firstGroup->addButton($share)
				->addButton($unShare);
		}

		// Delete / Trash
		if (RedshopbHelperACL::getPermission('manage', 'word', array('delete'), true))
		{
			$delete = RToolbarBuilder::createDeleteButton('words.delete');
			$secondGroup->addButton($delete);
		}

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup)
			->addGroup($secondGroup);

		return $toolbar;
	}
}
