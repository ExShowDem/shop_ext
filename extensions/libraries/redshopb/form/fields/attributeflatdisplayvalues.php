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
 * Attribute Flat Display Values Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldAttributeFlatDisplayValues extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	public $type = 'AttributeFlatDisplayValues';

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

		if (empty($this->cache))
		{
			// Depends on product_id or another value
			$dependsOnField = $this->element['dependsOnField'] ? $this->element['dependsOnField'] : false;

			$app   = Factory::getApplication();
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select(
					array(
						'CONCAT_WS(\' \', ' . $db->qn('pav.string_value') . ', ' . $db->qn('pav.float_value') .
						', ' . $db->qn('pav.int_value') . ') AS ' . $db->qn('value'),
						'CONCAT_WS(\' \', ' . $db->qn('pav.string_value') . ', ' . $db->qn('pav.float_value') .
						', ' . $db->qn('pav.int_value') . ') AS ' . $db->qn('text')
					)
				)
				->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
				->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pav.product_attribute_id = pa.id')
				->where('pa.main_attribute = 1')
				->group('text')
				->order('pav.ordering')
				->order('text ASC');

			if ($dependsOnField)
			{
				$dependsOnValue = (int) $this->form->getValue($dependsOnField);

				if ($dependsOnValue <= 0)
				{
					$dependsOnValue = $app->input->get((string) $dependsOnField, 0, 'int');
				}

				if ($dependsOnValue > 0)
				{
					$query->where($db->qn('pa.' . $dependsOnField) . ' = ' . $db->q($dependsOnValue));
				}

				$query->clear('select')
					->select(
						array(
							$db->qn('pav.id', 'value'),
							'CONCAT_WS(\' \', ' . $db->qn('pav.string_value') . ', ' . $db->qn('pav.float_value') .
							', ' . $db->qn('pav.int_value') . ') AS ' . $db->qn('text')
						)
					);
			}

			RedshopbHelperProduct_Attribute::replaceSizeLanguageQuery(
				array(RDatabaseSqlparserSqltranslation::createTableJoinParam('pa.name', '=', $db->quote('Str.')),
				)
			);
			$db->setQuery($query);
			$results = $db->loadObjectList();
			RedshopbHelperProduct_Attribute::clearSizeLanguageQuery();

			// Merge any additional options in the XML definition.
			$this->cache = array_merge(parent::getOptions(), $results);
		}

		// Build the field options.
		if (!empty($this->cache))
		{
			foreach ($this->cache as $item)
			{
				if (!empty($item->text))
				{
					$options[] = HTMLHelper::_('select.option', $item->value, $item->text);
				}
			}
		}

		return $options;
	}
}
