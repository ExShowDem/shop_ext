<?php
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$id      = $displayData['id'];
$sku     = $displayData['sku'];
$name    = $displayData['name'];
$image   = $displayData['image'];
$message = $displayData['message'];
?>

<div id="addToCartModal" class="vanirModal"></div>
<div id="addToCartModalContent" class="vanirModalContent">
	<span class="close right">&times;</span>
	<div class="row-fluid description">
		<div class="span4 text-center">
			<img src="<?php echo $image; ?>" />
		</div>
		<div class="span8">
			<h2><?php echo $name; ?></h2>

			<?php if ($message) : ?>
				<?php echo $message; ?>
			<?php else : ?>
				<p><?php echo Text::_('COM_REDSHOPB_SHOP_WAS_SUCCESSFULLY_ADDED_TO_THE_CART'); ?></p>
			<?php endif; ?>
			<hr />
			<div class="row-fluid">
				<div class="span4">
					<p class="close shop-more"><?php echo Text::_('COM_REDSHOPB_SHOP_MORE'); ?></p>
				</div>
				<div class="span5 offset3">
					<a href="<?php echo Route::_("index.php?option=com_redshopb&view=shop&layout=cart"); ?>" class="modalbtn"><?php echo Text::_('COM_REDSHOPB_GOTO_CHECKOUT'); ?></a>
				</div>
			</div>
		</div>
	</div>
</div>
