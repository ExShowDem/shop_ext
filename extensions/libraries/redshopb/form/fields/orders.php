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

jimport('redshopb.helper.acl');
jimport('redshopb.helper.user');

/**
 * Categories Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldOrders extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	public $type = 'Orders';

	/**
	 * A static cache.
	 *
	 * @var array
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

		// Get the categories.
		$items = $this->getOrders();

		// Build the field options.
		if (!empty($items))
		{
			if ($this->multiple)
			{
				$options[] = HTMLHelper::_('select.optgroup', Text::_('JOPTION_SELECT_ORDER'));
			}

			foreach ($items as $item)
			{
				if (isset($item->id))
				{
					$text = Text::_('COM_REDSHOPB_ORDER') . ' '
							. ((isset($this->element['showDates']) && (string) $this->element['showDates'] == 'true') ?
							$item->id . ' (' . HTMLHelper::_('date', $item->created_date, Text::_('DATE_FORMAT_LC3'), null) . ')' : $item->id);

					$options[] = HTMLHelper::_('select.option', $item->id, $text, 'value', 'text');
				}
				else
				{
					$options[] = HTMLHelper::_('select.option', $item->value, $item->text, 'value', 'text');
				}
			}
		}

		return $options;
	}

	/**
	 * Method to get the orders list.
	 *
	 * @return  array  An array of categories
	 */
	protected function getOrders()
	{
		$model = RedshopbModel::getAutoInstance('Orders');
		$model->getState();
		$model->setState('list.limit', 0);

		if (isset($this->element['orderstatus']))
		{
			$model->setState('filter.order_status', (int) $this->element['orderstatus']);
		}

		$options = $model->getItems();

		if (!$this->multiple && !empty($options))
		{
			$options = array_merge(parent::getOptions(), $options);
		}

		return $options;
	}
}
