<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;

/**
 * All Prices Controller
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerAll_Prices extends RedshopbControllerAdmin
{
	/**
	 * Method to save a record.
	 *
	 * @return  void
	 */
	public function saveAllPrices()
	{
		// Check for request forgeries
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

		$data         = $this->input->post->get('jform', array(), 'array');
		$model        = $this->getModel('All_Price');
		$countStoring = 0;
		$error        = false;

		if (isset($data['price']) && count($data['price']) > 0)
		{
			foreach ($data['price'] as $id => $price)
			{
				$countStoring++;
				$row = array(
					'id' => $id,
					'price' => $price
				);

				if (!$model->save($row))
				{
					$this->setMessage($model->getError(), 'error');
					$error = true;

					break;
				}
			}
		}

		if (isset($data['price_new']) && count($data['price_new']) > 0 && !$error)
		{
			foreach ($data['price_new'] as $typeId => $price)
			{
				if (!$price)
				{
					continue;
				}

				$countStoring++;
				$row = array(
					'id' => 0,
					'type_id' => $typeId,
					'type' => 'product_item',
					'sales_type' => 'all_customers',
					'sales_code' => '',
					'currency_id' => $data['default_currency_id'],
					'price' => $price,
					'starting_date' => '0000-00-00 00:00:00',
					'ending_date' => '0000-00-00 00:00:00',
					'allow_discount' => 1,
					'country_id' => null
				);

				if (!$model->save($row))
				{
					$this->setMessage($model->getError(), 'error');
					$error = true;

					break;
				}
			}
		}

		if (!$error)
		{
			$this->setMessage(Text::plural($this->text_prefix . '_N_PRICES_UPDATED', $countStoring));
		}

		// Set redirect
		$this->setRedirect($this->getRedirectToListRoute());
	}

	/**
	 * A Method to delete one or more prices by cid
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function ajaxDelete()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		parent::delete();

		$result              = new stdClass;
		$result->message     = $this->message;
		$result->messageType = $this->messageType;

		echo json_encode($result);
		Factory::getApplication()->close();

		if ($result->messageType == 'error')
		{
			header('HTTP/1.1 500 Internal Server Error');
		}

		echo json_encode($result);

		Factory::getApplication()->close();
	}

	/**
	 * Get prices via AJAX
	 *
	 * @return void
	 */
	public function ajaxPrices()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app           = Factory::getApplication();
		$productId     = $this->input->getInt('product_id');
		$productItemId = $this->input->getInt('product_item_id', 0);
		$returnObject  = $this->getReturnObject();

		if (!$productId)
		{
			$returnObject->message     = Text::_('COM_REDSHOPB_ALL_PRICES_ERROR_INVALID_PRODUCT_ID');
			$returnObject->messageType = 'alert-error';

			header('HTTP/1.1 400 Bad Request');

			echo json_encode($returnObject);

			$app->close();
		}

		/** @var RedshopbModelAll_Prices $model */
		$model = RModelAdmin::getInstance('All_prices', 'RedshopbModel');
		$model->getState();

		$filterType = 'filter_all_prices';

		if (!empty($productItemId))
		{
			$filterType = 'filter_product_item_prices';
		}

		$model->set('filterFormName', $filterType);
		$model->setState('filter.product_id', $productId);
		$model->setState('filter.product_item_id', $productItemId);

		// Also initializes product_id in filter form
		$form = $model->getForm();

		// Sets the other variables including pagination and items
		$formName   = $this->input->getString('formName', 'pricesForm');
		$pagination = $model->getPagination();
		$pagination->set('formName', $formName);

		$tab    = $this->input->get('tab', 'Prices');
		$url    = 'index.php?option=com_redshopb&view=all_prices&product_id=' . $productId;
		$return = 'index.php?option=com_redshopb&view=product&layout=edit&id=' . $productId . '&tab=' . $tab;

		if (!empty($productItemId))
		{
			$url            .= '&product_item_id=' . $productItemId;
			$secondaryReturn = base64_encode($return);
			$return          = 'index.php?option=com_redshopb&view=product_item&layout=edit&id=' . $productItemId;
			$return         .= '&tab=Prices&return=' . $secondaryReturn;
		}

		$layoutOptions = array(
			'state'         => $model->getState(),
			'items'         => $model->getItems(),
			'filter_form'   => $form,
			'activeFilters' => $model->getActiveFilters(),
			'pagination'    => $pagination,
			'formName'      => $formName,
			'action'        => $url,
			'return'        => base64_encode($return),
			'productId'     => $productId,
			'productItemId' => $productItemId,
			'showToolbar'   => !RedshopbEntityProduct::getInstance($productId)->canReadOnly()
				|| !RedshopbEntityProduct_Item::getInstance($productItemId)->canReadOnly()
		);

		$returnObject->html = RedshopbLayoutHelper::render('all_prices.list', $layoutOptions);

		echo json_encode($returnObject);

		$app->close();
	}
}
