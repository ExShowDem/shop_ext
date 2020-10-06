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
 * Product Attribute View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewProduct_Attribute extends RedshopbView
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

		if ($this->item->id && $this->item->conversion_sets)
		{
			$this->item->conversions = RedshopbHelperConversion::getProductAttributeConversionSets($this->item->id);
		}

		$this->productId = Factory::getApplication()->input->get('product_id', 0, 'int');
		$this->isNew     = ($this->item->id <= 0);

		if (!empty($this->productId) && $this->isNew)
		{
			$this->form->setValue('product_id', null, $this->productId);
		}

		if (!empty($this->productId))
		{
			$this->form->setFieldAttribute('product_id', 'disabled', 'disabled');
			$this->form->setFieldAttribute('product_id', 'readonly', 'readonly');
		}

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
		$title = Text::_('COM_REDSHOPB_PRODUCT_ATTRIBUTE_FORM_TITLE');
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
		$user  = Factory::getUser();

		if (RedshopbHelperACL::getPermission('manage', 'product', array('edit', 'edit.own'), true))
		{
			if ($this->get('IsLockedByWebservice'))
			{
				$locked = RedshopbToolbarBuilder::createAlertButton('JTOOLBAR_APPLY', 'COM_REDSHOPB_BUTTON_ALERT_LOCKED_BY_WEBSERVICE', 'icon-lock');
				$group->addButton($locked);
			}
			else
			{
				$save         = RToolbarBuilder::createSaveButton('product_attribute.apply');
				$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('product_attribute.save');
				$saveAndNew   = RToolbarBuilder::createSaveAndNewButton('product_attribute.save2new');

				$group->addButton($save)
					->addButton($saveAndClose)
					->addButton($saveAndNew);
			}
		}

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('product_attribute.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('product_attribute.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
