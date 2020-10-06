<?php
/**
 * @package     Aesir.E-Commerce.Backend
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
 * All Price View
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewAll_Price extends RedshopbView
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
	 * @var  boolean
	 */
	protected $isNew;

	/**
	 * @var  integer
	 */
	protected $productId;

	/**
	 * @var integer
	 */
	protected $productItemId;

	/**
	 * @var mixed
	 */
	public $type;

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		/** @var Form form */
		$this->item = $this->get('Item');
		$this->form = $this->get('Form');

		$this->isNew = ($this->item->id <= 0);

		$this->productId     = Factory::getApplication()->input->get('product_id', 0, 'int');
		$this->productItemId = Factory::getApplication()->input->get('product_item_id', 0, 'int');

		$this->type = $this->getType($this->productId, $this->productItemId);

		if (!empty($this->type) && $this->isNew)
		{
			$this->form->setValue('type', null, $this->type);
		}

		if (!empty($this->type))
		{
			$this->form->setFieldAttribute('type', 'readonly', 'readonly');
		}

		if (!empty($this->productId) && $this->isNew)
		{
			$this->form->setValue('type_product_id', null, $this->productId);
		}

		if (!empty($this->productItemId) && $this->isNew)
		{
			$this->form->setValue('type_product_item_id', null, $this->productItemId);
		}

		parent::display($tpl);
	}

	/**
	 * Get Type
	 *
	 * @param   int  $productId      The product identifier
	 * @param   int  $productItemId  The product item identifier
	 *
	 * @return null|string
	 */
	private function getType($productId, $productItemId)
	{
		if (!empty($productItemId))
		{
			return 'product_item';
		}

		if (!empty($productId))
		{
			return 'product';
		}

		return null;
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		$isNew = (int) $this->item->id <= 0;
		$title = Text::_('COM_REDSHOPB_PRODUCT_PRICE_DETAILS');
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

		if (RedshopbHelperACL::getPermission('manage', 'product', array('edit', 'edit.own'), true))
		{
			if ($this->get('IsLockedByWebservice'))
			{
				$locked = RedshopbToolbarBuilder::createAlertButton('JTOOLBAR_APPLY', 'COM_REDSHOPB_BUTTON_ALERT_LOCKED_BY_WEBSERVICE', 'icon-lock');
				$group->addButton($locked);
			}
			else
			{
				$save         = RToolbarBuilder::createSaveButton('all_price.apply');
				$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('all_price.save');

				$group->addButton($save)
					->addButton($saveAndClose);

				if (RedshopbHelperACL::getPermission('manage', 'product', array('create'), true))
				{
					$saveAndNew = RToolbarBuilder::createSaveAndNewButton('all_price.save2new');

					$group->addButton($saveAndNew);
				}
			}
		}

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('all_price.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('all_price.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
