<?php
/**
 * @package     Redshopb.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('rlist');

/**
 * Department Field
 *
 * @package     Redshopb.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldCalcType extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'CalcType';

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

		// Get the addresses.
		$items = $this->getCalcTypes();

		// Build the field options.
		if (!empty($items))
		{
			foreach ($items as $calcType)
			{
				$options[] = HTMLHelper::_(
					'select.option',
					$calcType->id,
					$calcType->name
				);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Method to get the list of departments.
	 *
	 * @return  array  An array of addresses.
	 */
	protected function getCalcTypes()
	{
		$db	   = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id, name');
		$query->from('#__redshopb_calc_type');

		$db->setQuery($query);

		$result = $db->loadObjectList();

		if (is_array($result))
		{
			$this->cache = $result;
		}

		return $this->cache;
	}
}
