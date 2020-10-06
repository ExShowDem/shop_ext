<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity.Trait
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Trait for entities with a product
 *
 * @since  2.0
 */
trait RedshopbEntityTraitProducts
{
	/**
	 * Products in this collection
	 *
	 * @var  RedshopbEntitiesCollection
	 */
	protected $products;

	/**
	 * Get the products of this collection
	 *
	 * @return  RedshopbEntitiesCollection
	 */
	public function getProducts()
	{
		if (null === $this->products)
		{
			$this->loadProducts();
		}

		return $this->products;
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
		$collection = new RedshopbEntitiesCollection;

		if (!$this->hasId())
		{
			return $collection;
		}

		// Default state
		$state = array(
			'list.ordering'  => 'cpx.ordering',
			'list.direction' => 'ASC'
		);

		// Override any received state
		foreach ($modelState as $key => $value)
		{
			$state[$key] = $value;
		}

		$model    = RedshopbModel::getFrontInstance('products');
		$products = $model->search($state);

		foreach ($products as $product)
		{
			$entity = RedshopbEntityProduct::getInstance($product->id)
				->bind($product);

			$collection->add($entity);
		}

		return $collection;
	}

	/**
	 * Load products from DB
	 *
	 * @return  self
	 */
	protected function loadProducts()
	{
		$this->products = $this->searchProducts(array('list.limit' => 0, 'list.start' => 0));

		return $this;
	}
}
