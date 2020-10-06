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

HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=addresses');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder == 'ordering';
?>
<script type="text/javascript">
	var rsbftPhone = 660;
</script>

<script>
jQuery(document).ready(function() {
	var csvButton = jQuery('#main > div.redcore div.btn-group.pull-right button:contains("Csv")');
	var onclick   = csvButton.attr('onclick');

	csvButton.removeAttr('onclick');

	csvButton.on('click', function(){
		var url = onclick.slice(onclick.indexOf('http'),-2);
		redSHOPB.ajax.generateCsvFile("addresses", "#addressList", url);
	});
});
</script>
<?php

RedshopbHtml::loadFooTable();
echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<div class="redshopb-addresses">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">

		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'searchField' => 'search_addresses',
					'searchFieldSelector' => '#filter_search_addresses',
					'limitFieldSelector' => '#list_address_limit',
					'activeOrder' => $listOrder,
					'activeDirection' => $listDirn
				)
			)
		);
		?>

		<hr/>

		<?php
			echo RedshopbLayoutHelper::render(
				'addresses.default',
				array(
					'listOrder'  => $listOrder,
					'listDirn'   => $listDirn,
					'items'      => $this->items,
					'pagination' => $this->pagination,
					'formName'   => 'adminForm'
				)
			);
		?>

		<div>
			<input type="hidden" name="task" value="">
			<input type="hidden" name="boxchecked" value="0">
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
