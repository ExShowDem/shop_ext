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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

FormHelper::loadFieldClass('rlist');

/**
 * AttributeType Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldAttributeValue extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'AttributeValue';

	/**
	 * Method to get the field input for a tag field.
	 *
	 * @return  string  The field input.
	 */
	protected function getInput()
	{
		// Get the field id
		$id    = isset($this->element['id']) ? $this->element['id'] : null;
		$cssId = '#' . $this->getId($id, $this->element['name']);

		// Load the ajax-chosen customised field
		HTMLHelper::_('rbproduct.attributevaluefield', $cssId);

		// String in format 2,5,4
		if (is_string($this->value))
		{
			$this->value = explode(',', $this->value);
		}

		return parent::getInput();
	}

	/**
	 * Method to get a list of tags
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		// Get the id of the attribute.
		$attributeId = $this->form->getValue('id');

		if (is_null($attributeId))
		{
			return array();
		}

		$attributeType = RedshopbHelperProduct_Attribute::getType($attributeId);

		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('pav.id AS value');

		switch ($attributeType)
		{
			case 0:
				$query->select('pav.string_value AS text');
				break;

			case 1:
				$query->select('pav.float_value AS text');
				break;

			case 2:
				$query->select('pav.int_value AS text');
				break;

			default :
				return array();
		}

		$query->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
			->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pav.product_attribute_id = pa.id')
			->where('pav.product_attribute_id = ' . $db->q($attributeId))
			->order('pav.ordering');

		RedshopbHelperProduct_Attribute::replaceSizeLanguageQuery(
			array(RDatabaseSqlparserSqltranslation::createTableJoinParam('pa.name', '=', $db->quote('Str.')),
			)
		);
		$db->setQuery($query);
		$items = $db->loadObjectList();
		RedshopbHelperProduct_Attribute::clearSizeLanguageQuery();

		if (!is_array($items))
		{
			$items = array();
		}

		$options = array();

		// Build the field options.
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				$options[] = HTMLHelper::_('select.option', $item->value, $item->text);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
