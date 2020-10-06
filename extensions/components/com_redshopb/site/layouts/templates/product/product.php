<?php
/**
 * @package     Aesir.E-Commerce.Template
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
?>
{product.print_screen_icon}
<div class="redshopb-shop-product">
	<form method="post" id="adminForm" name="adminForm">
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="option" value="com_redshopb">
		<?php echo HTMLHelper::_('form.token') ?>
	</form>

	<div class="productList container-fluid">
		<div class="row">
			<div class="col-md-10">
				<h1>{product.name}</h1>
			</div>
			<div class="col-md-2 favourite-buttons">
				{product.favoritebutton}
				{product.sendtofriend}
				{product.print_icon}
			</div>
		</div>
		<div class="row">
			<div class="col-md-6 productImagesBlock">{product.imagescarouselnavigation}</div>
			<div class="col-md-6 productInfoBlock">
				<div class="row">
					<div class="col-md-6 oneProductSku product-sku">
						<?php echo Text::_('COM_REDSHOPB_PRODUCT_SKU'); ?>: {product.sku}
					</div>
					{if product.manufacturename}
					<div class="col-md-6 manufactureName">
						{product.manufacturename}
					</div>
					{endif product.manufacturename}
				</div>

				{if product.tags}
				<div class="row">
					{product.tags}
				</div>
				{endif product.tags}

				<div class="row">
					<div class="col-md-6 span-in-stock">
						{if product.availableinstocknotice}
						<div class="availableInStockNotice">
							{product.availableinstocknotice}
						</div>
						{endif product.availableinstocknotice}
						{product.shippingavailable}
					</div>
					<?php if (RedshopbHelperPrices::displayPrices()): ?>
						<div class="col-md-6 span-ProductPrice">
							<div class="shop-product-price-title"><?php echo Text::_('COM_REDSHOPB_SHOP_PRICE'); ?> {product.unitofmeasure}
								{if product.oneproductretailprice}
								<div class="shop-product-retail-price">
									{product.oneproductretailprice}
								</div>
								{endif product.oneproductretailprice}
							</div>
							{if product.oneproductprice}
							<div class="oneProductPrice">
								{product.oneproductprice}
							</div>
							{endif product.oneproductprice}
							{if product.oneproductdiscount}
							<div class="oneProductDiscount">
								(<?php echo Text::_('COM_REDSHOPB_DISCOUNT_TITLE'); ?> {product.oneproductdiscount})
							</div>
							{endif product.oneproductdiscount}
						</div>
					<?php endif; ?>
				</div>

				<div class="span-dropdownaccessories" id="accessory__<?php echo $product->id; ?>">
					{product.dropdownaccessories}
				</div>

				{product.attributeoptions}

				{product.attributevariants}

				{if product.oneproductstockdropdown}
				<div class="oneProductStockDropdown">
					<?php echo Text::_('COM_REDSHOPB_STOCK'); ?>: {product.oneproductstockdropdown}
				</div>
				{endif product.oneproductstockdropdown}
				<div class="row">
					<div class="col-md-4 span-oneproductquantity">
						{product.oneproductquantity}
					</div>
					<div class="col-md-8 span-addtocart">
						{product.addtocart}
					</div>
				</div>

				{if product.description}
				<div class="product-description">
					<h4><?php echo Text::_('COM_REDSHOPB_DESCRIPTION'); ?></h4>
					<div class="product-description-text">
						{product.description}
					</div>
				</div>
				{endif product.description}

				{product.attribute.description}
				{product.wash_care_specs}
			</div>
		</div>

		{if product.tabinfo}
		{product.tabinfo}
		{endif product.tabinfo}

		<ul class="nav nav-tabs" id="shopProductTabs">
			{if product.fields-data}
			<li>
				<a href="#tabFieldsData" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_SHOP_PRODUCT_FIELDS_DATA'); ?>
				</a>
			</li>
			{endif product.fields-data}
			{if product.fields-data-global}
			<li>
				<a href="#tabFieldsDataGlobal" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_SHOP_PRODUCT_FIELDS_DATA_GLOBAL'); ?>
				</a>
			</li>
			{endif product.fields-data-global}
			{if product.fields-data-local}
			<li>
				<a href="#tabFieldsDataLocal" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_SHOP_PRODUCT_FIELDS_DATA_LOCAL'); ?>
				</a>
			</li>
			{endif product.fields-data-local}
			{if product.fields-documents}
			<li>
				<a href="#tabDocuments" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_SHOP_PRODUCT_DOCUMENTS'); ?>
				</a>
			</li>
			{endif product.fields-documents}
			{if product.fields-files}
			<li>
				<a href="#tabFiles" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_SHOP_PRODUCT_FIELDS_PRODUCT_FILE'); ?>
				</a>
			</li>
			{endif product.fields-files}
			{if product.fields-videos}
			<li>
				<a href="#tabVideos" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_SHOP_PRODUCT_FIELDS_PRODUCT_VIDEO'); ?>
				</a>
			</li>
			{endif product.fields-videos}
			{if product.fields-images}
			<li>
				<a href="#tabImages" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_SHOP_PRODUCT_FIELDS_PRODUCT_IMAGE'); ?>
				</a>
			</li>
			{endif product.fields-images}
		</ul>
		<div class="tab-content">
			{if product.fields-data}
			<div class="tab-pane" id="tabFieldsData">
				<div class="productFieldsData">
					{product.fields-data}
				</div>
			</div>
			{endif product.fields-data}

			{if product.fields-data-global}
			<div class="tab-pane" id="tabFieldsDataGlobal">
				<div class="productFieldsDataGlobal">
					{product.fields-data-global}
				</div>
			</div>
			{endif product.fields-data-global}

			{if product.fields-data-local}
			<div class="tab-pane" id="tabFieldsDataLocal">
				<div class="productFieldsDataLocal">
					{product.fields-data-local}
				</div>
			</div>
			{endif product.fields-data-local}

			{if product.fields-documents}
			<div class="tab-pane" id="tabDocuments">
				<div class="productFieldsDocuments">
					{product.fields-documents}
				</div>
			</div>
			{endif product.fields-documents}

			{if product.fields-files}
			<div class="tab-pane" id="tabFiles">
				<div class="productFieldsFiles">
					{product.fields-files}
				</div>
			</div>
			{endif product.fields-files}

			{if product.fields-videos}
			<div class="tab-pane" id="tabVideos">
				<div class="productFieldsVideos">
					{product.fields-videos}
				</div>
			</div>
			{endif product.fields-videos}

			{if product.fields-images}
			<div class="tab-pane" id="tabImages">
				<div class="productFieldsImages">
					{product.fields-images}
				</div>
			</div>
			{endif product.fields-images}
		</div>

		{if product.relatedproducts}
		<div class="relatedProducts">
			<h3><?php echo Text::_('COM_REDSHOPB_SHOP_RELATED_PRODUCTS'); ?></h3>
			{product.relatedproducts}
		</div>
		{endif product.relatedproducts}
		{if product.complimentaryproducts}
		<div class="complimentary">
			<h3><?php echo Text::_('COM_REDSHOPB_COMPLIMENTARY_PRODUCTS_TITLE'); ?></h3>
			{product.complimentaryproducts}
		</div>
		{endif product.complimentaryproducts}
		{if product.snippet}
		{product.snippet}
		{endif product.snippet}
	</div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function () {
		jQuery('#shopProductTabs a').first().tab('show');
	});
</script>
