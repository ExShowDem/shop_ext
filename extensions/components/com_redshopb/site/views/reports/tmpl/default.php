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

HTMLHelper::_('vnrbootstrap.tooltip');

$action  = RedshopbRoute::_('index.php?option=com_redshopb&view=reports');
$canView = RedshopbHelperACL::getPermission('view', 'reports');

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>

<div class="redshopb-reports">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
		<div class="redshopb-reports-table row">
			<div class="col-md-6">
				<?php
				echo RedshopbLayoutHelper::render('reports.reports', array(
						'view' => $this,
						'canView' => $canView,
						'title' => Text::_('COM_REDSHOPB_REPORTS_SALES_TITLE'),
						'reports' => array('sales_orders', 'sales_shipping'),
					)
				);
				?>
			</div>
			<div class="col-md-6">
				<?php
				echo RedshopbLayoutHelper::render('reports.reports', array(
						'view' => $this,
						'canView' => $canView,
						'title' => Text::_('COM_REDSHOPB_REPORTS_CUSTOMERS_TITLE'),
						'reports' => array('customers_new', 'customers_most_orders',),
					)
				);
				?>
			</div>
		</div>
		<div class="redshopb-reports-table row">
			<div class="col-md-6">
				<?php
				echo RedshopbLayoutHelper::render('reports.reports', array(
						'view' => $this,
						'canView' => $canView,
						'title' => Text::_('COM_REDSHOPB_REPORTS_PRODUCTS_TITLE'),
						'reports' => array('products_top_sellers', 'products_top_views', 'products_low_stock', 'products_in_carts'),
					)
				);
				?>
			</div>
			<div class="col-md-6">
				<?php
				echo RedshopbLayoutHelper::render('reports.reports', array(
						'view' => $this,
						'canView' => $canView,
						'title' => Text::_('COM_REDSHOPB_REPORTS_GENERAL_TITLE'),
						'reports' => array('general_newsletter'),
					)
				);
				?>
			</div>
		</div>
		<div>
			<input type="hidden" name="task" value="">
			<input type="hidden" name="boxchecked" value="0">
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
