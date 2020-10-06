<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
/**
 * Product Attribute Values Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerProduct_Attribute_Values extends RedshopbControllerAdmin
{
	/**
	 * Removes an item.
	 *
	 * @return  void
	 */
	public function delete()
	{
		// If we are in the product view it's special because values are nested inside the attributes
		// So we use vid instead of cid (which is already used by the attribute types)
		if ('product' === $this->input->get('view'))
		{
			$cid = $this->input->get('vid', array(), 'array');
			$this->input->set('cid', $cid);
		}

		parent::delete();
	}

	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return	void
	 */
	public function saveOrderValueAjax()
	{
		// Get the input
		$pks = $this->input->get->get('vid', array(), 'array');

		// Sanitize the input
		$pks = ArrayHelper::toInteger($pks);

		$order    = array();
		$ordering = 1;

		foreach ($pks AS $pk)
		{
			$order[] = $ordering;
			$ordering++;
		}

		/** @var RedshopbModelProduct_Attribute_Value $model */
		$model = $this->getModel('product_attribute_value');

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		Factory::getApplication()->close();
	}
}
