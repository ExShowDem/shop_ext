<?php
/**
 * @package     Aesir.E-Commerce.Site
 * @subpackage  mod_redshopb_megamenu
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Image\Image;
/**
 * Class MegaMenuModuleHelper
 *
 * @since  1.6.21
 */
class MegaMenuModuleHelper extends ModuleHelper
{
	/**
	 * Getting all modules
	 *
	 * @return  array
	 */
	public static function &getAllModules()
	{
		static $modules;

		if (isset($modules))
		{
			return $modules;
		}

		$modules    = array();
		$allModules = parent::load();

		if ($allModules)
		{
			foreach ($allModules as $module)
			{
				$modules[$module->id] = $module;
			}
		}

		return $modules;
	}
}

/**
 * Helper for mod_menu
 *
 * @package     Aesir.E-Commerce.Site
 * @subpackage  mod_redshopb_megamenu
 * @since       1.6.21
 */
class ModRedshopbMegaMenuHelper
{
	/**
	 * Get a list of the menu items.
	 *
	 * @param   Registry  $params  The module options.
	 *
	 * @return  array
	 */
	public static function getList(&$params)
	{
		$app          = Factory::getApplication();
		$menu         = $app->getMenu();
		$items        = $menu->getItems('menutype', $params->get('menutype'));
		$lastitem     = 0;
		$rootRedShopB = $params->get('rootRedshopb');

		if ($items)
		{
			do
			{
				$i                = key($items);
				$item             = current($items);
				$item->deeper     = false;
				$item->shallower  = false;
				$item->level_diff = 0;

				if (isset($items[$lastitem]))
				{
					$items[$lastitem]->deeper     = ($item->level > $items[$lastitem]->level);
					$items[$lastitem]->shallower  = ($item->level < $items[$lastitem]->level);
					$items[$lastitem]->level_diff = ($items[$lastitem]->level - $item->level);
				}

				$lastitem = $i;
				$pattern  = '/\[regular\]/';
				self::setValues($item);
				$item->mega = false;

				if ($item->id == $rootRedShopB)
				{
					self::getRedshopbList($item, $params);
				}
				elseif (!preg_match($pattern, $item->note, $result))
				{
					$item->note = preg_replace($pattern, '', $item->note);
					$item->mega = true;
					self::getListForJoomlaMegamenu($items, $params);
				}
			}
			while (next($items) !== false);

			if (isset($items[$lastitem]))
			{
				$items[$lastitem]->deeper     = (1 > $items[$lastitem]->level);
				$items[$lastitem]->shallower  = (1 < $items[$lastitem]->level);
				$items[$lastitem]->level_diff = ($items[$lastitem]->level - 1);
			}
		}

		return $items;
	}

