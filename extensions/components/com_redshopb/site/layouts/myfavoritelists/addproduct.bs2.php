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

extract($displayData);

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

?>

<script type="text/javascript">
	(function($) {
		$(document).ready(function(){
			window.checkFavForm = function checkFavForm(){
				return !!$('#jform_redshopb_favlist').val();
			};
		});
	})(jQuery);
</script>

<div class="redshopb-favlist-tool">
	<form method="post">
	<div class="row-fluid">
		<div class="span12">
			<?php
			$model = RModel::getFrontInstance('Myfavoritelist');
			$model->set('formName', 'add_favourite_product');
			$form = $model->getForm();

			echo $form->getInput('redshopb_favlist'); ?>
		</div>
	</div>
	<br />
	<div class="row-fluid">
		<div class="span6">
			<input type="hidden" id="favorite_list_id" name="favid" value="<?php echo $favlistId; ?>" />
			<input type="hidden" name="task" value="myfavoritelist.addProduct" />
			<input type="hidden" name="option" value="com_redshopb" />
			<?php echo HTMLHelper::_('form.token'); ?>
			<button type="submit" onclick="return checkFavForm();" id="redshopb-favlist-tool-addproduct-button" class="btn btn-success pull-right" data-id="<?php echo $favlistId; ?>">
				<i class="icon icon-plus"></i> <?php echo Text::_('JADD')?>
			</button>
		</div>
	</div>
	</form>
</div>
