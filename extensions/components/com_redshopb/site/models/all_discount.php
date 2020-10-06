<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Table\Table;

/**
 * All Discount Model
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelAll_Discount extends RedshopbModelAdmin
{
	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'product';

	/**
	 * Get the associated Table
	 *
	 * @param   string  $name    Table name
	 * @param   string  $prefix  Table prefix
	 * @param   array   $config  Configuration array
	 *
	 * @return  Table
	 */
	public function getTable($name = 'product_discount', $prefix = '', $config = array())
	{
		if (is_null($name))
		{
			$name = 'product_discount';
		}

		return parent::getTable($name, $prefix, $config);
	}

	/**
	 * Method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A Form object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = parent::getForm(array(), true);

		switch ($form->getValue('type'))
		{
			case 'product':
				if ($form->getValue('type_product_id'))
				{
					$form->setValue('type_id', null, $form->getValue('type_product_id'));
				}
				else
				{
					$form->setValue('type_product_id', null, $form->getValue('type_id'));
				}
				break;
			case 'product_item':
				if ($form->getValue('type_product_item_id'))
				{
					$form->setValue('type_id', null, $form->getValue('type_product_item_id'));
				}
				else
				{
					$form->setValue('type_product_item_id', null, $form->getValue('type_id'));
				}
				break;
			case 'product_discount_group':
				if ($form->getValue('type_product_discount_group_id'))
				{
					$form->setValue('type_id', null, $form->getValue('type_product_discount_group_id'));
				}
				else
				{
					$form->setValue('type_product_discount_group_id', null, $form->getValue('type_id'));
				}
				break;
		}

		switch ($form->getValue('sales_type'))
		{
			case 'debtor':
				if ($form->getValue('sales_debtor_id'))
				{
					$form->setValue('sales_id', null, $form->getValue('sales_debtor_id'));
				}
				else
				{
					$form->setValue('sales_debtor_id', null, $form->getValue('sales_id'));
				}
				break;
			case 'debtor_discount_group':
				if ($form->getValue('sales_debtor_discount_group_id'))
				{
					$form->setValue('sales_id', null, $form->getValue('sales_debtor_discount_group_id'));
				}
				else
				{
					$form->setValue('sales_debtor_discount_group_id', null, $form->getValue('sales_id'));
				}
				break;
			default:
				$form->setValue('sales_id', null, '');
		}

		return $form;
	}

	/**
	 * Method to validate the form data.
	 * Each field error is stored in session and can be retrieved with getFieldError().
	 * Once getFieldError() is called, the error is deleted from the session.
	 *
	 * @param   Form    $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 */
	public function validate($form, $data, $group = null)
	{
		switch ($data['type'])
		{
			case 'product':
				$form->setFieldAttribute('type_product_discount_group_id', 'required', 'false');
				$form->setFieldAttribute('type_product_item_id', 'required', 'false');
				$data['type_id'] = $data['type_product_id'];
				break;
			case 'product_item':
				$form->setFieldAttribute('type_product_discount_group_id', 'required', 'false');
				$form->setFieldAttribute('type_product_id', 'required', 'false');
				$data['type_id'] = $data['type_product_item_id'];
				break;
			case 'product_discount_group':
				$form->setFieldAttribute('type_product_id', 'required', 'false');
				$form->setFieldAttribute('type_product_item_id', 'required', 'false');
				$data['type_id'] = $data['type_product_discount_group_id'];
				break;
			default:
				$form->setFieldAttribute('type_product_discount_group_id', 'required', 'false');
				$form->setFieldAttribute('type_product_id', 'required', 'false');
				$form->setFieldAttribute('type_product_item_id', 'required', 'false');
		}

		switch ($data['sales_type'])
		{
			case 'debtor':
				$form->setFieldAttribute('sales_debtor_discount_group_id', 'required', 'false');
				$data['sales_id'] = $data['sales_debtor_id'];
				break;
			case 'debtor_discount_group':
				$form->setFieldAttribute('sales_debtor_id', 'required', 'false');
				$data['sales_id'] = $data['sales_debtor_discount_group_id'];
				break;
			default:
				$form->setFieldAttribute('sales_debtor_discount_group_id', 'required', 'false');
				$form->setFieldAttribute('sales_debtor_id', 'required', 'false');
		}

		return parent::validate($form, $data, null);
	}

	/**
	 * Validate incoming data from the update web service - maps non-incoming data to avoid problems with actual validation
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  false|array
	 */
	public function validateUpdateWS($data)
	{
		// Selection between product_id and product_discount_group_id
		$typeSelection = '';

		if (!empty($data['product_id']))
		{
			$typeSelection = 'product_id';
		}

		if (!empty($data['product_item_id']))
		{
			$typeSelection = 'product_item_id';
		}

		if (!empty($data['product_discount_group_id']))
		{
			$typeSelection = 'product_discount_group_id';
		}

		// Selection between company_id and customer_discount_group_id
		$salesTypeSelection = '';

		if (!empty($data['company_id']))
		{
			$salesTypeSelection = 'company_id';
		}

		if (!empty($data['customer_discount_group_id']))
		{
			$salesTypeSelection = 'customer_discount_group_id';
		}

		$data = parent::validateUpdateWS($data);

		switch ($typeSelection)
		{
			case 'product_id':
				$data['product_discount_group_id']      = '';
				$data['product_item_id']                = '';
				$data['type']                           = 'product';
				$data['type_product_item_id']           = '';
				$data['type_product_discount_group_id'] = '';
				$data['type_id']                        = $data['product_id'];
				break;

			case 'product_item_id':
				$data['product_id']                     = '';
				$data['product_discount_group_id']      = '';
				$data['type']                           = 'product_item';
				$data['type_product_id']                = '';
				$data['type_product_discount_group_id'] = '';
				$data['type_id']                        = $data['product_item_id'];
				break;

			case 'product_discount_group_id':
				$data['product_id']           = '';
				$data['product_item_id']      = '';
				$data['type']                 = 'product_discount_group';
				$data['type_product_id']      = '';
				$data['type_product_item_id'] = '';
				$data['type_id']              = $data['product_discount_group_id'];
				break;
		}

		switch ($salesTypeSelection)
		{
			case 'company_id':
				$data['customer_discount_group_id']     = '';
				$data['sales_type']                     = 'debtor';
				$data['sales_debtor_discount_group_id'] = '';
				$data['sales_id']                       = $data['company_id'];
				break;

			case 'customer_discount_group_id':
				$data['company_id']      = '';
				$data['sales_type']      = 'debtor_discount_group';
				$data['sales_debtor_id'] = '';
				$data['sales_id']        = $data['customer_discount_group_id'];
				break;
		}

		return $data;
	}

	/**
	 * Validate incoming data from the web service
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  mixed
	 */
	public function validateWS($data)
	{
		$app = Factory::getApplication();

		$productId               = 0;
		$productDiscountGroupId  = 0;
		$companyId               = 0;
		$customerDiscountGroupId = 0;

		$data['type']       = '';
		$data['sales_type'] = 'all_debtor';

		// Validate Product
		if (!empty($data['product_id']) && strtolower($data['product_id']) != 'null')
		{
			/** @var RedshopbModelProduct $productModel */
			$productModel = RedshopbModelAdmin::getFrontInstance('Product');
			$product      = $productModel->getItemFromWSData($data['product_id']);

			if (!$product)
			{
				$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_WEBSERVICE_RECORD_NOT_FOUND', $data["product_id"]), 'error');

				return false;
			}

			$data['type']            = 'product';
			$productId               = $product->id;
			$data['type_id']         = $productId;
			$data['type_product_id'] = $productId;
		}
		else
		{
			$data['type_product_id'] = '';
		}

		// Validate Product Item
		if (!empty($data['product_item_id']) && strtolower($data['product_item_id']) != 'null')
		{
			/** @var RedshopbModelProduct_Item $productItemModel */
			$productItemModel = RedshopbModelAdmin::getFrontInstance('Product_Item');
			$productItem      = $productItemModel->getItemFromWSData($data['product_item_id']);

			if (!$productItem)
			{
				$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_WEBSERVICE_RECORD_NOT_FOUND', $data["product_item_id"]), 'error');

				return false;
			}

			$data['type']                 = 'product_item';
			$productItemId                = $productItem->id;
			$data['type_id']              = $productItemId;
			$data['type_product_item_id'] = $productItemId;
		}
		else
		{
			$data['type_product_item_id'] = '';
		}

		// Validate Product Group
		if (!empty($data['product_discount_group_id']) && strtolower($data['product_discount_group_id']) != 'null')
		{
			/** @var RedshopbModelProduct_Discount_Group $productDiscountGroupModel */
			$productDiscountGroupModel = RedshopbModelAdmin::getFrontInstance('Product_Discount_Group');
			$productDiscountGroup      = $productDiscountGroupModel->getItemFromWSData($data['product_discount_group_id']);

			if (!$productDiscountGroup)
			{
				$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_WEBSERVICE_RECORD_NOT_FOUND', $data["product_discount_group_id"]), 'error');

				return false;
			}

			$data['type']                           = 'product_discount_group';
			$productDiscountGroupId                 = $productDiscountGroup->id;
			$data['type_id']                        = $productDiscountGroupId;
			$data['type_product_discount_group_id'] = $productDiscountGroupId;
		}
		else
		{
			$data['type_product_discount_group_id'] = '';
		}

		// Validate Company
		if (!empty($data['company_id']) && strtolower($data['company_id']) != 'null')
		{
			/** @var RedshopbModelCompany $companyModel */
			$companyModel = RedshopbModelAdmin::getFrontInstance('Company');
			$company      = $companyModel->getItemFromWSData($data['company_id']);

			if (!$company)
			{
				$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_WEBSERVICE_RECORD_NOT_FOUND', $data["company_id"]), 'error');

				return false;
			}

			$data['sales_type']      = 'debtor';
			$companyId               = $company->id;
			$data['sales_id']        = $companyId;
			$data['sales_debtor_id'] = $companyId;
		}
		else
		{
			$data['sales_debtor_id'] = '';
		}

		// Validate Customer Discount Group
		if (!empty($data['customer_discount_group_id']) && strtolower($data['customer_discount_group_id']) != 'null')
		{
			/** @var RedshopbModelDiscount_Debtor_Group $customerDiscountGroupModel */
			$customerDiscountGroupModel = RedshopbModelAdmin::getFrontInstance('Discount_Debtor_Group');
			$customerDiscountGroup      = $customerDiscountGroupModel->getItemFromWSData($data['customer_discount_group_id']);

			if (!$customerDiscountGroup)
			{
				$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_WEBSERVICE_RECORD_NOT_FOUND', $data["customer_discount_group_id"]), 'error');

				return false;
			}

			$data['sales_type']                     = 'debtor_discount_group';
			$customerDiscountGroupId                = $customerDiscountGroup->id;
			$data['sales_id']                       = $customerDiscountGroupId;
			$data['sales_debtor_discount_group_id'] = $customerDiscountGroupId;
		}
		else
		{
			$data['sales_debtor_discount_group_id'] = '';
		}

		// Validate discount type
		if (!isset($data['kind']) || !is_numeric($data['kind']))
		{
			$data['kind'] = 0;
		}

		// Validate discount value base on discount type
		if ($data['kind'] == 1 && (empty($data['total']) || strtolower($data['total']) == 'null'))
		{
			$app->enqueueMessage(Text::_('COM_REDSHOPB_WEBSERVICE_PRODUCT_DISCOUNT_ERROR_MISSING_AMOUNT'), 'error');

			return false;
		}
		else
		{
			$data['total'] = (float) $data['total'];
		}

		// Validate discount value base on discount type
		if ($data['kind'] == 0 && (empty($data['percent']) || strtolower($data['percent']) == 'null'))
		{
			$app->enqueueMessage(Text::_('COM_REDSHOPB_WEBSERVICE_PRODUCT_DISCOUNT_ERROR_MISSING_PERCENT'), 'error');

			return false;
		}
		else
		{
			$data['percent'] = (float) $data['percent'];
		}

		// Validate quantity if available on discount type
		if (!empty($data['quantity_min']) && strtolower($data['quantity_min']) != 'null')
		{
			$data['quantity_min'] = (float) $data['quantity_min'];
		}
		else
		{
			$data['quantity_min'] = null;
		}

		if (!empty($data['quantity_max']) && strtolower($data['quantity_max']) != 'null')
		{
			$data['quantity_max'] = (float) $data['quantity_max'];
		}
		else
		{
			$data['quantity_max'] = null;
		}

		if (is_float($data['quantity_min']) && is_float($data['quantity_max']) && $data['quantity_min'] > $data['quantity_max'])
		{
			$app->enqueueMessage(Text::_('COM_REDSHOPB_PRICE_SAVE_ERROR_VOLUME_MIN_MAX'), 'error');

			return false;
		}

		return parent::validateWS($data);
	}
}
