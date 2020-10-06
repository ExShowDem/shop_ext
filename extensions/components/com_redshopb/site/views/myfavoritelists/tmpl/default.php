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
use Joomla\CMS\Session\Session;

HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rdropdown.init');
HTMLHelper::_('rjquery.chosen', 'select');

RedshopbHtml::loadFooTable();

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();

/** @var RedshopbModelMyfavoritelists $model */
$model = $this->getModel();

$myFavoritesOptions = array(
	'state' => $model->getState(),
	'items' => $model->getItems(),
	'pagination' => $model->getPagination(),
	'formName' => 'myfavoritelists',
	'action' => 'index.php?option=com_redshopb&view=myfavoritelists',
	'itemLink' => 'index.php?option=com_redshopb&task=myfavoritelist.edit',
	'listOrder' => $model->getState('list.ordering'),
	'listDirn' => $model->getState('list.direction'),
	'filter_form'   => $model->getForm(),
	'activeFilters' => $model->getActiveFilters(),
	'isManage' => true,
	'showToolbar' => true,
);
?>
<script type="text/javascript">
	jQuery(document).ready(function()
	{
		redSHOPB.ajaxTabs.init('<?php echo Session::getFormToken();?>=1', false);
		var form = jQuery('#MyFavorites').find('form');
		form.find('input[name="task"]').val('');
		form.find('select[onchange][name^="list["]').attr('onchange', 'redSHOPB.ajaxTabs.updateTab(event);');
		form.find('select[name^="filter["]').attr('onchange', '').attr('onchange', 'redSHOPB.ajaxTabs.updateTab(event);');

		form.on('submit', function() {
			form = jQuery(this);
			var toDoTask = form.find('input[name="task"]').val();

			if (toDoTask == '') {
				var tabContent = form.parents('div.tab-pane');
				tabContent.addClass('opacity-40');

				redSHOPB.ajaxTabs.loadTab(tabContent.attr('id'));
				return false;
			}
		});
	});
</script>
<div class="redshopb-myfavoritelists">
	<div class="row">
		<div class="col-md-12">
			<ul class="nav nav-tabs">
				<li class="active">
					<a href="#MyFavorites" data-toggle="tab"><?php echo Text::_('COM_REDSHOPB_MYFAVORITELIST_MY_LISTS') ?></a>
				</li>
				<li>
					<a href="#SharedFavorites" data-toggle="tab" data-ajax-tab-load="true"><?php echo Text::_('COM_REDSHOPB_MYFAVORITELIST_SHARED_LISTS') ?></a>
				</li>
			</ul>

			<div class="tab-content">
				<div class="tab-pane active" id="MyFavorites"  data-load-task="myfavoritelists.ajaxLists">
					<div class="row">
						<div class="col-md-12 ajax-content">
							<?php echo RedshopbLayoutHelper::render('myfavoritelists.lists', $myFavoritesOptions);?>
						</div>
					</div>
				</div>
				<div id="SharedFavorites" class="tab-pane"
					 data-url="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=myfavoritelists'); ?>"
					 data-load-task="myfavoritelists.ajaxGetSharedList">
					<div class="row">
						<div class="col-md-12 ajax-content">
							<div class="spinner pagination-centered">
								<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