	/**
	 * Get List For Mega menu
	 *
	 * @param   array      $items   Menu Items
	 * @param   Registry   $params  Module params
	 *
	 * @throws Exception
	 *
	 * @return  void
	 */
	public static function getListForJoomlaMegamenu(&$items, $params)
	{
		$parent               = current($items);
		$parent->displayLevel = $parent->level + 1;
		$parent->replaceItem  = false;
		$minimalLevel         = $parent->level;
		$countChildren        = array();
		$ids                  = array();
		$images               = array();
		$parent->hasSprite    = false;
		$parent->activePath   = $params->get('activePath', array());
		$parent->activeId     = $params->get('activeId', 0);
		$useImageSprite       = (int) $params->get('useImageSprite', 1);
		$imageWidth           = (int) $params->get('imageWidth', 65);
		$imageHeight          = (int) $params->get('imageHeight', 65);
		$lastItem             = 0;
		$children             = array();
		$app                  = Factory::getApplication();
		$menu                 = $app->getMenu();

		while (next($items) !== false)
		{
			$i     = key($items);
			$child = current($items);

			if ($parent->level >= $child->level)
			{
				prev($items);
				break;
			}

			$relationLevel = $parent->level + ($child->level - $minimalLevel);

			if (!isset($countChildren[$child->parent_id]))
			{
				$countChildren[$child->parent_id] = 0;
			}

			$countChildren[$child->parent_id]++;

			$child->deeper        = false;
			$child->shallower     = false;
			$child->level_diff    = 0;
			$child->relationLevel = $relationLevel;
			$ids[$child->id]      = $i;
			$child->parent        = (boolean) $menu->getItems('parent_id', (int) $child->id, true);

			if (isset($children[$lastItem]))
			{
				$child[$lastItem]->deeper     = ($relationLevel > $child[$lastItem]->relationLevel);
				$child[$lastItem]->shallower  = ($relationLevel < $child[$lastItem]->relationLevel);
				$child[$lastItem]->level_diff = ($child[$lastItem]->relationLevel - $relationLevel);
			}

			$lastItem = $i;
			self::setValues($child);

			if ($useImageSprite && $child->level == 2)
			{
				$imagePath = JPATH_ROOT . '/' . $child->menu_image;

				if ($child->menu_image != '' && JFile::exists($imagePath))
				{
					$filename         = pathinfo($imagePath, PATHINFO_FILENAME);
					$fileExtension    = pathinfo($imagePath, PATHINFO_EXTENSION);
					$thumbFileName    = $filename . '_' . $imageWidth . 'x' . $imageHeight . '.' . $fileExtension;
					$imageThumbFolder = JPATH_ROOT . '/cache/mod_redshopb_megamenu';
					$imageThumbPath   = $imageThumbFolder . '/thumbs/' . $thumbFileName;
					$thumb            = false;

					if (!JFile::exists($imageThumbPath))
					{
						$image  = new Image($imagePath);
						$thumbs = $image->createThumbs(array($imageWidth . 'x' . $imageHeight), Image::CROP_RESIZE, $imageThumbFolder);

						if (count($thumbs))
						{
							$thumb = $thumbs[0]->getPath();
						}
					}
					else
					{
						$thumb = array($imageThumbPath);
					}

					if ($thumb)
					{
						$images[$child->id] = $thumb;
					}
				}
			}

			$children[] = $child;
			unset($items[$i]);
			prev($items);
		}

		if ($useImageSprite)
		{
			$parent->hasSprite = self::createSprite($images, $imageWidth, $imageHeight);
		}

		if (isset($children[$lastItem]))
		{
			$children[$lastItem]->deeper     = (1 > $children[$lastItem]->relationLevel);
			$children[$lastItem]->shallower  = (1 < $children[$lastItem]->relationLevel);
			$children[$lastItem]->level_diff = ($children[$lastItem]->relationLevel - 1);
		}

		$parent->children      = $children;
		$parent->countChildren = $countChildren;
	}

	/**
	 * Render the module
	 *
	 * @param   int     $moduleId  The module ID to load
	 * @param   string  $style     Style for module
	 *
	 * @return string with HTML
	 */
	public static function generateModuleById($moduleId, $style = 'xhtml')
	{
		$attribs['style'] = $style;
		$modules          = &MegaMenuModuleHelper::getAllModules();

		// Get the title of the module to load
		$modTitle = $modules[$moduleId]->title;
		$modName  = $modules[$moduleId]->module;

		// Load the module
		if (ModuleHelper::isEnabled($modName))
		{
			$module = ModuleHelper::getModule($modName, $modTitle);

			return ModuleHelper::renderModule($module, $attribs);
		}

		return Text::sprintf('MOD_REDSHOPB_MEGAMENU_MODULE_NOT_FOUND', $moduleId);
	}

	/**
	 * Get active menu item.
	 *
	 * @return  object
	 */
	public static function getActive()
	{
		$menu = Factory::getApplication()->getMenu();
		$lang = Factory::getLanguage();

		// Look for the home menu
		if (Multilanguage::isEnabled())
		{
			$home = $menu->getDefault($lang->getTag());
		}
		else
		{
			$home = $menu->getDefault();
		}

		return $menu->getActive() ? $menu->getActive() : $home;
	}

