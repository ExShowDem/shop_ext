<?php
/**
 * @package     Aesir.E-Commerce.Template
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

if (empty($collectionProducts))
	:
	return;
endif;

$collectionId     = isset($collectionId) ? $collectionId : 0;
$activeCollection = $collectionId;
$showPagination   = isset($showPagination) ? $showPagination : false;
$basePath         = isset($basePath) ? $basePath : '';
$cartPrefix       = isset($cartPrefix) && !empty($cartPrefix) ? $cartPrefix : null;
$input            = Factory::getApplication()->input;
$i                = 0;

if (!isset($currentPage))
	:
	$input = Factory::getApplication()->input;

	if (RedshopbApp::isUseAjaxReadMorePagination())
		:
		$currentPage = $input->getInt('page', 1);
	else:
		$currentPage = $app->getUserState('shop.productlist.page.' . $input->getCmd('layout') . '_' . $input->getInt('id', 0), 1);
	endif;
endif;
?>
<div class="row-fluid">
	<div class="span12">
		<?php
		if (!empty($collectionProducts))
			:
			if (!$activeCollection)
				:
				$activeCollection = (int) (key($collectionProducts));
			endif;

			$collectionIds = array_keys($collectionProducts);
			$i             = 0;

			if ($activeCollection)
				:
				$input->set('collection_id', $collectionIds);
				echo '<input type="hidden" name="collection_id" value ="' . implode(',', $collectionIds) . '"/>';
				echo '<input type="hidden" name="collection" value="' . $activeCollection . '"/>';
				echo HTMLHelper::_('vnrbootstrap.startTabSet', 'collection', array('active' => 'collection_' . $activeCollection));
			endif;

			foreach ($collectionProducts as $collection => $products) :
				if ($activeCollection)
					:
					echo HTMLHelper::_('vnrbootstrap.addTab', 'collection', 'collection_' . $collection, RedshopbHelperCollection::getName($collection, true));
				endif;
				?>
				<div id="collection_products_<?php echo $collection; ?>">
					<?php
					$collectionId = $collection;
					?>
					{template.product-list-style.[$showAs]}
					<input type="hidden" name="productsShown" id="productsShown" value="<?php echo implode(',', $products->ids); ?>" />
				</div>
				<?php
				if ($activeCollection)
					:
					echo HTMLHelper::_('vnrbootstrap.endTab');
				endif;

				if (empty($default) && $i == 0)
					:
					$default = $collection;
				endif;

				$i++;
			endforeach;

			if ($activeCollection)
				:
				echo HTMLHelper::_('vnrbootstrap.endTabSet');
			endif;

			if (isset($default) && !$collectionId)
				:
				$id = $default;
			elseif ($collectionId) :
				$id = $collectionId;
			endif;
			?>
			<?php
		endif;
		?>
	</div>

	<?php
	if ($showPagination)
		:
		if (RedshopbApp::isUseAjaxReadMorePagination())
			:
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
	?>
</div>
