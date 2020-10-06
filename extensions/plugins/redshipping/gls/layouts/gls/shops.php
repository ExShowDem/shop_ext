<?php

use Joomla\CMS\Language\Text;

/** @var array $displayData Imported layout data*/

if (array_key_exists('nodata', $displayData)) :
?>
<div id="gls-results">
	<?php if (true === $displayData['nodata']) : ?>
		<p><?php echo Text::_('PLG_REDSHIPPING_GLS_NO_SHOPS_FOUND'); ?></p>
	<?php else : ?>
		<?php foreach ($displayData['data'] as $shop) : ?>
			<label for="parcelshop-<?php echo $shop->Number; ?>" style="border-style: solid; padding: 5px;">
				<input type="radio" name="ParcelShopId" id="parcelshop-<?php echo $shop->Number; ?>" value="<?php echo $shop->Number; ?>"/>
				<span><?php echo $shop->CompanyName; ?></span>
				<span><?php echo $shop->Streetname; ?></span>
				<span><?php echo $shop->Streetname2; ?></span>
				<span><?php echo $shop->ZipCode; ?></span>
				<span><?php echo $shop->CityName; ?></span>
			</label>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
<?php endif;
