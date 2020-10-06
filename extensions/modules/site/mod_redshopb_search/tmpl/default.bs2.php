<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  mod_redshopb_search
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

RHtml::_('rjquery.ui');
HTMLHelper::script('mod_redshopb_search/mod_redshopb_search.js', array('framework' => false, 'relative' => true));

$app                  = Factory::getApplication();
$input                = $app->input;
$option               = $input->getCmd('option');
$view                 = $input->getCmd('view');
$layout               = $input->getCmd('layout');
$isCategoryView       = ('com_redshopb' == $option && 'shop' == $view && 'category' == $layout);
$isManufactuerersView = ('com_redshopb' == $option && 'shop' == $view && 'manufacturer' == $layout);

$id = $input->getInt('id', 0);
?>
<?php if ($doAjax): ?>
<script type="text/javascript">
	jQuery(document).ready(function () {
		var searchWordInput = jQuery('#mod_redshopb_search_searchword');
		searchWordInput.on('keyup', function (event) {
			redSHOPB.ajax.search(event, function () {
			}, '#mod_redshopb_search_searchword');
		});

		var searchForm = searchWordInput.closest('form');
		searchForm.on('submit', function (event) {
			jQuery(this).attr('data-submitted', 'true');

			if (redSHOPB.ajax.xhr) {
				redSHOPB.ajax.xhr.abort();
			}
		});
	});
</script>
<?php endif; ?>
<div class="redcore redshopb_search<?php echo $moduleclassSfx ?>">
	<form action="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=productlist');?>" method="post" class="form-inline" id="searchForm">
		<div class="row-fluid">
			<div class="span12">
				<div class="input-prepend input-append">
					<label for="mod-redshopb_search-searchword" class="element-invisible"><?php echo $label;?></label>
					<?php
					$output = '<input name="search" id="mod_redshopb_search_searchword" autocomplete="off" maxlength="' . $maxlength . '"
				type="text" size="' . $width . '" class="inputbox redshopb-search-query ' . $inputClass . '" value=""
				placeholder="' . $text . '" />';

					if ($submitType == 0)
					{
						$searchButton = '<button class="btn ' . $submitClass . '" type="submit" tabindex="21">' . $buttonText . '</button>';
					}
					else
					{
						$searchButton = '<button class="btn ' . $submitClass . '" type="submit" tabindex="21"><i class="icon icon-search"></i></button>';
					}

					if ($submitPosition == 1)
					{
						$output .= $searchButton;
					}
					else
					{
						$output = $searchButton . $output;
					}

					echo $output;
					?>

					<button class="btn <?php echo $clearClass; ?>" type="button" tabindex="22"
							onclick="jQuery('#mod_redshopb_search_searchword').val(''); jQuery('#searchForm').submit();">
						x
					</button>
					<input type="hidden" value="shop.search" name="task" />
				</div>
			</div>
		</div>
		<div class="row-fluid hidden">
			<div id="mod_redshopb_search_searchword-results" class="span12">
			</div>
		</div>
		<input type="hidden" name="simple_search" value="0"/>
		<input type="hidden" name="result_layout" value="linked-list"/>
		<?php echo JHtmlForm::token();?>
	</form>
</div>
