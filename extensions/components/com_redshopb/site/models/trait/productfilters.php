<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models.Trait
 *
 * @copyright   Copyright (C) 2012 - 2018 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Trait for models with product filters
 *
 * @since  1.12.61
 */
trait RedshopbModelsTraitProductFilters
{
	/**
	 * Add common product search filters
	 *
	 * @param   RedshopbModelList    $model  Model
	 * @param   JDatabaseDriver  	 $db     JDatabaseDriver object.
	 * @param   JQuery  	         $query  The query to be added upon
	 *
	 * @return  JQuery  	         $query
	 */
	public function applyCommonProductFilters($model, $db, $query)
	{
		// Filter by state
		$state = $model->getState('list.product_state', $model->getState('filter.product_state', $model->getState('filter.state')));

		if ($state == '0' || $state == 'false')
		{
			$query->where($db->qn('p.state') . ' = 0');
		}
		elseif ($state == '1' || $state == 'true')
		{
			$query->where($db->qn('p.state') . ' = 1');
		}

		// Filter by discontinued
		$discontinued = $model->getState(
			'list.product_discontinued', $model->getState('filter.product_discontinued', $model->getState('filter.discontinued'))
		);

		if ($discontinued == '0' || $discontinued == 'false')
		{
			$query->where($db->qn('p.discontinued') . ' = 0');
		}
		elseif ($discontinued == '1' || $discontinued == 'true')
		{
			$query->where($db->qn('p.discontinued') . ' = 1');
		}

		// Filter by company
		$company = $model->getState('filter.product_company', $model->getState('filter.company_id'));

		if (is_numeric($company) && $company > 0)
		{
			$query->where('p.company_id = ' . (int) $company);
		}
		elseif ($company == 'null')
		{
			$query->where('p.company_id IS NULL');
		}

		// Filter by tag
		$tag = $model->getState('filter.product_tag');

		if ($tag)
		{
			if (is_array($tag))
			{
				$tag = ArrayHelper::toInteger($tag);
				$tag = implode(',', $tag);
			}
			else
			{
				$tag = (int) $tag;
			}

			if (!empty($tag))
			{
				$query->innerJoin($db->qn('#__redshopb_product_tag_xref', 'tcx') . ' ON tcx.product_id = p.id')
					->where('tcx.tag_id IN (' . $tag . ')');
			}
		}

		// Filter by category
		$category = $model->getState('filter.product_category');

		if ($category)
		{
			if (is_array($category))
			{
				$category = ArrayHelper::toInteger($category);
			}
			else
			{
				if ($category == 'null')
				{
					$category = array($category);
				}
				else
				{
					$category = array((int) $category);
				}
			}

			$category = implode(',', $category);

			if (!empty($category) && $category != 'null')
			{
				$query->innerJoin($db->qn('#__redshopb_product_category_xref', 'pcx') . ' ON pcx.product_id = p.id')
					->where('pcx.category_id IN (' . $category . ')');
			}
			elseif (!empty($category) && $category == 'null')
			{
				$query->where($db->qn('p.category_id') . 'IS NULL');
			}
		}

		// Filter by manufacturer
		$filterManufacturer = (int) $model->getState('filter.product_manufacturer', $model->getState('filter.manufacturer_id'));

		if ($filterManufacturer)
		{
			$query->where($db->qn('p.manufacturer_id') . ' = ' . $filterManufacturer);
		}

		// Collection
		$isTotal         = $model->getState('list.isTotal', false);
		$forceCollection = $model->getState('list.force_collection', false);
		$collection      = $model->getState('filter.product_collection');

		if ($forceCollection && $collection == '')
		{
			$form            = $model->getForm();
			$userCollections = RedshopbHelperCollection::getUserCollections();

			if ($userCollections)
			{
				$form->setValue('product_collection', 'filter', $userCollections[0]->identifier);
				$model->setState('filter.product_collection', $userCollections[0]->identifier);
			}
		}

		$collection = $model->getState('filter.product_collection');

		if (!empty($collection) || $forceCollection)
		{
			if (empty($collection))
			{
				$collection = 0;
			}

			if (!$isTotal)
			{
				$query->select($db->qn('cpx.ordering', 'collection_order'));
			}

			$query->join('inner', $db->qn('#__redshopb_collection_product_xref', 'cpx') . ' ON cpx.product_id = p.id')
				->where('cpx.collection_id = ' . (int) $collection);
		}

		// List products without description
		if ($model->getState('filter.product_description') == 1)
		{
			$query->leftjoin('#__redshopb_product_descriptions AS pd ON pd.product_id = p.id');
			$query->where('pd.product_id IS NULL');
		}

		return $query;
	}
}
