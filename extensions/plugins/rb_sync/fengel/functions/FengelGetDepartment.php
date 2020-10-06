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
use Joomla\CMS\Date\Date;

require_once __DIR__ . '/base.php';

/**
 * GetDepartment function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelGetDepartment extends FengelFunctionBase
{
	/**
	 * @var string
	 */
	public $syncName = 'fengel.department';

	/**
	 * @var integer
	 */
	public $start = 0;

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
		$this->start  = microtime(1);
		$goToNextPart = false;
		$db           = Factory::getDbo();
		$counter      = 0;

		try
		{
			$xml        = $this->client->getDepartment('', '', '', '', '');
			$countInXml = count($xml->Department);

			if (!is_object($xml))
			{
				throw new Exception(Text::_('PLG_RB_SYNC_FENGEL_FAILED_TO_FETCH_ITEMS'));
			}

			$db->transactionStart();
			$config           = RedshopbEntityConfig::getInstance();
			$defaultCountryId = $config->getInt('default_country_id', 59);

			$query = $db->getQuery(true)
				->select(array('id', 'alpha2'))
				->from($db->qn('#__redshopb_country'));
			$db->setQuery($query);
			$countries = $db->loadObjectList('alpha2');

			$query = $db->getQuery(true)
				->select('execute_sync')
				->from($db->qn('#__redshopb_cron'))
				->where('name = ' . $db->q('GetDepartment'));
			$db->setQuery($query);

			if (!$db->loadResult())
			{
				// Fix flag from all old items as not synced
				$query = $db->getQuery(true)
					->update($db->qn('#__redshopb_sync'))
					->set('execute_sync = 1')
					->where('reference = ' . $db->q($this->syncName));

				$db->setQuery($query)->execute();

				$query->clear()
					->update($db->qn('#__redshopb_cron'))
					->set('execute_sync = 1')
					->where('name = ' . $db->q('GetDepartment'));

				$db->setQuery($query)->execute();

				$countExecuted = 0;
			}
			else
			{
				// Get list executed in previous sync items
				$query = $db->getQuery(true)
					->select(array('s.*'))
					->from($db->qn('#__redshopb_sync', 's'))
					->innerJoin(
						$db->qn('#__redshopb_department', 'd') . ' ON s.local_id = d.id AND ' .
						$db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' = 1'
					)
					->where('s.reference = ' . $db->q($this->syncName))
					->where('s.execute_sync <> 1')
					->where('d.id > 0');
				$db->setQuery($query);
				$executed      = $db->loadObjectList('remote_key');
				$countExecuted = count($executed);
			}

			foreach ($xml->Department as $obj)
			{
				$counter++;

				if ((string) $obj->ID == '')
				{
					$counter--;
					$countInXml--;
					continue;
				}

				// If the synced department was executed (saved / updated) in a previous run, continues to the next one
				if ($countExecuted > 0 && isset($executed[(string) $obj->ID]))
				{
					continue;
				}

				$row = array(
					'company_id' => null,
					'address' => (string) $obj->Address,
					'address2' => (string) $obj->Address2,
					'zip' => (string) $obj->PostCode,
					'city' => (string) $obj->City
				);

				if ((string) $obj->CountryCode == '' || $row['address'] == '' || $row['zip'] == '' || $row['city'] == '')
				{
					$row['deleteIfEmptyDefaultAddress'] = true;
				}

				if (isset($countries[(string) $obj->CountryCode]))
				{
					$row['country_id'] = $countries[(string) $obj->CountryCode]->id;
				}
				else
				{
					$row['country_id'] = $defaultCountryId;
				}

				$row['address_name'] = trim((string) $obj->Name . ' ' . (string) $obj->Name2);

				$isNew        = true;
				$now          = Date::getInstance();
				$nowFormatted = $now->toSql();

				// Inserts record without altering ACL
				$table = RTable::getInstance('Department', 'RedshopbTable')
					->setOption('forceWebserviceUpdate', true)
					->setOption('lockingMethod', 'Sync');
				$table->setOption('buildACL', false);
				$allData = $this->findSyncedId($this->syncName, (string) $obj->ID, (string) $obj->BelongstoDepartmentID, true);
				$md5Row  = md5('10' . $obj->asXML());

				if (!empty($allData))
				{
					if ($allData->serialize == $md5Row)
					{
						$this->recordSyncedId(
							$this->syncName, (string) $obj->ID, $allData->local_id, (string) $obj->BelongstoDepartmentID, false, 2, $md5Row
						);

						if (microtime(1) - $this->start >= 25)
						{
							$goToNextPart = true;
							break;
						}

						continue;
					}

					$id = $allData->local_id;

					if ($table->load($id))
					{
						$isNew = false;
					}
					else
					{
						$this->deleteSyncedId($this->syncName, (string) $obj->ID);
					}
				}

				if ($isNew)
				{
					$row['created_date'] = $nowFormatted;
					$row['parent_id']    = 0;
					$table->setLocation($row['parent_id'], 'last-child');
				}

				// Get the company_id
				if ((string) $obj->EndCustomer)
				{
					$row['company_id'] = $this->findSyncedId('fengel.customer', (string) $obj->EndCustomer);
				}

				if (!$row['company_id'] && (string) $obj->CustomerNo)
				{
					$row['company_id'] = $this->findSyncedId('fengel.customer', (string) $obj->CustomerNo);
				}

				if (!$row['company_id'])
				{
					continue;
				}

				$row['modified_date']     = $nowFormatted;
				$row['name']              = (string) $obj->Name;
				$row['name2']             = (string) $obj->Name2;
				$row['department_number'] = (string) $obj->No;

				if ($row['name'] == '')
				{
					$row['name'] = Text::_('COM_REDSHOPB_DEPARTMENT');
				}

				// New sync status = 2 for old records and 3 for new records (to rebuild ACL on them after asset tree is built)
				$departmentSyncStatus = 2;

				if (!$table->id)
				{
					$departmentSyncStatus = 3;
				}

				// New data
				if (!$table->save($row))
				{
					throw new Exception($table->getError());
				}

				// Records sync record with status = 2 or 3, for further processing
				$this->recordSyncedId(
					$this->syncName, (string) $obj->ID, $table->id, (string) $obj->BelongstoDepartmentID, $isNew, $departmentSyncStatus, $md5Row
				);

				if (microtime(1) - $this->start >= 25)
				{
					$goToNextPart = true;
					break;
				}
			}

			// After processing all records, rebuilds tree structure and ACL
			if (!$goToNextPart)
			{
				// Set the new right location for child tables, rebuilding ACL for them
				$query->clear()
					->select('COUNT(*)')
					->from($db->qn('#__redshopb_sync'))
					->where('reference = ' . $db->q($this->syncName))
					->where('execute_sync IN (2, 3)')
					->order('remote_parent_key');
				$db->setQuery($query);

				$counter2  = $countInXml - (int) $db->loadResult();
				$counter2 += $this->locateDepartments(null);
				$counter2 += $this->locateDepartments('');

				if ($counter2 < $countInXml)
				{
					$goToNextPart = true;
				}
			}

			// In last part if some sync departments not exists in new sync -> delete it
			if (!$goToNextPart)
			{
				$query->clear()
					->select('local_id')
					->from($db->qn('#__redshopb_sync'))
					->where('reference = ' . $db->q($this->syncName))
					->where('execute_sync = 1');

				$results = $db->setQuery($query)->loadColumn();

				if ($results)
				{
					$table = RTable::getInstance('Department', 'RedshopbTable')
						->setOption('forceWebserviceUpdate', true)
						->setOption('lockingMethod', 'Sync');

					foreach ($results as $result)
					{
						$childIds = array();

						if ($table->load($result))
						{
							$childs = $table->getChildrenIds($result);

							if (!is_array($childs))
							{
								$childs = array($childs);
							}

							$childIds = array_merge($childIds, $childs);

							if (!$table->delete($result, true))
							{
								throw new Exception($table->getError());
							}
						}

						if (count($childIds))
						{
							$query->clear()
								->delete($db->qn('#__redshopb_sync'))
								->where('reference = ' . $db->q($this->syncName))
								->where('local_id IN (' . implode(',', $childIds) . ')');

							$db->setQuery($query)->execute();
						}
					}

					$query->clear()
						->delete($db->qn('#__redshopb_sync'))
						->where('reference = ' . $db->q($this->syncName))
						->where('execute_sync = 1');

					$db->setQuery($query)->execute();
				}

				$query->clear()
					->update($db->qn('#__redshopb_cron'))
					->set('execute_sync = 0')
					->where('name = ' . $db->q('GetDepartment'));

				$db->setQuery($query)->execute();
			}

			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$db->transactionRollback();
			RedshopbHelperSync::addMessage($e->getMessage(), 'error');

			return false;
		}

		if ($goToNextPart)
		{
			RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_FENGEL_GOTO_NEXT_PART', $counter2, $countInXml));

			return array('parts' => $countInXml - $counter2, 'total' => $countInXml);
		}
		else
		{
			RedshopbHelperSync::addMessage(Text::_('PLG_RB_SYNC_FENGEL_SYNCHRONIZE_SUCCESS'), 'success');

			return true;
		}
	}

	/**
	 * Function for locating departments in order (recursively) in department tree table
	 *
	 * @param   string  $parentId  Remote ID of parent company
	 *
	 * @throws  Exception
	 *
	 * @return  integer  Number of departments located
	 */
	protected function locateDepartments($parentId = null)
	{
		$db      = Factory::getDBO();
		$counter = 0;

		// Set the new right location for child tables, rebuilding ACL for them
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__redshopb_sync'))
			->where('reference = ' . $db->q($this->syncName))
			->where('execute_sync IN (2, 3)');

		if ($parentId === null)
		{
			$query->where('remote_parent_key = ' . $db->q(''));
		}
		elseif ($parentId !== '')
		{
			$query->where('remote_parent_key = ' . $db->q($parentId));
		}
		else
		{
			$query->where('remote_parent_key <> ' . $db->q(''));
		}

		$recordedDepartments = $db->setQuery($query)->loadObjectList();

		if ($recordedDepartments)
		{
			foreach ($recordedDepartments as $recordedDepartment)
			{
				if ($recordedDepartment->remote_key !== '')
				{
					$counter += $this->locateDepartments($recordedDepartment->remote_key);
				}

				if (microtime(1) - $this->start >= 25)
				{
					return $counter;
				}

				$counter++;

				$table = RTable::getInstance('Department', 'RedshopbTable')
					->setOption('forceWebserviceUpdate', true)
					->setOption('lockingMethod', 'Sync');

				$table->load($recordedDepartment->local_id);

				if ($recordedDepartment->remote_parent_key != '')
				{
					$parentId = $this->findSyncedId($this->syncName, $recordedDepartment->remote_parent_key);

					if (!$parentId)
					{
						RedshopbHelperSync::addMessage(
							Text::sprintf(
								'PLG_RB_SYNC_FENGEL_UNKNOWN_BELONGS_TO_DEPARTMENT_ID',
								$recordedDepartment->remote_parent_key,
								$recordedDepartment->remote_key
							), 'warning'
						);

						$childIds = $table->getChildrenIds($recordedDepartment->local_id);

						if (!is_array($childIds))
						{
							$childIds = array($childIds);
						}

						if (!$table->delete($recordedDepartment->local_id, true))
						{
							throw new Exception($table->getError());
						}

						$query->clear()
							->delete($db->qn('#__redshopb_sync'))
							->where('reference = ' . $db->q($this->syncName))
							->where('local_id IN (' . implode(',', $childIds) . ')');

						$db->setQuery($query)->execute();
					}
				}
				else
				{
					$parentId = 1;
				}

				if (($parentId && $parentId != $table->parent_id))
				{
					// Reprocesses record, forcing it to alter its ACL.  Happens for relocated departments and for new ones (sync status = 3)
					$table->setLocation($parentId, 'last-child');
					$table->setOption('rebuildACL', true);
				}

				if ($recordedDepartment->execute_sync == 3)
				{
					$table->setOption('rebuildACL', true);
				}

				if (!$table->save(array('parent_id' => $parentId)))
				{
					throw new Exception($table->getError());
				}

				$query->clear()
					->update('#__redshopb_sync')
					->set('execute_sync = 0')
					->where('reference = ' . $db->q($this->syncName))
					->where('local_id = ' . $db->q($recordedDepartment->local_id));

				$db->setQuery($query)->execute();
			}
		}

		return $counter;
	}
}
