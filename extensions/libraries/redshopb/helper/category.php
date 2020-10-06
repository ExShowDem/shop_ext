<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
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
 * A Category helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperCategory
{
	/**
	 * Get image thumb html
	 *
	 * @param   int    $categoryId     Category id.
	 * @param   bool   $setDimensions  Set dimensions
	 * @param   int    $width          Image width
	 * @param   int    $height         Image height
	 * @param   bool   $forceImage     Force image return. In case where category doesn't have image, this check will force image return.
	 *                                 Function will then return image one of the category products or jholder image for non-image.
	 * @param   array  $collections    Collections used to filter the categories (if false, don't filter)
	 *
	 * @return  string  Thumbnail html
	 */
	public static function getCategoryImageThumbHtml(
		$categoryId, $setDimensions = false, $width = 144, $height = 144, $forceImage = false, $collections = null
	)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->qn('image'))
			->from($db->qn('#__redshopb_category'))
			->where($db->qn('id') . ' = ' . $categoryId);
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

			$imageAttributes = array('height' => $height, 'width' => $width, 'min-height' => $height);
		}

		if (!empty($image))
		{
			$image = RedshopbHelperThumbnail::originalToResize($image, $width, $height, 100, 0, 'categories');

			if ($image === false)
			{
				$thumb = RedshopbHelperMedia::drawDefaultImg($width, $height, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL'), '#999999', '#dfdfdf');
			}
			else
			{
				$thumb = HTMLHelper::_('image', $image, $image, $imageAttributes);
			}
		}
		elseif ($forceImage)
		{
			// Check for all n level category images if not then products
			$catimages     = array();
			$categoryTable = RTable::getAdminInstance('Category', array(), 'com_redshopb');
			$childrens     = $categoryTable->getTree($categoryId);

			foreach ($childrens as $children)
			{
				if ($children->image != '')
				{
					$catimages[] = $children->image;
				}
			}

			if (!empty($catimages))
			{
				$image = $catimages[array_rand($catimages)];
				$image = RedshopbHelperThumbnail::originalToResize($image, $width, $height, 100, 0, 'products', false);

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
				$query->clear()
					->select(
						array(
							$db->qn('m.name', 'name'),
							$db->qn('m.attribute_value_id', 'color'),
							$db->qn('m.remote_path', 'remote_path')
						)
					)
					->from($db->qn('#__redshopb_product', 'p'))
					->innerJoin($db->qn('#__redshopb_product_category_xref', 'pcx') . ' ON ' . $db->qn('p.id') . ' = ' . $db->qn('pcx.product_id'))
					->innerJoin($db->qn('#__redshopb_media', 'm') . ' ON ' . $db->qn('p.id') . ' = ' . $db->qn('m.product_id'))
					->where($db->qn('pcx.category_id') . ' = ' . (int) $categoryId)
					->where($db->qn('p.state') . ' = 1')
					->where('(p.publish_date = ' . $db->q($db->getNullDate()) . ' OR p.publish_date <= STR_TO_DATE('
						. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
					)
					->where('(p.unpublish_date = ' . $db->q($db->getNullDate()) . ' OR p.unpublish_date >= STR_TO_DATE('
						. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
					)
					->where($db->qn('m.state') . ' = 1');

				if (empty($collections))
				{
					// $query->where($db->qn('m.attribute_value_id') . ' IS NULL');
				}
				else
				{
					$query->innerJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON ' . $db->qn('pi.product_id') . ' = ' . $db->qn('p.id'))
						->innerJoin(
							$db->qn('#__redshopb_collection_product_item_xref', 'cpix') . ' ON ' .
							$db->qn('cpix.product_item_id') . ' = ' .
							$db->qn('pi.id')
						)
						->innerJoin($db->qn('#__redshopb_collection', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('cpix.collection_id'))
						->innerJoin(
							$db->qn('#__redshopb_collection_product_xref', 'cpx') .
							' ON ' . $db->qn('cpx.product_id') . ' = ' . $db->qn('p.id') .
							' AND ' . $db->qn('cpx.collection_id') . ' = ' . $db->qn('c.id')
						)
						->innerJoin(
							$db->qn('#__redshopb_product_item_attribute_value_xref', 'piavx') .
							' ON ' . $db->qn('piavx.product_item_id') . ' = ' . $db->qn('pi.id') .
							' AND ' . $db->qn('piavx.product_attribute_value_id') . ' = ' . $db->qn('m.attribute_value_id')
						)
						->where($db->qn('cpx.state') . ' = 1')
						->where($db->qn('cpix.state') . ' = 1')
						->where($db->qn('pi.state') . ' = 1')
						->where($db->qn('c.state') . ' = 1')
						->where($db->qn('c.id') . ' IN (' . implode(',', $collections) . ')');
				}

				$images = $db->setQuery($query)->loadObjectList();

				$mainImages = array();
				$allImages  = array();

				if (!empty($images))
				{
					foreach ($images as $img)
					{
						if ($img->color == 0)
						{
							$mainImages[] = $img;
						}

						$allImages[] = $img;
					}

					if (!empty($mainImages))
					{
						$image = $mainImages[array_rand($mainImages)];
					}
					else
					{
						$image = $allImages[array_rand($allImages)];
					}

					$image = RedshopbHelperThumbnail::originalToResize($image->name, $width, $height, 100, 0, 'products', false, $image->remote_path);

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
			}
		}

		return $thumb;
	}

	/**
	 * Get shop parent categories.
	 *
	 * @param   int        $customerId      Customer id.
	 * @param   string     $customerType    Customer type.
	 * @param   int|array  $categories      Category id.
	 * @param   bool       $onlyIds         Return only ids or full data
	 * @param   bool       $includeHimself  Include himself id in the result
	 *
	 * @return  array  Array of category objects.
	 */
	public static function getParentCategories($customerId, $customerType, $categories, $onlyIds = false, $includeHimself = true)
	{
		$isPlural = false;

		if (is_array($categories))
		{
			$isPlural = true;
		}
		else
		{
			$categories = (array) $categories;
		}

		$categories            = ArrayHelper::toInteger($categories);
		static $poolCategories = array();
		$foundCategories       = array();

		foreach ($categories as $key => $category)
		{
			if (array_key_exists($category, $poolCategories))
			{
				if (!is_null($poolCategories[$category]))
				{
					$foundCategories[$category] = $poolCategories[$category];
				}

				unset($categories[$key]);
			}
		}

		if (!empty($categories))
		{
			$db        = Factory::getDbo();
			$company   = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);
			$companies = RedshopbEntityCompany::getInstance($company)->getTree();
			$query     = $db->getQuery(true)
				->select('parent.*')
				->from($db->qn('#__redshopb_category', 'node'))
				->leftJoin($db->qn('#__redshopb_category', 'parent') . ' ON node.lft BETWEEN parent.lft AND parent.rgt')
				->where('node.id IN (' . implode(',', $categories) . ')')
				->where('parent.level > 0')
				->where($db->qn('node.state') . ' = 1')
				->group('parent.id')
				->order($db->qn('parent.lft') . ' DESC');

			if ($company)
			{
				$query->where(
					'(' . $db->qn('parent.company_id') . ' IN (' . implode(',', $companies) . ') OR ' . $db->qn('parent.company_id') . ' IS NULL)'
				);
			}
			else
			{
				$query->where($db->qn('parent.company_id') . ' IS NULL');
			}

			$reverseCategories = $db->setQuery($query)
				->loadObjectList('id');
			$cloneReverseCats  = $reverseCategories;
			$categoryIdAsKey   = array_flip($categories);

			foreach ($cloneReverseCats as $key => $reverseCategory)
			{
				if (!in_array($key, $poolCategories))
				{
					$categoryEntity = RedshopbEntityCategory::getInstance($reverseCategory->id);

					if (!$categoryEntity->isLoaded())
					{
						$categoryEntity->bind($reverseCategory);
					}

					unset($cloneReverseCats[$key]);
					$poolCategories[$key]   = self::recursiveCategoryParent($cloneReverseCats, $reverseCategory->parent_id);
					$poolCategories[$key][] = $reverseCategory->id;

					if (array_key_exists($key, $categoryIdAsKey))
					{
						$foundCategories[$key] = $poolCategories[$key];
						unset($categories[$categoryIdAsKey[$key]]);
					}
				}
				else
				{
					unset($cloneReverseCats[$key]);
				}
			}

			unset($categoryIdAsKey);

			if (!empty($categories))
			{
				foreach ($categories as $category)
				{
					if (!array_key_exists($category, $poolCategories))
					{
						$poolCategories[$category]  = null;
						$foundCategories[$category] = $poolCategories[$category];
					}
				}
			}
		}

		if (!$includeHimself)
		{
			foreach ($foundCategories as &$parents)
			{
				if (is_null($parents))
				{
					continue;
				}

				array_pop($parents);
			}
		}

		if ($isPlural)
		{
			if ($onlyIds)
			{
				return $foundCategories;
			}
			else
			{
				$return = array();

				foreach ($foundCategories as $categoryId => $parents)
				{
					$categoryParents = array();

					foreach ($parents as $parent)
					{
						$categoryParents[] = RedshopbEntityCategory::load($parent)
							->getItem();
					}

					$return[$categoryId] = $categoryParents;
				}

				return $return;
			}
		}
		else
		{
			$category = reset($foundCategories);

			if ($onlyIds)
			{
				return $category;
			}
			else
			{
				$categoryParents = array();

				foreach ($category as $parent)
				{
					$categoryParents[] = RedshopbEntityCategory::load($parent)
						->getItem();
				}

				return $categoryParents;
			}
		}
	}

	/**
	 * Recursive Category Parent
	 *
	 * @param   array  $reverseCategories  Reverse categories
	 * @param   int    $key                Current key
	 *
	 * @return  array
	 */
	static protected function recursiveCategoryParent($reverseCategories, $key)
	{
		$temp            = array();
		$foundCurrentKey = false;

		foreach ($reverseCategories as $reverseCategory)
		{
			if ($reverseCategory->id == $key)
			{
				$foundCurrentKey = true;
			}

			if ($foundCurrentKey)
			{
				array_unshift($temp, $reverseCategory->id);
			}

			if ($reverseCategory->level <= 1)
			{
				break;
			}
		}

		return $temp;
	}

	/**
	 * Get shop categories for given customer.
	 *
	 * @param   int      $parent           required  Category level.
	 * @param   mixed    $collections      required  Collections used to filter the categories (if false, don't filter)
	 * @param   int      $companyId        required  Company Id of the logged in user
	 * @param   string   $listType         optional  'comma' (default) for comma separated IDs for dB
	 *                                               'dropdown' for Drop down Select Box with items
	 *                                               'objectList' for an objectList direct from the DB (queries all fields)
	 *                                               'count' get count categories
	 * @param   int      $start            optional  Query start
	 * @param   int      $limit            optional  Query limit
	 * @param   boolean  $noEmpty          optional  True for just show category has product (child-categories indeed). False for show all.
	 * @param   int      $level            optional  Level of categories.
	 * @param   boolean  $requireTopLevel  optional  Check if only top level is required.
	 * @param   string   $layout           optional  Check if it's category og categories
	 * @param   boolean  $showHidden       optional  Show category with check "hide".
	 *
	 * @return  array|string                         Array of category objects.
	 */
	public static function getCustomerCategories($parent, $collections, $companyId, $listType = 'objectList', $start = 0, $limit = 0,
		$noEmpty = false, $level = 0, $requireTopLevel = false, $layout = null, $showHidden = true
	)
	{
		$config = RedshopbApp::getConfig();
		$width  = $config->get('category_image_width', 72);
		$height = $config->get('category_image_height', 72);

		if (empty($layout))
		{
			$layout = Factory::getApplication()->input->get('layout');
		}

		if (!$level)
		{
			$level = ($parent == 1) ? 2 : 1;
		}

		$categories = RedshopbHelperACL::listAvailableCategories(
			Factory::getUser()->id,
			$parent,
			$level,
			$companyId,
			$collections,
			$listType,
			'',
			'redshopb.category.view',
			$start,
			$limit,
			true,
			$noEmpty,
			'c.lft',
			array(),
			(boolean) $showHidden,
			(boolean) $requireTopLevel,
			$layout
		);

		if ($listType === 'count')
		{
			return (int) $categories;
		}

		if (is_array($categories) && count($categories))
		{
			// @TODO: RKM Look at this and make it less duplicating
			foreach ($categories as $category)
			{
				$imageUrl = false;

				if ($category->image != '')
				{
					$image = RedshopbHelperThumbnail::originalToResize($category->image, $width, $height, 100, 0, 'categories');

					if ($image)
					{
						$imageUrl = '<img src="' . $image . '" alt="' . RedshopbHelperThumbnail::safeAlt($category->name) . '" />';
					}
				}
				elseif ($category->pimage != '')
				{
					$detailsImage = explode('|', $category->pimage);
					$image        = RedshopbHelperThumbnail::originalToResize(
						$detailsImage[1], $width, $height, 100, 0, $detailsImage[0], false, isset($detailsImage[2]) ? $detailsImage[2] : ''
					);

					if ($image)
					{
						$imageUrl = '<img src="' . $image . '" alt="' . RedshopbHelperThumbnail::safeAlt($category->name) . '" />';
					}
				}

				if (!$imageUrl)
				{
					$catImg = RedshopbEntityCategory::load($category->id)->getImageRecursive();

					if (!empty($catImg->name))
					{
						$image = RedshopbHelperThumbnail::originalToResize($catImg->name, $width, $height, 100, 0, $catImg->section);

						if ($image)
						{
							$imageUrl = '<img src="' . $image . '" alt="' . RedshopbHelperThumbnail::safeAlt($category->name) . '" />';
						}
					}
					else
					{
						$imageUrl = RedshopbHelperMedia::drawDefaultImg($width, $height, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL'));
					}
				}

				$category->imageHTML = $imageUrl;
			}
		}

		return $categories;
	}

	/**
	 * Get only products has campaign price list.
	 *
	 * @param   int     $customerId    Customer id.
	 * @param   string  $customerType  Customer type.
	 * @param   array   $categories    Category ids.
	 * @param   int     $collectionId  Collection id
	 * @param   string  $listType      optional   'count' to get number of items
	 *                                              'objectList' for an objectList direct from the DB (queries all fields)
	 * @param   int     $start         optional  Query start
	 * @param   int     $limit         optional  Query limit
	 *
	 * @return array
	 */
	public static function getOnlyCampaignPriceProducts(
		$customerId, $customerType, $categories, $collectionId, $listType = 'objectList', $start = 0, $limit = 0
	)
	{
		if (!is_array($categories))
		{
			$categories = array((int) $categories);
		}

		$categories = ArrayHelper::toInteger($categories);

		if (empty($categories))
		{
			return array();
		}

		$app     = Factory::getApplication();
		$layout  = $app->getUserState('shop.layout', '');
		$sortBy  = $app->getUserState('shop.show.' . $layout . '.SortBy', RedshopbApp::getConfig()->getDefaultOrderByField($layout));
		$sortDir = strtoupper($app->getUserState('shop.show.' . $layout . '.SortByDir', 'asc'));

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(
				array(
					'p.*',
					$db->qn('pp.sales_code', 'sales_code'),
					$db->qn('pp.currency_id', 'currency'),
					$db->qn('pp.price', 'price')
				)
			)
			->from($db->qn('#__redshopb_product_price', 'pp'))
			->leftJoin(
				$db->qn('#__redshopb_product', 'p') . ' ON ('
				. '(' . $db->qn('pp.type_id') . ' = ' . $db->qn('p.id') . ')'
				. ' AND (' . $db->qn('pp.sales_type') . ' = ' . $db->quote('campaign') . ')'
				. ')'
			)
			->innerJoin($db->qn('#__redshopb_product_category_xref', 'pcx') . ' ON ' . $db->qn('p.id') . ' = ' . $db->qn('pcx.product_id'))
			->where($db->qn('pcx.category_id') . ' IN (' . implode(',', $categories) . ')')
			->where($db->qn('p.state') . ' = 1')
			->where('(p.publish_date = ' . $db->q($db->getNullDate()) . ' OR p.publish_date <= STR_TO_DATE('
				. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where('(p.unpublish_date = ' . $db->q($db->getNullDate()) . ' OR p.unpublish_date >= STR_TO_DATE('
				. $db->q(Factory::getDate()) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
			)
			->where($db->qn('p.service') . ' = 0')
			->group($db->qn('p.id'));

		if ($collectionId)
		{
			$query->innerJoin(
				$db->qn('#__redshopb_collection_product_xref', 'wpx') . ' ON ' .
				$db->qn('wpx.product_id') . ' = ' .
				$db->qn('pcx.product_id')
			)
				->where($db->qn('wpx.collection_id') . ' = ' . $collectionId);
		}

		switch ($sortBy)
		{
			case 'name':
				$query->order($db->qn('p.name') . ' ' . $sortDir);
				break;

			case 'sku':
				$query->order($db->qn('p.sku') . ' ' . $sortDir);
				break;
			case 'custom':
				$query->innerJoin($db->qn('#__redshopb_category', 'cat') . ' ON ' . $db->qn('cat.id') . ' = ' . $db->qn('pcx.category_id'));
				$query->order($db->qn('cat.lft') . ' ASC,' . $db->qn('pcx.ordering') . ' ' . $sortDir);
				break;

			default:
				$query->order($db->qn('p.name') . ' ' . $sortDir . ',' . $db->qn('p.sku'));
		}

		if ($listType == 'count')
		{
			$countQuery = $db->getQuery(true);
			$countQuery->select('COUNT(*)')
				->from('(' . $query . ') AS ' . $db->qn('count'));
			$db->setQuery($countQuery);
			$rows = (int) $db->loadResult();

			return $rows;
		}
		else
		{
			// Checking if logged in user is a b2b user to get the favorite lists
			$user      = Factory::getUser();
			$rsbUserId = RedshopbHelperUser::getUserRSid($user->id);

			if ($rsbUserId != 0 && $rsbUserId != '')
			{
				$query->select('COUNT(' . $db->qn('flpx.id') . ') AS ' . $db->qn('favoritelists'))
					->join(
						'left',
						$db->qn('#__redshopb_favoritelist_product_xref', 'flpx') . ' ON ' .
						$db->qn('flpx.product_id') . ' = ' .
						$db->qn('p.id')
					)
					->join(
						'left',
						$db->qn('#__redshopb_favoritelist', 'fl') . ' ON ' .
						$db->qn('fl.id') . ' = ' .
						$db->qn('flpx.favoritelist_id') . ' AND ' .
						$db->qn('fl.user_id') . ' = ' . (int) $rsbUserId
					);
			}
		}

		$products = $db->setQuery($query, (int) $start, (int) $limit)->loadObjectList('id');

		if ($products)
		{
			RedshopbHelperProduct::setProduct($products);
		}

		return $products;
	}

	/**
	 * Get Url Category Id
	 *
	 * @param   array  $categories  Categories for current item
	 *
	 * @return  integer
	 */
	public static function getUrlCategoryId($categories = array())
	{
		static $urlCategoryId = false;
		$rightCategoryId      = 0;

		if ($urlCategoryId === false)
		{
			$input         = Factory::getApplication()->input;
			$urlCategoryId = 0;

			if ($input->getCmd('option') == 'com_redshopb' && $input->getCmd('view') == 'shop')
			{
				switch ($input->getCmd('layout'))
				{
					case 'category':
						$urlCategoryId = $input->getInt('id', 0);
						break;
					case 'product':
						$urlCategoryId = $input->getInt('category_id', 0);
						break;
				}
			}
		}

		if (!empty($categories))
		{
			if (in_array($urlCategoryId, $categories))
			{
				$rightCategoryId = $urlCategoryId;
			}
			else
			{
				$rightCategoryId = $categories[0];
			}
		}

		return $rightCategoryId;
	}
}
