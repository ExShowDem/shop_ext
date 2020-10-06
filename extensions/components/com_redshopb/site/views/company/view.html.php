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
 * Company View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewCompany extends RedshopbView
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
	 * @var  array
	 */
	protected $accessSet;

	/**
	 * @var  boolean
	 */
	protected $isNew;

	/**
	 * @var  boolean
	 */
	protected $showSalesPersons = false;

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
		$model             = $this->getModel('Company');
		$this->form        = $this->get('Form');
		$this->item        = $this->get('Item');
		$this->anyRequired = $model->areThereAnyRequiredFields();

		$this->isNew = (int) $this->item->id <= 0;

		if (RedshopbHelperACL::getPermission('manage', 'mainwarehouse'))
		{
			$this->showSalesPersons = true;
		}

		$this->setFieldAttributes();

		parent::display($tpl);
	}

	/**
	 * Sets field attributes
	 *
	 * @return  void
	 */
	protected function setFieldAttributes()
	{
		if ($this->form)
		{
			if (!$this->isNew || RedshopbApp::getConfig()->get('set_webservices', 0))
			{
				$this->form->setFieldAttribute('customer_number', 'required', 'false');
			}
		}

		$user          = Factory::getUser();
		$userCompanyId = RedshopbHelperUser::getUserCompanyId($user->id, 'joomla');

		if ($this->isNew || RedshopbHelperACL::getPermission('manage', 'mainwarehouse') || $userCompanyId != $this->item->id)
		{
			return;
		}

		$this->form->setFieldAttribute('currency_id', 'readonly', 'true');
		$this->form->setFieldAttribute('currency_id', 'required', 'false');
		$this->form->setFieldAttribute('show_stock_as', 'readonly', 'true');
		$this->form->setFieldAttribute('show_stock_as', 'required', 'false');
		$this->form->setFieldAttribute('freight_amount_limit', 'readonly', 'true');
		$this->form->setFieldAttribute('freight_amount_limit', 'required', 'false');
		$this->form->setFieldAttribute('freight_amount', 'readonly', 'true');
		$this->form->setFieldAttribute('freight_amount', 'required', 'false');
		$this->form->setFieldAttribute('product_id', 'readonly', 'true');
		$this->form->setFieldAttribute('product_id', 'required', 'false');

		$this->form->setValue('price_group_ids', null, RedshopbEntityCompany::getInstance($this->item->id)->getPriceGroups()->ids());
		$this->form->setFieldAttribute('price_group_ids', 'readonly', 'true');
		$this->form->setFieldAttribute('price_group_ids', 'required', 'false');
		$this->form->setValue('customer_discount_ids', null, RedshopbHelperCompany::getDiscountGroupIds($this->item->id));
		$this->form->setFieldAttribute('customer_discount_ids', 'readonly', 'true');
		$this->form->setFieldAttribute('customer_discount_ids', 'required', 'false');

		// User does not have permission to edit this data
		$this->form->setFieldAttribute('calculate_fee', 'readonly', 'true');

		// Set stockroom_verification field to disabled.
		$this->form->setFieldAttribute('stockroom_verification', 'readonly', 'true');
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		$isNew = (int) $this->item->id <= 0;
		$title = Text::_('COM_REDSHOPB_COMPANY_FORM_TITLE');
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

		if (($this->item->asset_id && RedshopbHelperACL::getPermission(
			'manage',
			'company',
			Array('edit', 'edit.own'),
			false,
			$this->item->asset_id
		))
			|| (!$this->item->asset_id && RedshopbHelperACL::getPermission('manage', 'company', Array('create'), true)))
		{
			if ($this->get('IsLockedByWebservice'))
			{
				$locked          = RedshopbToolbarBuilder::createAlertButton(
					'JTOOLBAR_APPLY', 'COM_REDSHOPB_BUTTON_ALERT_LOCKED_BY_WEBSERVICE', 'icon-lock'
				);
				$saveContactInfo = RToolbarBuilder::createStandardButton(
					'company.saveContactInfo',
					'COM_REDSHOPB_BUTTON_SAVE_CONTACT_INFO',
					'btn-success',
					'icon-save',
					false
				);
				$group->addButton($locked)
					->addButton($saveContactInfo);
			}
			else
			{
				$save         = RToolbarBuilder::createSaveButton('company.apply');
				$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('company.save');

				$group->addButton($save)
					->addButton($saveAndClose);

				if (RedshopbHelperACL::getPermission('manage', 'company', Array('create'), true))
				{
					$saveAndNew = RToolbarBuilder::createSaveAndNewButton('company.save2new');

					$group->addButton($saveAndNew);
				}
			}

			if ($this->item->asset_id)
			{
				$savePermissions = RToolbarBuilder::createStandardButton(
					'company.savePermissions',
					'COM_REDSHOPB_BUTTON_SAVE_PERMISSIONS',
					'',
					'icon-save',
					false
				);
				$group->addButton($savePermissions);
			}
		}

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('company.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('company.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
