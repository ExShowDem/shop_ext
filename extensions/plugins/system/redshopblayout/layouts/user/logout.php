<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

extract($displayData);

?><div id="logoutNoticeModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="logoutNoticeModalLabel" aria-hidden="true">
	<div class="modal-body">
		<h4><?php
			$text = RedshopbEntityConfig::getInstance()->getString('warning_logout_when_products_in_cart_text');

		if (empty($text))
		{
			$text = Text::_('PLG_REDSHOPB_REDSHOPBLAYOUT_CART_NOT_EMPTY_DESCRIPTION');
		}

			echo $text ?></h4>
	</div>
	<div class="modal-footer">
		<button class="btn btn-success" data-dismiss="modal" aria-hidden="true"><i class="icon-remove"></i>&nbsp;<?php echo Text::_('PLG_REDSHOPB_REDSHOPBLAYOUT_CONTINUE_SHOP');?></button>
		<button class="btn btn-danger" id="logoutNoticeModalSubmit"><i class="icon-arrow-left"></i>&nbsp;<?php echo Text::_('PLG_REDSHOPB_REDSHOPBLAYOUT_LOGOUT_AND_CLEAN_CART');?></button>
	</div>
</div>
