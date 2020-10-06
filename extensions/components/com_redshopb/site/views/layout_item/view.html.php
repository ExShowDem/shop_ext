<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
/**
 * Layout Item View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.6.36
 */
class RedshopbViewLayout_Item extends RedshopbView
{
	/**
	 * @var  object
	 *
	 * @since   1.13.0
	 */
	protected $item;

	/**
	 * @var  object
	 *
	 * @since   1.13.0
	 */
	protected $state;

	/**
	 * @var  Form
	 *
	 * @since   1.13.0
	 */
	protected $form;

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 *
	 * @since   1.13.0
	 */
	public function display($tpl = null)
	{
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');
		$this->form  = $this->get('Form');

		parent::display($tpl);
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 *
	 * @since   1.13.0
	 */
	public function getToolbar()
	{
		$group = new RToolbarButtonGroup;

		if (RedshopbHelperACL::getPermission('manage', 'layout_item', array('edit', 'edit.own'), true)
			&& $this->item->editable)
		{
			$save         = RToolbarBuilder::createSaveButton('layout_item.apply');
			$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('layout_item.save');
			$group->addButton($save)
				->addButton($saveAndClose);
		}

		if (!$this->item->editable || $this->item->isNew)
		{
			$cancel = RToolbarBuilder::createCancelButton('layout_item.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('layout_item.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
