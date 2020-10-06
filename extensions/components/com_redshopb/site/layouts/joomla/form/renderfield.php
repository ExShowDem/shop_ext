<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

/**
 * Layout variables
 * ---------------------
 * 	$options         : (array)  Optional parameters
 * 	$label           : (string) The html code for the label (not required if $options['hiddenLabel'] is true)
 * 	$input           : (string) The input field html code
 */

if (!empty($displayData['options']['showonEnabled']))
{
	HTMLHelper::_('jquery.framework');
	HTMLHelper::_('script', 'jui/cms.js', false, true);
}

$class = empty($displayData['options']['class']) ? "" : " " . $displayData['options']['class'];
$rel   = empty($displayData['options']['rel']) ? "" : " " . $displayData['options']['rel'];
?>

<div class="form-group<?php echo $class; ?>"<?php echo $rel; ?>>

	<?php if (empty($displayData['options']['hiddenLabel'])) : ?>
		<div class="control-label"><?php echo $displayData['label']
			. (isset($displayData['options']['backWSValueButton']) ? ' ' . $displayData['options']['backWSValueButton'] : '') ?></div>
	<?php endif; ?>

	<div class="controls"><?php if (!empty($displayData['options']['hiddenLabel']) && isset($displayData['options']['backWSValueButton'])) :
			echo $displayData['options']['backWSValueButton'] . ' ';
						  endif; ?><?php echo $displayData['input']; ?></div>
</div>
