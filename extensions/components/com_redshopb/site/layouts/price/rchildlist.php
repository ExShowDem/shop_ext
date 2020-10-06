<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$data = (object) $displayData;

$options = !empty($data->field->ajaxchildOptions) ? $data->field->ajaxchildOptions : array();

// We won't load anything if it's not going to work
if (!empty($options['ajaxUrl']))
{
	$options['childSelector'] = isset($options['childSelector']) ? $options['childSelector'] : '.js-childlist-child';

	HTMLHelper::_('rjquery.childlist', $options['childSelector'], $options);
}

$attributes = array();

$attributes['id']            = $data->id;
$attributes['class']         = $data->element['class'] ? (string) $data->element['class'] : null;
$attributes['size']          = $data->element['size'] ? (int) $data->element['size'] : null;
$attributes['multiple']      = $data->multiple ? 'multiple' : null;
$attributes['required']      = $data->required ? 'required' : null;
$attributes['aria-required'] = $data->required ? 'true' : null;
$attributes['onchange']      = $data->element['onchange'] ? (string) $data->element['onchange'] : null;

if ((string) $data->element['readonly'] == 'true' || (string) $data->element['disabled'] == 'true')
{
	$attributes['disabled'] = 'disabled';
}

$renderedAttributes = null;

if ($attributes)
{
	foreach ($attributes as $attribute => $value)
	{
		if (null !== $value)
		{
			$renderedAttributes .= ' ' . $attribute . '="' . (string) $value . '"';
		}
	}
}

$readOnly = ((string) $data->element['readonly'] == 'true');

// If it's readonly the select will have no name
$selectName = $readOnly ? '' : $data->name;
$selected   = false;
?>

	<select name="<?php echo $selectName; ?>" <?php echo $renderedAttributes; ?>>

		<?php if ($data->options) : ?>
			<?php foreach ($data->options as $option) :?>
				<?php
				if ($option->value == $data->value)
				{
					$selected = true;
				}
				?>
				<option value="<?php echo $option->value; ?>" <?php if ($option->value == $data->value): ?>selected="selected"<?php
							   endif; ?>>
					<?php echo $option->text; ?>
				</option>
			<?php endforeach; ?>

			<?php if (!$selected): ?>
				<option value="<?php echo $data->value; ?>" selected="selected">
					<?php echo Text::_('JSELECT'); ?>
				</option>
			<?php endif; ?>
		<?php endif; ?>

	</select>
<?php if ((string) $data->element['readonly'] == 'true') : ?>
	<input type="hidden" name="<?php echo $data->name; ?>" value="<?php echo $data->value; ?>"/>
<?php endif;
