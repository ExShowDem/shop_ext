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
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Document\HtmlDocument;

/**
 * Shop helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperSeo
{
	/**
	 * Method for replacing SEO tags.
	 *
	 * @param   string  $string  String which needs tags replace.
	 * @param   string  $scope   View layout.
	 * @param   int     $id      Object id.
	 *
	 * @return  string  String with replaced SEO tags with proper values.
	 *
	 * @since   1.12.60
	 */
	public static function replaceTags($string, $scope, $id)
	{
		switch ($scope)
		{
			case 'product':
				$input        = Factory::getApplication()->input;
				$product      = RedshopbEntityProduct::getInstance($id);
				$manufacturer = $product->getManufacturer();
				$categoryId   = $input->getInt('category_id');

				if ($categoryId)
				{
					$category = RedshopbEntityCategory::getInstance($categoryId);
				}
				else
				{
					$category = $product->getCategories()->current();
				}

				// Replacing tags
				$string = self::replaceProductTags($product, $string);
				$string = self::replaceCategoryTags($category, $string);
				$string = self::replaceManufacturerTags($manufacturer, $string);

				break;
			case 'category':
				$category = RedshopbEntityCategory::getInstance($id);

				// Replacing tags
				$string = self::replaceCategoryTags($category, $string);

				break;
			case 'manufacturer':
				$manufacturer = RedshopbEntityManufacturer::getInstance($id);

				// Replacing tags
				$string = self::replaceManufacturerTags($manufacturer, $string);

				break;
			default:
				break;
		}

		// Replace site name tag
		if (strpos($string, '{sitename}') !== false)
		{
			$siteName = Factory::getConfig()->get('sitename');
			$string   = str_replace('{sitename}', $siteName, $string);
		}

		return self::prepareString($string);
	}

	/**
	 * Method for replacing category tags.
	 *
	 * @param   RedshopbEntityCategory  $category  Category entity.
	 * @param   string                  $string    String to replace tags.
	 *
	 * @return  string  String with replaced tags.
	 *
	 * @since   1.12.60
	 */
	private static function replaceCategoryTags($category, $string)
	{
		if (empty($category))
		{
			return str_replace(
				array(
					'{categoryname}',
					'{categorytree}',
					'{categorydesc}'
				),
				'',
				$string
			);
		}

		// Check & replace category name tag
		if (strpos($string, '{categoryname}') !== false)
		{
			$string = str_replace('{categoryname}', self::prepareString($category->get('name', '')), $string);
		}

		// Check & replace category tree tag
		if (strpos($string, '{categorytree}') !== false)
		{
			$parents = $category->getParents();

			if (!empty($parents))
			{
				$tree = $parents[0]->get('name', '');

				for ($i = 1; $i < count($parents); $i++)
				{
					$tree = $parents[$i]->get('name', '') . Text::_('COM_REDSHOPB_SEO_CATEGORY_TREE_SEPARATOR') . $tree;
				}

				$string = str_replace('{categorytree}', self::prepareString($tree), $string);
			}
			else
			{
				$string = str_replace('{categorytree}', '', $string);
			}
		}

		// Check & replace category short desc tag
		if (strpos($string, '{categorydesc}') !== false)
		{
			$desc = self::prepareDescription($category->get('description', ''));

			if (!empty($desc))
			{
				$string = str_replace('{categorydesc}', $desc, $string);
			}
			else
			{
				$string = str_replace('{categorydesc}', '', $string);
			}
		}

		return $string;
	}

	/**
	 * Method for replacing product tags.
	 *
	 * @param   RedshopbEntityProduct  $product  Product entity.
	 * @param   string                 $string   String to replace tags.
	 *
	 * @return  string  String with replaced tags.
	 *
	 * @since   1.12.60
	 */
	private static function replaceProductTags($product, $string)
	{
		if (empty($product))
		{
			return str_replace(
				array(
					'{productname}',
					'{productsku}',
					'{productshortdesc}',
					'{productdesc}'
				),
				'',
				$string
			);
		}

		// Check & replace product name tag
		if (strpos($string, '{productname}') !== false)
		{
			$string = str_replace('{productname}', self::prepareString($product->get('name', '')), $string);
		}

		// Check & replace product tree tag
		if (strpos($string, '{productsku}') !== false)
		{
			$string = str_replace('{productsku}', self::prepareString($product->get('sku', '')), $string);
		}

		// Check & replace product short desc tag
		if (strpos($string, '{productshortdesc}') !== false)
		{
			$desc = $product->getIndexedDescriptions();

			if (!empty($desc[0]->description_intro))
			{
				$desc = self::prepareDescription($desc[0]->description_intro);
			}
			else
			{
				$desc = '';
			}

			if (!empty($desc))
			{
				$string = str_replace('{productshortdesc}', $desc, $string);
			}
			else
			{
				$string = str_replace('{productshortdesc}', '', $string);
			}
		}

		// Check & replace product desc tag
		if (strpos($string, '{productdesc}') !== false)
		{
			$desc = $product->getIndexedDescriptions();

			if (!empty($desc[0]->description))
			{
				$desc = self::prepareDescription($desc[0]->description);
			}
			else
			{
				$desc = '';
			}

			if (!empty($desc))
			{
				$string = str_replace('{productdesc}', $desc, $string);
			}
			else
			{
				$string = str_replace('{productdesc}', '', $string);
			}
		}

		return $string;
	}

	/**
	 * Method for replacing manufacturer tags.
	 *
	 * @param   RedshopbEntityManufacturer  $manufacturer  Manufacturer entity.
	 * @param   string                      $string        String to replace tags.
	 *
	 * @return  string  String with replaced tags.
	 *
	 * @since   1.12.60
	 */
	private static function replaceManufacturerTags($manufacturer, $string)
	{
		if (empty($manufacturer))
		{
			return str_replace(array('{manufacturer}', '{manufacturerdesc}'), '', $string);
		}

		// Check & replace manufacturer name tag
		if (strpos($string, '{manufacturer}') !== false)
		{
			$string = str_replace('{manufacturer}', self::prepareString($manufacturer->get('name', '')), $string);
		}

		// Check & replace manufacturer short description tag
		if (strpos($string, '{manufacturerdesc}') !== false)
		{
			$desc = self::prepareDescription($manufacturer->get('description', ''));

			if (!empty($desc))
			{
				$string = str_replace('{manufacturerdesc}', $desc, $string);
			}
			else
			{
				$string = str_replace('{manufacturerdesc}', '', $string);
			}
		}

		return $string;
	}

	/**
	 * Function for getting meta settings for provided scope.
	 *
	 * @param   string  $scope  SEO setting scope.
	 * @param   int     $id     Entity id.
	 *
	 * @return  array   Array of settings data.
	 *
	 * @since   1.12.60
	 */
	public static function getMetaSettings($scope, $id = 0)
	{
		$entityConf = array();

		if ($id > 0)
		{
			$entity = null;

			switch ($scope)
			{
				case 'category':
					$entity = RedshopbEntityCategory::getInstance($id);

					break;
				case 'product':
					$entity = RedshopbEntityProduct::getInstance($id);

					break;
				case 'manufacturer':
					$entity = RedshopbEntityManufacturer::getInstance($id);

					break;
				default:
					break;
			}

			if (!is_null($entity))
			{
				$params = new Registry($entity->get('params', ''));

				if (!empty($params->get('seo_page_titles', '')))
				{
					$entityConf['titles'] = $params->get('seo_page_titles');
				}

				if (!empty($params->get('seo_page_headings', '')))
				{
					$entityConf['headings'] = $params->get('seo_page_headings');
				}

				if (!empty($params->get('seo_page_description', '')))
				{
					$entityConf['description'] = $params->get('seo_page_description');
				}

				if (!empty($params->get('seo_page_keywords', '')))
				{
					$entityConf['keywords'] = $params->get('seo_page_keywords');
				}
			}
		}

		$config  = RedshopbEntityConfig::getInstance();
		$seoConf = array(
			'category'     => array(),
			'product'      => array(),
			'manufacturer' => array()
		);

		// Category SEO settings
		$seoConf['category']['titles']      = $config->getString('category_page_titles', '{categoryname} | {sitename}');
		$seoConf['category']['headings']    = $config->getString('category_page_headings', '{categoryname}');
		$seoConf['category']['description'] = $config->getString('category_page_description', '{categorydesc}');
		$seoConf['category']['keywords']    = $config->getString('category_page_keywords', '');

		// Product SEO settings
		$seoConf['product']['titles']      = $config->getString(
			'product_page_titles', '{productname} | {categoryname} | {manufacturer} | {productsku}'
		);
		$seoConf['product']['headings']    = $config->getString('product_page_headings', '{productname}');
		$seoConf['product']['description'] = $config->getString('product_page_description', '{productshortdesc}');
		$seoConf['product']['keywords']    = $config->getString('product_page_keywords', '');

		// Manufacturer SEO settings
		$seoConf['manufacturer']['titles']      = $config->getString('manufacturer_page_titles', '{manufacturer} | {sitename}');
		$seoConf['manufacturer']['headings']    = $config->getString('manufacturer_page_headings', '{manufacturer}');
		$seoConf['manufacturer']['description'] = $config->getString('manufacturer_page_description', '{manufacturerdesc}');
		$seoConf['manufacturer']['keywords']    = $config->getString('manufacturer_page_keywords', '');

		return array_merge($seoConf[$scope], $entityConf);
	}

	/**
	 * Method for updating document page with meta data.
	 *
	 * @param   HtmlDocument   $document     Document object.
	 * @param   string         $title        Page title.
	 * @param   string         $keywords     Page keywords.
	 * @param   string         $description  Page description.
	 *
	 * @return  void
	 *
	 * @since   1.12.60
	 */
	public static function setDocumentMeta($document, $title, $keywords, $description)
	{
		// Setting page title
		if (!empty($title))
		{
			$document->setTitle($title);
			$document->setMetaData('og:title', $title);
		}

		// Setting page keywords
		if (!empty($keywords))
		{
			$document->setMetaData('keywords', $keywords);
		}

		// Setting page description
		if (!empty($description))
		{
			$document->setDescription($description);
			$document->setMetaData('description', $description);
			$document->setMetaData("og:description", $description);
		}
	}

	/**
	 * Function for striping and replacing all html stuff from the string for SEO usage.
	 *
	 * @param   string  $string  String to prepare.
	 *
	 * @return  string  Fixed string.
	 *
	 * @since   1.12.61
	 */
	private static function prepareString($string)
	{
		$string = strip_tags($string);
		$string = preg_replace('/\s+/', ' ', $string);
		$string = html_entity_decode($string);
		$string = trim($string);
		$string = str_replace(array('"', "'"), '', $string);

		return $string;
	}

	/**
	 * Prepare SEO description string.
	 *
	 * @param   string  $desc  SEO description.
	 *
	 * @return  string
	 *
	 * @since   1.12.72
	 */
	private static function prepareDescription($desc)
	{
		$string = self::prepareString($desc);
		$temp   = explode(' ', $string);
		$string = '';

		foreach ($temp as $tmp)
		{
			if ((strlen($string) + strlen($tmp)) < 158)
			{
				$string .= $tmp;

				if (strlen($string) + 1 < 158)
				{
					$string .= ' ';
				}
			}
		}

		$string .= '...';

		return $string;
	}
}
