<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;
?>
<div class="redshopb-user-joomla-usergroups">
	<?php if (empty($this->jusergroups)) : ?>
	<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
	<?php else: ?>
	<div class="container-fluid">
		<div class="row" id="joomla_usergroups">
			<?php foreach ($this->jusergroups as $usergroup): ?>
			<div class="form-group">
				<div class="controls">
					<label class="checkbox" for="group_<?php echo $usergroup->id; ?>">
						<input
							type="checkbox"
							name="jform[joomla_usergroups][]"
							value="<?php echo $usergroup->id; ?>"
							id="group_<?php echo $usergroup->id; ?>"

							<?php if (in_array($usergroup->id, $this->item->joomla_usergroups)) :?>
							checked="checked"
							<?php endif; ?>
						/>
						<?php echo LayoutHelper::render('joomla.html.treeprefix', array('level' => $usergroup->level + 1)) . $usergroup->title; ?>
					</label>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>
</div>
