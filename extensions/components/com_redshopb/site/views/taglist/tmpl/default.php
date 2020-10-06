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

$action       = RedshopbRoute::_('index.php?option=com_redshopb&view=taglist');
$counter      = 0;
$spanSize     = $this->spanWidth;
$mainSpanSize = 12 - $spanSize;
?>

<script type="text/javascript">
	(function($) {
		$(document).ready(function() {
			// Characters filter click process
			$(".search_char_href").click(function(event) {
				event.preventDefault();
				$("#searchchar").val($(this).attr('id'));
				$('form#adminForm').submit();
			});

			// Reset characters filter
			$("#reset_btn").click(function(event) {
				event.preventDefault();
				$("#searchchar").val('');
				$('form#adminForm').submit();
			});
		});
	})(jQuery);
</script>

<div class="redshopb-taglist">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
		<div class="row">
			<div class="sidebar1 col-md-<?php echo $spanSize;?>">
				<ul class="nav-list nav">
					<?php foreach ($this->items as $i => $item): ?>
						<li>
							<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=productlist&tag_id=' . $item->id);?>">
								<?php echo $item->name;?>
							</a>
						</li>
					<?php endforeach;?>
				</ul>
			</div>
			<div class="mainside col-md-<?php echo $mainSpanSize;?>">

				<h1 class="page-title">
					<?php echo $this->document->title; ?>
				</h1>
				<div class="redshopb-taglist-intro-text"><?php echo Text::_('COM_REDSHOPB_TAGLIST_INTRO_TEXT') ?></div>
				<h3><?php echo Text::_('COM_REDSHOPB_ALPHABETIC_FILTER') ?></h3>
				<div class="redshopb-taglist-alphabetic-filter">
					<?php foreach ($this->availableChars as $char) : ?>
						<a
							class="search_char_href <?php echo $this->searchchar == $char ? 'active' : ''; ?>"
							id="<?php echo $char; ?>" href="#"
						>
							<?php echo $char; ?>
						</a>
					<?php endforeach;?>
					<input type="submit" class="btn reset_btn" id="reset_btn" name="reset_btn" value="x">
				</div>
				<?php if (empty($this->items)) : ?>
					<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
				<?php else : ?>
					<div class="row">
						<?php foreach ($this->items as $i => $item): ?>
							<?php if ($counter % 3 == 0) :?>
								<div class="row">
							<?php endif;?>
							<div class="col-md-4">
								<div class="col-md-12">
									<div class="redshopb-taglist-image">
										<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=productlist&tag_id=' . $item->id);?>">
											<?php $thumb = RedshopbHelperTag::getTagImageThumbHtml($item->id);

											if ($thumb != '') : ?>
												<?php echo $thumb;?>
											<?php endif;?>
										</a>
									</div>
								</div>
								<div class="redshopb-taglist-title col-md-12">
									<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=productlist&tag_id=' . $item->id);?>">
										<?php echo $item->name;?>
									</a>
								</div>
							</div>
							<?php $counter++;

							if ($counter % 3 == 0) :?>
								</div>
							<?php endif;?>
						<?php endforeach;?>
					</div>
					<div class="redshopb-tags-pagination">
						<?php echo $this->pagination->getPaginationLinks(null, array('showLimitBox' => false)); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div>
			<input type="hidden" name="task" value="">
			<input type="hidden" name="boxchecked" value="0">
			<input type="hidden" name="searchchar" id="searchchar" value="<?php echo $this->searchchar;?>">
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
