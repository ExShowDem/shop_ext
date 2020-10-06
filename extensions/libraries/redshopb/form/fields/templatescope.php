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
 * Categories Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldTemplatescope extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	public $type = 'Templatescope';

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
		$db      = Factory::getDbo();
		$query   = $db->getQuery(true)
			->select($db->qn('template_group'))
			->select($db->qn('scope', 'value'))
			->from($db->qn('#__redshopb_template'))
			->group('scope')
			->order('template_group ASC, scope ASC');

		$templateGroup = $this->form->getValue((string) $this->element['templategroup'], $this->group);

		if (isset($this->element['templategroup'])
			&& $templateGroup)
		{
			$query->select('scope AS text')
				->where($db->qn('template_group') . ' = ' . $db->q($templateGroup));
		}
		else
		{
			$query->select('CONCAT_WS(' . $db->q(' - ') . ', template_group, scope) AS text');
		}

		$items = $db->setQuery($query)
			->loadObjectList();

		if ($items)
		{
			foreach ($items as $item)
			{
				$options[] = HTMLHelper::_('select.option', $item->value, $item->text, 'value', 'text');
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
