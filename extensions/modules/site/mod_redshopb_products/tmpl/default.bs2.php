<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts.Shop.Pages
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$keyCollection = 'modKeyProductsCollection_' . $module->id;
$width         = $params->get('imageHeight', 160);
$height        = $params->get('imageWidth', 160);
$itemMargin    = (int) $params->get('itemMargin', 40);
$doc           = Factory::getDocument();
$doc->addStyleDeclaration(
	'.modProductSlider_' . $module->id . ' .slides .modProductImage img {
		min-height: ' . $height . 'px;
	}
	.modProductSlider_' . $module->id . ' .slides li {
		margin-right: ' . $itemMargin . 'px;
	}
'
);

$flexsliderOptions = array(
	'slideshow' => (bool) $params->get('slideshow', 0),
	'directionNav' => (bool) $params->get('directionNav', 0),
	'controlNav' => (bool) $params->get('controlNav', 0),
	'animation' => 'slide',
	'animationLoop' => (bool) $params->get('animationLoop', 0),
	'itemWidth' => (int) $params->get('itemWidth', 160),
	'itemMargin' => $itemMargin,
	'minItems' => (int) $params->get('minItems', 2),
	'maxItems' => (int) $params->get('maxItems', 6)
);
HTMLHelper::_('rjquery.flexslider', '.modProductSlider_' . $module->id, $flexsliderOptions);
$activeCollection = key($collectionProducts);
$displayTabs      = false;

$links   = array();
$links[] = array('href' => $params->get('link_one', ''), 'text' => $params->get('link_one_txt', ''));
$links[] = array('href' => $params->get('link_two', ''), 'text' => $params->get('link_two_txt', ''));
$links[] = array('href' => $params->get('link_three', ''), 'text' => $params->get('link_three_txt', ''));
?>
<h3 class="red-products <?php echo $params->get('header_class');?>"><?php echo $module->title;?>
	<div class="pull-right">
		<?php foreach ($links AS $link):?>
			<?php if (empty($link['href']) || empty($link['text'])): ?>
				<?php continue;?>
			<?php endif;?>

			<?php $target = ' target="_blank"';?>

			<?php if (strpos($link['href'], 'http://') === false && strpos($link['href'], 'https://') === false):?>
				<?php $link['href'] = RedshopbRoute::_($link['href']);?>
				<?php $target       = '';?>
			<?php endif;?>

			<a href="<?php echo $link['href']; ?>"
				<?php echo $target;?>>
				<?php echo Text::_($link['text']); ?>
			</a>
		<?php endforeach;?>
	</div>
</h3>
<div class="row-fluid">
	<div class="span12" id="<?php echo $keyCollection; ?>">

		<?php if (count($types) > 1): ?>
			<div class="modTypeSwitcher">
				<form method="post" name="modRedshopbProductForm<?php echo $module->id; ?>" id="modRedshopbProductForm<?php echo $module->id; ?>">
					<?php
					$typeOptions = array();

					foreach ($types as $type)
					{
						$temp            = new stdClass;
						$temp->type_id   = $type;
						$temp->type_name = Text::_('MOD_REDSHOPB_PRODUCTS_' . strtoupper($type));
						$typeOptions[]   = $temp;
					}

					echo HTMLHelper::_('select.genericlist', $typeOptions, 'type_id',
						'class="inputbox" onchange="document.modRedshopbProductForm' . $module->id . '.submit();"      ',
						'type_id', 'type_name', $currentType
					);
					?>
					<input type="hidden" value="<?php echo $module->id; ?>" name="module_id" />
				</form>
			</div>
		<?php endif; ?>

		<?php if (count($collectionProducts) == 0): ?>
			<div class="clearfix"></div>
			<div class="alert alert-info">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
				<div class="pagination-centered">
					<h3><?php echo Text::_('MOD_REDSHOPB_PRODUCTS_NOTHING_TO_DISPLAY') ?></h3>
				</div>
			</div>
		<?php else: ?>
			<?php
			if (count($collectionProducts) > 1)
			{
				$displayTabs = true;
				echo HTMLHelper::_('vnrbootstrap.startTabSet', $keyCollection, array('active' => 'modBProdCollection_' . $module->id . '_' . $activeCollection));
				Factory::getDocument()->addScriptDeclaration('
				jQuery(document).ready(function () {
					jQuery(\'#' . $keyCollection . '\').on("click", "#' . $keyCollection . 'Tabs a", function(e){
						var $this = jQuery(this);
						e.preventDefault();
						$this.tab(\'show\');
						jQuery(window).trigger(\'resize\');
						jQuery($this.attr(\'href\')).find(\'.flexslider\').data(\'flexslider\').resize();
					});
				});
				'
				);
			}
			else
			{
				?><div class="clearfix"></div><?php
			}

			foreach ($collectionProducts as $collectionId => $products) :
				if ($displayTabs)
				{
					echo HTMLHelper::_(
						'vnrbootstrap.addTab', $keyCollection, 'modBProdCollection_' . $module->id . '_' . $collectionId,
						RedshopbHelperCollection::getName($collectionId, true)
					);
				}
				?>
				<div id="modCollectionProducts_<?php echo $collectionId . '_' . $module->id; ?>">
					<?php include RModuleHelper::getLayoutPath('mod_redshopb_products', $params->get('layout', 'default') . '_products'); ?>
				</div>
				<?php
				if ($displayTabs)
				{
					echo HTMLHelper::_('vnrbootstrap.endTab');
				}
			endforeach;

			if ($displayTabs)
			{
				echo HTMLHelper::_('vnrbootstrap.endTabSet');
			}
		endif; ?>
	</div>
</div>
