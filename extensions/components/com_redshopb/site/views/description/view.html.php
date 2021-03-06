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
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;

/**
 * Product Description View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewDescription extends RedshopbView
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
	 * @var  string
	 */
	protected $syncReference;

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		/** @var RedshopbModelProduct $model */
		$model = $this->getModel('Description');

		$this->form	= $model->getForm();
		$this->item	= $model->getItem();

		$this->isNew = ((int) $this->item->id <= 0);

		$input           = Factory::getApplication()->input;
		$this->productId = $input->getInt('product_id', null);

		if ($this->isNew && $this->productId)
		{
			$this->form->setValue('product_id', null, $this->productId);
		}

		// Gets sync reference
		$this->syncReference = RedshopbHelperSync::getEnrichmentBase($model);

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
		$title = Text::_('COM_REDSHOPB_PRODUCT_DESCRIPTION_FORM_TITLE');
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

		if (RedshopbHelperACL::getPermission('manage', 'product', array('edit', 'edit.own'), true))
		{
			if ($this->get('IsLockedByWebservice'))
			{
				$locked = RedshopbToolbarBuilder::createAlertButton('JTOOLBAR_APPLY', 'COM_REDSHOPB_BUTTON_ALERT_LOCKED_BY_WEBSERVICE', 'icon-lock');
				$firstGroup->addButton($locked);
			}
			else
			{
				$save         = RToolbarBuilder::createSaveButton('description.apply');
				$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('description.save');

				$firstGroup->addButton($save)
					->addButton($saveAndClose);

				if (RedshopbHelperACL::getPermission('manage', 'product', array('create'), true))
				{
					$saveAndNew = RToolbarBuilder::createSaveAndNewButton('description.save2new');

					$firstGroup->addButton($saveAndNew);
				}
			}
		}

		// Cancel
		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('description.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('description.cancel');
		}

		$firstGroup->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup);

		return $toolbar;
	}
}
