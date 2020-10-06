<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

// HTML helpers
HTMLHelper::_('rjquery.framework');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('rsortablelist.main');
HTMLHelper::_('rsearchtools.main');
HTMLHelper::_('rjquery.flexslider', '.flexslider', array('slideshow' => false, 'directionNav' => false, 'animation' => 'slide', 'animationLoop' => false));


RedshopbHtml::loadFooTable();

$url = 'index.php?option=com_redshopb&view=product_item&layout=edit';

$pricesUrl   = 'index.php?option=com_redshopb&view=all_prices';
$discountUrl = 'index.php?option=com_redshopb&view=all_discounts';

$productId = $this->item->product_id;

if (!empty($productId))
{
	$pricesUrl   .= '&product_id=' . $productId;
	$discountUrl .= '&product_id=' . $productId;
}

$productItemId = $this->item->id;

if (!empty($productItemId))
{
	$url         .= '&id=' . $productItemId;
	$pricesUrl   .= '&product_item_id=' . $productItemId;
	$discountUrl .= '&product_item_id=' . $productItemId;
}

$return = Factory::getApplication()->input->get('return');

if (!empty($return))
{
	$pricesUrl   .= '&return=' . $return;
	$discountUrl .= '&return=' . $return;
}

$action      = RedshopbRoute::_($url);
$config      = RedshopbEntityConfig::getInstance();
$thumb       = RedshopbHelperProduct::getProductImageThumbHtml($productId, $this->item->id);
$thumbWidth  = $config->getThumbnailWidth();
$thumbHeight = $config->getThumbnailHeight();
$input       = Factory::getApplication()->input;
$tab         = $input->getString('tab', 'Prices');

if ($tab != 'Prices' || $tab != 'Discounts')
{
	$tab = 'Prices';
}

$fromProductView = RedshopbInput::isFromProduct();
$isNew           = (int) $this->item->id <= 0;

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<?php if (true == false) : ?>
	<script type="text/javascript">
	var loadedProductTabs = {};
	(function ($) {
		function ajaxExecute(tabName) {
			// Tab already loaded
			if (loadedProductTabs[tabName] == true)
			{
				return true;
			}

			// Perform the ajax request
			$.ajax({
				url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=product_item.ajax'
					+ tabName
					+ '&view=product_item&productId=<?php echo $productId ?>&id=<?php echo $this->item->id ?>',
				type: 'POST',
				data: '<?php echo Session::getFormToken();?>=1'
			}).done(function (data)
			{
				$('.' + tabName + '-content').replaceWith(data);
				$('select').chosen();
				$('.chzn-search').hide();
				$('.hasTooltip').tooltip({"animation": true, "html": true, "placement": "top",
					"selector": false, "title": "", "trigger": "hover focus", "delay": 0, "container": false});
				loadedProductTabs[tabName] = true;
			});
		}

		$(document).ready(function ()
		{
			// Show the corresponding tab
			jQuery('#productItemTabs a[href="#<?php echo $tab ?>"]').tab('show');
			ajaxExecute('prices');
			ajaxExecute('discounts');
		});

	})(jQuery);
	</script>
<?php endif; ?>

<div class="row-fluid redshopb-product_item">
	<div class="col-md-12">
		<form action="<?php echo $action; ?>"
			  method="post" name="adminForm" id="adminForm"
			  class="form-validate form-horizontal redshopb-product_item-form">
			<?php if ((bool) $this->item->discontinued) : ?>
				<div class="alert">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					<strong>
						<i class="icon-warning-sign"></i>
						<?php echo Text::_('COM_REDSHOPB_ITEM_DISCONTINUED_ALERT') ?>
					</strong>
				</div>
			<?php endif; ?>

			<div class="row-fluid">
				<div class="col-md-3">
					<ul class="thumbnails">
						<li>
							<div class="thumbnail pagination-centered" style="width: <?php echo $thumbWidth;?>px; height:<?php echo $thumbHeight;?>px">
								<?php echo $thumb ?>
							</div>
						</li>
					</ul>
				</div>
				<div class="col-md-9">

					<div class="form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('product_id'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('product_id'); ?>
						</div>
					</div>

					<div class="form-group">
						<div class="control-label">
							<label>
								<?php echo Text::_('COM_REDSHOPB_SKU') ?>
							</label>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('sku'); ?>
						</div>
					</div>

				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('state'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('state'); ?>
					</div>
				</div>
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('stock_lower_level'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('stock_lower_level'); ?>
					</div>
				</div>
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('stock_upper_level'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('stock_upper_level'); ?>
					</div>
				</div>
			</div>

			<!-- hidden fields -->
			<input type="hidden" name="option" value="com_redshopb">
			<?php echo $this->form->getInput('product_id'); ?>
			<?php echo $this->form->getInput('id'); ?>
			<input type="hidden" name="task" value="">
			<input type="hidden" name="return" value="<?php echo $return; ?>">
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	</div>
</div>

<!-- Tabs -->
<?php if ($this->item->id) : ?>
	<script type="text/javascript">
		jQuery(document).ready(function()
		{
			redSHOPB.ajaxTabs.init('<?php echo Session::getFormToken();?>=1', true);
		});
	</script>
	<div class="row">
		<div class="col-md-12">
			<ul class="nav nav-tabs" id="productItemTabs">
				<li>
					<a href="#Prices" data-toggle="tab" data-ajax-tab-load="true"><?php echo Text::_('COM_REDSHOPB_PRICES') ?></a>
				</li>
				<li>
					<a href="#Discounts" data-toggle="tab" data-ajax-tab-load="true"><?php echo Text::_('COM_REDSHOPB_DISCOUNT_LIST_TITLE') ?></a>
				</li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane" id="Prices" data-url="<?php echo RedshopbRoute::_($pricesUrl);?>" data-load-task="all_prices.ajaxPrices">
					<div class="row ">
						<div class="col-md-12 ajax-content">
							<div class="spinner pagination-centered">
								<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
							</div>
						</div>
					</div>
				</div>
				<div class="tab-pane" id="Discounts" data-url="<?php echo RedshopbRoute::_($discountUrl);?>" data-load-task="all_discounts.ajaxDiscounts">
					<div class="row ">
						<div class="col-md-12 ajax-content">
							<div class="spinner pagination-centered">
								<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php endif;
