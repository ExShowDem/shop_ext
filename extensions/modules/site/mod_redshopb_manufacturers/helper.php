<?php
/**
 * @package     Aesir.E-Commerce.Site
 * @subpackage  mod_redshopb_manufacturers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

/**
 * Helper for mod_redshopb_manufacturers
 *
 * @package     Aesir.E-Commerce.Site
 * @subpackage  mod_redshopb_manufacturers
 * @since       1.6.70
 */
class ModRedshopbManufacturersHelper
{
	/**
	 * Get a list of manufacturers
	 *
	 * @param   Registry  $params  The module options.
	 *
	 * @return  null|object
	 */
	public static function getList(&$params)
	{
		$db    = Factory::getDbo();
		$app   = Factory::getApplication();
		$query = $db->getQuery(true)
			->select('m2.*');

		$subQuery = $db->getQuery(true)
			->select('DISTINCT(m.id)')
			->from($db->qn('#__redshopb_manufacturer', 'm'))
			->where($db->qn('m.image') . ' IS NOT NULL');

		$subQuery->leftJoin(
			$db->qn('#__redshopb_product', 'pManuf')
				. ' ON ' . $db->qn('pManuf.manufacturer_id') . ' = ' . $db->qn('m.id')
		)
			->where('pManuf.service = 0')
			->where('pManuf.state = 1')
			->where('m.state = 1');

		$customerType = $app->getUserState('shop.customer_type', '');
		$customerId   = $app->getUserState('shop.customer_id', 0);

		$companyId           = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);
		$availableCategories = RedshopbHelperACL::listAvailableCategories(Factory::getUser()->id, false, 100, $companyId, false, 'comma', '');
		$availableCategories = explode(',', $availableCategories);

		$subQuery->leftJoin(
			$db->qn('#__redshopb_product_category_xref', 'cref')
				. ' ON ' . $db->qn('cref.product_id') . ' = ' . $db->qn('pManuf.id')
		)
			->where($db->qn('cref.category_id') . ' IN (' . implode(',', $availableCategories) . ')');

		switch ($params->get('mode', 'RANDOM'))
		{
			case 'FEATURED':
				$subQuery->where($db->qn('m.featured') . ' = 1');
				break;
			case 'RANDOM_FEATURED':
				$subQuery->where($db->qn('m.featured') . ' = 1');
				$subQuery->order('RAND()');
				break;
			case 'RANDOM_BUT_FEATURED':
				$subQuery->where($db->qn('m.featured') . ' = 0');
				$subQuery->order('RAND()');
				break;
			case 'RANDOM':
			default:
				$subQuery->order('RAND()');
				break;
		}

		$limit = (int) $params->get('display_count', 6);
		$query->from('(' . $subQuery . ' LIMIT ' . (int) $limit . ') as m1')
			->innerJoin($db->qn('#__redshopb_manufacturer', 'm2') . ' ON m1.id = m2.id');

		$manufacturers = $db->setQuery($query, 0, $limit)
			->loadObjectList();

		if ($manufacturers && count($manufacturers))
		{
			$config = RedshopbEntityConfig::getInstance();
			$width  = (int) $params->get('thumbnail_width', $config->getThumbnailWidth());
			$height = (int) $params->get('thumbnail_height', $config->getThumbnailHeight());

			foreach ($manufacturers as $i => $manufacturer)
			{
				RedshopbEntityManufacturer::getInstance($manufacturer->id)
					->bind($manufacturer);

				if (empty($manufacturer->image))
				{
					continue;
				}

				$manufacturers[$i]->imagehtml = RedshopbHelperManufacturer::getImageThumbHtml($manufacturer->id, false, true, $width, $height);
			}
		}

		return $manufacturers;
	}
}
