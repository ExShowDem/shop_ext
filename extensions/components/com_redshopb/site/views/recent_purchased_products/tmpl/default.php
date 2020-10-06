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

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>

<div class="recent-purchased-products">
	<h2><?php echo Text::_('COM_REDSHOPB_RECENT_PURCHASED_PRODUCTS_TITLE'); ?></h2>
	<?php
		echo RedshopbLayoutHelper::render('mypage.products',
			array(
				'this'         => $this,
				'items'        => $this->items,
				'pagination'   => $this->pagination,
				'cartPrefix'   => 'inRecentPurchasedProducts',
				'action'       => RedshopbRoute::_('index.php?option=com_redshopb&view=products'),
				'showQuantity' => true
			)
		);
	?>
</div>
