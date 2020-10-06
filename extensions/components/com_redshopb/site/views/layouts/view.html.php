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
 * Orders View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewLayouts extends RedshopbView
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
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$model = $this->getModel('Layouts');

		$this->items      = $model->getItems();
		$this->state      = $model->getState();
		$this->pagination = $model->getPagination();
		$this->filterForm = $model->getForm();
		$this->form       = $this->get('Form');

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return Text::_('COM_REDSHOPB_LAYOUTS_TITLE');
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
		if (RedshopbHelperACL::getPermission('manage', 'layout', Array('create'), true))
		{
			$new = RToolbarBuilder::createNewButton('layout.add');
			$firstGroup->addButton($new);
		}

		if (RedshopbHelperACL::getPermission('manage', 'layout', Array('edit'), true))
		{
			$edit = RToolbarBuilder::createEditButton('layout.edit');
			$firstGroup->addButton($edit);
			$default = RToolbarBuilder::createStandardButton('layouts.setDefault', 'COM_REDSHOPB_SET_DEFAULT', '', 'icon-star');
			$firstGroup->addButton($default);
		}

		// Delete / Trash
		if (RedshopbHelperACL::getPermission('manage', 'layout', Array('delete'), true))
		{
			$delete = RToolbarBuilder::createStandardButton('layouts.delete', 'JTOOLBAR_DELETE', 'btn-danger', 'icon-trash');
			$secondGroup->addButton($delete);
		}

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup)
			->addGroup($secondGroup);

		return $toolbar;
	}
}