	/**
	 * Ger redshopb categories list
	 *
	 * @param   object     $parent   Link to parent joomla item menu
	 * @param   Registry   $params   The module options.
	 *
	 * @return  void
	 */
	public static function getRedshopbList(&$parent, $params)
	{
		$app          = Factory::getApplication();
		$input        = $app->input;
		$customerId   = $app->getUserState('shop.customer_id', 0);
		$customerType = $app->getUserState('shop.customer_type', '');
		$values       = new stdClass;
		RedshopbHelperShop::setUserStates($values);
		$end         = (int) $params->get('endLevel', 999);
		$collections = RedshopbHelperCollection::getCustomerCollectionsForShop($values->customerId, $values->customerType);

		if ($collections !== false && empty($collections))
		{
			// If it has to have collections but there is any, it returns
			return;
		}

		$redshopbCategories = RedshopbHelperACL::listAvailableCategories(
			Factory::getUser()->id,
			1,
			$end,
			$values->companyId,
			$collections,
			'objectList',
			'',
			'redshopb.category.view',
			0,
			0,
			true,
			true,
			'c.lft',
			array(2),
			false
		);

		if ($redshopbCategories)
		{
			$id                   = $input->getInt('id', 0);
			$layout               = $input->getCmd('layout', '');
			$parent->redshopbPath = array();
			$parent->current      = 0;
			$categoryIds          = array();

			foreach ($redshopbCategories as $oneCategory)
			{
				$categoryIds[] = $oneCategory->id;
			}

			// Cache parent categories
			RedshopbHelperCategory::getParentCategories($customerId, $customerType, $categoryIds, true);

			if ($input->getCmd('option', '') == 'com_redshopb'
				&& $input->getCmd('view', '') == 'shop')
			{
				if ($layout == 'category'  && $id)
				{
					$parent->current = $id;

					$result = RedshopbHelperCategory::getParentCategories($customerId, $customerType, $id, true);

					if ($result)
					{
						$parent->redshopbPath = $result;
					}
				}
				elseif ($layout == 'product' && $id)
				{
					$product = RedshopbHelperProduct::loadProduct($id);

					if ($product)
					{
						if (!empty($product->categories))
						{
							$categoryId = $input->getInt('category_id', 0);

							if (in_array($categoryId, $product->categories))
							{
								$parent->current = $categoryId;
							}
							else
							{
								$parent->current = $product->categories[0];
							}

							$result = RedshopbHelperCategory::getParentCategories($customerId, $customerType, $parent->current, true);

							if ($result)
							{
								$parent->redshopbPath = $result;
							}
						}
					}
				}
			}

			$lastRBItem        = 0;
			$minimalRBLevel    = $redshopbCategories[0]->level;
			$countChildren     = array();
			$ids               = array();
			$images            = array();
			$parent->hasSprite = false;
			$useImageSprite    = (int) $params->get('useImageSprite', 1);
			$showImages        = (int) $params->get('showImages', 1);
			$imageWidth        = (int) $params->get('imageWidth', 65);
			$imageHeight       = (int) $params->get('imageHeight', 65);

			foreach ($redshopbCategories as $i => $redshopbCategory)
			{
				$relationLevel = $parent->level + ($redshopbCategory->level - $minimalRBLevel);

				if ($end && $relationLevel > $end)
				{
					unset($redshopbCategories[$i]);
					continue;
				}

				if (!isset($countChildren[$redshopbCategory->parent_id]))
				{
					$countChildren[$redshopbCategory->parent_id] = 0;
				}

				$countChildren[$redshopbCategory->parent_id]++;
				$redshopbCategory->deeper        = false;
				$redshopbCategory->shallower     = false;
				$redshopbCategory->level_diff    = 0;
				$redshopbCategory->relationLevel = $relationLevel;
				$ids[$redshopbCategory->id]      = $i;
				$redshopbCategory->parent        = false;

				if (isset($redshopbCategories[$lastRBItem]))
				{
					$redshopbCategories[$lastRBItem]->deeper     = ($relationLevel > $redshopbCategories[$lastRBItem]->relationLevel);
					$redshopbCategories[$lastRBItem]->shallower  = ($relationLevel < $redshopbCategories[$lastRBItem]->relationLevel);
					$redshopbCategories[$lastRBItem]->level_diff = ($redshopbCategories[$lastRBItem]->relationLevel - $relationLevel);
				}

				if (isset($ids[$redshopbCategory->parent_id]))
				{
					$parentId                              = $ids[$redshopbCategory->parent_id];
					$redshopbCategories[$parentId]->parent = true;
				}

				$lastRBItem               = $i;
				$redshopbCategory->active = false;
				$redshopbCategory->flink  = RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=category&id=' . $redshopbCategory->id);

				// We prevent the double encoding because for some reason the $item is shared for menu modules and we get double encoding
				// when the cause of that is found the argument should be removed
				$redshopbCategory->title = htmlspecialchars($redshopbCategory->name, ENT_COMPAT, 'UTF-8', false);

				// Cleaning RAM, remove not using values
				unset(
					$redshopbCategory->lft, $redshopbCategory->rgt, $redshopbCategory->company_id, $redshopbCategory->path,
					$redshopbCategory->checked_out_time, $redshopbCategory->created_date, $redshopbCategory->modified_date,
					$redshopbCategory->state, $redshopbCategory->template_id, $redshopbCategory->checked_out,
					$redshopbCategory->created_by, $redshopbCategory->modified_by, $redshopbCategory->description,
					$redshopbCategory->name, $redshopbCategory->alias
				);

				if ($showImages && $useImageSprite && $redshopbCategory->level == 2)
				{
					if ($redshopbCategory->image != '')
					{
						$image = RedshopbHelperThumbnail::originalToResize(
							$redshopbCategory->image, $imageWidth, $imageHeight, 100, 1, 'categories', true
						);

						if ($image)
						{
							$images[$redshopbCategory->id] = array('section' => 'categories', 'image' => $image);
						}
					}
					elseif ($redshopbCategory->pimage != '')
					{
						$detailsImage = explode('|', $redshopbCategory->pimage);
						$image        = RedshopbHelperThumbnail::originalToResize(
							$detailsImage[1], $imageWidth, $imageHeight, 100, 1, $detailsImage[0], true,
							isset($detailsImage[2]) ? $detailsImage[2] : ''
						);

						if ($image)
						{
							$images[$redshopbCategory->id] = array('section' => $detailsImage[0], 'image' => $image);
						}
					}
				}
			}

			if (isset($redshopbCategories[$lastRBItem]))
			{
				$redshopbCategories[$lastRBItem]->deeper     = (1 > $redshopbCategories[$lastRBItem]->relationLevel);
				$redshopbCategories[$lastRBItem]->shallower  = (1 < $redshopbCategories[$lastRBItem]->relationLevel);
				$redshopbCategories[$lastRBItem]->level_diff = ($redshopbCategories[$lastRBItem]->relationLevel - 1);
			}

			if ($showImages && $useImageSprite)
			{
				$parent->hasSprite = self::createSprite($images, $imageWidth, $imageHeight);
			}

			$parent->redShopBCategories = $redshopbCategories;
			$parent->countChildren      = $countChildren;
		}
	}

