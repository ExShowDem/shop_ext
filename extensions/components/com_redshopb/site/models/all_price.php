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
 * All Price Model
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelAll_Price extends RedshopbModelAdmin
{
	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'product';

	/**
	 * Web service udate of sales type
	 *
	 * @var  string
	 */
	protected $wsSalesTypeUpdate = '';

	/**
	 * Get the associated Table
	 *
	 * @param   string  $name    Table name
	 * @param   string  $prefix  Table prefix
	 * @param   array   $config  Configuration array
	 *
	 * @return  Table
	 */
	public function getTable($name = 'product_price', $prefix = '', $config = array())
	{
		if (is_null($name))
		{
			$name = 'product_price';
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
				$typeProductItemId = $form->getValue('type_product_item_id');

				if ($typeProductItemId)
				{
					$form->setValue('type_id', null, $typeProductItemId);
					$form->setValue('type_product_id', null, $this->getProductId($typeProductItemId));
				}
				else
				{
					$typeId = $form->getValue('type_id');
					$form->setValue('type_product_item_id', null, $typeId);
					$form->setValue('type_product_id', null, $this->getProductId($typeId));
				}
				break;
		}

		switch ($form->getValue('sales_type'))
		{
			case 'customer_price':
				if ($form->getValue('sales_customer_id'))
				{
					$form->setValue('sales_code', null, $form->getValue('sales_customer_id'));
				}
				else
				{
					$form->setValue('sales_customer_id', null, $form->getValue('sales_code'));
				}
				break;
			case 'customer_price_group':
				if ($form->getValue('sales_customer_price_group_id'))
				{
					$form->setValue('sales_code', null, $form->getValue('sales_customer_price_group_id'));
				}
				else
				{
					$form->setValue('sales_customer_price_group_id', null, $form->getValue('sales_code'));
				}
				break;
			case 'campaign':
				if ($form->getValue('sales_campaign_id'))
				{
					$form->setValue('sales_code', null, $form->getValue('sales_campaign_id'));
				}
				else
				{
					$form->setValue('sales_campaign_id', null, $form->getValue('sales_code'));
				}
				break;
			default:
				$form->setValue('sales_code', null, '');
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
				$form->setFieldAttribute('type_product_item_id', 'required', 'false');
				$data['type_id'] = $data['type_product_id'];
				break;
			case 'product_item':
				$form->setFieldAttribute('type_product_id', 'required', 'false');
				$data['type_id'] = $data['type_product_item_id'];
				break;
			default:
				$form->setFieldAttribute('type_product_item_id', 'required', 'false');
				$form->setFieldAttribute('type_product_id', 'required', 'false');
		}

		switch ($data['sales_type'])
		{
			case 'customer_price':
				$form->setFieldAttribute('sales_customer_price_group_id', 'required', 'false');
				$form->setFieldAttribute('sales_campaign_id', 'required', 'false');
				$data['sales_code'] = $data['sales_customer_id'];
				break;
			case 'customer_price_group':
				$form->setFieldAttribute('sales_customer_id', 'required', 'false');
				$form->setFieldAttribute('sales_campaign_id', 'required', 'false');
				$data['sales_code'] = $data['sales_customer_price_group_id'];
				break;
			case 'campaign':
				$form->setFieldAttribute('sales_customer_price_group_id', 'required', 'false');
				$form->setFieldAttribute('sales_customer_id', 'required', 'false');
				$data['sales_code'] = $data['sales_campaign_id'];
				break;
			default:
				$form->setFieldAttribute('sales_customer_price_group_id', 'required', 'false');
				$form->setFieldAttribute('sales_customer_id', 'required', 'false');
				$form->setFieldAttribute('sales_campaign_id', 'required', 'false');
		}

		return parent::validate($form, $data, null);
	}

	/**
	 * Get Product Id
	 *
	 * @param   int  $productItemId  Product item id
	 *
	 * @return mixed
	 */
	public function getProductId($productItemId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('product_id')
			->from($db->qn('#__redshopb_product_item'))
			->where('id = ' . (int) $productItemId);
		$db->setQuery($query);

		return $db->loadResult();
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
		// Selection of sales type
		$this->wsSalesTypeUpdate = '';

		if (!empty($data['company_id']))
		{
			$this->wsSalesTypeUpdate = 'company_id';
		}

		if (!empty($data['customer_price_group_id']))
		{
			$this->wsSalesTypeUpdate = 'customer_price_group_id';
		}

		if (!empty($data['campaign_code']))
		{
			$this->wsSalesTypeUpdate = 'campaign_code';
		}

		return parent::validateUpdateWS($data);
	}

	/**
	 * Validate incoming data from the web service
	 *
	 * @param   array  $data  Data to be stored
	 *
	 * @return  array
	 */
	public function validateWS($data)
	{
		$app = Factory::getApplication();

		$productId            = 0;
		$productItemId        = 0;
		$companyId            = 0;
		$customerPriceGroupId = 0;
		$campaignCode         = '';

		$data['type']       = 'product';
		$data['sales_type'] = 'all_customers';

		// Update operation - select sales type
		switch ($this->wsSalesTypeUpdate)
		{
			case 'company_id':
				$data['customer_price_group_id'] = '';
				$data['campaign_code']           = '';
				break;

			case 'customer_price_group_id':
				$data['company_id']    = '';
				$data['campaign_code'] = '';
				break;

			case 'campaign_code':
				$data['company_id']              = '';
				$data['customer_price_group_id'] = '';
				break;
		}

		// Validate product id
		$productModel = RedshopbModelAdmin::getFrontInstance('Product');
		$product      = $productModel->getItemFromWSData($data['product_id']);

		if (!$product)
		{
			$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_WEBSERVICE_RECORD_NOT_FOUND', $data["product_id"]), 'error');

			return false;
		}

		$productId               = $product->id;
		$data['type_id']         = $productId;
		$data["type_product_id"] = $productId;

		// Validate product item id
		if (isset($data['product_item_id']) && !empty($data['product_item_id']))
		{
			$productItemModel = RedshopbModelAdmin::getFrontInstance('Product_Item');
			$productItem      = $productItemModel->getItemFromWSData($data['product_item_id']);

			if (!$productItem)
			{
				$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_WEBSERVICE_RECORD_NOT_FOUND', $data["product_item_id"]), 'error');

				return false;
			}

			if ($productItem->product_id != $productId)
			{
				$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_PRODUCT_ITEM_NOT_BELONG_PRODUCT', $productItem->id, $productId), 'error');

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

		// Validate Company
		if (isset($data['company_id']) && !empty($data['company_id']))
		{
			$companyModel = RedshopbModelAdmin::getFrontInstance('Company');
			$company      = $companyModel->getItemFromWSData($data['company_id']);

			if (!$company)
			{
				$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_WEBSERVICE_RECORD_NOT_FOUND', $data["company_id"]), 'error');

				return false;
			}

			$data['sales_type']        = 'customer_price';
			$companyId                 = $company->id;
			$data['sales_code']        = $companyId;
			$data['sales_customer_id'] = $companyId;
		}
		else
		{
			$data['sales_customer_id'] = '';
		}

		// Validate Customer Price Group
		if (isset($data['customer_price_group_id']) && !empty($data['customer_price_group_id']))
		{
			$customerPriceGroupModel = RedshopbModelAdmin::getFrontInstance('Price_Debtor_Group');
			$customerPriceGroup      = $customerPriceGroupModel->getItemFromWSData($data['customer_price_group_id']);

			if (!$customerPriceGroup)
			{
				$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_WEBSERVICE_RECORD_NOT_FOUND', $data["customer_price_group_id"]), 'error');

				return false;
			}

			$data['sales_type']                    = 'customer_price_group';
			$customerPriceGroupId                  = $customerPriceGroup->id;
			$data['sales_code']                    = $customerPriceGroupId;
			$data['sales_customer_price_group_id'] = $customerPriceGroupId;
		}
		else
		{
			$data['sales_customer_price_group_id'] = '';
		}

		// Validate Campaign Code
		if (isset($data['campaign_code']) && !empty($data['campaign_code']))
		{
			$data['sales_type']        = 'campaign';
			$campaignCode              = $data['campaign_code'];
			$data['sales_code']        = $campaignCode;
			$data['sales_campaign_id'] = $campaignCode;
		}

		if ($companyId && $customerPriceGroupId
			|| $companyId && !empty($campaignCode)
			|| $customerPriceGroupId && !empty($campaignCode))
		{
				$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_PRODUCT_PRICE_ONLY_ONE_SALES_TYPE'), 'error');

				return false;
		}

		if ($data['is_multiple'] == 1)
		{
			if ($data['quantity_min'] < 2)
			{
				$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_PRODUCT_PRICE_ISMULTIPLE_INVALID_MINQUANTITY'), 'error');

				return false;
			}
			elseif ($data['quantity_max'] != 0)
			{
				$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_PRODUCT_PRICE_ISMULTIPLE_INVALID_MAXQUANTITY'), 'error');

				return false;
			}
			else
			{
				$data['quantity_max'] = 0;
			}
		}

		$data = parent::validateWS($data);

		if (!$data)
		{
			return false;
		}

		$product       = RedshopbEntityProduct::getInstance((int) $data['type_id']);
		$vendorCompany = $product->getItem()->company_id;

		// Spots duplicated fallback price records
		if ($data['type'] == 'product'
			&& $data['sales_type'] == 'all_customers'
			&& $data['currency_id'] == RedshopbEntityCompany::getInstance((int) $vendorCompany)->getCustomerCurrency()
			&& $data['country_code'] == ''
			&& ($data['starting_date'] == '' || $data['starting_date'] == '0000-00-00 00:00:00')
			&& ($data['ending_date'] == '' || $data['ending_date'] == '0000-00-00 00:00:00')
			&& $data['quantity_min'] == ''
			&& $data['quantity_max'] == ''
			&& $data['price'] >= 0)
		{
			$fallbackPrice = $product->getFallbackPrice();

			if (!is_null($fallbackPrice) && $fallbackPrice->id != $data['id'])
			{
				$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_PRODUCT_PRICE_FALLBACK_EXISTS', $fallbackPrice->id), 'error');

				return false;
			}
		}

		return $data;
	}
}
