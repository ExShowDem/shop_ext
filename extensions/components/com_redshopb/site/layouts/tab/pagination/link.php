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

$item = $displayData['data'];

$display = $item->text;

switch ((string) $item->text)
{
	// Check for "Start" item
	case Text::_('JLIB_HTML_START') :
		$icon = "icon-backward";
		break;

	// Check for "Prev" item
	case $item->text == Text::_('JPREV') :
		$item->text = Text::_('JPREVIOUS');
		$icon       = "icon-step-backward";
		break;

	// Check for "Next" item
	case Text::_('JNEXT') :
		$icon = "icon-step-forward";
		break;

	// Check for "End" item
	case Text::_('JLIB_HTML_END') :
		$icon = "icon-forward";
		break;

	default:
		$icon = null;
		break;
}

if ($icon !== null)
{
	$display = '<i class="' . $icon . '"></i>';
}

if ($displayData['active'])
{
	if ($item->base > 0)
	{
		$limit = 'limitstart.value=' . $item->base;
	}
	else
	{
		$limit = 'limitstart.value=0';
	}

	$cssClasses = array();

	$title = '';

	if (!is_numeric($item->text))
	{
		HTMLHelper::_('vnrbootstrap.tooltip');
		$cssClasses[] = 'hasTooltip';
		$title        = ' title="' . $item->text . '" ';
	}

	$onClick = "document." . $item->formName . "." . $item->prefix . $limit . "; document.forms['" . $item->formName . "'].submit();return false;";
}
else
{
	$class = (property_exists($item, 'active') && $item->active) ? 'active' : 'disabled';
}
?>
<?php if ($displayData['active']) : ?>
	<li>
		<a class="<?php echo implode(' ', $cssClasses); ?>" <?php echo $title; ?> href="#" onclick="<?php echo $onClick; ?>">
			<?php echo $display; ?>
		</a>
	</li>
<?php else : ?>
	<li class="<?php echo $class; ?>">
		<span><?php echo $display; ?></span>
	</li>
<?php endif;
