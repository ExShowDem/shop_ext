<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Helpers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * Manufacturer helper.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Helpers
 * @since       1.6.51
 */
final class RedshopbHelperManufacturer
{
	/**
	 * Get image thumb html
	 *
	 * @param   int      $id             ID of manufacturer
	 * @param   boolean  $forceImage     Sets a dummy image if an actual image is not found
	 * @param   boolean  $setDimensions  Set image dimensions?
	 * @param   int      $width          If setDimensions this will be used for width
	 * @param   int      $height         If setDimensions this will be used for height
	 *
	 * @return  string  Thumbnail html
	 */
	public static function getImageThumbHtml($id, $forceImage = false, $setDimensions = false, $width = 144, $height = 144)
	{
		if (!$id)
		{
			return false;
		}

		$manufacturer = RedshopbEntityManufacturer::load($id);

		if (!$manufacturer->get('image'))
		{
			if ($forceImage)
			{
				return RedshopbHelperMedia::drawDefaultImg($width, $height, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL'), '#999999', '#dfdfdf');
			}

			return '';
		}

		if ($setDimensions)
		{
			if (empty($width) || empty($height))
			{
				// Thumbnail preparation
				$config = RedshopbEntityConfig::getInstance();
				$width  = empty($width) ? $config->getThumbnailWidth() : $width;
				$height = empty($height) ? $config->getThumbnailHeight() : $height;
			}
		}

		$image = RedshopbHelperThumbnail::originalToResize($manufacturer->get('image'), $width, $height, 100, 0, 'manufacturers');

		if ($image === false)
		{
			$thumb = RedshopbHelperMedia::drawDefaultImg($width, $height, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL'), '#999999', '#dfdfdf');
		}
		else
		{
			$thumb = HTMLHelper::_('image', $image, $manufacturer->get('name'));
		}

		return $thumb;
	}

	/**
	 * Get products count for specific manufacuters
	 *
	 * @param   array  $manufacturerIds  Array Id tags
	 * @param   int    $categoryId       Category id
	 * @param   array  $productIds       Array of product Id
	 *
	 * @return  mixed
	 */
	public static function getProductsCounts($manufacturerIds, $categoryId = 0, $productIds = array())
	{
		if (!is_array($manufacturerIds))
		{
			$manufacturerIds = array($manufacturerIds);
		}

		$manufacturerIds = ArrayHelper::toInteger($manufacturerIds);

		$db       = Factory::getDbo();
		$subQuery = $db->getQuery(true)
			->select('COUNT(p.id) AS count, p.manufacturer_id')
			->from($db->qn('#__redshopb_product', 'p'))
			->where($db->qn('p.state') . ' = 1')
			->where('(p.publish_date = ' . $db->q($db->getNullDate()) . ' OR p.publish_date <= STR_TO_DATE('
				. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where('(p.unpublish_date = ' . $db->q($db->getNullDate()) . ' OR p.unpublish_date >= STR_TO_DATE('
				. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where($db->qn('p.service') . ' = 0')
			->group('p.manufacturer_id');

		if ($categoryId)
		{
			$subQuery->leftJoin($db->qn('#__redshopb_product_category_xref', 'pcx') . ' ON ' . $db->qn('pcx.product_id') . ' = ' . $db->qn('p.id'))
				->where($db->qn('pcx.category_id') . ' = ' . (int) $categoryId);
		}

		if ($productIds)
		{
			if (!is_array($productIds))
			{
				$productIds = array($productIds);
			}

			$productIds = ArrayHelper::toInteger($productIds);

			$subQuery->where($db->qn('p.id') . ' IN (' . implode(',', $productIds) . ')');
		}

		$query = $db->getQuery(true)
			->select('pm.manufacturer_id, pm.count')
			->from($db->qn('#__redshopb_manufacturer', 'm'))
			->leftJoin('(' . $subQuery . ') AS pm ON pm.manufacturer_id = m.id')
			->where('m.id IN (' . implode(',', $manufacturerIds) . ')');

		$oldTranslate  = $db->translate;
		$db->translate = false;
		$result        = $db->setQuery($query)->loadObjectList('manufacturer_id');
		$db->translate = $oldTranslate;

		return $result;
	}

	/**
	 * Return list first chars of manufacturers which are has product related with.
	 *
	 * @param   int     $categoryId    primary key of a category, used for category filtering
	 * @param   int     $customerId    the id of the currently customer
	 * @param   string  $customerType  the type of customer
	 *
	 * @return  array     List of available chars.
	 *
	 * @deprecated Use RedshopbModelManufacturers::getFirstCharAvailable instead
	 */
	public static function getFirstCharAvailable($categoryId = 0, $customerId = null, $customerType = 'employee')
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('UPPER(LEFT(' . $db->qn('m.name') . ', 1)) AS ' . $db->qn('char'))
			->from($db->qn('#__redshopb_manufacturer', 'm'))
			->where($db->qn('m.state') . ' = 1');

		if (!empty($customerId) && !empty($categoryId))
		{
			$companyId = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);

			$allChildrenWithProducts = RedshopbHelperACL::listAvailableCategories(
				Factory::getUser()->id, $categoryId, 100, $companyId
			);

			$subQuery = $db->getQuery(true);
			$subQuery->select('COUNT(p.id)')
				->from($db->qn('#__redshopb_product', 'p'))
				->leftJoin($db->qn('#__redshopb_product_category_xref', 'pcx') . ' ON ' . $db->qn('pcx.product_id') . ' = ' . $db->qn('p.id'))
				->where($db->qn('p.manufacturer_id') . ' = ' . $db->qn('m.id'))
				->where($db->qn('p.state') . ' = 1')
				->where('(p.publish_date = ' . $db->q($db->getNullDate()) . ' OR p.publish_date <= STR_TO_DATE('
					. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
				)
				->where('(p.unpublish_date = ' . $db->q($db->getNullDate()) . ' OR p.unpublish_date >= STR_TO_DATE('
					. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
				)
				->where($db->qn('p.service') . ' = 0')
				->where($db->qn('pcx.category_id') . ' IN (' . (int) $categoryId . ',' . $allChildrenWithProducts . ')');

			$query->where('(' . $subQuery . ') <> 0');
		}

		// Remove number from result and exclude ROOT tree.
		$query->where($db->qn('m.name') . ' NOT REGEXP ' . $db->quote('^[0-9]'))
			->where($db->qn('m.level') . ' > 0')
			->group($db->qn('char'));

		return $db->setQuery($query)->loadColumn();
	}

	/**
	 * Method for get list of manufacturer categories
	 *
	 * @return  array  List of manufacturer categories
	 */
	public static function getManufacturerCategories()
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select($db->qn('category'))
			->from($db->qn('#__redshopb_manufacturer'))
			->where($db->qn('category') . ' IS NOT NULL')
			->group($db->qn('category'))
			->order($db->qn('category'));

		return $db->setQuery($query)->loadColumn();
	}
}
