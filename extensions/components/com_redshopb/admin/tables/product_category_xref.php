<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Product category table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableProduct_Category_Xref extends RedshopbTable
{
	/**
	 * The options.
	 *
	 * @var  array
	 */
	protected $_options = array(
		'forceOrderingValues' => false
	);

	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_product_category_xref';

	/**
	 * Name of the primary key fields in the table.
	 *
	 * @var    array
	 * @since  12.2
	 */
	protected $_tbl_keys = array('product_id', 'category_id');

	/**
	 * @var  integer
	 */
	public $product_id;

	/**
	 * @var  integer
	 */
	public $category_id;

	/**
	 * @var  integer
	 */
	public $ordering = 0;

	/**
	 * Method to store a node in the database table.
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = true)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		if ($this->ordering > 0 && !$this->getOption('forceOrderingValues'))
		{
			$query->update($db->qn('#__redshopb_product_category_xref'))
				->set($db->qn('ordering') . ' = ' . $db->qn('ordering') . '+1')
				->where($db->qn('ordering') . ' >= ' . (int) $this->ordering);
			$db->setQuery($query);

			if (!$db->execute())
			{
				return false;
			}
		}

		return parent::store($updateNulls);
	}
}
