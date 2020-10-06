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
use Joomla\CMS\Factory;

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('behavior.modal', '.jmodal');
HTMLHelper::script('com_redshopb/redshopb.favoritelist.js', array('framework' => false, 'relative' => true));


$action                  = RedshopbRoute::_('index.php?option=com_redshopb&view=myfavoritelist&layout=edit&id=' . $this->item->id, false);
$cartPrefix              = 'myFavoriteList';
$app                     = Factory::getApplication();
$totalCurrencies         = array();
$needChangeImpersonation = false;
$redshopbConfig          = RedshopbEntityConfig::getInstance();
$enableOffer             = $redshopbConfig->getInt('enable_offer', 1);
$isNew                   = $this->item->id ? false : true;

if (!$isNew)
{
	if ($this->isManage)
	{
		$this->form->setFieldAttribute('user_id', 'readonly', 'true');
	}
}
else
{
	$rUser = RedshopbHelperUser::getUser();

	if ($rUser)
	{
		$this->form->setValue('user_id', null, $rUser->id);

		// We need a user id or we cannot save the list
		$this->item->user_id = $rUser->id;
	}
}

$customerId   = $app->getUserState('shop.customer_id', 0);
$isSuperAdmin = RedshopbHelperACL::isSuperAdmin();

if ($isSuperAdmin && empty($customerId))
{
	$needChangeImpersonation = true;
}

RedshopbHtml::loadFooTable();
echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();


$searchOptions = array(
	'placeholder' => Text::_('COM_REDSHOPB_MYFAVORITELIST_PRODUCTS_ADD_PRODUCT'),
	'icon_class' => 'icon-plus',
	'button_text' => Text::_('JADD'),
	'fav_id' => $this->item->id
);

$productTableLayoutOptions = array(
	'action' => $action,
	'products' => $this->products,
	'item' => $this->item,
	'quantities' => array(),
	'cart_prefix' => $cartPrefix
);
?>
<script type="text/javascript">
	(function ($) {
		Joomla.submitbutton = function (task) {
			var $fields = $('#js-product-list-wrapper input.productQuantity, #js-product-list-wrapper .collectionIdClass').clone();
			$('#favoriteListExtraFields').empty().html($fields);
			Joomla.submitform(task);
		}
	})(jQuery);
</script>
<div class="redshopb-myfavoritelist">
	<div class="row-fluid">
		<div class="span12">
			<form action="<?php echo $action; ?>"
				  method="post"
				  name="adminForm"
				  id="adminForm"
				  class="form-validate form-horizontal redshopb-myfavoritelist-form">
				<?php echo $this->form->renderField('name'); ?>

				<?php if ($this->isManage) : ?>
					<?php echo $this->form->renderField('user_id'); ?>
				<?php else: ?>
					<input type="hidden" name="jform[user_id]" id="jform_user_id" value="<?php echo $isNew ? $rUser->id : $this->item->user_id; ?>" />
				<?php endif; ?>
				<?php echo $this->form->renderField('visible_others'); ?>
				<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" id="myFavoriteListId">
				<input type="hidden" name="task" value="">
				<div id="favoriteListExtraFields" class="hide"></div>
				<?php echo HTMLHelper::_('form.token'); ?>
			</form>
		</div>
	</div>
	<?php if ($this->item->id):?>
		<script type="text/javascript">
			jQuery(document)
				.ready(function()
				{
					jQuery('#js-product-search')
						.on('keyup', function(event) {
							redSHOPB.ajax.search(event, redSHOPB.favoritelist.productSelect);
						});
				});
		</script>
		<div class="row-fluid">
			<div class="span12">
				<div class="row-fluid">
					<div class="span12">
						<h3>
							<?php echo Text::_('COM_REDSHOPB_MYFAVORITELIST_PRODUCTS_LIST'); ?>
						</h3>
					</div>
				</div>
				<div class="row-fluid">
					<div class="span12">
						<?php echo RedshopbLayoutHelper::render('myfavoritelists.search', $searchOptions);?>
					</div>
				</div>
				<div class="row-fluid">
					<div class="span12">
						<?php // @TODO: Currently work on generate row data same as "shop.cart" layout ?>
						<?php echo RedshopbLayoutHelper::render('myfavoritelists.producttable', $productTableLayoutOptions);?>
					</div>
				</div>
			</div>
		</div>

		<?php
		if ($needChangeImpersonation)
		{
			echo HTMLHelper::_(
				'vnrbootstrap.renderModal', 'changeimpersonation',
				array('title' => Text::_('PLG_REDSHOPB_NEED_IMPERSONATE')),
				RedshopbLayoutHelper::render('myfavoritelists.changeimpersonation', array('return' => base64_encode($action), 'userId' => $this->item->user_id))
			);
		}
		?>
	<?php endif;?>
</div>
