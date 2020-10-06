<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('rlist');

/**
 * Order Status Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldOrderStatus extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	public $type = 'OrderStatus';

	/**
	 * Cached array of options.
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected static $options = array();

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$hash = md5($this->element);

		if (isset(static::$options[$hash]))
		{
			return static::$options[$hash];
		}

		static::$options[$hash] = array_merge(parent::getOptions(), $this->getOrderStatuses());

		return static::$options[$hash];
	}

	/**
	 * Method to get the Order Status list.
	 *
	 * @return  array  An array of order statuses.
	 */
	protected function getOrderStatuses()
	{
		$options = array();

		$statuses = RedshopbEntityOrder::getAllowedStatuses();

		foreach ($statuses as $statusId => $statusName)
		{
			$options[] = (object) array(
				'text'  => $statusName,
				'value' => $statusId
			);
		}

		return $options;
	}
}
