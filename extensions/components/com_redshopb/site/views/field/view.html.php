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
 * Field View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       2.0
 */
class RedshopbViewField extends RedshopbView
{
	/**
	 * @var  Form
	 */
	protected $form;

	/**
	 * @var Form
	 */
	protected $valuesForm;

	/**
	 * @var  object
	 */
	protected $item;

	/**
	 * @var boolean
	 */
	protected $xrefEditing = false;

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');

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
		$title = Text::_('COM_REDSHOPB_FIELD_FORM_TITLE') . ($isNew ? '' : ' : ' . $this->item->name);
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

		if (RedshopbHelperACL::getPermission('manage', 'field', Array('edit', 'edit.own'), true))
		{
			$save         = RToolbarBuilder::createSaveButton('field.apply');
			$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('field.save');

			$group->addButton($save)
				->addButton($saveAndClose);
		}

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('field.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('field.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
