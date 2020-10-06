<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Upgrade
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

JLoader::import('redshopb.library');
RTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_redshopb/tables');

/**
 * Custom upgrade of Redshop b2b.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Upgrade
 * @since       1.12.31
 */
class Com_RedshopbUpdateScript_1_12_31
{
	/**
	 * Performs the upgrade for this version
	 *
	 * @return  boolean
	 */
	public function execute()
	{
		$this->updateField('Cron', array('start_time', 'finish_time', 'next_start'));

		$this->updateField('Offer', array('expiration_date', 'order_date', 'execution_date', 'sent_date', 'requested_date'));

		$this->updateField('Cart', 'last_order');

		$this->updateField('Product_Price', array('starting_date', 'ending_date'));

		$this->updateField('Product_Discount', array('ending_date', 'starting_date'));

		$this->updateField('Wallet', array('end_date', 'start_date'));

		return true;
	}

	/**
	 * Update field
	 *
	 * @param   string  $tableName   Table name
	 * @param   array   $fieldNames  Field name
	 *
	 * @return  void
	 */
	public function updateField($tableName, $fieldNames)
	{
		$offset = Factory::getApplication()->get('offset');

		if ($offset)
		{
			$dateTimeZone   = new DateTimeZone($offset);
			$dateTimeConfig = new DateTime("now", $dateTimeZone);
			$timeOffset     = $dateTimeConfig->getOffset();

			if ($timeOffset)
			{
				$db         = Factory::getDbo();
				$fieldNames = (array) $fieldNames;
				$table      = RTable::getInstance($tableName, 'RedshopbTable');

				$references = array();

				foreach ((array) $table->get('wsSyncMapPK', array()) as $wsSyncMapPK)
				{
					if (is_array($wsSyncMapPK))
					{
						foreach ($wsSyncMapPK as $value)
						{
							if ($value)
							{
								$references[] = $db->q($value);
							}
						}
					}
				}

				if (!empty($references))
				{
					$query = $db->getQuery(true)
						->select('s.local_id, s.metadata, s.reference')
						->from($db->qn('#__redshopb_sync', 's'))
						->leftJoin($db->qn('#__' . $table->get('_tableName'), 'f') . ' ON f.id = s.local_id')
						->where('s.reference IN (' . implode(',', $references) . ')');

					foreach ($fieldNames as $fieldName)
					{
						$query->select($db->qn('f.' . $fieldName));
					}

					$results = $db->setQuery($query)
						->loadObjectList();

					if ($results)
					{
						foreach ($results as $result)
						{
							$metaData = $result->metadata;

							if ($metaData)
							{
								$metaData     = unserialize($metaData);
								$changesFound = false;

								foreach ($fieldNames as $fieldName)
								{
									if (array_key_exists($fieldName, $metaData['WSProperties'])
										&& $metaData['WSProperties'][$fieldName]
										&& $metaData['WSProperties'][$fieldName] != $db->getNullDate()
										&& $metaData['WSProperties'][$fieldName] != null)
									{
										$metaData['WSProperties'][$fieldName] = Factory::getDate(
											$metaData['WSProperties'][$fieldName], $offset
										)->toSql();
										$changesFound                         = true;
									}
								}

								if ($changesFound)
								{
									$metaData = serialize($metaData);
									$query->clear()
										->update($db->qn('#__redshopb_sync'))
										->set('metadata = ' . $db->q($metaData))
										->where('reference = ' . $db->q($result->reference))
										->where('local_id = ' . $db->q($result->local_id));

									try
									{
										$db->setQuery($query)->execute();
									}
									catch (Exception $e)
									{
										Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

										break;
									}
								}
							}
						}
					}
				}

				foreach ($fieldNames as $fieldName)
				{
					$query = $db->getQuery(true)
						->update($db->qn('#__' . $table->get('_tableName')))
						->where($db->qn($fieldName) . ' != ' . $db->q($db->getNullDate()))
						->where($db->qn($fieldName) . ' != NULL')
						->set($db->qn($fieldName) . ' = DATE_SUB(' . $db->qn($fieldName) . ', INTERVAL ' . (int) $timeOffset . ' SECOND)');

					try
					{
						$db->setQuery($query)->execute();
					}
					catch (Exception $e)
					{
						Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

						break;
					}
				}
			}
		}
	}
}
