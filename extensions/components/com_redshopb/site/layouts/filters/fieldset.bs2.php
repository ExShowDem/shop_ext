<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

extract($displayData);
?>

<div class="filter-fieldsets-wrapper">
	<?php if (isset($vertical) && $vertical === true): ?>
		<?php foreach ($data as $filterFieldset): ?>
			<?php if (!empty($filterFieldset->filters)): ?>
				<h3><?php echo $filterFieldset->name ?></h3>
				<fieldset class="form-vertical">
					<?php foreach ($filterFieldset->filters as $filter): ?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $filter->title; ?>
							</div>
							<div class="controls">
								<?php echo $filter->input ?>
							</div>
						</div>
					<?php endforeach; ?>
				</fieldset>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php else: ?>
		<?php foreach ($data as $filterFieldset): ?>
			<?php if (!empty($filterFieldset->filters)): ?>
				<fieldset class="form-horizontal filter-fieldsets">
				<?php foreach ($filterFieldset->filters as $filter): ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $filter->title ?>
						</div>
						<div class="controls">
							<?php echo $filter->input ?>
						</div>
					</div>
				<?php endforeach; ?>
				</fieldset>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php if (isset($showSubmit) && $showSubmit): ?>
		<input type="button" class="btn btn-primary" value="<?php echo Text::_('COM_REDSHOPB_FILTER_SEARCH') ?>" onclick="this.form.submit();" />
	<?php endif; ?>
</div>
