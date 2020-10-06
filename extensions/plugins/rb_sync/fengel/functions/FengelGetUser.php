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
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Mail\MailHelper;

require_once __DIR__ . '/base.php';
jimport('joomla.user.helper');

/**
 * GetUser function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelGetUser extends FengelFunctionBase
{
	/**
	 * @var string
	 */
	public $syncName = 'fengel.user';

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
		$start        = microtime(1);
		$goToNextPart = false;
		$db           = Factory::getDbo();
		$counter      = 0;
		$fullSync     = false;

		try
		{
			$db->transactionStart();

			if ($webserviceData->get('fullSync', 1) == 1)
			{
				$fullSync        = true;
				$currentDateTime = '';
			}
			else
			{
				$currentDateTime = $webserviceData->params->get('CurrentDateTime', '');
			}

			$xml = $this->client->getUser('', '', '', $currentDateTime);

			if (!is_object($xml))
			{
				throw new Exception(Text::_('PLG_RB_SYNC_FENGEL_FAILED_TO_FETCH_ITEMS'));
			}

			$config           = RedshopbEntityConfig::getInstance();
			$defaultCountryId = $config->getInt('default_country_id', 59);

			$query = $db->getQuery(true)
				->select(array('id', 'alpha2'))
				->from($db->qn('#__redshopb_country'));
			$db->setQuery($query);
			$countries = $db->loadObjectList('alpha2');

			$query->clear()
				->select('execute_sync')
				->from($db->qn('#__redshopb_cron'))
				->where('name = ' . $db->q('GetUser'));
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
					->where('name = ' . $db->q('GetUser'));

				$db->setQuery($query)->execute();

				$countExecuted = 0;
			}
			else
			{
				// Get list executed in previous sync items
				$query = $db->getQuery(true)
					->select(array('s.*'))
					->from($db->qn('#__redshopb_sync', 's'))
					->leftJoin($db->qn('#__redshopb_user', 'ru') . ' ON s.local_id = ru.id')
					->where('s.reference = ' . $db->q($this->syncName))
					->where('s.execute_sync IN (0,2)');
				$db->setQuery($query);
				$executed      = $db->loadObjectList('remote_key');
				$countExecuted = count($executed);
			}

			$table = RTable::getInstance('User', 'RedshopbTable')
				->setOption('forceWebserviceUpdate', true)
				->setOption('lockingMethod', 'Sync')
				->setOption('useTransaction', false);

			foreach ($xml->User as $obj)
			{
				$counter++;

				if ($countExecuted > 0 && isset($executed[(string) $obj->ID]))
				{
					continue;
				}

				$row   = array();
				$isNew = true;
				$table->reset();
				$now          = Date::getInstance();
				$nowFormatted = $now->toSql();
				$allData      = $this->findSyncedId($this->syncName, (string) $obj->ID, '', true);

				$objectFromMD5 = clone $obj;

				if (isset($objectFromMD5->CurrentDateTime))
				{
					unset($objectFromMD5->CurrentDateTime);
				}

				$md5Row = md5('9' . $objectFromMD5->asXML());

				if (!empty($allData))
				{
					if ($allData->serialize == $md5Row && !$fullSync)
					{
						$this->recordSyncedId($this->syncName, (string) $obj->ID, $allData->local_id, '', false, 0, $md5Row);

						if (microtime(1) - $start >= 25)
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
					$id                  = 0;
					$row['id']           = $id;
				}

				if ((string) $obj->STATUS == 'DELETED')
				{
					if (!empty($allData))
					{
						if ($table->get('joomla_user_id'))
						{
							$this->deleteUsers(array($table->get('joomla_user_id')));
						}

						$this->deleteSyncedId($this->syncName, (string) $obj->ID);
					}

					continue;
				}

				switch ((string) $obj->Role)
				{
					case 'Administrator':
						$row['role_type_id'] = 2;
						break;
					case 'Head of Department':
						$row['role_type_id'] = 3;
						break;
					case 'Sales Person':
						$row['role_type_id'] = 4;
						break;
					case 'Purchaser':
						$row['role_type_id'] = 5;
						break;
					case 'Employee with login':
						$row['role_type_id'] = 6;
						break;
					case 'Employee':
					default:
						$row['role_type_id'] = 7;
						break;
				}

				$row['department_id'] = null;

				if ($row['role_type_id'] != 4)
				{
					$row['company_id'] = null;

					if ((string) $obj->BelongsToDepartmentID)
					{
						$row['department_id'] = $this->findSyncedId('fengel.department', (string) $obj->BelongsToDepartmentID);
					}

					if ((string) $obj->EndCustomer)
					{
						$row['company_id'] = $this->findSyncedId('fengel.customer', (string) $obj->EndCustomer);
					}
					elseif ((string) $obj->CustomerNo)
					{
						$row['company_id'] = $this->findSyncedId('fengel.customer', (string) $obj->CustomerNo);
					}

					// New user doesn't have company relation - don't store user in DB
					if ($isNew && (!isset($row['company_id']) || !$row['company_id']))
					{
						RedshopbHelperSync::addMessage(
							Text::sprintf(
								'PLG_RB_SYNC_FENGEL_NOT_STORE_USER_WITHOUT_RELATE', (string) $obj->ID
							),
							'warning'
						);

						$this->recordSyncedId($this->syncName, (string) $obj->ID, (string) $obj->ID, '', $isNew, 2, $md5Row);

						continue;
					}

					if (isset($countries[(string) $obj->CountryCode]))
					{
						$row['country_id'] = $countries[(string) $obj->CountryCode]->id;
					}
					else
					{
						$row['country_id'] = $defaultCountryId;
					}

					$row['address_name']    = trim((string) $obj->Name . ' ' . (string) $obj->Name2);
					$row['employee_number'] = trim((string) $obj->No);
					$row['city']            = (string) $obj->City;
					$row['zip']             = (string) $obj->ZipCode;
					$row['address']         = (string) $obj->Address;
					$row['address2']        = (string) $obj->Address2;
					$row['phone']           = (string) $obj->PhoneNo;
					$row['cell_phone']      = (string) $obj->CellNo;

					if ((string) $obj->CountryCode == '' || $row['address'] == '' || $row['zip'] == '' || $row['city'] == '')
					{
						$row['deleteIfEmptyDefaultAddress'] = true;
					}
				}
				else
				{
					$row['phone']      = (string) $obj->PhoneNo;
					$row['company_id'] = 2;
				}

				$row['modified_date'] = $nowFormatted;
				$row['name1']         = (string) $obj->Name;
				$row['name2']         = (string) $obj->Name2;
				$row['printed_name']  = (string) $obj->PrintedName;

				if (trim($row['name1']) == '' && trim($row['name2']) == '')
				{
					$row['name1'] = 'name';
				}

				$row['username'] = trim((string) $obj->UserName);

				// Each user not Employee without login
				if ($row['role_type_id'] != 7)
				{
					if (preg_match("#[<>\"'%;()&]#i", $row['username']) || strlen(utf8_decode($row['username'])) < 2)
					{
						$row['username'] = str_replace($this->replaceSymbols, '_', trim((string) $obj->UserName));

						if (preg_match("#[<>\"'%;()&]#i", $row['username']) || strlen(utf8_decode($row['username'])) < 2)
						{
							$row['username'] = 'username';
						}

						$row['username'] = $this->getUnique('u.username', $row['username'], $id);
						RedshopbHelperSync::addMessage(
							Text::sprintf(
								'PLG_RB_SYNC_FENGEL_NOT_VALID_USERNAME_AZ09', 2, trim((string) $obj->UserName), (string) $obj->ID, $row['username']
							), 'notice'
						);
					}
					else
					{
						$row['username'] = $this->getUnique('u.username', $row['username'], $id);

						if ($row['username'] != trim((string) $obj->UserName))
						{
							RedshopbHelperSync::addMessage(
								Text::sprintf(
									'PLG_RB_SYNC_FENGEL_USERNAME_EXISTS', trim((string) $obj->UserName), (string) $obj->ID, $row['username']
								), 'notice'
							);
						}
					}

					if ((string) $obj->Active == 'true')
					{
						$row['userStatus'] = 1;
					}
					else
					{
						$row['userStatus'] = 0;
					}

					if (trim((string) $obj->Password))
					{
						$row['password']  = (string) $obj->Password;
						$row['password2'] = (string) $obj->Password;
					}
					elseif ($isNew)
					{
						RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_FENGEL_USER_PASSWORD_EMPTY', (string) $obj->ID), 'notice');
						$password        = UserHelper::genRandomPassword();
						$row['password'] = $password;

						$row['password2'] = $password;
					}

					$userMail = str_replace(' ', '', trim(strtolower((string) $obj->Email)));

					if ($userMail != '' && MailHelper::isEmailAddress($userMail))
					{
						$userMailArray = explode('@', $userMail);
						$row['email']  = $this->getUnique('u.email', $userMailArray, $id, true);

						if ($row['email'] != $userMail)
						{
							RedshopbHelperSync::addMessage(
								Text::sprintf('PLG_RB_SYNC_FENGEL_USERMAIL_EXISTS', $userMail, (string) $obj->ID, $row['email']), 'notice'
							);
						}
					}
					else
					{
						$userMailArray = array('noreply', 'f-engel.com');
						$row['email']  = $this->getUnique('u.email', $userMailArray, $id, true);
						RedshopbHelperSync::addMessage(
							Text::sprintf('PLG_RB_SYNC_FENGEL_USERMAIL_IS_EMPTY', (string) $obj->ID, $row['email']), 'notice'
						);
					}
				}
				else
				{
					if (!$row['username'])
					{
						$row['username'] = 'username';
					}

					$row['username'] = $this->getUnique('u.username', $row['username'], $id);

					if ($isNew)
					{
						$password        = UserHelper::genRandomPassword();
						$row['password'] = $password;

						$row['password2'] = $password;
					}

					$userMail = str_replace(' ', '', trim(strtolower((string) $obj->Email)));

					if ($userMail != '' && MailHelper::isEmailAddress($userMail))
					{
						$userMailArray = explode('@', $userMail);
						$row['email']  = $this->getUnique('u.email', $userMailArray, $id, true);
					}
					else
					{
						$userMailArray = array('noreply', 'f-engel.com');
						$row['email']  = $this->getUnique('u.email', $userMailArray, $id, true);
					}
				}

				if ((string) $obj->Active == 'true')
				{
					$row['userStatus'] = 1;
				}
				else
				{
					$row['userStatus'] = 0;
				}

				$table->set('password', '');
				$table->set('password2', '');

				if (!$table->save($row))
				{
					throw new Exception($table->getError());
				}

				$this->recordSyncedId($this->syncName, (string) $obj->ID, $table->id, '', $isNew, 0, $md5Row);

				if (microtime(1) - $start >= 25)
				{
					$goToNextPart = true;
					break;
				}
			}

			// In last part if some sync users not exists in new sync -> delete it
			if (!$goToNextPart)
			{
				if ($currentDateTime == '')
				{
					$query->clear()
						->select('ru.joomla_user_id')
						->from($db->qn('#__redshopb_user', 'ru'))
						->leftJoin($db->qn('#__redshopb_sync', 's') . ' ON ru.id = s.local_id')
						->where('s.reference = ' . $db->q($this->syncName))
						->where('s.execute_sync = 1');

					$results = $db->setQuery($query)->loadColumn();

					if ($results)
					{
						$this->deleteUsers($results);

						$query->clear()
							->delete($db->qn('#__redshopb_sync'))
							->where('reference = ' . $db->q($this->syncName))
							->where('execute_sync IN (1,2)');

						try
						{
							$db->setQuery($query)->execute();
						}
						catch (Exception $e)
						{
							return false;
						}
					}
				}
				else
				{
					$query->clear()
						->update($db->qn('#__redshopb_sync'))
						->set('execute_sync = 0')
						->where('reference = ' . $db->q($this->syncName))
						->where('execute_sync = 1');

					$db->setQuery($query)->execute();
				}

				$attributes      = current($xml->attributes());
				$currentDateTime = (string) $attributes['DateTime'];

				$params   = array('CurrentDateTime' => $currentDateTime, 'can_use_full_sync' => 1);
				$registry = new Registry;

				$registry->loadArray($params);
				$stringParams = (string) $registry;

				$query->clear()
					->update($db->qn('#__redshopb_cron'))
					->set('execute_sync = 0')
					->set('params = ' . $db->q($stringParams))
					->where('name = ' . $db->q('GetUser'));

				$db->setQuery($query)->execute();
			}

			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$db->transactionRollback();

			if ($e->getMessage())
			{
				RedshopbHelperSync::addMessage($e->getMessage(), 'error');
			}

			return false;
		}

		if ($goToNextPart)
		{
			$countInXml = count($xml->User);
			RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_FENGEL_GOTO_NEXT_PART', $counter, $countInXml));

			return array('parts' => $countInXml - $counter, 'total' => $countInXml);
		}
		else
		{
			RedshopbHelperSync::addMessage(Text::_('PLG_RB_SYNC_FENGEL_SYNCHRONIZE_SUCCESS'), 'success');

			return true;
		}
	}
}
