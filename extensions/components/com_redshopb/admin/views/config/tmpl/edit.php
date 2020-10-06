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
use Joomla\CMS\Router\Route;

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
$fieldSets                   = $this->form->getFieldsets();
?>
<hr/>
<form action="<?php echo Route::_('index.php?option=com_redshopb&view=config&layout=edit') ?>" method="post" name="adminForm" id="adminForm"
	  class="form-validate form-horizontal">
	<div class="row">
		<div class="col-md-2">
			<ul class="nav nav-stacked nav-pills">
				<?php $index = 0; ?>

				<?php foreach ($fieldSets as $domId => $fieldSet): ?>
					<li class="<?php echo ($index == 0) ? 'active' : '' ?>">
						<a href="#<?php echo $domId ?>" data-toggle="tab"><?php echo Text::_($fieldSet->label) ?></a>
					</li>
					<?php $index++; ?>
				<?php endforeach; ?>
			</ul>
		</div>
		<div class="col-md-10">
			<div class="tab-content">
				<?php $index = 0; ?>

				<?php foreach ($fieldSets as $domId => $fieldSet): ?>
					<div class="tab-pane <?php echo ($index == 0) ? 'active' : '' ?>" id="<?php echo $domId ?>">
						<div class="alert alert-info">
							<?php echo Text::_($fieldSet->description) ?>
						</div>
						<?php $index = 0; ?>

						<?php foreach ($this->form->getFieldset($domId) as $field): ?>
							<?php $class = ($index % 2 == 0) ? 'row-even' : 'row-odd'; ?>
							<div class="container-fluid">
								<?php echo $field->renderField(array('bs3' => true, 'class' => $class)) ?>
							</div>
							<?php $index++; ?>
						<?php endforeach; ?>
					</div>
					<?php $index++; ?>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<!-- hidden fields -->
	<input type="hidden" name="option" value="com_redshopb">
	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
