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
 * Tags View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewTags extends RedshopbView
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
		$model = $this->getModel('tags');

		$this->items                        = $model->getItems();
		$this->state                        = $model->getState();
		$this->pagination                   = $model->getPagination();
		$this->filterForm                   = $model->getForm();
		$this->activeFilters                = $model->getActiveFilters();
		$this->stoolsOptions['searchField'] = 'search_tags';

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return Text::_('COM_REDSHOPB_TAG_LIST_TITLE');
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
		if (RedshopbHelperACL::getPermission('manage', 'tag', array('create'), true))
		{
			$new = RToolbarBuilder::createNewButton('tag.add');
			$firstGroup->addButton($new);
		}

		if (RedshopbHelperACL::getPermission('manage', 'tag', array('edit'), true))
		{
			$edit = RToolbarBuilder::createEditButton('tag.edit');
			$firstGroup->addButton($edit);
		}

		// Publish / Unpublish
		if (RedshopbHelperACL::getPermission('manage', 'tag', array('edit.state'), true))
		{
			$publish   = RToolbarBuilder::createPublishButton('tags.publish');
			$unpublish = RToolbarBuilder::createUnpublishButton('tags.unpublish');

			$firstGroup->addButton($publish)
				->addButton($unpublish);
		}

		// Delete / Trash
		if (RedshopbHelperACL::getPermission('manage', 'tag', array('delete'), true))
		{
			$delete = RToolbarBuilder::createModalButton(
				'#tagsModal',
				'JTOOLBAR_DELETE',
				'btn-danger',
				'icon-trash'
			);
			$secondGroup->addButton($delete);
		}

		// Rebuild
		if (RedshopbHelperUser::isRoot())
		{
			$rebuild = RToolbarBuilder::createStandardButton(
				'tags.rebuild', Text::_('COM_REDSHOPB_REBUILD'), 'btn-warning', 'icon-retweet', false
			);
			$secondGroup->addButton($rebuild);
		}

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup)
			->addGroup($secondGroup);

		return $toolbar;
	}
}
