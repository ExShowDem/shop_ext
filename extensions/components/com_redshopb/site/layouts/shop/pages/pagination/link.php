<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts.Shop.Pages.Pagination
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$text          = $displayData['text'];
$active        = $displayData['active'];
$ajaxJS        = $displayData['ajaxJS'];
$numberOfPages = (int) $displayData['numberOfPages'];
$currentPage   = (int) $displayData['currentPage'];

switch ((string) $text)
{
	// Check for "Start" item
	case Text::_('JSTART') :
		$icon   = "icon-backward";
		$moveTo = 1;
		break;

	// Check for "Prev" item
	case $text == Text::_('JPREVIOUS') :
		$icon   = "icon-step-backward";
		$moveTo = $currentPage - 1;
		break;

	// Check for "Next" item
	case Text::_('JNEXT') :
		$icon   = "icon-step-forward";
		$moveTo = $currentPage + 1;
		break;

	// Check for "End" item
	case Text::_('JEND') :
		$moveTo = $numberOfPages;
		$icon   = "icon-forward";
		break;

	default:
		$icon   = null;
		$moveTo = $text;
		break;
}

if ($icon !== null)
{
	$display = '<i class="' . $icon . '"></i>';
}
else
{
	$display = $text;
}

if ($active) :
	$cssClasses = array();

	$title = '';

	if (!is_numeric($text))
	{
		HTMLHelper::_('bootstrap.tooltip');
		$cssClasses[] = 'hasTooltip';
		$title        = ' title="' . $text . '" ';
	}

	?>
	<li>
		<a href="javascript:void(0);"
		   rel="nofollow"
		   class="<?php echo implode(' ', $cssClasses); ?>"
			<?php echo $title; ?>
		   data-page="<?php echo $moveTo;?>"
		   data-page_total="<?php echo $numberOfPages;?>"
		   onclick="<?php echo $ajaxJS; ?>">
			<?php echo $display; ?>
		</a>
	</li>
<?php else : ?>
	<li class="disabled">
		<span><?php echo $display; ?></span>
	</li>
<?php endif;
