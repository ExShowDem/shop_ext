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
use Joomla\CMS\Language\Text;

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

RedshopbHtml::loadFooTable();

$action = RedshopbRoute::_('index.php?option=com_redshopb&view=manufacturerlist&itemId=101');

$columns       = $this->params !== null ? $this->params->get('columns', 4) : 4;
$itemSpanWidth = 'col-md-' . $this->roundUp(12 / $columns);
$counter       = 0;
$mainSpanSize  = 12;
?>
<script type="text/javascript">
	(function($) {
		$(document).ready(function() {
			// Characters filter click process
			$(".search_char_href").click(function(event) {
				event.preventDefault();
				$(this).children('input').prop('checked', $(this).children('input').checked);
				$('form#adminForm').submit();
			});

			// Reset characters filter
			$("#reset_btn").click(function(event) {
				event.preventDefault();
				$("form#adminForm input[name='searchchar[]']").prop('checked', false);
				$('form#adminForm #reset_flag').val('1');
				$('form#adminForm').submit();
			});
		});
	})(jQuery);
</script>

<div class="redshopb-manufacturerlist">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
		<div class="row">
			<?php if ($this->manufacturerListSidebar):
				$spanSize      = 2;
				$mainSpanSize -= $spanSize;
				?>
			<div class="sidebar1 col-md-<?php echo $spanSize;?>">
				<div class="row">
					<div class="col-md-12">
						<?php if (!empty($this->items)): ?>
							<ul class="nav-list nav">
								<?php foreach ($this->items as $i => $item): ?>
									<li>
										<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=manufacturer&id=' . $item->id);?>">
											<?php echo $item->name;?>
										</a>
									</li>
								<?php endforeach;?>
							</ul>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<?php endif; ?>
			<div class="mainside col-md-<?php echo $mainSpanSize;?>">
				<h1 class="page-title">
					<?php echo $this->document->title; ?>
				</h1>
				<div class="redshopb-taglist-intro-text"><?php echo Text::_('COM_REDSHOPB_MANUFACTURER_INTRO_TEXT') ?></div>
				<?php if ($this->showSearchField): ?>
				<div class="manufacturerSearchDiv">
					<div class="input-append">
						<?php echo $this->filterForm->getInput('search_manufacturers', 'filter') ?>
						<button class="btn" type="submit"><?php echo Text::_('COM_REDSHOP_MANUFACTURER_SEARCH_BUTTON') ?></button>
					</div>
				</div>
				<?php endif; ?>

				<?php if ($this->showCategoryFilter): ?>
					<div class="redshopb-manufacturerlist-category-filter-wrapper">
						<?php echo $this->filterForm->getInput('category', 'manufacturers_list') ?>
					</div>
				<?php endif; ?>

				<?php if ($this->showAlphabetFilter): ?>
				<div class="redshopb-manufacturerlist-alphabet-filter-wrapper">
					<h3><?php echo Text::_('COM_REDSHOPB_ALPHABETIC_FILTER') ?></h3>
					<ul class="redshopb-manufacturerlist-alphabet-filter-list">
						<?php foreach (range('A', 'Z') as $char): ?>
							<?php if (in_array($char, $this->alphabeticalFilters)): ?>
							<li class="redshopb-manufacturerlist-alphabet-filter-item">
								<a class="search_char_href <?php echo (in_array($char, $this->searchchar)) ? 'active' : '' ?>" href="#">
									<?php echo $char ?>
									<input type="checkbox" value="<?php echo $char ?>" name="searchchar[]"
										<?php echo (in_array($char, $this->searchchar)) ? 'checked' : '' ?> />
								</a>
							</li>
							<?php else: ?>
							<li class="redshopb-manufacturerlist-alphabet-filter-item muted">
								<?php echo $char ?>
							</li>
							<?php endif; ?>
						<?php endforeach;?>
						<li class="redshopb-manufacturerlist-alphabet-filter-item">
							<input type="button" class="btn reset_btn" id="reset_btn" value="x">
							<input type="hidden" name="reset_flag" id="reset_flag" value="0" />
						</li>
					</ul>
				</div>
				<?php endif; ?>

				<?php if (empty($this->items)): ?>
					<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
				<?php else : ?>
					<?php $chunks = array_chunk($this->items, $columns); ?>

					<?php foreach ($chunks AS $items): ?>
					<div class="row">
						<?php foreach ($items as $i => $item): ?>
							<div class="<?php echo $itemSpanWidth; ?>">
								<div class="col-md-12">
									<div class="redshopb-manufacturer-image">
										<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=manufacturer&id=' . $item->id, false);?>">
											<?php $thumb = RedshopbHelperManufacturer::getImageThumbHtml($item->id, true); ?>

											<?php if ($thumb != ''): ?>
												<?php echo $thumb ?>
											<?php endif; ?>
										</a>
									</div>
								</div>
								<div class="redshopb-manufacturer-title col-md-12">
									<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=manufacturer&id=' . $item->id);?>">
										<?php echo $item->name ?>
									</a>
								</div>
							</div>
						<?php endforeach;?>
					</div>
					<?php endforeach;?>
					<div class="redshopb-manufacturers-pagination">
						<?php echo $this->pagination->getPaginationLinks(null, array('showLimitBox' => false)); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div>
			<input type="hidden" name="task" value="">
			<input type="hidden" name="boxchecked" value="0">
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
