<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$data = $displayData;

$formName = $data['formName'];
$return   = isset($data['return']) ? $data['return'] : null;
?>
<h2>
	<?php echo Text::_('COM_REDSHOPB_SALES_PERSONS_VIEW_DEFAULT_TITLE'); ?>
</h2>
<div class="row">
	<div class="btn-toolbar toolbar">
		<div class="btn-group">
			<div class="btn-group">
				<button class="btn btn-success" type="button" onclick="jQuery('#addSalesPerson').modal('toggle');">
					<i class="icon-file-text-alt"></i>
					<?php echo Text::_('JADD') ?>
				</button>
			</div>

			<button class="btn btn-danger"
					onclick="if (document.<?php echo $formName; ?>.boxchecked.value==0){alert('<?php echo Text::_('COM_REDSHOPB_PLEASE_SELECT_ITEM', true); ?>');}
						else{ Joomla.submitform('company.removesalespersons', document.getElementById('<?php echo $formName; ?>'))}"
					href="#">
				<i class="icon-trash"></i>
				<?php echo Text::_('JREMOVE') ?>
			</button>
		</div>
	</div>
</div>
