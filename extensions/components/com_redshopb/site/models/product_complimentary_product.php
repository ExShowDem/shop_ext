<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Table\Table;
/**
 * Country Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelProduct_Complimentary_Product extends RedshopbModelAdmin
{
	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  Table  A Table object
	 *
	 * @since   12.2
	 * @throws  Exception
	 */
	public function getTable($name = null, $prefix = 'RedshopbTable', $options = array())
	{
		if (empty($name))
		{
			$name = 'product_complimentary';
		}

		return parent::getTable($name, $prefix, $options);
	}

	/**
	 * Method to validate the form data.
	 * Each field error is stored in session and can be retrieved with getFieldError().
	 * Once getFieldError() is called, the error is deleted from the session.
	 *
	 * @param   Form    $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 */
	public function validate($form, $data, $group = null)
	{
		$data = parent::validate($form, $data, $group);

		if (!$data)
		{
			return false;
		}

		// Validates that no duplicate records are inserted
		$productCTable = RedshopbTable::getAdminInstance('Product_Complimentary');

		if ($productCTable->load(
			array(
				'product_id' => $data['product_id'],
				'complimentary_product_id' => $data['complimentary_product_id']
			)
		))
		{
			if ($data['id'] != $productCTable->id)
			{
				Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_COMPLIMENTARY_PRODUCT_DUPLICATE'), 'error');

				return false;
			}
		}

		return $data;
	}
}
