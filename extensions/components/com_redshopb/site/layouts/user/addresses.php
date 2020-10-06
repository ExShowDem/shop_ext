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
use Joomla\CMS\Factory;

$data = $displayData;

$state       = $data['state'];
$items       = $data['items'];
$pagination  = $data['pagination'];
$filterForm  = $displayData['filter_form'];
$formName    = $data['formName'];
$showToolbar = isset($data['showToolbar']) ? $data['showToolbar'] : false;
$return      = isset($data['return']) ? $data['return'] : null;
$action      = RedshopbRoute::_('index.php?option=com_redshopb&view=users');

// Allow to override the form action
if (isset($data['action']))
{
	$action = $data['action'];
}

$userId    = Factory::getApplication()->input->getInt('id');
$listOrder = $state->get('list.ordering');
$listDirn  = $state->get('list.direction');
$saveOrder = $listOrder == 'ordering';

$searchToolsOptions = array(
	"searchFieldSelector" => "#filter_search_addresses",
	"orderFieldSelector" => "#list_fullordering",
	"searchField" => "search_addresses",
	"limitFieldSelector" => "#list_address_limit",
	"activeOrder" => $listOrder,
	"activeDirection" => $listDirn,
	"formSelector" => ("#" . $formName),
	"filtersHidden" => (bool) empty($data['activeFilters'])
);
?>
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
			$('#<?php echo $formName; ?>').searchtools(
				<?php echo json_encode($searchToolsOptions); ?>
			);
		});
	})(jQuery);
</script>

<form action="<?php echo $action; ?>" name="<?php
	echo $formName;
	?>" class="adminForm" id="<?php echo $formName; ?>"
	method="post">
	<?php
	// Render the toolbar?
	if ($showToolbar)
	{
		echo RedshopbLayoutHelper::render('addresses.toolbar', $data);
	}
	?>

	<?php
	echo RedshopbLayoutHelper::render(
		'searchtools.default',
		array(
			'view' => (object) array(
					'filterForm' => $data['filter_form'],
					'activeFilters' => $data['activeFilters']
				),
			'options' => $searchToolsOptions
		)
	);
	?>

	<hr/>
	<?php
		echo RedshopbLayoutHelper::render(
			'addresses.default',
			array(
				'listOrder' => $listOrder,
				'listDirn' => $listDirn,
				'items' => $items,
				'pagination' => $pagination,
				'formName' => $formName
			)
		);
	?>
	<div>
		<input type="hidden" name="task" value="user.saveModelState">
		<?php
		if ($return)
		:
			?>
			<input type="hidden" name="return" value="<?php echo $return ?>">
		<?php
		endif;
		?>
		<input type="hidden" name="jform[user_id]" value="<?php echo $userId; ?>">
		<input type="hidden" name="from_user" value="1">
		<input type="hidden" name="boxchecked" value="0">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
