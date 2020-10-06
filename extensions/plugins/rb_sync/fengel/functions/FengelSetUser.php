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
 * SetUser function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelSetUser extends FengelFunctionBase
{
	/**
	 * @var string
	 */
	public $syncName = 'fengel.user';

	/**
	 * Send data in Set webservice, then store
	 *
	 * @param   object   $table           Table class from current item
	 * @param   boolean  $updateNulls     True to update null values as well.
	 * @param   boolean  $storingChanges  If true - changes storing in DB
	 *
	 * @throws Exception
	 *
	 * @return boolean
	 */
	public function store(&$table = null, $updateNulls = false, $storingChanges = true)
	{
		$db = Factory::getDbo();

		try
		{
			// We do not want to translate
			$db->translate = false;

			$query = $db->getQuery(true);
			$ID    = false;

			$query->clear()
				->select('s.remote_key')
				->from($db->qn('#__redshopb_company', 'parent'))
				->where($db->qn('parent.deleted') . ' = 0')
				->leftJoin(
					$db->qn('#__redshopb_company', 'node') . ' ON node.lft BETWEEN parent.lft AND parent.rgt AND ' . $db->qn('node.deleted') . ' = 0'
				)
				->leftJoin($db->qn('#__redshopb_sync', 's') . ' ON local_id = parent.id')
				->where('reference = ' . $db->q('fengel.customer'))
				->where('parent.type = ' . $db->q('customer'))
				->where('node.id = ' . (int) $table->get('company_id'))
				->group('parent.id')
				->order('parent.lft DESC');

			$remoteCompanyKey = $db->setQuery($query)->loadResult();

			if (!$remoteCompanyKey)
			{
				$query->clear()
					->select('type')
					->from($db->qn('#__redshopb_company', 'c'))
					->where('c.id = ' . (int) $table->get('company_id'));

				$db->setQuery($query);
				$companyType = $db->loadResult();

				if (!$companyType || $companyType != 'main')
				{
					if ($table->get('id'))
					{
						$code = $this->getCode($table->get('id'));

						if ($code)
						{
							$table->set('userCode', $code);

							if (!$this->deleteInWebservice($table))
							{
								throw new Exception;
							}
						}
					}

					return true;
				}
			}

			if ($table->get('id'))
			{
				$query->clear()
					->select(array($db->qn('s.remote_key')))
					->from($db->qn('#__redshopb_sync', 's'))
					->where('s.reference = ' . $db->q($this->syncName))
					->where('s.local_id = ' . (int) $table->get('id'));

				$ID = $db->setQuery($query)->loadResult();

				if ($ID)
				{
					$setXml = '<status>update</status>'
						. '<ActiveFrom></ActiveFrom>'
						. '<ActiveTo></ActiveTo>'
						. '<DefaultShip2Code></DefaultShip2Code>';
				}
			}

			if (!$ID)
			{
				$ID     = '';
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

			if ($table->get('company_id') > 1)
			{
				$subQuery = $db->getQuery(true)
					->select('parent.customer_number')
					->from($db->qn('#__redshopb_company', 'parent'))
					->where($db->qn('parent.deleted') . ' = 0')
					->leftJoin(
						$db->qn('#__redshopb_company', 'node') . ' ON node.lft BETWEEN parent.lft AND parent.rgt AND ' .
						$db->qn('node.deleted') . ' = 0'
					)
					->where('parent.type = ' . $db->q('customer'))
					->where('node.id = ' . (int) $table->get('company_id'))
					->group('parent.id')
					->order('parent.lft DESC');

				$query->clear()
					->select(
						array(
							'IF (c.type = ' . $db->q('end_customer') . ', c.customer_number, ' . $db->q('') . ') AS endCustomerNo',
							'IF (c.type = ' . $db->q('end_customer') . ', (' . $subQuery . ' LIMIT 0,1), ' .
							'IF (c.type = ' . $db->q('main') . ',' . $db->q('') . ', c.customer_number)) AS customerNo'
						)
					)
					->from($db->qn('#__redshopb_company', 'c'))
					->where($db->qn('c.deleted') . ' = 0')
					->where('c.id = ' . (int) $table->get('company_id'));

				$customer = $db->setQuery($query)->loadObject();

				if (!$customer)
				{
					$customer                = new stdClass;
					$customer->endCustomerNo = '';
					$customer->customerNo    = '';
				}
			}
			else
			{
				$customer                = new stdClass;
				$customer->endCustomerNo = '';
				$customer->customerNo    = '';
			}

			if ($table->get('department_id'))
			{
				$query->clear()
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
					->where('d.id = ' . (int) $table->get('department_id'))
					->where($db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' = 1');

				$department = $db->setQuery($query)->loadObject();

				if (!$department)
				{
					$department                        = new stdClass;
					$department->BelongsToDepartment   = '';
					$department->BelongsToDepartmentID = '';
				}
			}
			else
			{
				$department                        = new stdClass;
				$department->BelongsToDepartment   = '';
				$department->BelongsToDepartmentID = '';
			}

			switch ($table->get('role_type_id'))
			{
				case 2:
					$role = 'Administrator';
					break;
				case 3:
					$role = 'Head of Department';
					break;
				case 4:
					$role = 'Sales Person';
					break;
				case 5:
					$role = 'Purchaser';
					break;
				case 6:
					$role = 'Employee with login';
					break;
				case 7:
				default:
					$role = 'Employee';
					break;
			}

			$pointBalance = 0;

			$setXml .= '<ID>' . $ID . '</ID>'
				. '<CustomerNo>' . $customer->customerNo . '</CustomerNo>'
				. '<EndCustomer>' . $customer->endCustomerNo . '</EndCustomer>'
				. '<No>' . $table->get('employee_number') . '</No>'
				. '<BelongsToDepartment>' . $department->BelongsToDepartment . '</BelongsToDepartment>'
				. '<BelongsToDepartmentID>' . $department->BelongsToDepartmentID . '</BelongsToDepartmentID>'
				. '<Name>' . $table->get('name1') . '</Name>'
				. '<Name2>' . $table->get('name2') . '</Name2>'
				. '<PrintedName>' . $table->get('printed_name') . '</PrintedName>'
				. '<Address>' . $table->get('address') . '</Address>'
				. '<Address2>' . $table->get('address2') . '</Address2>'
				. '<ZipCode>' . $table->get('zip') . '</ZipCode>'
				. '<City>' . $table->get('city') . '</City>'
				. '<CountryCode>' . $countryCode . '</CountryCode>'
				. '<PhoneNo>' . $table->get('phone') . '</PhoneNo>'
				. '<CellNo>' . $table->get('cell_phone') . '</CellNo>'
				. '<UserName>' . $table->get('username') . '</UserName>'
				. '<Password></Password>'
				. '<Email>' . $table->get('email') . '</Email>'
				. '<Active>' . ($table->get('block') == 0 ? 'true' : 'false') . '</Active>'
				. '<Role>' . $role . '</Role>'
				. '<PointBalance>' . $pointBalance . '</PointBalance>';

			$setXml = '<?xml version="1.0" encoding="UTF-8"?><Users><User>' . $setXml . '</User></Users>';
			$xml    = $this->client->setUser($setXml);

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

				$table->setOption('disableOnBeforeRedshopb', true)
					->setOption('useTransaction', false);

				if ($storingChanges)
				{
					if (!$table->store($updateNulls))
					{
						throw new Exception($table->getError());
					}
				}

				$table->setOption('disableOnBeforeRedshopb', false)
					->setOption('useTransaction', true);

				if (isset($xml->Employees->ID) && !$this->findSyncedId($this->syncName, (string) $xml->Employees->ID))
				{
					// Insert new item in sync table
					$this->recordSyncedId($this->syncName, (string) $xml->Employees->ID, $table->id);
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
	 * Send query to delete item in Set webservice, then delete in DB
	 *
	 * @param   object   $table   Table class from current item
	 * @param   integer  $pk      The primary key of the node to delete.
	 *
	 * @throws Exception
	 *
	 * @return  boolean  True on success.
	 */
	public function delete(&$table, $pk = null)
	{
		$db = Factory::getDbo();

		try
		{
			$db->transactionStart();
			$key  = $table->getKeyName();
			$pk   = (is_null($pk)) ? $table->$key : $pk;
			$code = $this->getCode($pk);

			if ($code)
			{
				$table->set('userCode', $code);

				if (!$this->deleteInWebservice($table))
				{
					throw new Exception;
				}
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
	 * Delete In Webservice
	 *
	 * @param   object  $table  Table class from current item
	 *
	 * @return  boolean
	 *
	 * @throws Exception
	 */
	public function deleteInWebservice(&$table)
	{
		try
		{
			$setXml = '<?xml version="1.0" encoding="UTF-8"?><Users><User><status>delete</status><ID>'
				. $table->get('userCode') . '</ID></User></Users>';
			$xml    = $this->client->setUser($setXml);

			if (!$xml)
			{
				throw new Exception(Text::sprintf('PLG_RB_SYNC_FENGEL_SET_WEBSERVICE_NOT_ANSWER'));
			}
			else
			{
				if (isset($xml->Errottext))
				{
					throw new Exception(Text::sprintf('PLG_RB_SYNC_FENGEL_SET_WEBSERVICE_GET_ERROR', (string) $xml->Errottext));
				}

				$this->deleteSyncedId($this->syncName, $table->get('userCode'));
			}
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
	 * Get tag Code
	 *
	 * @param   int  $id  Id current tag
	 *
	 * @return mixed
	 */
	public function getCode($id)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->qn('s.remote_key'))
			->from($db->qn('#__redshopb_sync', 's'))
			->where('s.reference = ' . $db->q($this->syncName))
			->where('local_id = ' . (int) $id);
		$db->setQuery($query);

		return $db->loadResult();
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
