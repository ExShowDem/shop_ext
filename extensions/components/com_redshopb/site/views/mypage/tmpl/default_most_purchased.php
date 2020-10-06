<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
?>
<div class="tab-pane" id="myPageMostPurchased">
	<h2><?php echo Text::_('COM_REDSHOPB_MYPAGE_MOST_PURCHASED'); ?></h2>
	<?php
		echo RedshopbLayoutHelper::render('mypage.products',
			array(
				'this'       => $this,
				'items'      => $this->mostPurchasedProds,
				'pagination' => $this->productPagination,
				'cartPrefix' => 'inMyPageMostPurchased',
				'action'     => RedshopbRoute::_('index.php?option=com_redshopb&view=products')
			)
		);
	?>
</div>

