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
<div class="tab-pane" id="myPageRecentProducts">
	<h2><?php echo Text::_('COM_REDSHOPB_MYPAGE_RECENT_PRODUCTS'); ?></h2>
	<?php
		echo RedshopbLayoutHelper::render('mypage.products',
			array(
				'this'       => $this,
				'items'      => $this->recentlyPurchProds,
				'pagination' => $this->recentlyPurchPag,
				'cartPrefix' => 'inMyPageRecentProducts',
				'action'     => RedshopbRoute::_('index.php?option=com_redshopb&view=products')
			)
		);
	?>
</div>
