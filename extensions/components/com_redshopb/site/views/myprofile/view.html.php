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
use Joomla\CMS\Input\Input;
use Joomla\CMS\Pagination\Pagination;
/**
 * My Profile View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewMyProfile extends RedshopbView
{
	/**
	 * Do we have to display a sidebar?
	 *
	 * @var  boolean
	 */
	protected $displaySidebar = false;

	/**
	 * @var  array
	 */
	public $items;

	/**
	 * @var  object
	 */
	public $state;

	/**
	 * @var  Pagination
	 */
	public $pagination;

	/**
	 * @var  Form
	 */
	public $form;

	/**
	 * @var  array
	 */
	public $defaultAddressDetails;

	/**
	 * @var  array
	 */
	public $billingAddressDetails;

	/**
	 * @var  Input
	 */
	public $jinput;

	/**
	 * @var  array
	 */
	public $fieldsData = array();

	/**
	 * Display method.
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$layout = $this->getLayout();

		if ($layout != 'default')
		{
			parent::display($tpl);

			return;
		}

		$addressModel = RModelAdmin::getInstance('Addresses', 'RedshopbModel');
		$this->form   = $this->getForm();
		$this->jinput = Factory::getApplication()->input;
		$this->state  = $addressModel->getState();
		$addressModel->setState('list.limit', 100);
		$this->pagination = $addressModel->getPagination();

		$rsbUser          = RedshopbEntityUser::loadFromJoomlaUser(Factory::getUser()->id);
		$this->fieldsData = $this->getFieldData($rsbUser);
		$this->items      = RedshopbHelperAddress::getAllShippingAddresses($rsbUser->getId(), 'employee');

		// Default delivery address = shipping address -> type_id = 3
		// Regular address = billing address => the one stored in the address_id of the user (type_id = 2)
		$this->defaultAddressDetails = $this->getAddress($rsbUser, 3);
		$this->billingAddressDetails = $this->getAddress($rsbUser, 2);
		$this->prepareBillingAddressDetails($rsbUser);

		parent::display($tpl);
	}

	/**
	 * Method to load scope field data if needed
	 *
	 * @param   RedshopbEntityUser  $rsbUser  current user entity
	 *
	 * @return array
	 */
	private function getFieldData($rsbUser)
	{
		if ($rsbUser->getCompany()->get('b2c') != 1)
		{
			return array();
		}

		$fieldData = RedshopbHelperField::loadScopeFieldData('user', $rsbUser->getId(), 0, true);

		if (!$fieldData)
		{
			return array();
		}

		return $fieldData;
	}

	/**
	 * Method to load a user address by typeId
	 *
	 * @param   RedshopbEntityUser  $rsbUser  current user entity
	 * @param   int                 $typeId   3 for shipping address 2 for billing address
	 *
	 * @return stdClass
	 */
	private function getAddress($rsbUser, $typeId)
	{
		/** @var RedshopbModelMyProfile $model */
		$model = $this->getModel();

		// Default delivery address = shipping address -> type_id = 3
		$addressId = $model->getAddressId($rsbUser->getId(), $typeId);

		if (empty($addressId))
		{
			$address                      = new stdClass;
			$address->delivery_address_id = 0;

			return $address;
		}

		$address = RedshopbEntityAddress::getInstance($addressId)->getExtendedData();

		return $address;
	}

	/**
	 * Method to prepare the billing address
	 *
	 * @param   RedshopbEntityUser  $rsbUser  current user entity
	 *
	 * @return void
	 */
	private function prepareBillingAddressDetails($rsbUser)
	{
		$company = $rsbUser->getCompany();

		if ($company->getId() && !$company->get('hide_company'))
		{
			$companyDetails                         = $company->getItem();
			$this->billingAddressDetails->company   = $companyDetails->name;
			$this->billingAddressDetails->vatNumber = ($companyDetails->vat_number) ? $companyDetails->vat_number : false;
		}

		if (empty($rsbUser->getId()))
		{
			return;
		}

		$userItem = RedshopbHelperUser::getUser();

		$this->billingAddressDetails->userName = $userItem->username;

		if (!empty($userItem->number))
		{
			$this->billingAddressDetails->userNumber = $userItem->number;
		}

		if (!empty($userItem->phone))
		{
			$this->billingAddressDetails->userPhone = $userItem->phone;
		}

		if (!empty($userItem->email) && !$userItem->use_company_email)
		{
			$this->billingAddressDetails->userEmail = $userItem->email;
		}
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return Text::_('COM_REDSHOPB_MYPROFILE');
	}
}
