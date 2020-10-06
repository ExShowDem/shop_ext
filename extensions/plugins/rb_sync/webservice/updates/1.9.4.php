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

/**
 * Custom upgrade of Redshop b2b.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Upgrade
 * @since       1.6
 */
class WebserviceUpdateScript_1_9_4
{
	/**
	 * Performs the upgrade for this version
	 *
	 * @return  boolean
	 */
	public function execute()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('s.local_id, s.metadata, s.reference, c.*')
			->from($db->qn('#__redshopb_sync', 's'))
			->leftJoin($db->qn('#__redshopb_category', 'c') . ' ON c.id = s.local_id')
			->where('s.reference IN (' . $db->q('erp.webservice.categories') . ', ' . $db->q('pim.related_parent_reference.category') . ')');

		$results = $db->setQuery($query)
			->loadObjectList();

		if ($results)
		{
			foreach ($results as $result)
			{
				$metaData = $result->metadata;

				if ($metaData)
				{
					$metaData = unserialize($metaData);

					// Reset user override flags
					$metaData['WSFlags'] = array();

					foreach ($metaData['WSProperties'] as $name => $WSProperty)
					{
						if (property_exists($result, $name))
						{
							$metaData['WSProperties'][$name] = $result->{$name};
						}
					}

					$metaData = serialize($metaData);

					$query->clear()
						->update($db->qn('#__redshopb_sync'))
						->set('metadata = ' . $db->q($metaData))
						->set('serialize = ' . $db->q(''))
						->set('hash_key = ' . $db->q(''))
						->where('reference = ' . $db->q($result->reference))
						->where('local_id = ' . $db->q($result->local_id));

					try
					{
						$db->setQuery($query)->execute();
					}
					catch (Exception $e)
					{
						Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

						return false;
					}
				}
			}
		}

		return true;
	}
}
