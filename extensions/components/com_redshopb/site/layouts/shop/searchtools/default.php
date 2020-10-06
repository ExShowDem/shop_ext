<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : array();

$onSale = false;

if (isset($data['options']['onSale']))
{
	$onSale = $data['options']['onSale'];
}

// Set some basic options
$customOptions = array(
	'filtersHidden'       => isset($data['options']['filtersHidden']) ? $data['options']['filtersHidden'] : empty($data['view']->activeFilters),
	'defaultLimit'        => isset($data['options']['defaultLimit']) ? $data['options']['defaultLimit'] : Factory::getApplication()->get('list_limit', 20),
	'searchFieldSelector' => '#filter_search',
	'orderFieldSelector'  => '#list_fullordering'
);

$data['options'] = array_unique(array_merge($customOptions, $data['options']));

$formSelector = !empty($data['options']['formSelector']) ? $data['options']['formSelector'] : '#adminForm';

// Load search tools
RHtml::_('rsearchtools.form', $formSelector, $data['options']);

$onsaleChecked = $data['view']->filter_onsale ? 'checked="checked"' : '';

?>
<div class="js-stools clearfix">
	<div class="clearfix">
		<div class="js-stools-container-bar shop-stools-container-bar">
			<?php echo RedshopbLayoutHelper::render('shop.searchtools.default.bar', $data); ?>
		</div>
		<div class="js-stools-container-list shop-stools-container-list">
			<?php echo RedshopbLayoutHelper::render('shop.searchtools.default.list', $data); ?>
		</div>
		<?php
		if ($onSale)
		:
		?>
		<div class="js-stools-container-single pull-left shop-stools-container-onsale">
		<input type="checkbox" name="filter[onsale]" id="filter_onsale" class="checkbox-onsale" value="1"
		data-label-prepend="<?php echo Text::_('COM_REDSHOPB_SHOP_FILTER_ON_SALE_ONLY'); ?>"
		onClick="this.form.submit();"
		<?php echo $onsaleChecked; ?>
		/>
		</div>
		<?php
		endif;
		?>
	</div>
	<!-- Filters div -->
	<div class="js-stools-container-filters clearfix">
			<?php echo RedshopbLayoutHelper::render('shop.searchtools.default.filters', $data); ?>
		 <div class="js-stools-field-filter">
			 <button class="btn btn-success" type="button" onclick="this.form.submit();">
				 <i class="icon-search"></i>
					<?php echo Text::_('JTOOLBAR_UPDATE') ?>
			 </button>
		 </div>

	</div>
</div>
