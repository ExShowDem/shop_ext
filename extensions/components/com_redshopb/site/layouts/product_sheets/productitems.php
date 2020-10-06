<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$data = $displayData;

$items            = $data['items'];
$selectedProducts = !empty($data['selectedProducts']);
$dropDownTypes    = $data['dropDownTypes'];

$config      = RedshopbEntityConfig::getInstance();
$thumbWidth  = 144;
$thumbHeight = 144;
$column      = 0;
?>
<?php if (empty($items)) : ?>
	<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
<?php else : ?>
	<div class="container-fluid">
		<div class="row">
		<?php foreach ($items as $i => $item): ?>
			<?php if ($column > 0 && $column % 4 == 0) :?>
			</div>
			<div class="row">
			<?php endif; ?>
			<div class="product-box col-md-3">
				<br />
				<div class="row">
				<div class="col-md-12">
					<?php
					$selected = '';

					if (isset($dropDownTypes[$item->id]))
					{
						$options = array();

							// Generate select
						foreach ($dropDownTypes[$item->id] as $dropDownType)
							{
							$text = RedshopbHelperCollection::getProductItemValueFromType($dropDownType->type, $dropDownType, true);

							if ($selected == '')
							{
								$selected = $dropDownType->id;
							}

							if (!empty($item->dropDownSelected))
							{
								$selected = $item->dropDownSelected;
							}

							$options[] = HTMLHelper::_(
								'select.option', $dropDownType->id, $text, 'value', 'text'
							);
						}

							echo HTMLHelper::_(
								'select.genericlist', $options, 'dropDownType[' . $item->id . ']',
								' class="dropDownAttribute"', 'value', 'text', $selected,
								($selectedProducts ? 'selected_' : '')
								. 'dropDownType_'
								. $item->id
								. '_' . $dropDownType->product_attribute_id
								. (!empty($item->dropDownSelected) ? '_' . $item->dropDownSelected : '')
							);
					}
					?>
				</div>
			</div>
				<div class="productThumb" id="productThumb_<?php echo $item->id; ?>" >
					<div class="thumbnail pagination-centered" style="width: <?php echo $thumbWidth;?>px; height:<?php echo $thumbHeight;?>px">
						<?php $productItemSelectedId = (!empty($selected)) ? (int) $selected : 0;
						echo RedshopbHelperProduct::getProductImageThumbHtml($item->id, 0, $productItemSelectedId); ?>
					</div>
				</div>
				<div class="productName">
					<?php echo $this->escape($item->name); ?>
					<br />
					<?php echo $this->escape($item->sku); ?>
				</div>
				<div class="buttonProductSheets">
					<?php if ($selectedProducts) : ?>
						<button type="button" class="btn btn-danger btn-remove-from-list" name="product_<?php echo $item->id; ?>">
							<i class="icon-minus"></i>
							<?php echo Text::_('COM_REDSHOPB_PRODUCT_SHEETS_REMOVE_FROM_LIST'); ?>
						</button>
					<?php else: ?>
						<button type="button" class="btn btn-success btn-add-to-list" name="product_<?php echo $item->id; ?>">
							<i class="icon-plus"></i>
							<?php echo Text::_('COM_REDSHOPB_PRODUCT_SHEETS_ADD_TO_LIST'); ?>
						</button>
					<?php endif; ?>
				</div>
			</div>
			<?php $column++; ?>
		<?php endforeach; ?>
		</div>
	</div>
<?php endif;
