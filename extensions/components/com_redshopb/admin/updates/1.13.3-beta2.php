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
 * @since       1.13.3
 */
class Com_RedshopbUpdateScript_1_13_3_Beta2
{
	/**
	 * Performs the upgrade for this version
	 *
	 * @return  boolean
	 *
	 * @since   1.13.3
	 */
	public function executeAfterUpdate()
	{
		try
		{
			$app = Factory::getApplication();
		}
		catch (\Exception $exception)
		{
			return false;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id')
			->from($db->qn('#__redshopb_field'))
			->where('type_id IN (19, 20)', 'OR')
			->where('filter_type_id IN (19, 20)', 'OR');
		$db->setQuery($query);

		$fields = $db->loadColumn(0);

		if (!$fields)
		{
			return true;
		}

		/** @var RedshopbModelField $model */
		$model = RedshopbModel::getFrontInstance('Field');

		if (!$model->delete($fields))
		{
			$app->enqueueMessage('Error deleting fields from types 19/20 (old ranges): ' . $model->getError(), 'error');

			return false;
		}

		$app->enqueueMessage('Deleted ' . count($fields) . ' fields from types 19/20 (old ranges): ' . $model->getError());

		return true;
	}
}
