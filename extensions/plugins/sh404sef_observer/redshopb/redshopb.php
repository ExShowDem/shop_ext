<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Plugin
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\CMSPlugin;
use Sh404sefObserver\Helper\PreprocessHelper;
use Sh404sefObserver\Helper\UrlHelper;
use Sh404sefObserver\Observer\Sh404sefObserver;
use Joomla\Utilities\ArrayHelper;

JLoader::import('redshopb.library');

/**
 * sh404sef_observer Plugin
 *
 * @package     Aesir.E-Commerce
 * @subpackage  sh404sef_observer
 * @since       1.0
 */
class PlgSh404sef_ObserverRedshopb extends CMSPlugin
{
	/**
	 * @var boolean
	 * @since  2.6.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * @var string
	 * @since  2.6.0
	 */
	protected $option = 'com_redshopb';

	/**
	 * On Sh404sef Observer Before Store Table Item(except Menu Item)
	 *
	 * @param   Sh404sefObserver  $observer  Observer object
	 * @param   int               $pk        Primary key
	 *
	 * @return  void
	 *
	 * @since  1.0
	 */
	public function onSh404sefObserverBeforeStoreTable($observer, $pk)
	{
		$table = $observer->getTable();

		switch ($table->get('_tbl'))
		{
			case '#__redshopb_manufacturer':
			case '#__redshopb_product':
			case '#__redshopb_category':
				$tableAfterLoad = $table->get('propertiesAfterLoad');

				if (empty($tableAfterLoad))
				{
					$cloneTable = clone $table;

					if (!$cloneTable->load($pk))
					{
						return;
					}

					$tableAfterLoad = $cloneTable->getProperties(true);
				}

				$subCondition = [
					'option' => $this->option,
					'view' => 'shop',
					'id' => $pk
				];

				switch ($table->get('_tbl'))
				{
					case '#__redshopb_manufacturer':
						if ($table->get('alias') != $tableAfterLoad['alias'])
						{
							$subCondition['layout'] = 'manufacturer';
							$observer->addCondition($subCondition);
						}

						break;
					case '#__redshopb_product':
						$updateNeeded = false;

						if ($table->get('alias') != $tableAfterLoad['alias'])
						{
							$updateNeeded = true;
						}
						else
						{
							$categoriesDiff1 = array_diff($tableAfterLoad['categories'], $table->get('categories', []));
							$categoriesDiff2 = array_diff($table->get('categories', []), $tableAfterLoad['categories']);

							if (!empty($categoriesDiff1) || !empty($categoriesDiff2))
							{
								$updateNeeded = true;
							}
						}

						if ($updateNeeded)
						{
							$subCondition['layout'] = 'product';
							$observer->addCondition($subCondition);
						}
						break;
					case '#__redshopb_category':
						if ($table->get('alias') != $tableAfterLoad['alias']
							|| $table->get('parent_id') != $tableAfterLoad['parent_id'])
						{
							$this->processCategoryChildren($pk, $observer);
						}

						break;
				}
				break;
		}
	}

	/**
	 * On Sh404sef Observer Before Delete Table Item
	 *
	 * @param   Sh404sefObserver  $observer  Observer object
	 * @param   mixed             $pk        An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  void
	 *
	 * @since  1.0
	 */
	public function onSh404sefObserverBeforeDeleteItem($observer, $pk = null)
	{
		$table  = $observer->getTable();
		$tblKey = $table->get('_tbl_key');

		if (is_null($pk) && $table->get($tblKey))
		{
			$pk = array($tblKey => $table->get($tblKey));
		}
		elseif (!is_array($pk))
		{
			$pk = array($tblKey => $pk);
		}

		foreach ($pk as $key => $value)
		{
			if (!$value)
			{
				continue;
			}

			switch ($table->get('_tbl'))
			{
				case '#__redshopb_manufacturer':
					$observer->addCondition([
						'option' => $this->option,
						'view' => 'shop',
						'id' => $value,
						'layout' => 'manufacturer'
						], true
					);
					break;
				case '#__redshopb_product':
					$observer->addCondition([
						'option' => $this->option,
						'view' => 'shop',
						'id' => $value,
						'layout' => 'product'
						],  true
					);
					break;
				case '#__redshopb_category':
					$this->processCategoryChildren($value, $observer, true);
					break;
			}
		}
	}