	/**
	 * Create Sprite for megamenu
	 *
	 * @param   array   $files   Array data images
	 * @param   int     $width   Width images
	 * @param   int     $height  Height images
	 * @param   string  $output  Name of class for sprite
	 *
	 * @return  boolean
	 */
	public static function createSprite($files = array(), $width = 65, $height = 65, $output = 'redshopb_megamenu_sprite')
	{
		if (empty($files))
		{
			return false;
		}

		$md5        = md5(serialize(func_get_args()));
		$nameFile   = $output . '-' . $md5;
		$spritePath = JPATH_ROOT . '/cache/mod_redshopb_megamenu/' . $nameFile;
		$urlPath    = Uri::root() . 'cache/mod_redshopb_megamenu/' . $nameFile;

		if (!JFile::exists($spritePath . '.css') || !JFile::exists($spritePath . '.png'))
		{
			// The variable yy is the height of the sprite to be created, basically height * number of images
			$yy = $height * count($files);
			$im = imagecreatetruecolor($width, $yy);

			// Add alpha channel to image (transparency)
			imagesavealpha($im, true);
			$alpha = imagecolorallocatealpha($im, 0, 0, 0, 127);
			imagefill($im, 0, 0, $alpha);

			// Append images to sprite and generate CSS lines
			$i   = 0;
			$css = '.' . $output . '{width:' . $width . 'px;height:' . $height
					. 'px;background:url(' . $nameFile . ".png) no-repeat 0px " . $height . "px #dfdfdf}\n";

			foreach ($files as $key => $fileData)
			{
				$css .= '.' . $output . '_' . $key . "{background-position: 0px -" . ($height * $i) . "px}\n";

				if (is_string($fileData))
				{
					$thumbFile = $fileData;
				}
				else
				{
					$thumbFile = JPATH_SITE . '/' . $fileData['image'];
				}

				if (JFile::exists($thumbFile))
				{
					$info = getimagesize($thumbFile);

					switch ($info['mime'])
					{
						case 'image/gif':
							$im2 = imagecreatefromgif($thumbFile);
							break;
						case 'image/jpeg':
							$im2 = imagecreatefromjpeg($thumbFile);
							break;
						case 'image/png':
							$im2 = imagecreatefrompng($thumbFile);
							break;
						default:
							$i++;
							continue(2);
					}

					imagecopy($im, $im2, 0, ($height * $i), 0, 0, $width, $height);
					imagedestroy($im2);
				}

				$i++;
			}

			JFile::write($spritePath . '.css', $css);
			imagepng($im, $spritePath . '.png');
			imagedestroy($im);

			// Extra file optimization - depends on binary files to work properly
			$factory   = new \ImageOptimizer\OptimizerFactory;
			$optimizer = $factory->get();
			$optimizer->optimize($spritePath . '.png');
		}

		Factory::getDocument()->addStyleSheet($urlPath . '.css');

		return true;
	}

