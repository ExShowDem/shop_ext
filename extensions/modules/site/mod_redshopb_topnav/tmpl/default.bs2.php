<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  mod_redshopb_topnav
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

RHtml::_('rjquery.ui');

Text::script('COM_REDSHOPB_SHOP_ITEMS');

?>
<ul class="nav redcore">
	<?php
	// Shows up the Dashboard if at least one of the permissions is granted
	if (in_array(true, RedshopbHelperACL::getViewPermissions())) : ?>
		<li>
			<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=dashboard') ?>">
				<?php echo Text::_('COM_REDSHOPB_DASHBOARD') ?>
			</a>
		</li>
	<?php endif; ?>
	<li class="dropdown">
		<a href="#"
		   class="dropdown-toggle"
		   data-toggle="dropdown">
			<?php echo Text::_('COM_REDSHOPB_SHOP') ?>
			<b class="caret"></b>
		</a>
		<ul class="dropdown-menu">
			<li>
				<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=shop') ?>">
					<i class="icon-shopping-cart"></i>
					<?php echo Text::_('COM_REDSHOPB_SHOP') ?>
				</a>
			</li>
			<?php if (RedshopbHelperACL::allowDisplayView('offers')): ?>
				<li>
					<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=offers') ?>">
						<i class="icon-asterisk"></i>
						<?php echo Text::_('COM_REDSHOPB_OFFER_LIST_TITLE') ?>
					</a>
				</li>
			<?php endif; ?>
			<li>
				<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=carts') ?>">
					<i class="icon-shopping-cart"></i>
					<?php echo Text::_('COM_REDSHOPB_SAVED_CARTS') ?>
				</a>
			</li>
			<?php
			if (RedshopbHelperACL::allowDisplayView('users')) : ?>
				<li>
					<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=users') ?>">
						<i class="icon-user"></i>
						<?php echo Text::_('COM_REDSHOPB_USER_LIST_TITLE') ?>
					</a>
				</li>
				<?php
			endif;

			if (RedshopbHelperACL::allowDisplayView('addresses')) : ?>
				<li>
					<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=addresses') ?>">
						<i class="icon-truck"></i>
						<?php echo Text::_('COM_REDSHOPB_ADDRESS_LIST_TITLE') ?>
					</a>
				</li>
				<?php
			endif;

			if (RedshopbHelperACL::allowDisplayView('companies')) : ?>
				<li>
					<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=companies') ?>">
						<i class="icon-globe"></i>
						<?php echo Text::_('COM_REDSHOPB_COMPANY_LIST_TITLE') ?>
					</a>
				</li>
				<?php
			endif;

			if (RedshopbHelperACL::allowDisplayView('departments')) : ?>
				<li>
					<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=departments') ?>">
						<i class="icon-building"></i>
						<?php echo Text::_('COM_REDSHOPB_DEPARTMENT_LIST_TITLE') ?>
					</a>
				</li>
				<?php
			endif;

			if (RedshopbHelperACL::allowDisplayView('manufacturers')) : ?>
				<li>
					<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=manufacturers') ?>">
						<i class="icon-suitcase"></i>
						<?php echo Text::_('COM_REDSHOPB_MANUFACTURER_LIST_TITLE') ?>
					</a>
				</li>
				<?php
			endif;

			if (RedshopbHelperACL::allowDisplayView('collections')) : ?>
				<li>
					<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=collections') ?>">
						<i class="icon-briefcase"></i>
						<?php echo Text::_('COM_REDSHOPB_COLLECTION_LIST_TITLE') ?>
					</a>
				</li>
				<?php
			endif;

			if (RedshopbHelperACL::allowDisplayView('products')) : ?>
				<li>
					<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=products') ?>">
						<i class="icon-barcode"></i>
						<?php echo Text::_('COM_REDSHOPB_PRODUCT_LIST_TITLE') ?>
					</a>
				</li>
				<?php
			endif;

			if (RedshopbHelperACL::allowDisplayView('stockrooms')) : ?>
				<li>
					<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=stockrooms') ?>">
						<i class="icon-archive"></i>
						<?php echo Text::_('COM_REDSHOPB_STOCKROOMS_TITLE') ?>
					</a>
				</li>
				<?php
			endif;

			if (RedshopbHelperACL::allowDisplayView('all_discounts')) : ?>
				<li class="dropdown-submenu">
					<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=all_discounts') ?>">
						<i class="icon-asterisk"></i>
						<?php echo Text::_('COM_REDSHOPB_DISCOUNT_LIST_TITLE') ?>
					</a>
					<ul class="dropdown-menu">
						<li>
							<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=all_discounts') ?>">
								<?php echo Text::_('COM_REDSHOPB_ALL_DISCOUNTS') ?>
							</a>
						</li>
						<li>
							<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=discount_debtor_groups') ?>">
								<?php echo Text::_('COM_REDSHOPB_DEBTOR_DISCOUNT_GROUPS') ?>
							</a>
						</li>
						<li>
							<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=product_discount_groups') ?>">
								<?php echo Text::_('COM_REDSHOPB_PRODUCT_DISCOUNT_GROUPS') ?>
							</a>
						</li>
					</ul>
				</li>
				<?php
			endif;

			if (RedshopbHelperACL::allowDisplayView('all_prices')) : ?>
				<li class="dropdown-submenu">
					<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=all_prices') ?>">
						<i class="icon-tag"></i>
						<?php echo Text::_('COM_REDSHOPB_PRICES') ?>
					</a>
					<ul class="dropdown-menu">
						<li>
							<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=all_prices') ?>">
								<?php echo Text::_('COM_REDSHOPB_PRODUCT_PRICE_ALL') ?>
							</a>
						</li>
						<li>
							<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=price_debtor_groups') ?>">
								<?php echo Text::_('COM_REDSHOPB_DEBTOR_PRICE_GROUPS') ?>
							</a>
						</li>
					</ul>
				</li>
				<?php
			endif;

			if (RedshopbHelperACL::allowDisplayView('categories')) : ?>
				<li>
					<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=categories') ?>">
						<i class="icon-sitemap"></i>
						<?php echo Text::_('COM_REDSHOPB_CATEGORY_LIST_TITLE') ?>
					</a>
				</li>
				<?php
			endif;

			if (RedshopbHelperACL::allowDisplayView('orders')) : ?>
				<li>
					<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=orders') ?>">
						<i class="icon-book"></i>
						<?php echo Text::_('COM_REDSHOPB_ORDER_LIST_TITLE') ?>
					</a>
				</li>
				<?php
			endif;

			if (RedshopbHelperACL::allowDisplayView('return_orders')) : ?>
				<li>
					<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=return_orders') ?>">
						<i class="icon-book"></i>
						<?php echo Text::_('COM_REDSHOPB_RETURN_ORDERS_LIST_TITLE') ?>
					</a>
				</li>
				<?php
			endif;

			if (RedshopbHelperACL::allowDisplayView('layouts')) : ?>
				<li>
					<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=layouts') ?>">
						<i class="icon-desktop"></i>
						<?php echo Text::_('COM_REDSHOPB_LAYOUT_LIST_TITLE') ?>
					</a>
				</li>
				<?php
			endif;

			if (RedshopbHelperACL::allowDisplayView('tags')) : ?>
				<li>
					<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=tags') ?>">
						<i class="icon-tags"></i>
						<?php echo Text::_('COM_REDSHOPB_TAG_LIST_TITLE') ?>
					</a>
				</li>
				<?php
			endif;

			if (RedshopbHelperACL::allowDisplayView('wash_care_specs')) : ?>
				<li>
					<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=wash_care_specs') ?>">
						<i class="icon-info-sign"></i>
						<?php echo Text::_('COM_REDSHOPB_WASH_CARE_SPEC_LIST_TITLE') ?>
					</a>
				</li>
				<li>
					<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=fields') ?>">
						<i class="icon-search"></i>
						<?php echo Text::_('COM_REDSHOPB_FIELDS_LIST_TITLE');?>
					</a>
				</li>
				<li>
					<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=field_groups') ?>">
						<i class="icon-link"></i>
						<?php echo Text::_('COM_REDSHOPB_FIELD_GROUPS_LIST_TITLE');?>
					</a>
				</li>
				<li>
					<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=filter_fieldsets') ?>">
						<i class="icon-filter"></i>
						<?php echo Text::_('COM_REDSHOPB_FILTER_FIELDSET_LIST_TITLE');?>
					</a>
				</li>
				<?php
			endif;

			// @TODO: Need add ACL permission for Newsletter
			if (RedshopbHelperACL::allowDisplayView('newsletter_lists')) : ?>
				<li class="dropdown-submenu">
					<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=newsletter_lists') ?>">
						<i class="icon-envelope"></i>
						<?php echo Text::_('COM_REDSHOPB_NEWSLETTER_LIST_TITLE') ?>
					</a>
					<ul class="dropdown-menu">
						<li>
							<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=newsletter_lists') ?>">
								<i class="icon-envelope"></i>
								<?php echo Text::_('COM_REDSHOPB_NEWSLETTER_LIST_TITLE') ?>
							</a>
						</li>
						<li>
							<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=newsletters') ?>">
								<i class="icon-tag"></i>
								<?php echo Text::_('COM_REDSHOPB_NEWSLETTERS_TITLE') ?>
							</a>
						</li>
					</ul>
				</li>
			<?php endif; ?>
			<li>
				<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=shipping_rates') ?>">
					<i class="icon-truck"></i>
					<?php echo Text::_('COM_REDSHOPB_SHIPPING_RATES_TITLE');?>
				</a>
			</li>
			<li>
				<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=reports') ?>">
					<i class="icon-table"></i>
					<?php echo Text::_('COM_REDSHOPB_REPORTS_TITLE');?>
				</a>
			</li>
			<?php
			if (RedshopbHelperACL::allowDisplayView('companies')): ?>
				<li>
					<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=table_locks') ?>">
						<i class="icon-lock"></i>
						<?php echo Text::_('COM_REDSHOPB_TABLE_LOCKS_TITLE');?>
					</a>
				</li>
			<?php
			endif; ?>
			<li>
				<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=templates') ?>">
					<i class="icon-desktop"></i>
					<?php echo Text::_('COM_REDSHOPB_TEMPLATE_LIST_TITLE');?>
				</a>
			</li>
			<li>
				<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=unit_measures') ?>">
					<i class="icon-puzzle-piece"></i>
					<?php echo Text::_('COM_REDSHOPB_UNIT_MEASURE_LIST_TITLE');?>
				</a>
			</li>
			<?php if (RedshopbHelperACL::allowDisplayView('words')) : ?>
				<li>
					<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=words') ?>">
						<i class="icon-comments-alt"></i>
						<?php echo Text::_('COM_REDSHOPB_WORD_LIST_TITLE') ?>
					</a>
				</li>
			<?php endif; ?>

			<?php if (RedshopbHelperACL::allowDisplayView('products')): ?>
				<li class="dropdown-submenu">
					<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=countries') ?>">
						<i class="icon-globe"></i>
						<?php echo Text::_('COM_REDSHOPB_COUNTRY_LIST_TITLE') ?>
					</a>
					<ul class="dropdown-menu">
						<li>
							<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=countries') ?>">
								<i class="icon-globe"></i>
								<?php echo Text::_('COM_REDSHOPB_COUNTRY_LIST_TITLE') ?>
							</a>
						</li>
						<li>
							<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=states') ?>">
								<i class="icon-globe"></i>
								<?php echo Text::_('COM_REDSHOPB_STATE_LIST_TITLE') ?>
							</a>
						</li>
					</ul>
				</li>
				<li class="dropdown-submenu">
					<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=taxes') ?>">
						<i class="icon-globe"></i>
						<?php echo Text::_('COM_REDSHOPB_TAX_LIST_TITLE') ?>
					</a>
					<ul class="dropdown-menu">
						<li>
							<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=tax_groups') ?>">
								<i class="icon-retweet"></i>
								<?php echo Text::_('COM_REDSHOPB_TAX_GROUP_LIST_TITLE') ?>
							</a>
						</li>
						<li>
							<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=taxes') ?>">
								<i class="icon-retweet"></i>
								<?php echo Text::_('COM_REDSHOPB_TAX_LIST_TITLE') ?>
							</a>
						</li>
					</ul>
				</li>
			<?php endif; ?>
		</ul>
	</li>
	<?php if ($offers) : ?>
		<li>
			<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=offers') ?>">
				<?php echo Text::_('MOD_REDSHOPB_TOPNAV_OFFERS_TITLE') ?>
			</a>
		</li>
	<?php endif; ?>

	<?php if (count($menuItems)) :
		foreach ($menuItems as $menuItem) : ?>
			<li>
				<?php
				$link  = $menuItem->link . '&Itemid=' . $menuItem->id;
				$class = '';
				$title = $menuItem->title;

				switch ($menuItem->type)
				{
					case 'separator':
						$link  = '#';
						$class = 'separator';
						$title = '&nbsp;';
						break;

					case 'heading':
						$link  = '#';
						$class = 'separator';
						break;
				}

				?>
				<a href="<?php echo Route::_($link) ?>" class="<?php echo $class ?>">
					<?php echo $title ?>
				</a>
			</li>
		<?php endforeach;
	endif; ?>
</ul>
