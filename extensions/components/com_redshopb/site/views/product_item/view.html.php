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
 * Product Item View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewProduct_Item extends RedshopbView
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
	 * @var mixed
	 */
	protected $sku;

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->item	= $this->get('Item');
		$this->form	= $this->get('Form');

		/** @var RedshopbModelProduct_Item model */
		$model = $this->getModel();

		$this->sku = $model->getSKU($this->item->id);

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
		$title = Text::_('COM_REDSHOPB_PRODUCT_ITEM_FORM_TITLE');
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
		$user        = Factory::getUser();
		$firstGroup  = new RToolbarButtonGroup;
		$secondGroup = new RToolbarButtonGroup;

		if (RedshopbHelperACL::getPermission('manage', 'product', Array('edit', 'edit.own'), true))
		{
			if ($this->get('IsLockedByWebservice'))
			{
				$locked = RedshopbToolbarBuilder::createAlertButton('JTOOLBAR_APPLY', 'COM_REDSHOPB_BUTTON_ALERT_LOCKED_BY_WEBSERVICE', 'icon-lock');
				$firstGroup->addButton($locked);
			}
			else
			{
				$save         = RToolbarBuilder::createSaveButton('product_item.apply');
				$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('product_item.save');

				$firstGroup->addButton($save)
					->addButton($saveAndClose);
			}
		}

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('product_item.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('product_item.cancel');
		}

		// Discontinue if not already discontinued
		if (!empty($this->item->id) && !$this->item->discontinued && !$this->get('IsLockedByWebservice'))
		{
			$discontinued = RToolbarBuilder::createStandardButton('product_items.discontinue', 'JTOOLBAR_DISCONTINUE',
				'btn-warning', 'icon-warning-sign', false
			);

			$secondGroup->addButton($discontinued);
		}

		$firstGroup->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup)
			->addGroup($secondGroup);

		return $toolbar;
	}
}
