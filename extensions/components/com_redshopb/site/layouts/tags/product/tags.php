<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

$product = $displayData['product'];

$tags = RedshopbEntityProduct::getInstance($product->id)->getTags();

if (!empty($tags)): ?>
	<div class="productTagList">
		<ul class="productTags">
		<?php foreach ($tags as $tag): ?>
			<li class="productTag"><?php echo $tag->get('name'); ?></li>
		<?php endforeach; ?>
		</ul>
	</div>
<?php endif;
