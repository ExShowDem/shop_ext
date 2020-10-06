<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$data = (object) $displayData;

$item = $data->item;

$config      = RedshopbEntityConfig::getInstance();
$thumbWidth  = $config->getThumbnailWidth();
$thumbHeight = $config->getThumbnailHeight();

if (isset($item->background) && $item->background != '')
:
?>
<div style="width: 100%; height: 141px; background-size: 100%; background-repeat: no-repeat; background-position: center center; background-image:url('<?php echo $item->background;?>');">
<h1 style="color:#fff; text-align: center; width: 100%; text-transform: uppercase; padding-top: 50px;"><?php echo $item->name; ?></h1>
</div>
<?php else: ?>
	<div style="width: 100%; height: 141px;">
		<h1 style="text-align: center; width: 100%; text-transform: uppercase; padding-top: 50px;"><?php echo $item->name; ?></h1>
	</div>
<?php
endif;
?>
<table style="width:100%">
	<tr>
		<td style="width: 100%; vertical-align: top; text-align: center;">
			<div class="pagination-centered" style="width: 550px; height: auto; text-align: center;">
				<?php echo RedshopbHelperProduct::getProductImageThumbHtml($item->id, 0, $item->colorId, false, 550, 550); ?>
			</div>
		</td>
	</tr>
	<tr>
		<td style="width: 100%; vertical-align: top;">
			<div style="width:100%; clear: both; display: block;">
				<table style="width:100%; border: 1px solid #ccc; border-radius: 10px; margin-top: 10px;">
					<tr>
						<td style="vertical-align: top;">
							<div class="control-group">
								<div class="control-label">
									<?php echo strtoupper($item->name); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo strtoupper($item->sku); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo (isset($item->description[$item->colorId]) ? $item->description[$item->colorId] : (isset($item->description['']) ? $item->description[''] : '')); ?>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td style="vertical-align: top;">
							<table>
								<?php
								if (isset($item->compositions))
								:
								?>
								<tr>
								<td style="width: 20%"><?php echo strtoupper(Text::_('COM_REDSHOPB_QUALITY')); ?>:</td>
								<td style="width: 80%">
								<?php foreach ($item->compositions as $composition): ?>
										</p><?php echo $composition->type . ' : ' . $composition->quality; ?></p>
								<?php endforeach; ?>
								</td>
								</tr>
								<?php
								endif;
								?>
								<?php
								if ($item->colors)
								:
								?>
								<tr>
								<td style="width: 20%"><?php echo strtoupper(Text::_('COM_REDSHOPB_COLOR')); ?>:</td>
								<td style="width: 80%"><?php echo implode(', ', $item->colors)?></td>
								</tr>
								<?php
								endif;
								?>
								<?php
								if ($item->sizes)
								:
								?>
								<tr>
								<td style="width: 20%"><?php echo strtoupper(Text::_('COM_REDSHOPB_SIZE')); ?>:</td>
								<td style="width: 80%"><?php echo $item->sizes; ?></td>
								</tr>
								<?php
								endif;
								?>
								<?php
								if ($item->wash)
								:
								?>
								<tr>
								<td style="width: 20%"><?php echo strtoupper(Text::_('COM_REDSHOPB_WASH')); ?>:</td>
								<td style="width: 80%">
								<?php
								foreach ($item->wash as $witem)
										:
									?>
									<?php echo '<img src=' . RedshopbHelperThumbnail::originalToResize($witem->image, 38, 38, 100, 0, 'wash_care_spec') . ' >'; ?>
									<?php
								endforeach;
									?>
									</td>
								</tr>
								<?php
								endif;
								?>
							</table>
						</td>
					</tr>
				</table>
			</div>
		</td>
	</tr>
</table>
