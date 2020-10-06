<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Product search compatibility class
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Database
 * @since       1.13.0
 */
class RedshopbDatabaseProductsearchCompatibility
{
	/**
	 * @var   array
	 */
	protected $appliedFields = array();

	/**
	 * @var   array
	 */
	protected $newFields = array();

	/**
	 * Convert Old Criteria
	 *
	 * @param   array  $criteria  Old style Criteria
	 *
	 * @since  1.13.0
	 */
	public function __construct($criteria)
	{
		$oldCriteria = $criteria;
		$priority    = 1;

		foreach ($oldCriteria as $criterion)
		{
			$priority++;
			$countPriorities = count($this->newFields);

			switch ($criterion)
			{
				case 'exact_product_title':
					$this->getFieldArray($priority, 'product_name', 'exact');
					break;
				case 'partially_product_title_description_extra':
					$this->getFieldArray($priority, 'product_name', 'exact_and_partial');
					$this->getFieldArray($priority, 'product_description', 'exact_and_partial');
					break;
				case 'partially_product_description_extra':
					$this->getFieldArray($priority, 'product_description', 'exact_and_partial');
					break;
				case 'partially_product_description_category_extra':
					$this->getFieldArray($priority, 'product_description', 'exact_and_partial');
					$this->getFieldArray($priority, 'category_name', 'exact_and_partial');
					break;
				case 'exact_category_name':
					$this->getFieldArray($priority, 'category_name', 'exact');
					break;
				case 'partially_category_name_description':
					$this->getFieldArray($priority, 'category_name', 'exact_and_partial');
					$this->getFieldArray($priority, 'category_description', 'exact_and_partial');
					break;
				case 'product_sku':
					$this->getFieldArray($priority, 'product_sku', 'exact_and_partial');
					break;
			}

			if ($countPriorities != count($this->newFields))
			{
				$priority++;
			}
		}
	}

	/**
	 * Get new format criteria
	 *
	 * @return array
	 *
	 * @since 1.13.0
	 */
	public function getNewFormatCriteria()
	{
		return $this->newFields;
	}

	/**
	 * getFieldArray
	 *
	 * @param   int     $priority   Priority
	 * @param   string  $fieldName  Field name
	 * @param   string  $method     Method
	 *
	 * @return void
	 *
	 * @since  1.13.0
	 */
	protected function getFieldArray($priority, $fieldName, $method = '-1')
	{
		if (array_key_exists($fieldName, $this->appliedFields))
		{
			if ($this->appliedFields[$fieldName] == 'exact')
			{
				if ($method == 'exact')
				{
					return;
				}

				foreach ($this->newFields as $newPriority => $fields)
				{
					if (property_exists($fields, $fieldName))
					{
						$priority = $newPriority;
						break;
					}
				}
			}
			else
			{
				return;
			}
		}

		if (!array_key_exists($priority, $this->newFields))
		{
			$this->newFields[$priority] = new stdClass;
		}

		$this->appliedFields[$fieldName]          = $method;
		$this->newFields[$priority]->{$fieldName} = (object) array(
			'name' => $fieldName,
			'synonym' => '-1',
			'stem' => '-1',
			'method' => $method
		);
	}
}
