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
 * Product Attribute Value View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewProduct_Attribute_Value extends RedshopbView
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
	 * @var boolean
	 */
	protected $isNew;

	/**
	 * @var integer
	 */
	protected $attributeId;

	/**
	 * @var object
	 */
	protected $attribute;

	/**
	 * Conversion Sets flag
	 *
	 * @var  boolean
	 */
	protected $isConversionSet = false;

	/**
	 * Conversion Set list
	 *
	 * @var  array
	 */
	protected $conversionSets = array();

	/**
	 * Conversion data list
	 *
	 * @var  array
	 */
	protected $conversions = array();

	/**
	 * @var integer
	 */
	protected $productId;

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->isNew = ((int) $this->item->id <= 0);

		$input             = Factory::getApplication()->input;
		$this->productId   = $input->get('product_id', 0, 'int');
		$this->attributeId = $input->getInt('attribute_id', 0);

		if (!empty($this->item->id))
		{
			$this->attributeId = $this->item->product_attribute_id;
		}

		if (!empty($this->attributeId) && $this->isNew)
		{
			$this->form->setValue('attribute_id', null, $this->attributeId);
		}

		$this->attribute = RedshopbEntityProduct_Attribute::getInstance($this->attributeId);

		$this->isConversionSet = $this->attribute->isConversionSet();

		if ($this->isConversionSet)
		{
			$this->conversionSets = RedshopbHelperConversion::getProductAttributeConversionSets($this->attributeId);
			$this->conversions    = RedshopbHelperConversion::getProductAttributeValueConversionData($this->item->id);
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
		$title = Text::_('COM_REDSHOPB_PRODUCT_ATTRIBUTE_VALUE_FORM_TITLE');
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
				$save         = RToolbarBuilder::createSaveButton('product_attribute_value.apply');
				$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('product_attribute_value.save');
				$saveAndNew   = RToolbarBuilder::createSaveAndNewButton('product_attribute_value.save2new');

				$group->addButton($save)
					->addButton($saveAndClose)
					->addButton($saveAndNew);
			}
		}

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('product_attribute_value.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('product_attribute_value.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
