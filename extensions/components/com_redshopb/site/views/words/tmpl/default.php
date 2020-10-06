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

HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

$synonymEnabled = RedshopbEntityConfig::getInstance()->getBool('product_search_synonyms', false);

RedshopbHtml::loadFooTable();

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=words');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>

<?php if (!$synonymEnabled): ?>
<div class="alert alert-warning">
	<button type="button" class="close" data-dismiss="alert">×</button>
	<p><?php echo Text::_('COM_REDSHOPB_WORD_NOTICE_PRODUCT_SEARCH_SYNONYMS_NOT_ENABLED') ?></p>
</div>
<?php endif; ?>

<div class="redshopb-tags">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">

		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'searchField' => 'search_words',
					'searchFieldSelector' => '#filter_search_words',
					'limitFieldSelector' => '#list_word_limit',
					'activeOrder' => $listOrder,
					'activeDirection' => $listDirn
				)
			)
		);
		?>

		<hr/>
		<?php if (empty($this->items)) : ?>
			<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
		<?php else : ?>
			<div class="redshopb-words-table">
				<table class="table table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="wordsList">
					<thead>
					<tr>
						<th width="1%">
							<input type="checkbox" name="checkall-toggle" value=""
								   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_WORD_SHARED', 'sm.shared', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_WORD', 'sm.word', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo Text::_('COM_REDSHOPB_WORD_MAIN_MEANINGS'); ?>
						</th>
						<th>
							<?php echo Text::_('COM_REDSHOPB_WORD_SYNONYMS'); ?>
						</th>
						<th width="1%" class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 'sm.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<?php if ($this->items):
						$canChange = true;
						$canEdit   = true;

						$states = array(
							1 => array('unshare', 'COM_REDSHOPB_WORD_SHARED', 'COM_REDSHOPB_WORD_SHARED', '', false, 'ok-sign icon-green', 'ok-sign icon-green'),
							0 => array('share', 'COM_REDSHOPB_WORD_NOT_SHARED', 'COM_REDSHOPB_WORD_NOT_SHARED', '', false, 'remove icon-red', 'remove icon-red'),
						);

						?>
						<tbody>
						<?php foreach ($this->items as $i => $item): ?>
							<tr>
								<td>
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td>
									<?php echo HTMLHelper::_('rgrid.state', $states, $item->shared, $i, 'words.', $canChange, 'cb');?>
								</td>
								<td>
									<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=word.edit&id=' . $item->id); ?>" title="<?php echo $this->escape($item->word); ?>">
										<?php echo $this->escape($item->word); ?>
									</a>
								</td>
								<td>
									<?php
									if (!empty($item->main_words))
									{
										echo implode(', ', $item->main_words);
									}
									else
									{
										echo '<b>' . Text::_('COM_REDSHOPB_WORD_MAIN_WORD') . '</b>';
									}
									?>
								</td>
								<td>
									<?php
									if (isset($item->synonyms))
									{
										echo implode(', ', $item->synonyms);
									}
									?>
								</td>
								<td>
									<?php echo $item->id; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					<?php endif; ?>
				</table>
			</div>
			<div class="redshopb-tags-pagination">
				<?php echo $this->pagination->getPaginationLinks(null, array('showLimitBox' => false)); ?>
			</div>
		<?php endif; ?>

		<div>
			<input type="hidden" name="task" value="">
			<input type="hidden" name="boxchecked" value="0">
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
