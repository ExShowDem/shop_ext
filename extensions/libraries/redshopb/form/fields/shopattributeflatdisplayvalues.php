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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('rlist');

/**
 * Shop Attribute Flat Display Values Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldShopAttributeFlatDisplayValues extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	public $type = 'ShopAttributeFlatDisplayValues';

	/**
	 * @var int Collection id.
	 */
	protected $collection_id;

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
		$options = parent::getOptions();
		$app     = Factory::getApplication();

		if ($this->collection_id > 0)
		{
			$shopDropDowns = $app->getUserState('shop.dropdowns_collection_' . $this->collection_id);
		}
		else
		{
			$shopDropDowns = $app->getUserState('shop.dropdowns');
		}

		if (!empty($shopDropDowns))
		{
			$this->cache = $shopDropDowns;
		}

		// Build the field options.
		if (!empty($this->cache))
		{
			natcasesort($this->cache);

			if ($this->multiple)
			{
				$options[] = HTMLHelper::_('select.optgroup', Text::_('JOPTION_SELECT_ATTRIBUTE_NAME'));
			}

			foreach ($this->cache as $type => $values)
			{
				foreach ($values as $value)
				{
					if (!empty($value))
					{
						$options[] = HTMLHelper::_('select.option', $value, $value);
					}
				}
			}

			if ($this->multiple)
			{
				$options[] = HTMLHelper::_('select.optgroup', Text::_('JOPTION_SELECT_ATTRIBUTE_NAME'));
			}
		}

		return $options;
	}
}
