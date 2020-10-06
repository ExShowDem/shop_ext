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

?>

<div class="modal hide fade" id="addSalesPerson" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">x</button>
		<h3 id="myModalLabel"><?php echo Text::_('COM_REDSHOPB_COMPANY_SALESPERSON_ADD'); ?></h3>
	</div>
	<div class="modal-body">
		<form name="addSalesPersonForm" class="form-horizontal" id="addSalesPersonForm">
					<?php
					if ($data['availableSalesPersons'])
					:
						foreach ($data['availableSalesPersons'] as $salesPerson)
						:
					?>
					<label for="salesperson_<?php echo $salesPerson->id ?>" class="checkbox">
						<input type="checkbox" id="salesperson_<?php echo $salesPerson->id ?>" name="salesperson_<?php echo $salesPerson->id ?>" value="<?php echo $salesPerson->id ?>" />
						<?php
							echo $salesPerson->name1 . ' ' . $salesPerson->name2 . ($salesPerson->use_company_email == 0 ? ' - ' . $salesPerson->email : '')
						?>
						</label>
					<?php
						endforeach;
					endif;
					?>
			<div class="form-actions">
				<input type="button" class="button btn btn-success pull-right" id="btnAddSalesPersons" value="<?php echo Text::_('JADD'); ?>" />
			</div>
		</form>
	</div>
</div>

<script type="text/javascript">
	jQuery(document).ready(function () {
		jQuery('#btnAddSalesPersons').click(function () {
			var salesPersons = new Array;
			var i = 0;
			jQuery('#<?php echo $data['formName'] ?> input[name="task"]').val('company.addsalespersons');
			jQuery('#addSalesPersonForm :input[type="checkbox"]').each(function () {
				if (jQuery(this).attr('checked')) {
					salesPersons[i] = jQuery(this).val();
					i++;
				}
			});
			jQuery('#<?php echo $data['formName'] ?> input[name="newsalespersons"]').val(JSON.stringify(salesPersons));
			jQuery('#<?php echo $data['formName'] ?>').submit();
		})
	});
</script>
