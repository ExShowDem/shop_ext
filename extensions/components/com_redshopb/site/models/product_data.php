<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
/**
 * Product Data Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelProduct_Data extends RedshopbModelAdmin
{
	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'product';

	/**
	 * Get the associated Table
	 *
	 * @param   string  $name    Table name
	 * @param   string  $prefix  Table prefix
	 * @param   array   $config  Configuration array
	 *
	 * @return  Table
	 */
	public function getTable($name = 'Field_Data', $prefix = 'RedshopbTable', $config = array())
	{
		if ($name == '')
		{
			$name = 'Field_Data';
		}

		return parent::getTable($name, $prefix, $config);
	}

	/**
	 * Adds media field data
	 *
	 * @param   array   $data   Data to be stored
	 * @param   string  $scope  Scope
	 *
	 * @return integer  ID of the Field data
	 *
	 * @throws Exception
	 */
	public function addMediaFieldData($data, $scope = 'products')
	{
		/** @var RedshopbModelField_Data $model */
		$model = RModelAdmin::getInstance('Field_Data', 'RedshopbModel');

		return $model->addMediaFieldData($data, $scope);
	}
}
