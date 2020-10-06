<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Input
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Input class.
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Input
 * @since       1.0
 */
final class RedshopbInput
{
	/**
	 * Get a field value from a jform.
	 *
	 * @param   string  $name     The input name
	 * @param   mixed   $default  The default value
	 *
	 * @return  mixed  The value or the default value
	 */
	public static function getField($name, $default = null)
	{
		$input = Factory::getApplication()->input;
		$form  = $input->get('jform', array(), 'array');

		if (isset($form[$name]))
		{
			return $form[$name];
		}

		return $default;
	}

	/**
	 * Check if the field variable is present.
	 *
	 * @param   string  $fieldName  Name field
	 *
	 * @return  boolean  True from company, false otherwise.
	 */
	public static function isFromField($fieldName)
	{
		$input = Factory::getApplication()->input;

		return $input->getBool($fieldName, false);
	}

	/**
	 * Get the product Id from jform.
	 *
	 * @return  mixed  The product id or NULL.
	 */
	public static function getProductIdForm()
	{
		return self::getField('product_id');
	}

	/**
	 * Get the product item Id from jform.
	 *
	 * @return  mixed  The product item id or NULL.
	 */
	public static function getProductItemIdForm()
	{
		return self::getField('product_item_id');
	}

	/**
	 * Get the company Id from jform.
	 *
	 * @return  mixed  The company id or NULL.
	 */
	public static function getCompanyIdForm()
	{
		return self::getField('company_id');
	}

	/**
	 * Get the user Id from jform.
	 *
	 * @return  mixed  The user id or NULL.
	 */
	public static function getUserIdForm()
	{
		return self::getField('user_id');
	}

	/**
	 * Get the collection Id from jform.
	 *
	 * @return  mixed  The collection id or NULL.
	 */
	public static function getCollectionIdForm()
	{
		return self::getField('collection_id');
	}

	/**
	 * Get the company Id from jform.
	 *
	 * @return  mixed  The company id or NULL.
	 */
	public static function getPriceDebtorGroupIdForm()
	{
		return self::getField('price_debtor_group_id');
	}

	/**
	 * Check if the from product variable is present.
	 *
	 * @return  boolean  True from product, false otherwise.
	 */
	public static function isFromProduct()
	{
		return self::isFromField('from_product');
	}

	/**
	 * Check if the from shop variable is present.
	 *
	 * @return  boolean  True from shop, false otherwise.
	 */
	public static function isFromShop()
	{
		return self::isFromField('from_shop');
	}

	/**
	 * Check if the from_company variable is present.
	 *
	 * @return  boolean  True from company, false otherwise.
	 */
	public static function isFromCompany()
	{
		return self::isFromField('from_company');
	}

	/**
	 * Check if the from_collection variable is present.
	 *
	 * @return  boolean  True from collection, false otherwise.
	 */
	public static function isFromCollection()
	{
		return self::isFromField('from_collection');
	}

	/**
	 * Check if the from_user variable is present.
	 *
	 * @return  boolean  True from user, false otherwise.
	 */
	public static function isFromUser()
	{
		return self::isFromField('from_user');
	}

	/**
	 * Get the department id from jform.
	 *
	 * @return  mixed  The department id or NULL.
	 */
	public static function getDepartmentIdForm()
	{
		return self::getField('department_id');
	}

	/**
	 * Check if the from_department variable is present.
	 *
	 * @return  boolean  True from department, false otherwise.
	 */
	public static function isFromDepartment()
	{
		return self::isFromField('from_department');
	}

	/**
	 * Check if the from_department variable is present.
	 *
	 * @return  boolean  True from department, false otherwise.
	 */
	public static function isFromOrder()
	{
		return self::isFromField('from_order');
	}

	/**
	 * Check if the from_price_debtor_group variable is present.
	 *
	 * @return  boolean  True from company, false otherwise.
	 */
	public static function isFromPriceDebtorGroup()
	{
		return self::isFromField('from_price_debtor_group');
	}

	/**
	 * Get request variables
	 *
	 * @param   bool  $includeFieldId  Include item id for url string
	 * @param   bool  $returnString    Return url string or array
	 * @param   bool  $force           Force cached values
	 *
	 * @return  array|null|string
	 */
	public static function getRequestVariables($includeFieldId = true, $returnString = true, $force = false)
	{
		static $foundVariables = null;

		if ($foundVariables == null || $force)
		{
			$input          = Factory::getApplication()->input;
			$inputs         = $input->getArray();
			$jForm          = $input->get('jform', array(), 'array');
			$foundVariables = array();

			foreach ($inputs as $name => $value)
			{
				if (substr($name, 0, 5) == 'from_' && $value == 1)
				{
					$cropFieldName = substr($name, 5);
					$result        = array('variable' => $name);

					if (array_key_exists($cropFieldName . '_id', $jForm) && is_string($jForm[$cropFieldName . '_id']))
					{
						$result['name']  = 'jform[' . $cropFieldName . '_id]';
						$result['value'] = $jForm[$cropFieldName . '_id'];
					}

					$foundVariables[$name] = (object) $result;
				}
			}
		}

		if ($returnString)
		{
			$append = '';

			foreach ($foundVariables as $foundVariable)
			{
				if ($includeFieldId)
				{
					$append .= '&' . $foundVariable->name . '=' . $foundVariable->value;
				}

				$append .= '&' . $foundVariable->variable . '=1';
			}

			return $append;
		}
		else
		{
			return $foundVariables;
		}
	}
}
