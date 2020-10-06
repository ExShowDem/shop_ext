<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

extract($displayData);
?>
<div class="row">
	<div class="<?php echo (empty($tags) ? 'col-md-12' : 'col-md-4');?>">
		<textarea
			name="<?php echo $name; ?>"
			id="<?php echo $id; ?>"
			style="min-height:100px; height: auto"
			<?php echo $columns . $rows . $class . $hint . $disabled
			. $readonly . $onchange . $onclick . $required
			. $autocomplete . $autofocus . $spellcheck . $maxlength; ?>><?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?></textarea>
	</div>
	<?php if (!empty($tags)) : ?>
	<div class="col-md-8">
		<dl class="dl-horizontal">
		<?php foreach ($tags as $tag) : ?>
			<dt>{<?php echo $tag;?>}</dt>
			<dd><?php echo Text::_('COM_REDSHOPB_SEO_TAG_' . strtoupper($tag) . '_DESC'); ?></dd>
		<?php endforeach; ?>
		</dl>
	</div>
	<?php endif; ?>
</div>
