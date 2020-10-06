<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('rlist');

/**
 * ProductSKU Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldProductSKU extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'ProductSKU';

	/**
	 * A static cache.
	 *
	 * @var  array
	 */
	protected $cache = array();

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options = array();

		// Get the product Id
		$id = $this->form->getValue($this->getAttribute('productid'), $this->getAttribute('group'));

		if ($id != '')
		{
			// Get the items with their SKUs
			$items = RedshopbHelperProduct::getSKUCollection($id, 'objectList', false);
		}

		$options[] = HTMLHelper::_('select.option', '', Text::_('JOPTION_SELECT_SKU'));

		// Build the field options.
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				$options[] = HTMLHelper::_('select.option', $item->pi_id, $item->sku);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
