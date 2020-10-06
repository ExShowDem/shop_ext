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

$state      = $data['state'];
$items      = $data['items'];
$pagination = $data['pagination'];
$filterForm = $displayData['filter_form'];
$formName   = $data['formName'];
$return     = isset($data['return']) ? $data['return'] : null;
$action     = $data['action'];
$document   = Factory::getDocument();

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rdropdown.init');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

$collectionId = Factory::getApplication()->input->getInt('id');
$listOrder    = $state->get('list.ordering');
$listDirn     = $state->get('list.direction');
$saveOrder    = $listOrder == 'ordering';

$searchToolsOptions = array(
	"searchFieldSelector" => "#filter_search_products",
	"orderFieldSelector" => "#list_fullordering",
	"searchField" => "search_products",
	"limitFieldSelector" => "#list_product_limit",
	"activeOrder" => $listOrder,
	"activeDirection" => $listDirn,
	"formSelector" => ("#" . $formName),
	"filtersHidden" => (bool) empty($data['activeFilters'])
);

$user               = Factory::getUser();
$allowedCompanies   = explode(",", RedshopbHelperACL::listAvailableCompanies($user->id));
$mayEditProducts    = RedshopbHelperACL::getPermission('manage', 'product', array('edit','edit.own'), true);
$canManageWareHouse = RedshopbHelperACL::getPermission('manage', 'mainwarehouse');
$config             = RedshopbEntityConfig::getInstance();
$thumbWidth         = $config->getThumbnailWidth();
$thumbHeight        = $config->getThumbnailHeight();

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
<form action="<?php echo $action; ?>" name="<?php echo $formName; ?>" class="adminForm" id="<?php echo $formName; ?>"
	  method="post">
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
	<?php if (empty($items)) : ?>
		<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
	<?php else : ?>
		<?php
		$dropdownModel = RModelAdmin::getInstance('Shop', 'RedshopbModel');
		$dropdownModel->setState('product_collection', null);
		$dropdownModel->customerCType = '';
		$itemsIds                     = array();

		foreach ($items as $item)
		{
			$itemsIds[] = $item->id;
		}

		echo RedshopbLayoutHelper::render('product_sheets.productitems', array('items' => $items,'dropDownTypes' => $dropdownModel->getDropDownTypes($itemsIds))); ?>
		<?php echo $pagination->getPaginationLinks('tab.pagination.links', array('showLimitBox' => false)); ?>
	<?php endif; ?>

	<input type="hidden" name="task" value="collection.saveModelState">
	<?php if ($return) : ?>
		<input type="hidden" name="return" value="<?php echo $return ?>">
	<?php endif; ?>
	<input type="hidden" name="boxchecked" value="0">
	<input type="hidden" name="layout" value="create_products">
	<input type="hidden" name="id" value="<?php echo $collectionId ?>">
	<?php echo HTMLHelper::_('form.token'); ?>
	<script type="text/javascript"><?php echo !empty($document->_script['text/javascript']) ? $document->_script['text/javascript'] : ''; ?></script>
</form>