	/**
	 * Display one redshopb level
	 *
	 * @param   array   $items       Redshop list items
	 * @param   object  $parentItem  Joomla parent item
	 * @param   int     $level       Current level display
	 * @param   int     $parentId    Use parent id for filter redshop items
	 * @param   string  $parentLink  Link of parent item for use in "See all child categories" link
	 *
	 * @return  integer
	 */
	public static function displayLevel(&$items, $parentItem, $level = 2, $parentId = 0, $parentLink = '')
	{
		if ($level != 1 && (!isset($parentItem->countChildren[$parentId]) || $parentItem->countChildren[$parentId] == 0))
		{
			return $parentItem->lastItem;
		}

		$countItems     = 0;
		$isLevel        = false;
		$countColumns   = $parentItem->pluginParams->get('countColumns', 4);
		$numberSpan     = round(12 / $countColumns, 0, PHP_ROUND_HALF_DOWN);
		$itemsInColumn  = 0;
		$itemsPerColumn = array();

		if ($level == 2)
		{
			$column = 0;

			for ($i = 0; $i < $parentItem->countChildren[$parentId]; $i++)
			{
				$column++;

				if (!isset($itemsPerColumn[$column]))
				{
					$itemsPerColumn[$column] = 0;
				}

				$itemsPerColumn[$column]++;

				if ($column >= $countColumns)
				{
					$column = 0;
				}
			}
		}

		$column   = 1;
		$lastItem = $parentItem->lastItem;
		$ci       = count($items);

		for ($i = $parentItem->lastItem; $i < $ci; $i++)
		{
			if (!array_key_exists($i, $items))
			{
				continue;
			}

			$item = $items[$i];

			if ($level != 1 && $item->parent_id != $parentId)
			{
				continue;
			}

			if ($item->relationLevel != $level)
			{
				continue;
			}

			$parentItem->lastItem = $i;
			$key                  = $parentItem->id . '-' . $item->id;
			$class                = array('item-' . $key, 'level-item-' . $level);
			$attr                 = array('href' => $item->flink);
			$item->browserNav     = $parentItem->browserNav;

			if ($item->deeper)
			{
				$class[] = 'deeper';
			}

			if ($item->parent)
			{
				$class[] = 'parent';
			}

			if (in_array($item->id, $parentItem->redshopbPath))
			{
				$class[] = 'active';

				if ($item->id == $parentItem->current)
				{
					$class[] = 'current';
				}
			}

			if ($level == 1)
			{
				$attr = self::setBrowserNav($item, $attr);
				echo '<li class="' . implode(' ', $class) . '"><a ' . self::getLinkAttributes($attr)
					. '><span class="menuLinkTitle">' . $item->title . '</span></a>';
				$i = self::displayLevel($items, $parentItem, $level + 1, $item->id, $item->flink);
				echo '</li>';
			}
			elseif ($level == 2)
			{
				if (!$isLevel)
				{
					echo '<ul class="nav-child unstyled list-unstyled megamenu"><li><div class="row-fluid">';
					$isLevel = true;
				}

				$countItems++;
				$itemsInColumn++;

				if ($itemsInColumn == 1)
				{
					echo '<div id="accordion' . $key . '" class="accordion span' . $numberSpan . ' ">';
				}

				echo '<div class="accordion-group ' . implode(' ', $class) . '"><div class="accordion-heading">';
				$attr['class'] = 'categoryLink';

				$showImages = (int) $parentItem->pluginParams->get('showImages', 1);

				if ($showImages)
				{
					$imageWidth  = (int) $parentItem->pluginParams->get('imageWidth', 65);
					$imageHeight = (int) $parentItem->pluginParams->get('imageHeight', 65);

					if ($parentItem->hasSprite)
					{
						$imageUrl = '<div class="redshopb_megamenu_sprite redshopb_megamenu_sprite_' . $item->id . '"></div>';
					}
					else
					{
						$imageUrl = false;

						if ($item->image != '')
						{
							$image = RedshopbHelperThumbnail::originalToResize($item->image, $imageWidth, $imageHeight, 100, 1, 'categories');

							if ($image)
							{
								$imageUrl = '<img src="' . $image . '" alt="' . $item->title . '" />';
							}
						}
						elseif ($item->pimage != '')
						{
							$detailsImage = explode('|', $item->pimage);
							$image        = RedshopbHelperThumbnail::originalToResize(
								$detailsImage[1], $imageWidth, $imageHeight, 100, 1, $detailsImage[0], false,
								isset($detailsImage[2]) ? $detailsImage[2] : ''
							);

							if ($image)
							{
								$imageUrl = '<img src="' . $image . '" alt="' . $item->title . '" />';
							}
						}

						if (!$imageUrl)
						{
							$imageUrl = '<div style="width: ' . $imageWidth . 'px;height: ' . $imageHeight . 'px;background: #dfdfdf"></div>';
						}
					}

					$linktype = '<div class="thumbnail">' . $imageUrl . '</div><span class="menuLinkTitle">' . $item->title . '</span>';
				}
				else
				{
					$linktype = '<span class="menuLinkTitle">' . $item->title . '</span>';
				}

				$attr = self::setBrowserNav($item, $attr);

				echo '<a ' . self::getLinkAttributes($attr) . '>' . $linktype . '</a>';

				if ($item->parent)
				{
					$attr = array(
						'href' => '#collapseAnchor' . $key . '-' . $countItems,
						'data-parent' => '#accordion' . $key,
						'data-toggle' => 'collapse',
						'class' => 'accordion-toggle collapsed'
					);
					echo '<a ' . self::getLinkAttributes($attr) . '>' . $parentItem->pluginParams->get('indicatorSecondLevel', '+') . '</a>';
				}

				// Close <div class="accordion-heading">
				echo '</div>';

				echo '<div class="accordion-body collapse" id="collapseAnchor'
					. $key . '-' . $countItems . '"><div class="accordion-inner">';

				$i = self::displayLevel($items, $parentItem, $level + 1, $item->id);

				// Close <div class="accordion-inner">
				echo '</div>';

				// Close <div class="accordion-body collapse">
				echo '</div>';

				// Close <div class="accordion-group">
				echo '</div>';

				if ($itemsInColumn == $itemsPerColumn[$column])
				{
					// Close previous <div id="accordion{$key}">
					echo '</div>';

					// Next item display in next column
					$column++;
					$itemsInColumn = 0;
				}
			}
			elseif ($parentId == $item->parent_id && $level >= 3)
			{
				if (!$isLevel)
				{
					if ($level == 3)
					{
						echo '<ul class="nav-child unstyled list-unstyled">';
					}
					else
					{
						echo '<ul class="nav-child unstyled list-unstyled dropdown">';
					}

					$isLevel = true;
				}

				$attr = self::setBrowserNav($item, $attr);

				echo '<li class="' . implode(' ', $class) . '"><a ' . self::getLinkAttributes($attr) . '>' . $item->title . '</a>';

				$i = self::displayLevel($items, $parentItem, $level + 1, $item->id);
				echo '</li>';
			}
		}

		if ($isLevel)
		{
			if ($level == 2)
			{
				// Close <div class="row-fluid">, and <li>, and <ul class="nav-child unstyled megamenu">
				echo '</div></li>';

				// Show 'see all categories' link
				echo '<li class="all-child-categories-wrapper pagination-center">
					<a href="' . RedshopbRoute::_($parentLink, false) . '" class="all-child-categories-link">
						' . Text::sprintf('MOD_REDSHOPB_MEGAMENU_SEE_ALL_CHILD_CATEGORIES', $items[$lastItem]->title) . '
					</a>
				</li>';

				echo '</ul>';
			}
			elseif ($level >= 3)
			{
				// Close <ul class="nav-child unstyled">
				echo '</ul>';
			}
		}

		return $parentItem->lastItem;
	}

