<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Product Description table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableProduct_Description extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_product_descriptions';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  integer
	 */
	public $product_id;

	/**
	 * @var  string
	 */
	public $main_attribute_value_id;

	/**
	 * @var  string
	 */
	public $description;

	/**
	 * @var  string
	 */
	public $description_intro;

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'pim' => array(
			'erp.pim.product_description'
		),
		'erp' => array(
			'ws.product_description'
		),
		'fengel' => array(
			'fengel.product_description'
		),
		'b2b' => array(
			'erp.webservice.product_descriptions'
		)
	);

	/**
	 * WS sync mapping for other fields of the model table with other model pks
	 *
	 * @var  array
	 */
	protected $wsSyncMapFields = array(
		'product_id' => array(
			'model' => 'Products'
		),
		'main_attribute_value_id' => array(
			'model' => 'Product_Attribute_Values'
		)
	);

	/**
	 * Checks that the object is valid and able to be stored.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 */
	public function check()
	{
		if (!parent::check())
		{
			return false;
		}

		if ($this->getOption('forceWebserviceUpdate', false) == false)
		{
			// Make sure there is no other product description with the same color
			$productDescription = clone $this;

			if ($productDescription->load(
				array(
						'main_attribute_value_id' => $this->main_attribute_value_id,
						'product_id' => $this->product_id
					)
			) && $productDescription->id != $this->id)
			{
				$this->setError(Text::_('COM_REDSHOPB_PRODUCT_DESCRIPTION_EXISTS_FOR_COLOR'));

				return false;
			}
		}

		return true;
	}

	/**
	 * Method to store a node in the database table.
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = true)
	{
		// Check if description is for product general description
		if (empty($this->main_attribute_value_id))
		{
			$db = Factory::getDbo();

			$this->main_attribute_value_id = null;

			// Temporary disable foreign key checks
			$db->setQuery('SET FOREIGN_KEY_CHECKS = 0');
			$db->execute();

			if (!parent::store($updateNulls))
			{
				// Enable foreign key checks
				$db->setQuery('SET FOREIGN_KEY_CHECKS = 1');
				$db->execute();

				return false;
			}

			// Enable foreign key checks
			$db->setQuery('SET FOREIGN_KEY_CHECKS = 1');
			$db->execute();

			return true;
		}

		if (!parent::store($updateNulls))
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to bind an associative array or object to the Table instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the Table instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws  InvalidArgumentException
	 */
	public function bind($src, $ignore = array())
	{
		$description = $src['description'];
		$pattern     = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
		$tagPos      = preg_match($pattern, $description);

		if ($tagPos > 0)
		{
			list ($src['description_intro'], $description) = preg_split($pattern, $description, 2);

			return parent::bind($src, $ignore);
		}

		$src['description_intro'] = JHtmlString::truncateComplex($description, 300);

		return parent::bind($src, $ignore);
	}
}
