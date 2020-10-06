<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$data = $displayData;

if (!isset($data['button']))
{
	throw new InvalidArgumentException('The button is not passed to the layout "button.standard".');
}

/** @var RToolbarButtonStandard $button */
$button = $data['button'];

$text      = $button->getText();
$alert     = $button->getAlert();
$iconClass = $button->getIconClass();
$class     = $button->getClass();

// Get the button command.
HTMLHelper::_('behavior.framework');

$cmd = "alert('" . addslashes(Text::_($alert)) . "')";

// Get the button class.
$btnClass = 'btn';

if (!empty($class))
{
	$btnClass .= ' ' . $class;
}
?>

<button href="#" onclick="<?php echo $cmd ?>" class="<?php echo $btnClass ?>">
	<?php if (!empty($iconClass)) : ?>
		<i class="<?php echo $iconClass ?>"></i>
	<?php endif; ?>
	<?php echo $text ?>
</button>
