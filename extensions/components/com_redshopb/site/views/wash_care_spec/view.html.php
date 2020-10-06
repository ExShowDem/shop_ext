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
 * Wash and care spec View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewWash_Care_Spec extends RedshopbView
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
		/** @var RedshopbModelWash_Care_Spec $model */
		$model = $this->getModel('Wash_Care_Spec');

		$this->form	= $model->getForm();
		$this->item	= $model->getItem();

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
		$title = Text::_('COM_REDSHOPB_WASH_CARE_SPEC_FORM_TITLE');
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
		$firstGroup = new RToolbarButtonGroup;

		if (RedshopbHelperACL::getPermission('manage', 'mainwarehouse', array('edit', 'edit.own'), true))
		{
			if ($this->get('IsLockedByWebservice'))
			{
				$locked = RedshopbToolbarBuilder::createAlertButton('JTOOLBAR_APPLY', 'COM_REDSHOPB_BUTTON_ALERT_LOCKED_BY_WEBSERVICE', 'icon-lock');
				$firstGroup->addButton($locked);
			}
			else
			{
				$save         = RToolbarBuilder::createSaveButton('wash_care_spec.apply');
				$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('wash_care_spec.save');

				$firstGroup->addButton($save)
					->addButton($saveAndClose);

				if (RedshopbHelperACL::getPermission('manage', 'mainwarehouse', array('create'), true))
				{
					$saveAndNew = RToolbarBuilder::createSaveAndNewButton('wash_care_spec.save2new');

					$firstGroup->addButton($saveAndNew);
				}
			}
		}

		// Cancel
		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('wash_care_spec.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('wash_care_spec.cancel');
		}

		$firstGroup->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup);

		return $toolbar;
	}
}
