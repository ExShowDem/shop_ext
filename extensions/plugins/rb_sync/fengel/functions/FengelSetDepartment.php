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
 * SetDepartment function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelSetDepartment extends FengelFunctionBase
{
	/**
	 * @var string
	 */
	public $syncName = 'fengel.department';

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
			$db->transactionStart();

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
			$db->setQuery($query);

			if (!$db->loadResult())
			{
				if ($table->get('id'))
				{
					$companyId = $table->get('company_id');
					$table->set('company_id', $table->get('oldCompanyId', $companyId));
					$parentId = $table->get('parent_id');
					$table->set('parent_id', $table->get('oldParentId', $parentId));

					if (!$this->deleteInWebservice($table, $table->get('id'), false))
					{
						throw new Exception;
					}

					$table->set('company_id', $companyId);
					$table->set('parent_id', $parentId);
				}

				return true;
			}

			if ((int) $table->parent_id > 1)
			{
				$query->clear()
					->select(
						array(
							$db->qn('s.remote_key', 'BelongstoDepartmentID'),
							$db->qn('d.department_number', 'BelongstoDepartment')
						)
					)
					->from($db->qn('#__redshopb_department', 'd'))
					->leftJoin($db->qn('#__redshopb_sync', 's') . ' ON s.local_id = d.id AND s.reference = ' . $db->q($this->syncName))
					->where('d.id = ' . (int) $table->parent_id)
					->where($db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' = 1');

				$parent = $db->setQuery($query)->loadObject();

				if (!$parent)
				{
					$parent                        = new stdClass;
					$parent->BelongstoDepartmentID = '';
					$parent->BelongstoDepartment   = '';
				}
			}
			else
			{
				$parent                        = new stdClass;
				$parent->BelongstoDepartmentID = '';
				$parent->BelongstoDepartment   = '';
			}

			if ($table->id)
			{
				$query->clear()
					->select(array($db->qn('s.remote_key')))
					->from($db->qn('#__redshopb_sync', 's'))
					->where('s.reference = ' . $db->q($this->syncName))
					->where('s.local_id = ' . (int) $table->id);

				$ID = $db->setQuery($query)->loadResult();

				if ($ID)
				{
					$setXml = '<status>update</status>';
				}
			}

			if (!$ID)
			{
				$ID     = self::generateGUID();
				$setXml = '<status>create</status>';
				$table->set('department_number', null);
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

			if ($table->company_id > 1)
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
					->where('node.id = ' . (int) $table->company_id)
					->group('parent.id')
					->order('parent.lft DESC');

				$query->clear()
					->select(
						array(
							'IF (c.type = ' . $db->q('end_customer') . ', c.customer_number, ' . $db->q('') . ') AS endCustomerNo',
							'IF (c.type = ' . $db->q('end_customer') . ', (' . $subQuery . ' LIMIT 0,1), c.customer_number) AS customerNo'
						)
					)
					->from($db->qn('#__redshopb_company', 'c'))
					->where($db->qn('c.deleted') . ' = 0')
					->where('c.id = ' . (int) $table->company_id);

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

			$setXml .= '<ID>' . $ID . '</ID>'
				. '<CustomerNo>' . $customer->customerNo . '</CustomerNo>'
				. '<EndCustomer>' . $customer->endCustomerNo . '</EndCustomer>'
				. '<No>' . $table->get('department_number') . '</No>'
				. '<BelongstoDepartment>' . $parent->BelongstoDepartment . '</BelongstoDepartment>'
				. '<BelongstoDepartmentID>' . $parent->BelongstoDepartmentID . '</BelongstoDepartmentID>'
				. '<Name>' . $table->get('name') . '</Name>'
				. '<Name2>' . $table->get('name2') . '</Name2>'
				. '<Address>' . $table->get('address') . '</Address>'
				. '<Address2>' . $table->get('address2') . '</Address2>'
				. '<PostCode>' . $table->get('zip') . '</PostCode>'
				. '<City>' . $table->get('city') . '</City>'
				. '<CountryCode>' . $countryCode . '</CountryCode>';

			$setXml = '<?xml version="1.0" encoding="UTF-8"?><Departments><Department>' . $setXml . '</Department></Departments>';
			$xml    = $this->client->setDepartment($setXml);

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

				if (isset($xml->Departments->No))
				{
					$table->department_number = (string) $xml->Departments->No;
				}

				if ($storingChanges)
				{
					if (!$table->store($updateNulls))
					{
						throw new Exception;
					}
				}

				$table->setOption('disableOnBeforeRedshopb', false);

				if (isset($xml->Departments->No) && !$this->findSyncedId($this->syncName, $ID, $parent->BelongstoDepartmentID))
				{
					// Insert new item in sync table
					$this->recordSyncedId($this->syncName, $ID, $table->id, $parent->BelongstoDepartmentID);
				}
			}

			// We put translation check back on
			$db->translate = true;

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
	 * Send query to delete item in Set webservice, then delete in DB
	 *
	 * @param   object   $table     Table class from current item
	 * @param   integer  $pk        The primary key of the node to delete.
	 * @param   boolean  $children  True to delete child nodes, false to move them up a level.
	 *
	 * @throws Exception
	 *
	 * @return  boolean  True on success.
	 */
	public function delete(&$table, $pk = null, $children = true)
	{
		$db = Factory::getDbo();

		try
		{
			$db->transactionStart();

			if (!$this->deleteInWebservice($table, $pk, $children))
			{
				throw new Exception;
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
	 * @param   object   $table     Table class from current item
	 * @param   integer  $pk        The primary key of the node to delete.
	 * @param   boolean  $children  True to delete child nodes, false to move them up a level.
	 *
	 * @return  boolean
	 *
	 * @throws Exception
	 */
	public function deleteInWebservice(&$table, $pk = null, $children = true)
	{
		try
		{
			$key = $table->get('_tbl_key');
			$pk  = (is_null($pk)) ? $table->get($key) : $pk;

			if ($children)
			{
				$pk = $table->getChildrenIds($pk);
			}

			if (!is_array($pk))
			{
				$pk = array($pk);
			}

			$db         = Factory::getDbo();
			$query      = $db->getQuery(true);
			$cloneTable = clone $table;

			foreach ($pk as $id)
			{
				if (!$cloneTable->load($id, true))
				{
					continue;
				}

				$query->clear()
					->select(array($db->qn('s.remote_key')))
					->from($db->qn('#__redshopb_sync', 's'))
					->where('s.reference = ' . $db->q($this->syncName))
					->where('s.local_id = ' . (int) $id);

				$ID = $db->setQuery($query)->loadResult();

				if (!$ID)
				{
					return true;
				}

				if ($cloneTable->get('company_id') > 1)
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
						->where('node.id = ' . (int) $cloneTable->get('company_id'))
						->group('parent.id')
						->order('parent.lft DESC');

					$query->clear()
						->select(
							array(
								'IF (c.type = ' . $db->q('end_customer') . ', c.customer_number, ' . $db->q('') . ') AS endCustomerNo',
								'IF (c.type = ' . $db->q('end_customer') . ', (' . $subQuery . ' LIMIT 0,1), c.customer_number) AS customerNo'
							)
						)
						->from($db->qn('#__redshopb_company', 'c'))
						->where($db->qn('c.deleted') . ' = 0')
						->where('c.id = ' . (int) $cloneTable->get('company_id'));

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

				if ((int) $cloneTable->get('parent_id') > 1)
				{
					$query->clear()
						->select(
							array(
								$db->qn('s.remote_key', 'BelongstoDepartmentID'),
								$db->qn('d.department_number', 'BelongstoDepartment')
							)
						)
						->from($db->qn('#__redshopb_department', 'd'))
						->leftJoin($db->qn('#__redshopb_sync', 's') . ' ON s.local_id = d.id AND s.reference = ' . $db->q($this->syncName))
						->where('d.id = ' . (int) $cloneTable->get('parent_id'))
						->where($db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' = 1');

					$parent = $db->setQuery($query)->loadObject();

					if (!$parent)
					{
						$parent                        = new stdClass;
						$parent->BelongstoDepartmentID = '';
						$parent->BelongstoDepartment   = '';
					}
				}
				else
				{
					$parent                        = new stdClass;
					$parent->BelongstoDepartmentID = '';
					$parent->BelongstoDepartment   = '';
				}

				$setXml = '<?xml version="1.0" encoding="UTF-8"?><Departments><Department>'
					. '<status>delete</status>'
					. '<ID>' . $ID . '</ID>'
					. '<CustomerNo>' . $customer->customerNo . '</CustomerNo>'
					. '<EndCustomer>' . $customer->endCustomerNo . '</EndCustomer>'
					. '<BelongstoDepartment>' . $parent->BelongstoDepartment . '</BelongstoDepartment>'
					. '<BelongstoDepartmentID>' . $parent->BelongstoDepartmentID . '</BelongstoDepartmentID>'
					. '<No>' . $cloneTable->get('department_number') . '</No>'
					. '</Department></Departments>';
				$xml    = $this->client->setDepartment($setXml);

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

					$this->deleteSyncedId($this->syncName, $ID);
				}
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
