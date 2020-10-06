<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('rlist');

/**
 * Order items Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldOrderitems extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Orderitems';

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

		// Get the countries.
		$items = $this->getOrderItems();

		// Build the field options.
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				$options[] = HTMLHelper::_('select.option', $item->identifier, $item->data);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Method to get the list of countries.
	 *
	 * @return  array  An array of country names.
	 */
	protected function getOrderItems()
	{
		if (empty($this->cache))
		{
			$db = Factory::getDbo();

			$query = $db->getQuery(true)
				->select($db->qn('id', 'identifier'))
				->select(
					'CONCAT(' .
					$db->qn('product_name') . ',' .
					$db->quote(' (') . ',' .
					$db->qn('product_item_sku') . ',' .
					$db->quote(')') . ') as ' .
					$db->qn('data')
				)
				->from($db->qn('#__redshopb_order_item'))
				->order($db->qn('product_name'));

			$orderId = isset($this->element['order_id']) ? (int) $this->element['order_id'] : '';

			if ($orderId == '')
			{
				// If no order is selected, no options are given
				$query->where('0 = 1');
			}
			else
			{
				$query->where('order_id = ' . $orderId);
			}

			$db->setQuery($query);

			$result = $db->loadObjectList();

			if (is_array($result))
			{
				$this->cache = $result;
			}
		}

		return $this->cache;
	}
}
