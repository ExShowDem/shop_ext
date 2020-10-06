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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('rlist');

/**
 * Attribute Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldAttribute extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Attribute';

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

		// Filter by state
		$state = $this->element['state'] ? (int) $this->element['state'] : null;

		// Get the attributes
		$items = $this->getAttributes($state, Factory::getApplication()->input->getInt('product_id', 0));

		// Build the field options
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
	 * Method to get the list of attributes.
	 *
	 * @param   integer  $state      The attributes state
	 * @param   integer  $productId  The product id
	 *
	 * @return  array  An array of attribute names.
	 */
	protected function getAttributes($state = null, $productId = null)
	{
		$this->default = 251;

		if (empty($this->cache))
		{
			$db = Factory::getDbo();

			$query = $db->getQuery(true)
				->select('id as identifier')
				->select('name as data')
				->from('#__redshopb_product_attribute')
				->order('name');

			// Filter by state
			if (is_numeric($state))
			{
				$query->where('state = ' . $db->quote($state));
			}
			else
			{
				$query->where('state IN (0,1)');
			}

			// Filter by product id
			if ($productId)
			{
				$query->where('product_id = ' . (int) $productId);
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

	/**
	 * @param   SimpleXMLElement $element The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed            $value   The form field value to validate.
	 * @param   string           $group   The field name group control value. This acts as as an array container for the field.
	 *                                    For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                    full field name would end up being "bar[foo]".
	 * @uses FormField::setup()
	 *
	 * @return  true
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		parent::setup($element, $value, $group);
		$default     = Factory::getApplication()->input->getInt('attribute_id', null);
		$this->value = $default ? $default : $value;

		return true;
	}
}
