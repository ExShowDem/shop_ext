<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

/**
 * Variables
 * =======================
 * @var  array   $displayData  Layout data.
 * @var  string  $label        The html code for the label (not required if $options['hiddenLabel'] is true).
 * @var  string  $input        The input field html code.
 */
extract($displayData);

if (!empty($options['showonEnabled']))
{
	HTMLHelper::_('jquery.framework');
	HTMLHelper::_('script', 'jui/cms.js', false, true);
}

$class = empty($options['class']) ? "" : " " . $options['class'];
$rel   = empty($options['rel']) ? "" : " " . $options['rel'];
$isBs3 = empty($options['bs3']) ? false : true;
?>
<?php if (!empty($displayData['label']) || !empty($displayData['input'])) : ?>
	<?php if ($isBs3): ?>
		<div class="form-group <?php echo $class; ?>"<?php echo $rel; ?>>
			<div class="container-fluid">
				<?php if (empty($options['hiddenLabel'])) : ?>
					<div class="col-md-3 col-sm-4">
						<?php echo $label ?>
					</div>
				<?php endif; ?>
				<div class="col-md-9 col-sm-8">
					<?php echo $input ?>
				</div>
			</div>
		</div>
	<?php else: ?>
		<div class="control-group<?php echo $class; ?>"<?php echo $rel; ?>>

			<?php if (empty($options['hiddenLabel'])) : ?>
				<div class="control-label"><?php echo $label; ?></div>
			<?php endif; ?>
			<div class="controls"><?php echo $input; ?></div>
		</div>
	<?php endif; ?>
<?php endif;
