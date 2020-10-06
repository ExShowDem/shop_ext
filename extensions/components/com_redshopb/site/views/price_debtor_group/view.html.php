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
use Joomla\CMS\Form\FormHelper;
/**
 * Price Debtor Group View
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewPrice_Debtor_Group extends RedshopbView
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
	protected $paymentsEnabled = false;

	/**
	 * @var  RModel
	 */
	protected $paymentConfigurationsModel = null;

	/**
	 * @var  RPagination
	 */
	protected $paymentConfigurationsPagination = null;

	/**
	 * @var  RModel
	 */
	protected $shippingConfigurationsModel = null;

	/**
	 * @var  RPagination
	 */
	protected $shippingConfigurationsPagination = null;

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

		if (RBootstrap::getConfig('enable_payment', 0) == 1)
		{
			$this->paymentsEnabled = true;
		}

		// Make available the fields
		FormHelper::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_redcore/models/fields');

		if ($this->paymentsEnabled && $this->item->id)
		{
			// Load redCORE backend language file for payment plugins
			$lang = Factory::getLanguage();
			$lang->load('com_redcore', JPATH_ADMINISTRATOR)
				|| $lang->load('com_redcore', JPATH_ADMINISTRATOR, RTranslationHelper::getSiteLanguage());

			$this->paymentConfigurationsModel = RedshopbModel::getFrontInstance('Payment_Configurations');
			$this->paymentConfigurationsModel->set('filterFormName', 'filter_price_debtor_group_payment_configurations');
			$this->paymentConfigurationsModel->getState();
			$this->paymentConfigurationsModel->setState('filter.extension_name', 'com_redshopb');
			$this->paymentConfigurationsModel->setState('filter.owner_name', $this->item->id);

			$this->paymentConfigurationsPagination = $this->paymentConfigurationsModel->getPagination();
			$this->paymentConfigurationsPagination->set('formName', 'paymentConfigurationsForm');
		}

		if ($this->item->id)
		{
			// Get shipping configuration data
			$this->shippingConfigurationsModel = RedshopbModel::getFrontInstance('Shipping_Configurations');
			$this->shippingConfigurationsModel->getState();
			$this->shippingConfigurationsModel->set('filterFormName', 'filter_price_debtor_group_shipping_configurations');
			$this->shippingConfigurationsModel->setState('filter.shipping_extension_name', 'com_redshopb');
			$this->shippingConfigurationsModel->setState('filter.owner_name', $this->item->id);

			$this->shippingConfigurationsPagination = $this->shippingConfigurationsModel->getPagination();
			$this->shippingConfigurationsPagination->set('formName', 'shippingConfigurationsForm');
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
		$title = Text::_('COM_REDSHOPB_DEBTOR_PRICE_GROUP');
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
				$save         = RToolbarBuilder::createSaveButton('price_debtor_group.apply');
				$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('price_debtor_group.save');

				$group->addButton($save)
					->addButton($saveAndClose);

				if (RedshopbHelperACL::getPermission('manage', 'product', array('create'), true))
				{
					$saveAndNew = RToolbarBuilder::createSaveAndNewButton('price_debtor_group.save2new');

					$group->addButton($saveAndNew);
				}
			}
		}

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('price_debtor_group.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('price_debtor_group.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
