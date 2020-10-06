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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

/**
 * Tag helper.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Helpers
 * @since       1.6
 */
final class RedshopbHelperTag
{
	/**
	 * Tags cache
	 *
	 * @var  array
	 */
	private static $tags = array();

	/**
	 * Method for return how many times this tag has been tagged to an product.
	 *
	 * @param   integer  $tagId  ID of tag
	 *
	 * @return  integer          Number of count if success. False otherwise.
	 */
	public static function getTagCount($tagId)
	{
		$tagId = (int) $tagId;

		if (!$tagId)
		{
			return 0;
		}

		$db = RFactory::getDbo();

		$query = $db->getQuery(true)
			->select('COUNT(*) AS ' . $db->qn('count'))
			->from($db->qn('#__redshopb_product_tag_xref'))
			->where($db->qn('tag_id') . ' = ' . $tagId);
		$db->setQuery($query);
		$result = $db->loadObject();

		return $result->count;
	}

	/**
	 * Get Tags Counts
	 *
	 * @param   array  $tagKeys     Array Id tags
	 * @param   int    $categoryId  Category id
	 * @param   array  $productIds  Array of product Id
	 *
	 * @return  mixed
	 */
	public static function getTagsCounts($tagKeys = array(), $categoryId = 0, $productIds = array())
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(array('COUNT(' . $db->qn('ptx.product_id') . ') AS ' . $db->qn('count'), 'ptx.tag_id'))
			->from($db->qn('#__redshopb_product_tag_xref', 'ptx'))
			->leftJoin($db->qn('#__redshopb_product', 'p') . ' ON ' . $db->qn('p.id') . ' = ' . $db->qn('ptx.product_id'))
			->where($db->qn('p.state') . ' = 1')
			->where('(p.publish_date = ' . $db->q($db->getNullDate()) . ' OR p.publish_date <= STR_TO_DATE('
				. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where('(p.unpublish_date = ' . $db->q($db->getNullDate()) . ' OR p.unpublish_date >= STR_TO_DATE('
				. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where($db->qn('p.service') . ' = 0')
			->where($db->qn('ptx.tag_id') . ' IN (' . implode(',', $tagKeys) . ')')
			->group($db->qn('ptx.tag_id'));

		if ($categoryId)
		{
			$query->leftJoin($db->qn('#__redshopb_product_category_xref', 'pcx') . ' ON pcx.product_id = p.id')
				->where('pcx.category_id = ' . (int) $categoryId);
		}

		if ($productIds)
		{
			if (!is_array($productIds))
			{
				$productIds = array($productIds);
			}

			$productIds = ArrayHelper::toInteger($productIds);

			$query->where($db->qn('p.id') . ' IN (' . implode(',', $productIds) . ')');
		}

		$oldTranslate  = $db->translate;
		$db->translate = false;
		$result        = $db->setQuery($query)->loadObjectList('tag_id');
		$db->translate = $oldTranslate;

		return $result;
	}

	/**
	 * Get related products
	 *
	 * @param   int  $productId     Product Id
	 * @param   int  $limit         Limit product selection
	 * @param   int  $collectionId  Collection id
	 *
	 * @return  array|null
	 */
	public static function getRelatedProducts($productId, $limit = 0, $collectionId = 0)
	{
		$relateMode = (int) RedshopbEntityConfig::getInstance()->get('relate_mode', 0);

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('p.id')
			->from($db->qn('#__redshopb_product', 'p'))
			->where($db->qn('p.state') . ' = 1')
			->where($db->qn('p.discontinued') . ' != 1')
			->where('(p.publish_date = ' . $db->q($db->getNullDate()) . ' OR p.publish_date <= STR_TO_DATE('
				. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where('(p.unpublish_date = ' . $db->q($db->getNullDate()) . ' OR p.unpublish_date >= STR_TO_DATE('
				. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where($db->qn('p.service') . ' = 0')
			->where('p.id != ' . (int) $productId)
			->group('p.id');

		$union = array();

		if ($relateMode === 0 || $relateMode === 1)
		{
			$q = $db->getQuery(true)
				->select('a.product_id')
				->from('#__redshopb_product_category_xref AS a')
				->leftJoin('#__redshopb_product_category_xref AS b ON(a.category_id=b.category_id)')
				->where('b.product_id = ' . (int) $productId);

			$whereQuery = clone $query;
			$whereQuery->where('p.id IN(' . $q . ')');
			$union[] = $whereQuery->__toString();
		}

		if ($relateMode === 0 || $relateMode === 2)
		{
			$q = $db->getQuery(true)
				->select('ptx.product_id')
				->from($db->qn('#__redshopb_product_tag_xref', 'ptx'))
				->leftJoin($db->qn('#__redshopb_tag', 't') . ' ON t.id = ptx.tag_id')
				->leftJoin($db->qn('#__redshopb_product_tag_xref', 'ptx2') . ' ON t.id = ptx2.tag_id')
				->where('ptx2.product_id = ' . (int) $productId)
				->where('p.id = ptx.product_id')
				->where('t.state = 1');

			$whereQuery = clone $query;
			$whereQuery->where('p.id IN(' . $q . ')');
			$union[] = $whereQuery->__toString();
		}

		// Collection assignment
		if (($relateMode === 0 || $relateMode === 3) && $collectionId)
		{
			$q = $db->getQuery(true)
				->select('wpx.product_id')
				->from($db->qn('#__redshopb_collection_product_xref', 'wpx'))
				->where($db->qn('wpx.product_id') . ' = ' . $db->qn('p.id'))
				->where($db->qn('wpx.state') . ' = 1')
				->innerJoin(
					$db->qn('#__redshopb_collection', 'w')
					. ' ON ' . $db->qn('w.id') . ' = ' . $db->qn('wpx.collection_id')
				)
				->where($db->qn('w.id') . ' = ' . (int) $collectionId)
				->where($db->qn('w.state') . ' = 1');

			$whereQuery = clone $query;
			$whereQuery->where('p.id IN(' . $q . ')');
			$union[] = $whereQuery->__toString();
		}

		if (empty($union))
		{
			return null;
		}

		$subQuery = $db->getQuery(true)
			->select('COUNT(data.id), data.id')
			->from('((' . implode(') union ALL(', $union) . '))  AS data')
			->where('data.id != ' . (int) $productId)
			->group('data.id')
			->order('COUNT(data.id) DESC')
			->setLimit((int) $limit);

		$query = $db->getQuery(true)
			->select('p2.*')
			->from('(' . $subQuery . ') as p1')
			->innerJoin($db->qn('#__redshopb_product', 'p2') . ' ON p1.id = p2.id')
			->order('RAND ()');

		$results = $db->setQuery($query, 0, $limit)->loadObjectList('id');

		return $results;
	}

	/**
	 * Get image thumb html
	 *
	 * @param   int      $tagId          Tag id
	 * @param   boolean  $setDimensions  Set image dimensions?
	 * @param   int      $width          If setDimensions this will be used for width
	 * @param   int      $height         If setDimensions this will be used for height
	 *
	 * @return  string  Thumbnail html
	 */
	public static function getTagImageThumbHtml($tagId, $setDimensions = false, $width = 144, $height = 144)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->qn('image'))
			->from($db->qn('#__redshopb_tag'))
			->where($db->qn('id') . ' = ' . $tagId);
		$image           = $db->setQuery($query)->loadResult();
		$thumb           = '';
		$imageAttributes = array();

		if ($setDimensions)
		{
			if (empty($width) || empty($height))
			{
				// Thumbnail preparation
				$config = RedshopbEntityConfig::getInstance();
				$width  = empty($width) ? $config->getThumbnailWidth() : $width;
				$height = empty($height) ? $config->getThumbnailHeight() : $height;
			}

			$imageAttributes = array('height' => $height, 'width' => $width);
		}

		if (!empty($image))
		{
			$image = RedshopbHelperThumbnail::originalToResize($image, $width, $height, 100, 0, 'tags');

			if ($image === false)
			{
				$thumb = RedshopbHelperMedia::drawDefaultImg($width, $height, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL'), '#999999', '#dfdfdf');
			}
			else
			{
				$thumb = HTMLHelper::_('image', $image, $image, $imageAttributes);
			}
		}
		else
		{
			$thumb = RedshopbHelperMedia::drawDefaultImg($width, $height, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL'), '#999999', '#dfdfdf');
		}

		return $thumb;
	}

	/**
	 * Method for get list type of tags
	 *
	 * @return  array    List type of tags
	 */
	public static function getTagTypes()
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select($db->qn('type'))
			->from($db->qn('#__redshopb_tag'))
			->where($db->qn('type') . ' IS NOT NULL')
			->group($db->qn('type'))
			->order($db->qn('type'));

		return $db->setQuery($query)->loadColumn();
	}

	/**
	 * Load tag
	 *
	 * @param   integer  $id  tag id
	 *
	 * @return  mixed    Tag object on success, false on failure.
	 */
	public static function loadTag($id)
	{
		if (!array_key_exists($id, self::$tags))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('t.*')
				->from($db->qn('#__redshopb_tag', 't'))
				->where($db->qn('t.id') . ' = ' . (int) $id);

			self::$tags[$id] = $db->setQuery($query)->loadObject();
		}

		return self::$tags[$id];
	}

	/**
	 * Return list first chars of tags which are has product related with.
	 *
	 * @param   string  $tagType  Type of tag. Will be ignore if not specific.
	 *
	 * @return  array             List of available chars.
	 */
	public static function getFirstCharProductAvailableTags($tagType = '')
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('UPPER(LEFT(' . $db->qn('t.name') . ', 1)) AS ' . $db->qn('char'))
			->from($db->qn('#__redshopb_tag', 't'))
			->leftJoin($db->qn('#__redshopb_product_tag_xref', 'ref') . ' ON ' . $db->qn('t.id') . ' = ' . $db->qn('ref.tag_id'))
			->where($db->qn('t.state') . ' = 1')
			->where($db->qn('t.name') . ' NOT REGEXP ' . $db->quote('^[0-9]')) // Remove number from result.
			->group($db->qn('char'));

		if (!empty($tagType))
		{
			$query->where($db->qn('type') . ' = ' . $db->quote($tagType));
		}

		return $db->setQuery($query)->loadColumn();
	}
}
