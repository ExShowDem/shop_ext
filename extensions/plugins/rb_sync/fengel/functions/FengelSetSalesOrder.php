<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;

require_once __DIR__ . '/base.php';

/**
 * PutOrder function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelSetSalesOrder extends FengelFunctionBase
{
	/**
	 * @var string
	 */
	public $syncName = 'fengel.setsalesorder';

	/**
	 * Read and store the data.
	 *
	 * @param   RTable     $webserviceData   Webservice object
	 * @param   Registry   $params           Parameters of the plugin
	 *
	 * @return  boolean
	 *
	 * @throws  Exception
	 */
	public function read(&$webserviceData, $params)
	{
		$db = Factory::getDbo();

		try
		{
			$query = $db->getQuery(true)
				->select(
					array (
						$db->qn('local_id')
					)
				)
				->from($db->qn('#__redshopb_sync'))
				->where($db->qn('reference') . ' = ' . $db->q($this->syncName))
				->where($db->qn('execute_sync') . ' = ' . 0);

			$delayedOrders = $db->setQuery($query)->loadColumn(0);

			if ($delayedOrders)
			{
				foreach ($delayedOrders as $orderId)
				{
					$this->send(array('ids' => array((int) $orderId)), false);
				}
			}
		}
		catch (Exception $e)
		{
			$db->transactionRollback();
			RedshopbHelperSync::addMessage($e->getMessage(), 'error');

			return false;
		}

		RedshopbHelperSync::addMessage(Text::_('PLG_RB_SYNC_FENGEL_SYNCHRONIZE_SUCCESS'), 'success');

		return true;
	}

	/**
	 * Send data.
	 *
	 * @param   array  $order                Order for expedition.
	 * @param   bool   $displayUserMessages  Sends the message output for the regular user (default true)
	 *
	 * @return  boolean
	 *
	 * @throws  Exception
	 */
	public function send($order, $displayUserMessages = true)
	{
		$db     = Factory::getDbo();
		$return = true;

		foreach ($order['ids'] as $key => $orderId)
		{
			try
			{
				$orderWillBeSent = false;
				$db->transactionStart();
				$orderXML = RedshopbHelperOrder_Log::getOrderLog(Array($orderId));

				if (is_null($orderXML) && (int) $orderId > 0)
				{
					$query = $db->getQuery(true);
					$query->update($db->qn('#__redshopb_order'))
						->set($db->qn('status') . ' = 6')
						->where($db->qn('id') . ' = ' . (int) $orderId);

					$db->setQuery($query)->execute();

					$db->transactionCommit();

					$order['ids'] = array_slice($order['ids'], $key + 1);

					return $this->send($order, $displayUserMessages);
				}

				$xml = $this->client->setSalesOrder($orderXML);

				if (!$xml)
				{
					$this->recordDelayedOrder($orderId);
					$orderWillBeSent = true;

					throw new Exception(Text::sprintf('PLG_RB_SYNC_FENGEL_SETSALESORDER_NOT_DEFINED'));
				}

				if (isset($xml->Errottext))
				{
					$this->recordDelayedOrder($orderId);

					if ($displayUserMessages)
					{
						RedshopbHelperSync::addMessage(
							Text::sprintf(
								'PLG_RB_SYNC_FENGEL_SETSALESORDER_DELAYED', RedshopbApp::getMainCompany()->name, $xml->Errottext
							),
							'warning'
						);
					}
					else
					{
						RedshopbHelperSync::addMessage(
							Text::sprintf('PLG_RB_SYNC_FENGEL_SET_WEBSERVICE_GET_ERROR', $xml->Errottext) . ' - ' .
							Text::_('COM_REDSHOPB_ORDER') . ' ' . $orderId, 'warning'
						);

						$return = false;
					}
				}
				elseif (isset($xml->Salesheaders))
				{
					if (isset($xml->Salesheaders->SalesheaderType))
					{
						$headerType = $xml->Salesheaders->SalesheaderType;
					}

					if (isset($xml->Salesheaders->SalesheaderNo))
					{
						$headerId = $xml->Salesheaders->SalesheaderNo;
					}

					if (isset($xml->Salesheaders->WebOrderNo))
					{
						$webOrderNo = $xml->Salesheaders->WebOrderNo;
					}

					if (isset($headerType) && isset($headerId) && isset($webOrderNo))
					{
						$query = $db->getQuery(true);
						$query->update($db->qn('#__redshopb_order'))
							->set($db->qn('sales_header_type') . ' = ' . $db->q($headerType))
							->set($db->qn('sales_header_id') . ' = ' . (int) $headerId)
							->set($db->qn('status') . ' = 6')
							->where($db->qn('id') . ' = ' . (int) $webOrderNo);

						try
						{
							$db->setQuery($query)->execute();
						}
						catch (Exception $e)
						{
							RedshopbHelperSync::addMessage($e->getMessage(), 'warning');
						}

						if ($this->findSyncedId($this->syncName, $orderId))
						{
							// Insert delayed order in sync table
							try
							{
								$this->recordSyncedId($this->syncName, $orderId, $orderId, '', false, 1);
							}
							catch (Exception $e)
							{
								RedshopbHelperSync::addMessage($e->getMessage(), 'warning');
							}
						}
					}
					else
					{
						$this->recordDelayedOrder($orderId);

						if ($displayUserMessages)
						{
							RedshopbHelperSync::addMessage(
								Text::sprintf(
									'PLG_RB_SYNC_FENGEL_SET_WEBSERVICE_GET_ERROR', Text::_('PLG_RB_SYNC_FENGEL_SET_WEBSERVICE_MISSING_DATA')
								),
								'error'
							);
						}
						else
						{
							RedshopbHelperSync::addMessage(
								Text::sprintf(
									'PLG_RB_SYNC_FENGEL_SET_WEBSERVICE_GET_ERROR', Text::_('PLG_RB_SYNC_FENGEL_SET_WEBSERVICE_MISSING_DATA')
								) . ' - ' . Text::_('COM_REDSHOPB_ORDER') . ' ' . $orderId,
								'warning'
							);
						}
					}
				}

				$db->transactionCommit();
			}
			catch (Exception $e)
			{
				$db->transactionRollback();

				if (!$orderWillBeSent)
				{
					RedshopbHelperSync::addMessage($e->getMessage(), 'warning');

					return false;
				}

				$this->recordDelayedOrder($orderId);

				if ($displayUserMessages)
				{
					RedshopbHelperSync::addMessage(
						Text::sprintf('PLG_RB_SYNC_FENGEL_SETSALESORDER_DELAYED', RedshopbApp::getMainCompany()->name, $e->getMessage()), 'warning'
					);
				}
				else
				{
					RedshopbHelperSync::addMessage(
						Text::sprintf('PLG_RB_SYNC_FENGEL_SET_WEBSERVICE_GET_ERROR', $e->getMessage()) . ' - ' .
						Text::_('COM_REDSHOPB_ORDER') . ' ' . $orderId, 'warning'
					);

					return false;
				}

				return true;
			}
		}

		if ($displayUserMessages)
		{
			RedshopbHelperSync::addMessage(Text::_('PLG_RB_SYNC_FENGEL_ORDER_SENT_VIA_WS'));
		}
		else
		{
			RedshopbHelperSync::addMessage(
				Text::_('PLG_RB_SYNC_FENGEL_ORDER_SENT_VIA_WS') . ' - ' . Text::_('COM_REDSHOPB_ORDER') . ' ' . $orderId
			);
		}

		return $return;
	}

	/**
	 * Record a delayed expedition
	 *
	 * @param   int  $orderId  Order id to delay
	 *
	 * @return  true
	 *
	 * @throws  Exception
	 */
	private function recordDelayedOrder($orderId)
	{
		if (!$this->findSyncedId($this->syncName, $orderId))
		{
			// Insert delayed order in sync table
			$this->recordSyncedId($this->syncName, $orderId, $orderId);
		}

		return true;
	}
}
