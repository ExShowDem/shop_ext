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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');

$app          = Factory::getApplication();
$customerId   = $app->getUserState('shop.customer_id', 0);
$customerType = $app->getUserState('shop.customer_type', '');
$action       = RedshopbRoute::_('index.php?option=com_redshopb');

// Placing this here so that we can see what vars are expected in the $displayData array
$data        = $displayData;
$placeHolder = $data['placeholder'];
$buttonIcon  = $data['icon_class'];
$buttonText  = $data['button_text'];
$favId       = $data['fav_id'];

?>
<div>
	<form action="<?php echo $action ?>">
		<div class="row-fluid">
			<div class="span12">
				<input type="text"
					   id="js-product-search"
					   name="search"
					   class="input input-block-level"
					   placeholder="<?php echo $placeHolder;?>"
					   autocomplete="off" />
				<div class="row-fluid hidden searchProductResultBlock">
					<div id="js-product-search-results" class="span12"></div>
				</div>
			</div>
			<div class="span4">
				<div id="myfavoritelists-buttons" class="input-group">
					<span id="myfavoritelists-attribute-container" class="in-line"></span>
					<a href="javascript:void(0);" id="redshopb-myfavoritelists-tool-addtocart-button" class="btn btn-muted disabled" tabindex="22">
						<i class="icon icon-shopping-cart"></i>
						<span class="addtocart-span"><?php echo Text::_('COM_REDSHOPB_MY_FAVORITE_LIST_ADD') ?></span>
					</a>
				</div>
			</div>
		</div>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="result_layout" value="result-list"/>
		<input type="hidden" name="product_id" value=""/>
		<input type="hidden" name="collection_id" value=""/>
		<input type="hidden" name="fav_id" value="<?php echo $favId;?>"/>
		<input type="hidden" name="simple_search" value="1"/>
		<div id="token">
			<?php echo HTMLHelper::_('form.token') ?>
		</div>
	</form>
</div>
