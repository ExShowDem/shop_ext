<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('list');

/**
 * Field to load a list of installed components
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Field
 * @since       1.0
 */
class JFormFieldShippingplugins extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $type = 'Shippingplugins';

	/**
	 * Cached array of the plugin items.
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected static $cache = array();

	/**
	 * Method to get the options to populate to populate list
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.0
	 */
	protected function getOptions()
	{
		// Accepted modifiers
		$hash = md5($this->element);

		if (!isset(static::$cache[$hash]))
		{
			static::$cache[$hash] = parent::getOptions();
			$lang                 = Factory::getLanguage();

			$options = array();
			$db      = Factory::getDbo();
			$query   = $db->getQuery(true)
				->select('*')
				->from('#__extensions')
				->where($db->qn('type') . ' = "plugin"')
				->where($db->qn('folder') . ' IN ("redshipping", "system")');

			// Setup the query
			$db->setQuery($query);

			// Return the result
			$plugins = $db->loadObjectList();

			if (!empty($plugins))
			{
				foreach ($plugins as $value)
				{
					$extension = 'plg_redshipping_' . $value->element;
					$source    = JPATH_PLUGINS . '/' . $value->folder . '/' . $value->element;
					$params    = new Registry($value->params);

					// We are checking if this is really a shipping plugin
					if (!$params->exists('is_shipper'))
					{
						continue;
					}

					$lang->load($extension . '.sys', JPATH_ADMINISTRATOR, null, false, true)
					||	$lang->load($extension . '.sys', $source, null, false, true);

					if ($this->getAttribute('showTitle', 'true') == 'true')
					{
						$title = $params->get('shipping_title', $extension);
					}
					elseif ($this->getAttribute('showFullName', 'false') == 'true')
					{
						$title = Text::_($value->name);
					}
					else
					{
						$title = $extension;
					}

					$options[] = HTMLHelper::_('select.option', $value->element, $title);
				}

				static::$cache[$hash] = array_merge(static::$cache[$hash], $options);
			}
		}

		return static::$cache[$hash];
	}
}