	/**
	 * Display one redshopb level
	 *
	 * @param   array   $items       Redshop list items
	 * @param   object  $parentItem  Joomla parent item
	 * @param   int     $level       Current level display
	 * @param   int     $parentId    Use parent id for filter redshop items
	 *
	 * @return  integer
	 */
	public static function displayJoomlaLevel(&$items, $parentItem, $level = 2, $parentId = 0)
	{
		if ($level > 1 && (!isset($parentItem->countChildren[$parentId]) || $parentItem->countChildren[$parentId] == 0))
		{
			return $parentItem->lastItem;
		}

		$countItems     = 0;
		$isLevel        = false;
		$countColumns   = $parentItem->pluginParams->get('countColumns', 4);
		$numberSpan     = round(12 / $countColumns, 0, PHP_ROUND_HALF_DOWN);
		$itemsInColumn  = 0;
		$itemsPerColumn = array();

		if ($level == 2)
		{
			$column = 0;

			for ($i = 0; $i < $parentItem->countChildren[$parentId]; $i++)
			{
				$column++;

				if (!isset($itemsPerColumn[$column]))
				{
					$itemsPerColumn[$column] = 0;
				}

				$itemsPerColumn[$column]++;

				if ($column >= $countColumns)
				{
					$column = 0;
				}
			}
		}

		$column = 1;
		$ci     = count($items);

		for ($i = $parentItem->lastItem; $i < $ci; $i++)
		{
			$item = $items[$i];

			if ($level != 1 && $item->parent_id != $parentId)
			{
				continue;
			}

			if ($item->relationLevel != $level)
			{
				continue;
			}

			$parentItem->lastItem = $i;
			$key                  = $parentItem->id . '-' . $item->id;
			$class                = array('item-' . $key, 'level-item-' . $level);
			$attr                 = array('href' => $item->flink);

			if ($item->deeper)
			{
				$class[] = 'deeper';
			}

			if ($item->parent)
			{
				$class[] = 'parent';
			}

			if (in_array($item->id, $parentItem->activePath))
			{
				$class[] = 'active';

				if ($item->id == $parentItem->activeId)
				{
					$class[] = 'current';
				}
			}

			if ($level == 1)
			{
				$attr = self::setBrowserNav($item, $attr);
				echo '<li class="' . implode(' ', $class) . '"><a ' . self::getLinkAttributes($attr)
					. '><span class="menuLinkTitle">' . $item->title . '</span></a>';
				$i = self::displayJoomlaLevel($items, $parentItem, $level + 1, $item->id);
				echo '</li>';
			}
			elseif ($level == 2)
			{
				if (!$isLevel)
				{
					echo '<ul class="nav-child unstyled list-unstyled megamenu"><li class="maxMegaMenuWidth"><div class="row-fluid">';
					$isLevel = true;
				}

				$countItems++;
				$itemsInColumn++;

				if ($itemsInColumn == 1)
				{
					echo '<div id="accordion' . $key . '" class="accordion span' . $numberSpan . ' ">';
				}

				echo '<div class="accordion-group ' . implode(' ', $class) . '"><div class="accordion-heading">';
				$attr['class'] = 'categoryLink';

				if ($parentItem->hasSprite)
				{
					$imageUrl = '<div class="redshopb_megamenu_sprite redshopb_megamenu_sprite_' . $item->id . '"></div>';
				}
				else
				{
					$imageUrl = false;

					if (JFile::exists(JPATH_ROOT . '/' . $item->menu_image))
					{
						$imageUrl = '<img src="' . $item->menu_image . '" alt="' . $item->title . '" />';
					}

					if (!$imageUrl)
					{
						$imageUrl = '<div class="megaMenuEmptyBox"></div>';
					}
				}

				$linktype = '<div class="thumbnail">' . $imageUrl . '</div><span class="menuLinkTitle">' . $item->title . '</span>';

				$attr = self::setBrowserNav($item, $attr);

				echo '<a ' . self::getLinkAttributes($attr) . '>' . $linktype . '</a>';

				if ($item->parent)
				{
					$attr = array(
						'href' => '#collapseAnchor' . $key . '-' . $countItems,
						'data-parent' => '#accordion' . $key,
						'data-toggle' => 'collapse',
						'class' => 'accordion-toggle collapsed'
					);
					echo '<a ' . self::getLinkAttributes($attr) . '>' . $parentItem->pluginParams->get('indicatorSecondLevel', '+') . '</a>';
				}

				// Close <div class="accordion-heading">
				echo '</div>';

				echo '<div class="accordion-body collapse" id="collapseAnchor'
					. $key . '-' . $countItems . '"><div class="accordion-inner">';

				$i = self::displayJoomlaLevel($items, $parentItem, $level + 1, $item->id);

				// Close <div class="accordion-inner">
				echo '</div>';

				// Close <div class="accordion-body collapse">
				echo '</div>';

				// Close <div class="accordion-group">
				echo '</div>';

				if ($itemsInColumn == $itemsPerColumn[$column])
				{
					// Close previous <div id="accordion{$key}">
					echo '</div>';

					// Next item display in next column
					$column++;
					$itemsInColumn = 0;
				}
			}
			elseif ($parentId == $item->parent_id && $level >= 3)
			{
				if (!$isLevel)
				{
					if ($level == 3)
					{
						echo '<ul class="nav-child unstyled list-unstyled">';
					}
					else
					{
						echo '<ul class="nav-child unstyled list-unstyled dropdown">';
					}

					$isLevel = true;
				}

				$attr = self::setBrowserNav($item, $attr);

				echo '<li class="' . implode(' ', $class) . '"><a ' . self::getLinkAttributes($attr) . '>' . $item->title . '</a>';

				$i = self::displayJoomlaLevel($items, $parentItem, $level + 1, $item->id);
				echo '</li>';
			}
		}

		if ($isLevel)
		{
			if ($level == 2)
			{
				// Close <div class="row-fluid">, and <li>, and <ul class="nav-child unstyled megamenu">
				echo '</div></li></ul>';
			}
			elseif ($level >= 3)
			{
				// Close <ul class="nav-child unstyled">
				echo '</ul>';
			}
		}

		return $parentItem->lastItem;
	}

