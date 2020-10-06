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
 * Product wash and care spec Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelProduct_Wash_Care_Spec_Xref extends RedshopbModelAdmin
{
	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'mainwarehouse';

	/**
	 * Get the associated Table
	 *
	 * @param   string  $name    Table name
	 * @param   string  $prefix  Table prefix
	 * @param   array   $config  Configuration array
	 *
	 * @return  Table
	 */
	public function getTable($name = null, $prefix = '', $config = array())
	{
		$name   = is_null($name) ? 'Product_Wash_Care_Spec_Xref' : $name;
		$prefix = is_null($prefix) ? 'RedshopbTable' : $prefix;

		// Call parent
		return parent::getTable($name, $prefix, $config);
	}
}
