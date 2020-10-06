<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Registry\Registry;

/**
 * Orders Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerOrders extends RedshopbControllerAdmin
{
	/**
	 * A hook implementation to delete order items related to this order
	 *
	 * @param   BaseDatabaseModel  $model  RModel instance
	 * @param   array              $id     order item ids
	 *
	 * @return boolean
	 */
	public function postDeleteHook(BaseDatabaseModel $model, $id = null)
	{
		$suc = $this->getModel('orders')->deleteOrderItems($id);

		return $suc;
	}

	/**
	 * Method to render order items from layout using ajax
	 *
	 * Outputs html
	 *
	 * @return void
	 */
	public function ajaxOrderItems()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app         = Factory::getApplication();
		$orderId     = $app->input->getInt('orderid', 0);
		$config      = RedshopbEntityConfig::getInstance();
		$modelOrders = $this->getModel('Orders');

		if ($orderId)
		{
			$customerItemsSettings = array(
				'config'              => $config,
				'state'               => $modelOrders->getState(),
				'customerOrders'      => array($modelOrders->getCustomerOrder($orderId)),
				'form'                => $modelOrders->getCustomForm('cartitems'),
				'showStockAs'         => RedshopbHelperStockroom::getStockVisibility(),
				'showToolbar'         => false,
				'checkbox'            => false,
				'quantityfield'       => 'quantity',
				'canEdit'             => false,
				'lockquantity'        => true,
				'orderId'             => $orderId,
				'showDeliveryAddress' => false,
				'isEmail'             => false,
				'view'                => $app->input->get('view'),
				'delivery'            => $config->get('stockroom_delivery_time', 'hour'),
				'feeProducts'         => RedshopbHelperShop::getChargeProducts('fee'),
				'return'              => base64_encode('index.php?option=com_redshopb&view=orders'),
			);

			echo RedshopbLayoutHelper::render('checkout.customer_basket', $customerItemsSettings);
		}

		$app->close();
	}

	/**
	 * This function sends selected orders for processing to a higher company level.
	 *
	 * @return void
	 */
	public function expedite()
	{
		if (!RedshopbEntityConfig::getInstance()->get('order_expedition', 0))
		{
			$this->setRedirect(RedshopbRoute::_('index.php?option=com_redshopb&view=orders', false));
			$this->redirect();
		}

		$orderIds    = $this->input->post->get('cid', array(), 'array');
		$address     = $this->input->post->get('delivery_address_id', 0, 'int');
		$comment     = $this->input->post->get('comment', '', 'string');
		$requisition = $this->input->post->get('requisition', '', 'string');
		$model       = $this->getModel('orders');

		// Keep original address for multiple orders being expedited
		if ($address == -1)
		{
			$address = 0;
		}

		$model->expediteOrders($orderIds, $address, $comment, $requisition);

		$this->setRedirect(RedshopbRoute::_('index.php?option=com_redshopb&view=orders', false));
		$this->redirect();
	}

	/**
	 * This function collects set of orders, making new order on collector level.
	 *
	 * @return void
	 */
	public function collect()
	{
		$orderIds    = $this->input->post->get('cid', array(), 'array');
		$address     = $this->input->post->get('delivery_address_id', Factory::getApplication()->getUserState('orders.address_id', 0), 'int');
		$comment     = $this->input->post->get('comment', '', 'string');
		$requisition = $this->input->post->get('requisition', '', 'string');
		$model       = $this->getModel('orders');
		$model->collectOrders($orderIds, $address, $comment, $requisition);

		$this->setRedirect(RedshopbRoute::_('index.php?option=com_redshopb&view=orders', false));
		$this->redirect();
	}

	/**
	 * Gets the children orders for a parent order and
	 * renders the rows
	 *
	 * @return void
	 */
	public function ajaxGetChildrenOrders()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app    = Factory::getApplication();
		$parent = $app->input->get('parentId', 0);
		$model  = $this->getModel('orders');

		$children = $model->getChildrenOrders($parent);

		if ($children)
		{
			echo RedshopbLayoutHelper::render(
				'orders.childrenrows',
				array(
					'children' => $children,
					'parent' => $parent
				)
			);
		}

		$app->close();
	}

	/**
	 * Ajax function for getting options.
	 *
	 * @return void
	 */
	public function ajaxGetOrderModal()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app = Factory::getApplication();
		$app->input->set('view', 'orders');
		$orderIds = $app->input->get('orderIds', array(), 'array');
		$action   = $app->input->get('action', '', 'string');

		/** @var RedshopbModelOrders $model */
		$model      = $this->getModel('orders');
		$form       = $model->getFilterForm();
		$fields     = $form->getFieldset('modal');
		$ordersInfo = $model->getConcatenatedOrdersInfo($orderIds);

		if (count($orderIds) <= 1)
		{
			/** @var RedshopbModelOrder $model */
			$model = $this->getModel('order');
			$order = $model->getItem($orderIds[0]);

			/** @var FormField $field */
			foreach ($fields as $field)
			{
				if ($field->name == 'comment')
				{
					$field->setValue($order->comment);
				}
				elseif ($field->name == 'requisition')
				{
					$field->setValue($order->requisition);
				}

				echo $field->renderField();
			}

			$app->close();
		}

		if ($action == 'orders.expedite')
		{
			foreach ($fields as $field)
			{
				if ($field->name != 'delivery_address_id')
				{
					$field->disabled = true;
				}

				echo $field->renderField();
			}

			$app->close();
		}

		foreach ($fields as $field)
		{
			if ($field->name == 'comment')
			{
				$field->setValue($ordersInfo['comment']);
			}
			elseif ($field->name == 'requisition')
			{
				$field->setValue($ordersInfo['requisition']);
			}

			echo $field->renderField();
		}

		$app->close();
	}

	/**
	 * Get expedite XML output for selected orders.
	 *
	 * @return void
	 */
	public function xmloutput()
	{
		$this->validateOrderExpedition();

		$app      = Factory::getApplication();
		$orderIds = $this->input->post->get('cid', array(), 'array');
		header('Content-Type: text/xml');

		try
		{
			echo htmlspecialchars_decode(RedshopbHelperOrder_Log::getOrderLog($orderIds));
		}
		catch (Exception $e)
		{
			echo htmlspecialchars_decode('<?xml version="1.0" encoding="utf-8" ?><Error>' . $e->getMessage() . '</Error>');
		}

		$app->close();
	}

	/**
	 * Method to check order expedition setting
	 * if order expedition is not enabled this method closes the app with error message
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	private function validateOrderExpedition()
	{
		$app = Factory::getApplication();

		if (RedshopbEntityConfig::getInstance()->get('order_expedition', 0))
		{
			return;
		}

		echo Text::_('COM_REDSHOPB_ORDER_EXPEDITE_FAIL_NOT_ENABLE');
		$app->close();
	}

	/**
	 * Get string raw data for order expedition.
	 *
	 * XML string result which we send to web services.
	 *
	 * @return void
	 */
	public function rawoutput()
	{
		$this->validateOrderExpedition();

		$app      = Factory::getApplication();
		$orderIds = $this->input->post->get('cid', array(), 'array');

		try
		{
			echo htmlspecialchars(RedshopbHelperOrder_Log::getOrderLog($orderIds));
		}
		catch (Exception $e)
		{
			echo htmlspecialchars('<?xml version="1.0" encoding="utf-8" ?><Error>' . $e->getMessage() . '</Error>');
		}

		$app->close();
	}

	/**
	 * Ajax function for checking permission on given action.
	 * This function also sets customer preforming the action for new order.
	 *
	 * Json string result.
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function ajaxCheckActionPermissions()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app = Factory::getApplication();
		$app->setUserState('orders.address_id', 0);
		$app->setUserState('orders.purchaser', null);
		$app->setUserState('orders.vendor', null);
		$result = array(
			'mShow' => 0,
			'msg'   => '',
			'grant' => 1
		);

		// Order ids for checking permissions
		$orderIds = $app->input->get('orderIds', array(), 'array');
		$action   = $app->input->get('action', 'orders.collect', 'string');
		$userId   = RedshopbHelperUser::getUserRSid();

		try
		{
			if ($action == 'orders.collect')
			{
				if (!RedshopbHelperOrder::areAllPending($orderIds))
				{
					throw new Exception(Text::_('COM_REDSHOPB_ORDERS_COLLECT_FAIL_NOT_PENDING'));
				}

				if (!RedshopbHelperOrder::areAllCheckedOut($orderIds))
				{
					throw new Exception(Text::_('COM_REDSHOPB_ORDERS_COLLECT_FAIL_NOT_CHECKED_OUT'));
				}

				if (count($orderIds) < 2)
				{
					throw new Exception(Text::_('COM_REDSHOPB_ORDERS_COLLECT_FAIL_ONE_ORDER_SELECTED'));
				}

				$addressId = RedshopbHelperOrder::areAllFromSameDeliveryAddress($orderIds);

				if (!$addressId)
				{
					throw new Exception(Text::_('COM_REDSHOPB_ORDERS_COLLECT_FAIL_DELIVERY_ADDRESS'));
				}
				else
				{
					$app->setUserState('orders.address_id', $addressId);
				}

				$currency = RedshopbHelperOrder::areAllHavingSameCurrency($orderIds);

				if (!$currency)
				{
					throw new Exception(Text::_('COM_REDSHOPB_ORDERS_COLLECT_FAIL_CURRENCY'));
				}
				else
				{
					$app->setUserState('orders.currency', $currency);
				}

				$purchaser = RedshopbHelperOrder::areAllFromSameCompany($orderIds, 'customer');

				if (!$purchaser)
				{
					throw new Exception(Text::_('COM_REDSHOPB_ORDERS_COLLECT_FAIL_CUSTOMER'));
				}
				else
				{
					$endCustomer = RedshopbHelperOrder::areAllFromSameCompany($orderIds);

					if ($endCustomer)
					{
						$vendor    = $purchaser;
						$purchaser = $endCustomer;
					}

					$app->setUserState('orders.parentEntity', $purchaser);

					if (RedshopbHelperACL::isSuperAdmin() || !$userId)
					{
						if (!isset($vendor))
						{
							$vendor = RedshopbHelperCompany::getCustomerCompanyByCustomer($purchaser->id, $purchaser->pType, false);
						}

						$app->setUserState('orders.customer_id', $vendor->id);
						$app->setUserState('orders.customer_type', 'company');
						$result['mShow'] = 1;
					}
					else
					{
						$userCompany    = RedshopbHelperUser::getUserCompany($userId, 'redshopb', false);
						$userDepartment = RedshopbHelperUser::getUserDepartment($userId, 'redshopb', false);

						if ($userCompany->type == 'customer' && is_null($userDepartment))
						{
							$app->setUserState('orders.customer_id', $userCompany->id);
							$app->setUserState('orders.customer_type', 'company');
							$result['mShow'] = 1;
						}
						elseif (!is_null($userDepartment))
						{
							$app->setUserState('orders.customer_id', $userDepartment->id);
							$app->setUserState('orders.customer_type', 'department');
							$result['mShow'] = 0;
						}
						else
						{
							$app->setUserState('orders.customer_id', $userCompany->id);
							$app->setUserState('orders.customer_type', 'company');
							$result['mShow'] = 0;
						}
					}
				}
			}
			elseif ($action == 'orders.expedite')
			{
				if (!RedshopbEntityConfig::getInstance()->get('order_expedition', 0))
				{
					throw new Exception(Text::_('COM_REDSHOPB_ORDER_EXPEDITE_FAIL_NOT_ENABLE'));
				}

				if (!RedshopbHelperOrder::areAllPending($orderIds))
				{
					throw new Exception(Text::_('COM_REDSHOPB_ORDERS_EXPEDITE_FAIL_NOT_PENDING'));
				}

				if (!RedshopbHelperOrder::areAllCheckedOut($orderIds))
				{
					throw new Exception(Text::_('COM_REDSHOPB_ORDERS_EXPEDITE_FAIL_NOT_CHECKED_OUT'));
				}

				$purchaser   = RedshopbHelperOrder::areAllFromSameCompany($orderIds, 'customer');
				$endCustomer = RedshopbHelperOrder::areAllFromSameCompany($orderIds);

				if (count($orderIds) == 1)
				{
					$app->setUserState('orders.address_id', RedshopbHelperOrder::getDeliveryAddress($orderIds[0]));
				}
				else
				{
					$addressId = RedshopbHelperOrder::areAllFromSameDeliveryAddress($orderIds);

					if ($addressId)
					{
						$app->setUserState('orders.address_id', RedshopbHelperOrder::getDeliveryAddress($orderIds[0]));
					}
				}

				if ($purchaser)
				{
					$app->setUserState('orders.customer_id', $purchaser->id);
					$app->setUserState('orders.customer_type', 'company');
				}
				else
				{
					throw new Exception(Text::_('COM_REDSHOPB_ORDERS_EXPEDITE_FAIL_CUSTOMER'));
				}

				if ($endCustomer)
				{
					$result['mShow'] = 1;
				}
			}
		}
		catch (Exception $e)
		{
			$result['grant'] = 0;
			$result['msg']   = $e->getMessage();
			$result['mShow'] = 0;
			$app->setUserState('orders.address_id', 0);
			$app->setUserState('orders.purchaser', null);
			$app->setUserState('orders.vendor', null);
		}

		echo json_encode($result);

		$app->close();
	}

	/**
	 * Print order as PDF file.
	 *
	 * @return void
	 */
	public function printPDF()
	{
		$app      = Factory::getApplication();
		$orderIds = $this->input->post->get('cid', array(), 'array');
		RedshopbHelperOrder::printPDF($orderIds);
		$app->close();
	}

	/**
	 * Generates data for creating CSV files
	 *
	 * @param   CMSApplication     $app       The Joomla application
	 * @param   string             $type      Name of the currect view, used to get the associating model
	 * @param   RedshopbViewOrders $view      CSV View
	 * @param   Registry           $viewData  Protected view data
	 *
	 * @return  array
	 */
	protected function getCsvData($app, $type, $view, $viewData)
	{
		$csvLines = array();

		// Get the columns
		$columns = $viewData->get('columns');

		/** @var RedshopbModelOrders $model */
		$model = $this->getModel('Orders', 'RedshopbModel');

		$data = json_decode($app->input->post->getString('result', '[]'));

		$model->setState('streamOutput', 'csv');
		$model->setState('nongrouping', 'true');

		// Prepare the items
		$items = $model->getItemsCsv('o', $data);

		$csvLines[0] = $columns;
		$inc         = 1;

		foreach ($items as $item)
		{
			$csvLines[$inc] = array();

			foreach ($columns as $name => $title)
			{
				if (property_exists($item, $name))
				{
					$csvLines[$inc][$name] = $view->preprocess($name, $item->$name);
				}
			}

			$inc++;
		}

		return $csvLines;
	}
}
