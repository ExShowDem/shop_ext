<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

extract($displayData);

if (empty($tags)):
?>
	<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
<?php else :
	ksort($tags);

	foreach ($tags as $key => $value):
		if (is_array($value)): ?>
			<h5><?php echo $key; ?></h5>
			<?php
			foreach ($value as $tagName => $tagDescription):
				$cssClass = in_array($tagName, $usedTags) ? 'label-success' : 'label-default';
				?>
				<label class="label <?php echo $cssClass; ?>" title="<?php echo $tagDescription; ?>"><?php echo $tagName; ?></label>
				<?php
			endforeach;
		else:
			$cssClass = in_array($key, $usedTags) ? 'label-success' : 'label-default';
			?>
			<label class="label <?php echo $cssClass; ?>" title="<?php echo $value; ?>"><?php echo $key; ?></label>
			<?php
		endif;
	endforeach;
endif;