	/**
	 * On Sh404sef Observer Changes a On Menu Item
	 *
	 * @param   Sh404sefObserver  $observer  Observer object
	 * @param   string            $link      Menu URL
	 *
	 * @return  void
	 * @throws Exception
	 * @since  1.0
	 */
	public function onSh404sefObserverChangesOnMenuItem($observer, $link)
	{
		$options = (array) (new Uri($link))->getQuery(true);

		if (empty($options['option']) || $options['option'] != $this->option)
		{
			return;
		}

		$homeMenuItem = Factory::getApplication()->getMenu()->getDefault();

		if (!empty($options['view']))
		{
			switch ($options['view'])
			{
				case 'dashboard':
					$observer->addCondition([
						'option' => $this->option,
						'Itemid' => $homeMenuItem->id
						]
					);
					break;
				case 'shop':
					if (!empty($options['layout']))
					{
						switch ($options['layout'])
						{
							case 'categories':
								$this->processCategoryChildren(1, $observer);
								break;
							case 'category':
								$this->processCategoryChildren((!empty($options['id']) ? $options['id'] : 1), $observer);
								break;
							case 'product':
							default:
								$subCondition = array(
									'view' => 'shop',
									'layout' => $options['layout'],
									'option' => $this->option
								);

								if (!empty($options['id']))
								{
									$subCondition['id'] = $options['id'];
								}

								$observer->addCondition($subCondition);
								break;
						}
					}
					else
					{
						$observer->addCondition([
							'option' => $this->option,
							'view' => $options['view']
							]
						);
					}

					break;
				case 'manufacturerlist':
					$observer->addCondition([
						'option' => $this->option,
						'view' => $options['view'],
						'Itemid' => $homeMenuItem->id
						]
					);

					$observer->addCondition([
						'option' => $this->option,
						'view' => 'shop',
						'layout' => 'manufacturer',
						'Itemid' => $homeMenuItem->id
						]
					);
					break;
				default:
					$subCondition = array(
						'option' => $this->option,
						'view' => $options['view']
					);

					if (!empty($options['layout']))
					{
						$subCondition['layout'] = $options['layout'];
					}

					if (!empty($options['id']))
					{
						$subCondition['id'] = $options['id'];
					}

					$observer->addCondition($subCondition);
					break;
			}
		}
		else
		{
			$observer->addCondition([
				'option' => $this->option,
				'Itemid' => $homeMenuItem->id
				]
			);
		}
	}

	/**
	 * Remove Children categories and products
	 *
	 * @param   int               $categoryId  Category id
	 * @param   Sh404sefObserver  $observer    Conditions
	 * @param   boolean           $toDelete    To delete
	 *
	 * @return  void
	 *
	 * @since  1.0
	 */
	protected function processCategoryChildren($categoryId, $observer, $toDelete = false)
	{
		$categories = array_keys(UrlHelper::getChildrenItems($categoryId, '#__redshopb_category', true));

		if (!empty($categories))
		{
			foreach ($categories as $oneCategory)
			{
				// Remove category
				$observer->addCondition([
					'view' => 'shop',
					'layout' => 'category',
					'id' => $oneCategory,
					'option' => $this->option
					], $categoryId == $oneCategory ? $toDelete : false
				);
			}

			$products = $this->getProductsByCategories($categories);

			if (!empty($products))
			{
				foreach ($products as $productId)
				{
					$observer->addCondition([
						'view' => 'shop',
						'layout' => 'product',
						'id' => $productId,
						'option' => $this->option
						]
					);
				}
			}
		}
	}

	/**
	 * @param   array|integer  $catIds  Cat ids
	 *
	 * @return array
	 * @since  2.6.0
	 */
	protected function getProductsByCategories($catIds)
	{
		$catIds = ArrayHelper::toInteger((array) $catIds);

		if (empty($catIds))
		{
			return [];
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('p.id')
			->from($db->qn('#__redshopb_product', 'p'))
			->leftJoin($db->qn('#__redshopb_product_category_xref', 'pcx') . ' ON p.id = pcx.product_id')
			->where('(p.category_id IN (' . implode(',', $catIds) . ') OR pcx.category_id IN (' . implode(',', $catIds) . '))')
			->group('p.id');

		return (array) $db->setQuery($query)->loadColumn();
	}

	/**
	 * @param   Uri  $uri  URI
	 *
	 * @return boolean|null
	 * @since  2.6.0
	 */
	public function onCheckIfRelatedEntityStillExist(Uri $uri)
	{
		if ((string) $uri->getVar('option') != $this->option
			|| (string) $uri->getVar('view') != 'shop'
			|| empty($uri->getVar('layout'))
			|| empty($uri->getVar('id')))
		{
			return null;
		}

		$layout = ucfirst((string) $uri->getVar('layout'));
		$class  = 'RedshopbEntity' . $layout;

		if (class_exists($class))
		{
			$isLoaded = $class::load((int) $uri->getVar('id'))->isLoaded();

			$class::clearInstance((int) $uri->getVar('id'));

			return $isLoaded;
		}

		return null;
	}

	/**
	 * Catch state changes from list views
	 *
	 * @param   string   $context  Context
	 * @param   array    $pks      Ids
	 * @param   integer  $value    Value
	 *
	 * @return boolean
	 * @since  2.6.0
	 */
	public function onContentChangeState($context, $pks, $value)
	{
		if (!\Sh404sefFactory::getConfig()->Enabled)
		{
			return true;
		}

		switch ($context)
		{
			case 'com_redshopb.category':
				$preprocess = new PreprocessHelper;

				// Un-publish children as well
				if ($value != 1)
				{
					$pks = array_keys(UrlHelper::getChildrenItems($pks, '#__redshopb_category', true));
				}

				if (!empty($pks))
				{
					$products = $this->getProductsByCategories($pks);

					if (!empty($products))
					{
						foreach ($products as $productId)
						{
							$preprocess->addCondition([
									'view' => 'shop',
									'layout' => 'product',
									'id' => $productId,
									'option' => $this->option
								]
							);
						}
					}
				}

				$preprocess->processSefUrl();
				break;
		}

		return true;
	}
}
