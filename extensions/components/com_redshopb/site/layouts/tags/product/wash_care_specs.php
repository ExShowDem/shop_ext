<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

if (!empty($product->wash_care_specs)): ?>
	<div id="product-washcarespecs" class="product-washcarespecs">
		<div class="product-washcarespecs-title">
			<?php echo Text::_('COM_REDSHOPB_SHOP_PRODUCT_WASH_CARE_SPECS') ?>
		</div>
		<div class="washcarespecs-specs">
			<?php
			foreach ($product->wash_care_specs as $washCareSpec) :
				if ($washCareSpec->image) :
					?>
					<div class="washcarespecs-spec image">
						<?php
						$washCareImage = RedshopbHelperThumbnail::originalToResize($washCareSpec->image, 150, 80, 100, 0, 'wash_care_spec');
						?>
						<?php
						if ($washCareImage) :
							?>
							<img class="hasTooltip" src="<?php echo $washCareImage ?>"
								 title="<?php echo RedshopbHelperThumbnail::safeAlt($washCareSpec->description) ?>"
							/>
							<?php
						else :
							echo RedshopbHelperMedia::drawDefaultImg(150, 80, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL'), '#999999', '#dfdfdf');
						endif;
						?>
					</div>
					<?php
				else :
					?>
					<div class="washcarespecs-spec text">
						<?php
						echo $washCareSpec->description;
						?>
					</div>
					<?php
				endif;
				?>
				<?php
			endforeach;
			?>
		</div>
	</div>
<?php endif;
