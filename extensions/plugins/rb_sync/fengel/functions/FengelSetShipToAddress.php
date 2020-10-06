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
 * SetShipToAddress function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelSetShipToAddress extends FengelFunctionBase
{
	/**
	 * @var string
	 */
	public $syncName = 'fengel.ship_to_address';

	/**
	 * Send data in Set webservice, then store
	 *
	 * @param   object   $table        Table class from current item
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @throws Exception
	 *
	 * @return boolean
	 */
	public function store(&$table = null, $updateNulls = false)
	{
		$db = Factory::getDbo();

		try
		{
			// We do not want to translate
			$db->translate = false;

			$query = $db->getQuery(true);

			if ($table->id)
			{
				$query->clear()
					->select(array($db->qn('s.remote_key')))
					->from($db->qn('#__redshopb_sync', 's'))
					->where('s.reference = ' . $db->q($this->syncName))
					->where('s.local_id = ' . (int) $table->id);
				$db->setQuery($query);

				if ($db->loadResult())
				{
					$setXml = '<status>update</status>';
				}
				else
				{
					$setXml = '<status>create</status>';
				}
			}
			else
			{
				$setXml = '<status>create</status>';
			}

			$countryCode = '';

			if ($table->get('country_id'))
			{
				$query->clear()
					->select('alpha2')
					->from($db->qn('#__redshopb_country'))
					->where('id = ' . (int) $table->get('country_id'));

				$result = $db->setQuery($query)->loadResult();

				if ($result)
				{
					$countryCode = $result;
				}
			}

			switch ($table->get('customer_type'))
			{
				case 'company':
					$type = 'Customer';

					$query->clear()
						->select(array('customer_number', 'type'))
						->from($db->qn('#__redshopb_company'))
						->where($db->qn('deleted') . ' = 0')
						->where('id = ' . (int) $table->get('customer_id'));

					$result = $db->setQuery($query)->loadObject();

					if ($result)
					{
						if ($result->type == 'customer')
						{
							$type    = 'Customer';
							$setXml .= '<CustomerNo>' . $result->customer_number . '</CustomerNo>'
								. '<EndCustomer/>'
								. '<DepartmentEmployeeNo/>'
								. '<DepartmentID>{00000000-0000-0000-0000-000000000000}</DepartmentID>';
						}
						else
						{
							$type     = 'End Customer';
							$customer = $this->getEndCustomer($table);
							$setXml  .= '<CustomerNo>' . $customer->customerNo . '</CustomerNo>'
								. '<EndCustomer>' . $customer->endCustomerNo . '</EndCustomer>'
								. '<DepartmentEmployeeNo/>'
								. '<DepartmentID>{00000000-0000-0000-0000-000000000000}</DepartmentID>';
						}
					}
					break;
				case 'department':
					$type = 'Department';

					$customer = $this->getEndCustomer($table);
					$setXml  .= '<CustomerNo>' . $customer->customerNo . '</CustomerNo>'
						. '<EndCustomer>' . $customer->endCustomerNo . '</EndCustomer>';

					$department = $this->getDepartment($table);
					$setXml    .= '<DepartmentEmployeeNo>' . $department->BelongsToDepartment . '</DepartmentEmployeeNo>'
						. '<DepartmentID>' . $department->BelongsToDepartmentID . '</DepartmentID>'
						. '<EmployeeID>{00000000-0000-0000-0000-000000000000}</EmployeeID>';
					break;
				case 'employee':
					$type     = 'Employee';
					$customer = $this->getEndCustomer($table);
					$setXml  .= '<CustomerNo>' . $customer->customerNo . '</CustomerNo>'
						. '<EndCustomer>' . $customer->endCustomerNo . '</EndCustomer>';

					$user    = $this->getUser($table);
					$setXml .= '<DepartmentEmployeeNo>' . $user->EmployeeNo . '</DepartmentEmployeeNo>'
						. '<DepartmentID>' . $user->DepartmentID . '</DepartmentID>'
						. '<EmployeeID>' . $user->EmployeeID . '</EmployeeID>';
					break;
				default:
					$type = '';
			}

			$setXml .= '<Type>' . $type . '</Type>'
				. '<Code>' . $table->get('code') . '</Code>'
				. '<Name>' . $table->get('name') . '</Name>'
				. '<Name2>' . $table->get('name2') . '</Name2>'
				. '<Address>' . $table->get('address') . '</Address>'
				. '<Address2>' . $table->get('address2') . '</Address2>'
				. '<PostCode>' . $table->get('zip') . '</PostCode>'
				. '<City>' . $table->get('city') . '</City>'
				. '<CountryCode>' . $countryCode . '</CountryCode>'
				. '<DefaultAdress>' . ($table->get('type') == 3 ? 'true' : 'false') . '</DefaultAdress>';

			$setXml = '<?xml version="1.0" encoding="UTF-8"?><ShiptoAddresses><ShiptoAddress>' . $setXml . '</ShiptoAddress></ShiptoAddresses>';
			$xml    = $this->client->SetShiptoAddress($setXml);

			if (!$xml)
			{
				throw new Exception(Text::sprintf('PLG_RB_SYNC_FENGEL_SET_WEBSERVICE_NOT_ANSWER'));
			}
			else
			{
				if (isset($xml->Errottext))
				{
					throw new Exception(Text::sprintf('PLG_RB_SYNC_FENGEL_SET_WEBSERVICE_GET_ERROR', $xml->Errottext));
				}

				$table->setOption('disableOnBeforeRedshopb', true);

				if (isset($xml->ShipTos->Code))
				{
					$table->set('code', (string) $xml->ShipTos->Code);
				}

				if (!$table->store($updateNulls))
				{
					throw new Exception;
				}

				$table->setOption('disableOnBeforeRedshopb', false);

				$remoteKey = (string) $xml->ShipTos->Code . '_' . (string) $xml->ShipTos->Type . '_' . (string) $xml->ShipTos->CustomerNo;

				switch ((string) $xml->ShipTos->Type)
				{
					case 'Department':
						$remoteKey .= '_' . (string) $department->BelongsToDepartmentID;
						break;

					case 'Employee':
						$remoteKey .= '_' . (string) $user->DepartmentID . '_' . (string) $user->EmployeeID;
						break;
				}

				if (!$this->findSyncedId($this->syncName, $remoteKey))
				{
					// Insert new item in sync table
					$this->recordSyncedId($this->syncName, $remoteKey, $table->id);
				}
			}

			// We put translation check back on
			$db->translate = true;
		}
		catch (Exception $e)
		{
			if ($e->getMessage())
			{
				$table->setError($e->getMessage());
			}

			return false;
		}

		return true;
	}

	/**
	 * Get User
	 *
	 * @param   object  $table  Values current item
	 *
	 * @return mixed|stdClass
	 */
	public function getUser($table)
	{
		$user               = new stdClass;
		$user->EmployeeNo   = '';
		$user->DepartmentID = '';
		$user->EmployeeID   = '';
		$db                 = Factory::getDbo();

		$query = $db->getQuery(true)
			->select(
				array(
					$db->qn('u.employee_number', 'EmployeeNo'),
					$db->qn('s.remote_key', 'EmployeeID'),
					$db->qn('sd.remote_key', 'DepartmentID')
				)
			)
			->from($db->qn('#__redshopb_user', 'u'))
			->leftJoin(
				$db->qn('#__redshopb_sync', 's')
				. ' ON s.local_id = u.id AND s.reference = ' . $db->q('fengel.user')
			)
			->leftJoin(
				$db->qn('#__redshopb_sync', 'sd')
				. ' ON sd.local_id = u.department_id AND sd.reference = ' . $db->q('fengel.department')
			)
			->where('u.id = ' . (int) $table->get('customer_id'));

		$result = $db->setQuery($query)->loadObject();

		if ($result)
		{
			$user = $result;

			if ($user->DepartmentID == '')
			{
				$user->DepartmentID = '{00000000-0000-0000-0000-000000000000}';
			}
		}

		return $user;
	}

	/**
	 * Get Department
	 *
	 * @param   object  $table  Values current item
	 *
	 * @return mixed|stdClass
	 */
	public function getDepartment($table)
	{
		$department                        = new stdClass;
		$department->BelongsToDepartment   = '';
		$department->BelongsToDepartmentID = '';
		$db                                = Factory::getDbo();

		$query = $db->getQuery(true)
			->select(
				array(
					$db->qn('d.department_number', 'BelongsToDepartment'),
					$db->qn('s.remote_key', 'BelongsToDepartmentID')
				)
			)
			->from($db->qn('#__redshopb_department', 'd'))
			->leftJoin(
				$db->qn('#__redshopb_sync', 's')
				. ' ON s.local_id = d.id AND s.reference = ' . $db->q('fengel.department')
			)
			->where('d.id = ' . (int) $table->get('customer_id'))
			->where($db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' = 1');

		$result = $db->setQuery($query)->loadObject();

		if ($result)
		{
			$department = $result;
		}

		return $department;
	}

	/**
	 * Get End Customer
	 *
	 * @param   object  $table  Values current item
	 *
	 * @return mixed|stdClass
	 */
	public function getEndCustomer($table)
	{
		$db = Factory::getDbo();

		$subQuery = $db->getQuery(true)
			->select('parent.customer_number')
			->from($db->qn('#__redshopb_company', 'parent'))
			->where($db->qn('parent.deleted') . ' = 0')
			->leftJoin(
				$db->qn('#__redshopb_company', 'node') . ' ON node.lft BETWEEN parent.lft AND parent.rgt AND ' . $db->qn('node.deleted') . ' = 0'
			)
			->where('parent.type = ' . $db->q('customer'))
			->where('node.id = c.id')
			->group('parent.id')
			->order('parent.lft DESC');

		$query = $db->getQuery(true)
			->select(
				array(
					'IF (c.type = ' . $db->q('end_customer') . ', c.customer_number, ' . $db->q('') . ') AS endCustomerNo',
					'IF (c.type = ' . $db->q('end_customer') . ', (' . $subQuery . ' LIMIT 0,1), c.customer_number) AS customerNo'
				)
			)
			->from($db->qn('#__redshopb_company', 'c'))
			->where($db->qn('c.deleted') . ' = 0');

		if ($table->get('customer_type') == 'company')
		{
			$query->where('c.id = ' . (int) $table->get('customer_id'));
		}
		elseif ($table->get('customer_type') == 'department')
		{
			$query->leftJoin($db->qn('#__redshopb_department', 'd') . ' ON c.id = d.company_id')
				->where('d.id = ' . (int) $table->get('customer_id') . ' AND ' . $db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' = 1');
		}
		elseif ($table->get('customer_type') == 'employee')
		{
			$query
				->leftJoin('#__redshopb_user_multi_company AS umc ON umc.company_id = c.id')
				->leftJoin($db->qn('#__redshopb_user', 'u') . ' ON umc.user_id = c.id')
				->where('u.id = ' . (int) $table->get('customer_id'));
		}

		$customer = $db->setQuery($query)->loadObject();

		if (!$customer)
		{
			$customer                = new stdClass;
			$customer->endCustomerNo = '';
			$customer->customerNo    = '';
		}

		return $customer;
	}

	/**
	 * Send query to delete item in Set webservice, then delete in DB
	 *
	 * @param   object   $table  Table class from current item
	 * @param   integer  $pk     The primary key of the node to delete.
	 *
	 * @throws Exception
	 *
	 * @return  boolean  True on success.
	 */
	public function delete($table, $pk = null)
	{
		$db = Factory::getDbo();

		try
		{
			$db->transactionStart();
			$query  = $db->getQuery(true);
			$setXml = '';
			$type   = '';

			switch ($table->get('customer_type'))
			{
				case 'company':
					$query->clear()
						->select('customer_number, type')
						->from($db->qn('#__redshopb_company'))
						->where($db->qn('deleted') . ' = 0')
						->where('id = ' . (int) $table->get('customer_id'));

					$result = $db->setQuery($query)->loadObject();

					if ($result)
					{
						if ($result->type == 'customer')
						{
							$type    = 'Customer';
							$setXml .= '<CustomerNo>' . $result->customer_number . '</CustomerNo>';
						}
						elseif ($result->type == 'end_customer')
						{
							$type     = 'End Customer';
							$customer = $this->getEndCustomer($table);
							$setXml  .= '<CustomerNo>' . $customer->customerNo . '</CustomerNo>'
								. '<EndCustomer>' . $customer->endCustomerNo . '</EndCustomer>';
						}
					}

					break;
				case 'department':
					$type = 'Department';

					$customer = $this->getEndCustomer($table);
					$setXml  .= '<CustomerNo>' . $customer->customerNo . '</CustomerNo>'
						. '<EndCustomer>' . $customer->endCustomerNo . '</EndCustomer>';

					$department = $this->getDepartment($table);
					$setXml    .= '<DepartmentEmployeeNo>' . $department->BelongsToDepartment . '</DepartmentEmployeeNo>'
						. '<DepartmentID>' . $department->BelongsToDepartmentID . '</DepartmentID>';
					break;
				case 'employee':
					$type     = 'Employee';
					$customer = $this->getEndCustomer($table);
					$setXml  .= '<CustomerNo>' . $customer->customerNo . '</CustomerNo>'
						. '<EndCustomer>' . $customer->endCustomerNo . '</EndCustomer>';

					$user    = $this->getUser($table);
					$setXml .= '<DepartmentEmployeeNo>' . $user->EmployeeNo . '</DepartmentEmployeeNo>'
						. '<DepartmentID>' . $user->DepartmentID . '</DepartmentID>'
						. '<EmployeeID>' . $user->EmployeeID . '</EmployeeID>';
					break;
			}

			$setXml .= '<status>delete</status>'
				. '<Type>' . $type . '</Type>'
				. '<Code>' . $table->get('code') . '</Code>';

			$setXml = '<?xml version="1.0" encoding="UTF-8"?><ShiptoAddresses><ShiptoAddress>' . $setXml . '</ShiptoAddress></ShiptoAddresses>';
			$xml    = $this->client->SetShiptoAddress($setXml);

			if (!$xml)
			{
				throw new Exception(Text::sprintf('PLG_RB_SYNC_FENGEL_SET_WEBSERVICE_NOT_ANSWER'));
			}
			else
			{
				if (isset($xml->Errottext))
				{
					throw new Exception(Text::sprintf('PLG_RB_SYNC_FENGEL_SET_WEBSERVICE_GET_ERROR', $xml->Errottext));
				}

				$remoteKey = (string) $xml->ShipTos->Code . '_' . (string) $xml->ShipTos->Type . '_' . (string) $xml->ShipTos->CustomerNo;

				switch ((string) $xml->ShipTos->Type)
				{
					case 'Department':
						$remoteKey .= '_' . (string) $department->BelongsToDepartmentID;
						break;

					case 'Employee':
						$remoteKey .= '_' . (string) $user->DepartmentID . '_' . (string) $user->EmployeeID;
						break;
				}

				$this->deleteSyncedId($this->syncName, $remoteKey);
			}

			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$db->transactionRollback();

			if ($e->getMessage())
			{
				$table->setError($e->getMessage());
			}

			return false;
		}

		return true;
	}

	/**
	 * Read and store the data.
	 *
	 * @param   RTable     $webserviceData   Webservice object
	 * @param   Registry   $params           Parameters of the plugin
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 */
	public function read(&$webserviceData, $params)
	{
	}
}
