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

HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
RHtml::_('vnrbootstrap.modal', 'orderModal');
HTMLHelper::script('com_redshopb/redshopb.shop.js', array('framework' => false, 'relative' => true));

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=mypage');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder == 'company_name';
$app       = Factory::getApplication();
$tab       = $app->input->getString('tab');
$isShop    = RedshopbHelperPrices::displayPrices();
$ordersTab = RedshopbHelperACL::isSuperAdmin() || $isShop;

if (empty($tab))
{
	$tab = 'myPageRecentOrders';

	if (!$ordersTab)
	{
		$tab = 'myPageRecentProducts';
	}
}

$canSearch = (!empty($this->rsbUserId) && $isShop);
?>

<div class="redshopb-mypage-orders">
	<h3><?php echo Text::_('COM_REDSHOPB_MYPAGE') ?></h3>
	<?php if ($canSearch)
	:
	?>
		<div class="col-md-12 well quickOrderDiv">
			<h3><?php echo Text::_('COM_REDSHOPB_QUICK_ORDER') ?></h3>
			<?php
			if (true === $this->enableQuickOrder)
			{
				echo RedshopbLayoutHelper::render('quickorder.tool');
			}
			else
			{
				echo RedshopbLayoutHelper::render('notification.warning', array(
						'message' => Text::_('COM_REDSHOPB_QUICK_ORDER_IMPERSONATE_TO_USE')
					)
				);
			}
			?>
		</div>
	<?php endif; ?>
	<div class="well myPageTabsDiv">
		<?php if (!empty($this->impersonating)) : ?>
		<h4>
			<?php echo Text::_('COM_REDSHOPB_CONFIG_IMPERSONATION_BREADCRUMBS_HERE') . ' ' . $this->impersonating ?>
		</h4>
		<?php endif; ?>
		<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">

			<?php if (!$this->isFromMainCompany) : ?>
				<ul class="nav nav-tabs" id="mainTabs">
					<?php if ($ordersTab) : ?>
						<li><a href="#myPageRecentOrders" data-toggle="tab"><?php echo Text::_('COM_REDSHOPB_MYPAGE_RECENT_ORDERS'); ?></a></li>
					<?php endif; ?>

					<?php if (!empty($this->customerId) && !empty($this->customerType)) : ?>
						<li><a href="#myPageRecentProducts" data-toggle="tab"><?php echo Text::_('COM_REDSHOPB_MYPAGE_RECENT_PRODUCTS'); ?></a></li>
						<li><a href="#myPageMostPurchased" data-toggle="tab"><?php echo Text::_('COM_REDSHOPB_MYPAGE_MOST_PURCHASED'); ?></a></li>
					<?php endif;?>
				</ul>
				<div class="tab-content">
					<?php if ($ordersTab) : ?>
						<?php echo $this->loadTemplate('recent'); ?>
					<?php endif;?>

					<?php if (!empty($this->customerId) && !empty($this->customerType)) : ?>
						<?php echo $this->loadTemplate('recent_products'); ?>
						<?php echo $this->loadTemplate('most_purchased'); ?>
					<?php endif;?>
				</div>
			<?php else : ?>
				<?php echo $this->loadTemplate('recent'); ?>
			<?php endif; ?>
		</form>
	</div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function () {
		jQuery('#mainTabs a').on('click', function () {
			jQuery('#currentTab').val(jQuery(this).attr('href').substr(1));
		});

		jQuery('#mainTabs a[href="#<?php echo $tab; ?>"]').tab('show');
	});
</script>
