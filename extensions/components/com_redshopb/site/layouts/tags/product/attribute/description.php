<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

if (!empty($extThis->product->dropDownSelected[$product->id])): ?>
	<div id="product-attribute-description_<?php echo $product->id ?>" class="product-attribute-description">
		<?php foreach ($extThis->product->dropDownTypes[$product->id] as $dropDownType): ?>
			<?php if ($extThis->product->dropDownSelected[$product->id] == $dropDownType->id): ?>
				<?php if (!empty($dropDownType->description) && !empty($dropDownType->description)) :
					echo RedshopbHelperProduct::getProductDescription($dropDownType->description->description);
				endif; ?>
				<?php break; ?>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
<?php endif;
