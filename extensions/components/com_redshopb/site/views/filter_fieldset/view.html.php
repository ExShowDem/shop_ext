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
 * Filter Fieldset View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       2.0
 */
class RedshopbViewFilter_Fieldset extends RedshopbView
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
	 * @var array
	 */
	protected $unselectedFields;

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

		if (!is_array($this->item->fields))
		{
			$this->item->fields = array();
		}

		$this->unselectedFields = $this->get('UnselectedFields');

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
		$title = Text::_('COM_REDSHOPB_FILTER_FIELDSET_FORM_TITLE');
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

		if (RedshopbHelperACL::getPermission('manage', 'filter_fieldset', Array('edit', 'edit.own'), true))
		{
			$save         = RToolbarBuilder::createSaveButton('filter_fieldset.apply');
			$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('filter_fieldset.save');

			$group->addButton($save)
				->addButton($saveAndClose);

			if (RedshopbHelperACL::getPermission('manage', 'filter_fieldset', Array('create'), true))
			{
				$saveAndNew = RToolbarBuilder::createSaveAndNewButton('filter_fieldset.save2new');

				$group->addButton($saveAndNew);
			}
		}

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('filter_fieldset.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('filter_fieldset.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
