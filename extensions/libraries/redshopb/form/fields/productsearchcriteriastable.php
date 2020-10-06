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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormField;

/**
 * Product Search - Criterias selection field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.13.0
 */
class JFormFieldProductSearchCriteriasTable extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 *
	 * @since  1.13.0
	 */
	public $type = 'productsearchcriteriastable';

	/**
	 * @var  string
	 *
	 * @since  1.13.0
	 */
	protected $layout = 'redshopb.field.productsearch_criterias_table';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since  1.13.0
	 */
	protected function getInput()
	{
		$options = self::getOptions();

		return RedshopbLayoutHelper::render(
			$this->layout,
			array(
				'id'       => $this->id,
				'name'     => $this->name,
				'options'  => $options,
				'required' => $this->required,
				'value'    => $this->value,
			)
		);
	}

	/**
	 * Method for return options
	 *
	 * @return  array  List of available criterias
	 *
	 * @since  1.13.0
	 */
	private static function getOptions()
	{
		$options = array();

		$options[] = HTMLHelper::_('select.option', '', Text::_('JSELECT'));
		$options[] = HTMLHelper::_('select.option', 'product_sku', Text::_('COM_REDSHOPB_CONFIG_PRODUCT_SEARCH_ADDITIONAL_FIELDS_OPTION_PRODUCT_SKU'));
		$options[] = HTMLHelper::_('select.option', 'related_sku', Text::_('COM_REDSHOPB_CONFIG_PRODUCT_SEARCH_ADDITIONAL_FIELDS_OPTION_RELATED_SKU'));
		$options[] = HTMLHelper::_(
			'select.option', 'product_name', Text::_('COM_REDSHOPB_CONFIG_PRODUCT_SEARCH_ADDITIONAL_FIELDS_OPTION_PRODUCT_NAME')
		);
		$options[] = HTMLHelper::_(
			'select.option', 'product_description', Text::_('COM_REDSHOPB_CONFIG_PRODUCT_SEARCH_ADDITIONAL_FIELDS_OPTION_PRODUCT_DESCRIPTION')
		);
		$options[] = HTMLHelper::_(
			'select.option', 'product_item_sku', Text::_('COM_REDSHOPB_CONFIG_PRODUCT_SEARCH_ADDITIONAL_FIELDS_OPTION_ATTRIBUTE_SKU')
		);
		$options[] = HTMLHelper::_(
			'select.option', 'product_attribute_value', Text::_('COM_REDSHOPB_CONFIG_PRODUCT_SEARCH_ADDITIONAL_FIELDS_OPTION_ATTRIBUTE_VALUE')
		);
		$options[] = HTMLHelper::_(
			'select.option', 'category_name', Text::_('COM_REDSHOPB_CONFIG_PRODUCT_SEARCH_ADDITIONAL_FIELDS_OPTION_CATEGORY_NAME')
		);
		$options[] = HTMLHelper::_(
			'select.option', 'category_description', Text::_('COM_REDSHOPB_CONFIG_PRODUCT_SEARCH_ADDITIONAL_FIELDS_OPTION_CATEGORY_DESCRIPTION')
		);
		$options[] = HTMLHelper::_(
			'select.option', 'manufacturer_name', Text::_('COM_REDSHOPB_CONFIG_PRODUCT_SEARCH_ADDITIONAL_FIELDS_OPTION_MANUFACTURER_NAME')
		);
		$options[] = HTMLHelper::_(
			'select.option', 'manufacturer_sku', Text::_('COM_REDSHOPB_CONFIG_PRODUCT_SEARCH_ADDITIONAL_FIELDS_OPTION_MANUFACTURER_SKU')
		);
		$options[] = HTMLHelper::_('select.option', 'tags', Text::_('COM_REDSHOPB_CONFIG_PRODUCT_SEARCH_ADDITIONAL_FIELDS_OPTION_TAGS'));
		$options[] = HTMLHelper::_('select.option', 'image_alt_text',
			Text::_('COM_REDSHOPB_CONFIG_PRODUCT_SEARCH_ADDITIONAL_FIELDS_OPTION_IMAGE_ALT_TEXT')
		);
		$options[] = HTMLHelper::_('select.option', 'extra_fields',
			Text::_('COM_REDSHOPB_CONFIG_PRODUCT_SEARCH_ADDITIONAL_FIELDS_OPTION_EXTRA_FIELDS')
		);

		// Future code might be helpful when we will need adjust extra fields relevance priority
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->qn(array('f.id', 'f.title', 'f.name')))
			->from($db->qn('#__redshopb_field', 'f'))
			->leftJoin($db->qn('#__redshopb_type', 't') . ' ON ' . $db->qn('t.id') . ' = ' . $db->qn('f.type_id'))
			->where($db->qn('f.scope') . ' = ' . $db->quote('product'))
			->where($db->qn('f.searchable_frontend') . ' = 1')
			->where($db->qn('f.state') . ' = 1')
			->where($db->qn('t.field_name') . ' = ' . $db->quote('rText'));

		$fields = $db->setQuery($query)->loadObjectList();

		if (!empty($fields))
		{
			foreach ($fields as $field)
			{
				$options[] = HTMLHelper::_('select.option', $field->id, $field->title . ' [' . $field->name . ']');
			}
		}

		return $options;
	}
}
