<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Field
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

jimport('joomla.filesystem.folder');

FormHelper::loadFieldClass('rlist');

/**
 * Base field for overridable list of options
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Field
 * @since       1.0
 */
class JFormFieldTemplateTheme extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	protected $type = 'TemplateTheme';

	/**
	 * Gets template themes (Wright template themes)
	 *
	 * @return  array  Array with options
	 */
	protected function getOptions()
	{
		$app      = Factory::getApplication();
		$template = $app->getTemplate();
		$options  = array();

		if (file_exists(JPATH_ROOT . '/templates/' . $template . '/wrighttemplate.php'))
		{
			$filesFound = false;

			$styles = JFolder::files(JPATH_ROOT . '/templates/' . $template . '/css', 'style-([^\.]*)\.css');

			if (count($styles))
			{
				foreach ($styles as $style)
				{
					if (!preg_match('/-responsive.css$/', $style) && !preg_match('/-extended.css$/', $style))
					{
						$item      = substr($style, 6, strpos($style, '.css') - 6);
						$val       = $item;
						$text      = ucfirst($item);
						$options[] = HTMLHelper::_('select.option', $val, Text::_($text));
					}
				}
			}
		}
		else
		{
			$options[] = HTMLHelper::_('select.option', '', Text::_('JNONE'));
		}

		return $options;
	}
}
