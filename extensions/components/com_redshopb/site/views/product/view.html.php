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
 * Product View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewProduct extends RedshopbView
{
	/**
	 * @var  Form
	 */
	protected $form;

	/**
	 * @var object
	 */
	protected $state;

	/**
	 * @var  object
	 */
	protected $item;

	/**
	 * @var object
	 */
	protected $images;

	/**
	 * @var mixed
	 */
	protected $isLockedByWebservice;

	/**
	 * @var  boolean
	 */
	protected $anyRequired = false;

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
		$model = $this->getModel('Product');

		$this->form	       = $model->getForm();
		$this->item	       = $model->getItem();
		$this->anyRequired = $model->areThereAnyRequiredFields();
		$this->state       = $model->getState();

		$product = RedshopbEntityProduct::getInstance($this->item->id)->bind($this->item);

		$this->images = $product->getImages();

		$this->isLockedByWebservice = $this->get('IsLockedByWebservice');
		$model->checkInImageSync();

		// Load language string for JS
		Text::script('COM_REDSHOPB_STOCKROOM_UNLIMITED');

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
		$title = Text::_('COM_REDSHOPB_PRODUCT_FORM_TITLE');
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
		$firstGroup  = new RToolbarButtonGroup;
		$secondGroup = new RToolbarButtonGroup;
		$thirdGroup  = new RToolbarButtonGroup;

		if (RedshopbHelperACL::getPermission('manage', 'product', array('edit', 'edit.own'), true))
		{
			if ($this->get('IsLockedByWebservice'))
			{
				$locked = RedshopbToolbarBuilder::createAlertButton('JTOOLBAR_APPLY', 'COM_REDSHOPB_BUTTON_ALERT_LOCKED_BY_WEBSERVICE', 'icon-lock');
				$firstGroup->addButton($locked);
			}
			else
			{
				$save         = RToolbarBuilder::createSaveButton('product.apply', 'hide');
				$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('product.save', 'hide');

				$firstGroup->addButton($save)
					->addButton($saveAndClose);

				if (RedshopbHelperACL::getPermission('manage', 'product', array('create'), true))
				{
					$saveAndNew = RToolbarBuilder::createSaveAndNewButton('product.save2new', 'hide');

					$firstGroup->addButton($saveAndNew);
				}

				// Discontinue if not already discontinued
				if (!$this->item->discontinued)
				{
					// Discontinue
					$discontinued = RToolbarBuilder::createModalButton(
						'#productDiscontinue',
						'JTOOLBAR_DISCONTINUE',
						'btn-warning',
						'icon-warning-sign'
					);
					$secondGroup->addButton($discontinued);
				}
			}
		}

		// Cancel
		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('product.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('product.cancel');
		}

		$firstGroup->addButton($cancel);

		if ($this->item->id && !$this->get('IsLockedByWebservice'))
		{
			// Generate items
			$generateItems = RToolbarBuilder::createStandardButton(
				'product.generate',
				Text::_('COM_REDSHOPB_PRODUCT_GENERATE_ITEMS'),
				'btn btn-default',
				'icon-spinner',
				false
			);

			$thirdGroup->addButton($generateItems);
		}

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup)
			->addGroup($secondGroup)
			->addGroup($thirdGroup);

		return $toolbar;
	}
}
