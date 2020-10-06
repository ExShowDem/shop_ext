<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
/**
 * Category Entity.
 *
 * @since  2.0
 */
class RedshopbEntityCategory extends RedshopbEntity
{
	use RedshopbEntityTraitParent, RedshopbEntityTraitFields, RedshopbEntityTraitTemplate;
	use RedshopbEntityTraitProducts
	{
		searchProducts as traitSearchProducts;
	}

	/**
	 * Child Categories
	 *
	 * @var    array
	 * @since  1.7
	 */
	protected $children;

	/**
	 * Matching template group/scopes with actual fields in the table
	 *
	 * @var   array
	 * @since 1.13
	 */
	protected $templateMatches = array(
		// Shop group
		'shop' => array (
			// Category scope
			'category' => 'template_id',
			// List product scope
			'list-product' => 'product_list_template_id',
			// Grid product scope
			'grid-product' => 'product_grid_template_id'
		)
	);

	/**
	 * Get the child categories
	 *
	 * @return  array
	 *
	 * @since   1.7
	 */
	public function getChildren()
	{
		if (null === $this->children)
		{
			$this->loadChildren();
		}

		return $this->children;
	}

	/**
	 * Get the children categories ids
	 *
	 * @return  array
	 *
	 * @since   1.7
	 */
	public function getChildrenIds()
	{
		$children = $this->getChildren();

		$ids = array();

		foreach ($children as $category)
		{
			$ids[] = $category->id;
		}

		return $ids;
	}

	/**
	 * Load child categories from DB
	 *
	 * @return  RedshopbEntityCategory
	 *
	 * @since   1.7
	 */
	protected function loadChildren()
	{
		$this->children = array();

		if (!$this->hasId())
		{
			return $this;
		}

		$model = RedshopbModel::getFrontInstance('categories');

		$state = array(
			'filter.parent_id' => $this->id
		);

		$children = $model->search($state);

		foreach ($children as $child)
		{
			$this->children[$child->id] = static::getInstance($child->id)->bind($child);
		}

		return $this;
	}

	/**
	 * Method to get all children ids including subcategories
	 *
	 * @return array
	 */
	public function getAllChildrenIds()
	{
		if (empty($this->item->lft) || empty($this->item->rgt))
		{
			return array();
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id')
			->from('#__redshopb_category')
			->where('lft > ' . (int) $this->item->lft)
			->where('rgt < ' . (int) $this->item->rgt);

		$results = $db->setQuery($query)->loadObjectList();
		$ids     = array();

		if (empty($results))
		{
			return $ids;
		}

		foreach ($results AS $result)
		{
			$ids[] = $result->id;
		}

		return $ids;
	}

	/**
	 * Get the products ids
	 *
	 * @return  array
	 *
	 * @since   1.7
	 */
	public function getProductsIds()
	{
		$products = $this->getProducts();

		$ids = array();

		foreach ($products as $product)
		{
			$ids[] = $product->id;
		}

		return $ids;
	}

	/**
	 * Search on this collection products
	 *
	 * @param   array  $modelState  State for the Products model
	 *
	 * @return  RedshopbEntitiesCollection
	 *
	 * @since   2.0
	 */
	public function searchProducts($modelState = array())
	{
		$modelState['filter.category_id'] = $this->id;

		return $this->traitSearchProducts($modelState);
	}

	/**
	 * Temp method to retrieve all products related to the category
	 *
	 * @return array
	 *
	 * @since 1.13.0
	 */
	public function getAllProductIds()
	{
		// Get the product where this category is main.
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id')
			->from($db->qn('#__redshopb_product'))
			->where('category_id = ' . $this->id);

		$mainProduct = $db->setQuery($query)->loadAssoc();

		// Get all products where this category is a in the xref table.
		$query = $db->getQuery(true);

		$query->select('product_id')
			->from($db->qn('#__redshopb_product_category_xref'))
			->where('category_id = ' . $this->id);

		$products      = $db->setQuery($query)->loadObjectList();
		$countProducts = count($products);
		$return        = array();

		if ($mainProduct !== null)
		{
			$return[] = $mainProduct['id'];
		}

		for ($i = 0; $i < $countProducts; $i++)
		{
			$id = $products[$i]->product_id;

			if ($mainProduct !== null && $mainProduct['id'] == $id)
			{
				continue;
			}

			$return[] = $id;
		}

		return $return;
	}

	/**
	 *
	 * @return stdClass
	 *
	 * @since 1.13.0
	 */
	public function getImageRecursive()
	{
		$catImg = new stdClass;

		if ($this->get('image') != '')
		{
			$catImg->name    = $this->get('image');
			$catImg->section = 'categories';

			return $catImg;
		}

		// Get all child node images.
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id, image')
			->from('#__redshopb_category')
			->where('lft >' . $this->get('lft') . ' AND rgt < ' . $this->get('rgt'))
			->order('lft');

		$categories = $db->setQuery($query)->loadObjectList();

		// Run the category tree
		foreach ($categories as $category)
		{
			if ($category->image != '')
			{
				$catImg->name    = $category->image;
				$catImg->section = 'categories';

				return $catImg;
			}
		}

		// It isn't quite good if it hits here but things can happen.
		$products = $this->getAllProductIds();

		foreach ($products as $product)
		{
			$image = RedshopbHelperProduct::getProductImage($product);

			if ($image !== null && $image->name != '')
			{
				$catImg->name    = $image->name;
				$catImg->section = 'products';

				return $catImg;
			}
		}

		foreach ($categories as $category)
		{
			$catEnt   = self::load($category->id);
			$products = $catEnt->getAllProductIds();

			foreach ($products as $product)
			{
				$image = RedshopbHelperProduct::getProductImage($product);

				if ($image !== null && $image->name != '')
				{
					$catImg->name    = $image->name;
					$catImg->section = 'products';

					return $catImg;
				}
			}
		}

		return $catImg;
	}

	/**
	 * Gets All data from the first image it finds.
	 *
	 * @param   null|integer $width  the image width you wish
	 * @param   null|integer $height the image height you wish
	 *
	 * @return false|stdClass
	 *
	 * @since 1.13.0
	 */
	public function getMainImageData($width = null, $height = null)
	{
		$image = $this->getImageRecursive();

		if (empty($image->name))
		{
			return false;
		}

		if (!is_object($image))
		{
			$imgName        = $image;
			$image          = new stdClass;
			$image->name    = $imgName;
			$image->section = 'categories';
		}

		if ($width === null || $height === null)
		{
			$path = RedshopbHelperMedia::getFullMediaPath($image->name, $image->section);
		}
		else
		{
			$path = RedshopbHelperThumbnail::originalToResize($image->name, $width, $height, 100, 0, $image->section, true);
		}

		// Setting right properties for the image to return
		unset($image->section);
		$image->alt = $this->get('name');
		$image->url = Uri::base() . $path;

		return $image;
	}
}
