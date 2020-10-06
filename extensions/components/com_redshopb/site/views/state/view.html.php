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
/**
 * State View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewState extends RedshopbView
{
	/**
	 * @var  Form
	 */
	protected $form;

	/**
	 * @var  object
	 */
	protected $item;

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->form	= $this->get('Form');
		$this->item	= $this->get('Item');

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		$isNew = (int) $this->item->id <= 0;
		$title = Text::_('COM_REDSHOPB_STATE_FORM_TITLE');
		$state = $isNew ? Text::_('JNEW') : Text::_('JEDIT');

		return $title . ' <small>' . $state . '</small>';
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$group = new RToolbarButtonGroup;

		if (RedshopbHelperACL::getPermission('manage', 'product', Array('edit','edit.own'), false))
		{
			$save         = RToolbarBuilder::createSaveButton('state.apply');
			$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('state.save');

			$group->addButton($save)
				->addButton($saveAndClose);

			if (RedshopbHelperACL::getPermission('manage', 'product', Array('create'), false))
			{
				$saveAndNew = RToolbarBuilder::createSaveAndNewButton('state.save2new');
				$group->addButton($saveAndNew);
			}
		}

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('state.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('state.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
