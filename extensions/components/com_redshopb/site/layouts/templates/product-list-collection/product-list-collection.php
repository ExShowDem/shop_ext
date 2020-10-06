<?php
/**
 * @package     Aesir.E-Commerce.Template
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

if (empty($collectionProducts))
{
	return;
}

$collectionId     = isset($collectionId) ? $collectionId : 0;
$activeCollection = $collectionId;
$showPagination   = isset($showPagination) ? $showPagination : false;
$basePath         = isset($basePath) ? $basePath : '';
$cartPrefix       = isset($cartPrefix) && !empty($cartPrefix) ? $cartPrefix : null;
$input            = Factory::getApplication()->input;
$i                = 0;

if (!isset($currentPage))
{
	$currentPage = RedshopbApp::isUseAjaxReadMorePagination()
		? $input->getInt('page', 1)
		: $app->getUserState('shop.productlist.page.' . $input->getCmd('layout') . '_' . $input->getInt('id', 0), 1);
}

if (!empty($collectionProducts)) :
	if (!$activeCollection) :
		$activeCollection = (int) (key($collectionProducts));
	endif;

	$collectionIds = array_keys($collectionProducts);

	if ($activeCollection) :
		$input->set('collection_id', $collectionIds);
		echo '<input type="hidden" name="collection_id" value ="' . implode(',', $collectionIds) . '"/>';
		echo '<input type="hidden" name="collection" value="' . $activeCollection . '"/>';
		echo HTMLHelper::_('vnrbootstrap.startTabSet', 'collection', array('active' => 'collection_' . $activeCollection));
	endif;

	foreach ($collectionProducts as $collection => $products) :
		if ($activeCollection) :
			echo HTMLHelper::_('vnrbootstrap.addTab', 'collection', 'collection_' . $collection, RedshopbHelperCollection::getName($collection, true));
		endif;
		?>
		<div id="collection_products_<?php echo $collection; ?>">
			<?php
			$collectionId = $collection;
			$currency     = RedshopbHelperProduct::getCurrency($products->currency);
			?>
			{template.product-list-style.[$showAs]}
			<input type="hidden" name="productsShown" id="productsShown" value="<?php echo implode(',', $products->ids); ?>" />
			<input type="hidden" id="currencySymbol" value="<?php echo $currency->symbol; ?>" />
			<input type="hidden" id="currencySymbolPosition" value="<?php echo $currency->symbol_position; ?>" />
			<input type="hidden" id="currencyDecimals" value="<?php echo $currency->decimals; ?>" />
			<input type="hidden" id="currencyDecimalSeparator" value="<?php echo $currency->decimal_separator; ?>" />
			<input type="hidden" id="currencyThousandsSeparator" value="<?php echo $currency->thousands_separator; ?>" />
			<input type="hidden" id="currencyBlankSpace" value="<?php echo $currency->blank_space; ?>" />
		</div>
		<?php if ($activeCollection) :
			echo HTMLHelper::_('vnrbootstrap.endTab');
		endif;

if (empty($default) && $i == 0) :
	$default = $collection;
endif;

		$i++;
	endforeach;

	if ($activeCollection) :
		echo HTMLHelper::_('vnrbootstrap.endTabSet');
	endif;

	if (isset($default) && !$collectionId) :
		$id = $default;
	elseif ($collectionId) :
		$id = $collectionId;
	endif;
	?>
	<?php
endif;

if ($showPagination) :
	if (RedshopbApp::isUseAjaxReadMorePagination()) :
		echo RedshopbLayoutHelper::render(
			'shop.pages.nopagination',
			array(
				'numberOfPages' => $numberOfPages,
				'currentPage'   => $currentPage,
				'ajaxJS'        => $ajaxJS,
				'enableDirectLinks' => true
			)
		);
	else :
		echo RedshopbLayoutHelper::render(
			'shop.pages.pagination.links',
			array(
				'numberOfPages' => $numberOfPages,
				'currentPage'   => $currentPage,
				'ajaxJS'        => $ajaxJS,
				'enableDirectLinks' => true
			)
		);
	endif;
endif;