	/**
	 * Build link attributes to string
	 *
	 * @param   array  $attr  Array link attributes
	 *
	 * @return  string
	 */
	public static function getLinkAttributes($attr)
	{
		return implode(' ',
			array_map(
				function ($v, $k)
				{
					return sprintf('%s="%s"', $k, $v);
				},
				$attr,
				array_keys($attr)
			)
		);
	}

	/**
	 * Set browser navigation attributes
	 *
	 * @param   object  $item  Current menu item
	 * @param   array   $attr  Array item attributes
	 *
	 * @return  array
	 */
	public static function setBrowserNav($item, $attr = array())
	{
		switch ($item->browserNav)
		{
			case 1:
				// _blank
				$attr['target'] = '_blank';
				break;
			case 2:
				// Use JavaScript "window.open"
				$attr['onclick'] = "window.open(this.href,'targetWindow'"
					. ",'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes');return false;";
				break;
		}

		return $attr;
	}

	/**
	 * Set menu item values
	 *
	 * @param   object  $item  Menu item
	 *
	 * @throws Exception
	 *
	 * @return  void
	 */
	public static function setValues(&$item)
	{
		$app          = Factory::getApplication();
		$menu         = $app->getMenu();
		$item->parent = (boolean) $menu->getItems('parent_id', (int) $item->id, true);
		$item->active = false;
		$item->flink  = $item->link;

		switch ($item->type)
		{
			case 'separator':
			case 'heading':
				// No further action needed.
				continue;

			case 'url':
				if ((strpos($item->link, 'index.php?') === 0) && (strpos($item->link, 'Itemid=') === false))
				{
					// If this is an internal Joomla link, ensure the Itemid is set.
					$item->flink = $item->link . '&Itemid=' . $item->id;
				}
				break;

			case 'alias':
				$item->flink = 'index.php?Itemid=' . $item->params->get('aliasoptions');
				break;

			default:
				$item->flink = 'index.php?Itemid=' . $item->id;
				break;
		}

		if (strcasecmp(substr($item->flink, 0, 4), 'http') && (strpos($item->flink, 'index.php?') !== false))
		{
			$item->flink = Route::_($item->flink, true, $item->params->get('secure'));
		}
		else
		{
			$item->flink = Route::_($item->flink);
		}

		// We prevent the double encoding because for some reason the $item is shared for menu modules and we get double encoding
		// when the cause of that is found the argument should be removed
		$item->title        = htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8', false);
		$item->anchor_css   = htmlspecialchars($item->params->get('menu-anchor_css', ''), ENT_COMPAT, 'UTF-8', false);
		$item->anchor_title = htmlspecialchars($item->params->get('menu-anchor_title', ''), ENT_COMPAT, 'UTF-8', false);
		$item->menu_image   = $item->params->get('menu_image', '') ?
			htmlspecialchars($item->params->get('menu_image', ''), ENT_COMPAT, 'UTF-8', false) : '';
		$pattern            = '/\[modid=([0-9]+)\]/';

		if (preg_match($pattern, $item->title, $result))
		{
			$item->title = preg_replace($pattern, '', $item->title);
			$modules     = &RedMegaMenuModuleHelper::getAllModules();

			if (isset($modules[$result[1]]) && $modules[$result[1]]->module != 'mod_redshopb_megamenu')
			{
				$item->type    = 'module';
				$item->content = self::generateModuleById($result[1], 'xhtml');
			}
		}

		$result = explode("||", $item->note);

		if (isset($result[1]))
		{
			$item->desc = $result[1];
		}
		else
		{
			$item->desc = '';
		}
	}
}
