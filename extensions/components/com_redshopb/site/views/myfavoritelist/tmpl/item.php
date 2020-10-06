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

$action                  = RedshopbRoute::_('index.php?option=com_redshopb&view=myfavoritelist&layout=item&id=' . $this->item->id, false);
$myoffersPage            = RedshopbRoute::_('index.php?option=com_redshopb&view=myoffers', false);
$cartPrefix              = 'myFavoriteList';
$app                     = Factory::getApplication();
$totalCurrencies         = array();
$needChangeImpersonation = false;
$redshopbConfig          = RedshopbEntityConfig::getInstance();
$enableOffer             = $redshopbConfig->getInt('enable_offer', 1);
$isShop                  = RedshopbHelperPrices::displayPrices();

$customerId   = $app->getUserState('shop.customer_id', 0);
$isSuperAdmin = RedshopbHelperACL::isSuperAdmin();

if ($isSuperAdmin && empty($customerId))
{
	$needChangeImpersonation = true;
}

$isNew = $this->item->id ? false : true;

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('behavior.modal', '.jmodal');
HTMLHelper::script('com_redshopb/redshopb.favoritelist.js', array('framework' => false, 'relative' => true));

$productTableLayoutOptions = array(
	'action' => $action,
	'products' => $this->products,
	'item' => $this->item,
	'quantities' => array(),
	'cart_prefix' => $cartPrefix
);

$this->form->setFieldAttribute('user_id', 'type', 'hidden');
$this->form->setFieldAttribute('name', 'type', 'hidden');
RedshopbHtml::loadFooTable();
echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?><script type="text/javascript">
	(function ($) {
		Joomla.submitbutton = function (task) {
			var $fields = $('#js-product-list-wrapper input.productQuantity, #js-product-list-wrapper .collectionIdClass').clone();
			$('#favoriteListExtraFields').empty().html($fields);
			Joomla.submitform(task);
		}
	})(jQuery);
</script>
<div class="redshopb-myfavoritelist">
	<h2><?php echo $this->item->name . ': <small>' . Text::_('COM_REDSHOPB_MYFAVORITELIST_SHARED_LIST') . '</small>';?></h2>
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal redshopb-myfavoritelist-form">
		<!-- hidden fields -->
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" id="myFavoriteListId">
		<input type="hidden" name="task" value="">
		<div id="favoriteListExtraFields" class="hide"></div>
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
	<?php echo RedshopbLayoutHelper::render('myfavoritelists.producttable', $productTableLayoutOptions);?>
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
</div>
