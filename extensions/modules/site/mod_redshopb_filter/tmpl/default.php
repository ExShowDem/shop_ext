<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  mod_redshopb_filter
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

// Load jQuery library
RHtml::_('rjquery.framework');
HTMLHelper::_('rjquery.chosen', '.chosen');

$app   = Factory::getApplication();
$input = $app->input;

$option         = $input->getCmd('option', 'com_redshopb');
$view           = $input->getCmd('view', 'shop');
$layout         = $input->getCmd('layout', $app->getUserState('shop.layout', 'productlist'));
$id             = $input->getInt('id', 0);
$itemId         = $input->getInt('Itemid', 101);
$loadedFromAjax = $input->getCmd('module', '');
$collectionId   = $input->get('collection_id', array(), 'array');
$collections    = implode(',', $collectionId);

$action  = 'index.php?option=' . $option;
$action .= '&view=' . $view;
$action .= '&layout=' . $layout;
$action .= '&Itemid=' . $itemId;

if (!empty($id))
{
	$action .= '&id=' . $id;
}

// Load module CSS
RHelperAsset::load('mod_redshopb_filter.min.css', 'mod_redshopb_filter');

// Load Bootstrap-Slider to support extrafields as slider or price filter.
RHelperAsset::load('lib/bootstrap-slider/bootstrap-slider.min.js', 'com_redshopb');
RHelperAsset::load('lib/bootstrap-slider/bootstrap-slider.min.css', 'com_redshopb');
?>
<script type="text/javascript">
	jQuery(document).ready(function()
	{
		var form = jQuery('#mod_redshopb_filter_form_<?php echo $module->id ?>');
		form.on('submit', function(event) {
			redSHOPB.shop.filters.loadFilters(event);
			return false;
		});

		form.submit();
	});
</script>
<div class="mod_redshopb_filter<?php echo $moduleClassSuffix ?>"
	 id="redshopb-filter-module-wrapper_<?php echo $module->id ?>">
	<form action="<?php echo RedshopbRoute::_($action);?>"
		  id="mod_redshopb_filter_form_<?php echo $module->id ?>"
		  data-module_id="<?php echo $module->id ?>">
		<div class="loadingAjax">

		</div>
			<input type="hidden" name="id" value="<?php echo $input->getInt('id', 0); ?>" data-protected="true" />

			<?php if (!empty($collections)): ?>
			<input type="hidden" name="collection_id" value="<?php echo $collections; ?>" data-protected="true" />
			<?php endif;?>
			<input type="hidden" name="option" value="<?php echo $option ?>"/>
			<input type="hidden" name="layout" value="<?php echo $layout; ?>" data-protected="true" />
			<input type="hidden" name="task" value="" data-protected="true"/>
			<input type="hidden" name="modId" value="<?php echo $module->id ?>"/>
			<?php echo JHtmlForm::token();?>
	</form>
</div>
