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
use Joomla\CMS\Factory;

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('rsearchtools.main');

RedshopbHtml::loadFooTable();
Factory::getLanguage()->load('com_users');
$action = RedshopbRoute::_('index.php?option=com_redshopb&view=user&layout=own');
$input  = Factory::getApplication()->input;

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>

<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">

	<div class="form-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('name'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('name'); ?>
		</div>
	</div>
	<div class="form-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('name1'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('name1'); ?>
		</div>
	</div>
	<div class="form-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('name2'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('name2'); ?>
		</div>
	</div>
	<div class="form-group" id="password">
		<div class="control-label">
			<?php echo $this->form->getLabel('password'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('password'); ?>
		</div>
	</div>
	<div class="form-group" id="password2">
		<div class="control-label">
			<?php echo $this->form->getLabel('password2'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('password2'); ?>
		</div>
	</div>
	<input type="hidden" name="option" value="com_redshopb" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="id" value="<?php echo $this->item->id ?>" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
