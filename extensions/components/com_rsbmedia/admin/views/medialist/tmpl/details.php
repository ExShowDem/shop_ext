<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  com_rsbmedia
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

$user = Factory::getUser();
?>
<form target="_parent" action="index.php?option=com_rsbmedia&amp;tmpl=index&amp;folder=<?php echo $this->state->folder; ?>" method="post" id="mediamanager-form" name="mediamanager-form">
	<div class="manager">
	<table class="table table-striped table-condensed">
	<thead>
		<tr>
			<th width="1%"><?php echo Text::_('JGLOBAL_PREVIEW'); ?></th>
			<th><?php echo Text::_('COM_RSBMEDIA_NAME'); ?></th>
			<th width="15%"><?php echo Text::_('COM_RSBMEDIA_PIXEL_DIMENSIONS'); ?></th>
			<th width="8%"><?php echo Text::_('COM_RSBMEDIA_FILESIZE'); ?></th>
			<th width="8%"><?php echo Text::_('JACTION_DELETE'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php echo $this->loadTemplate('up'); ?>

		<?php for ($i = 0, $n = count($this->folders); $i < $n; $i++) :
			$this->setFolder($i);
			echo $this->loadTemplate('folder');
		endfor; ?>

		<?php for ($i = 0, $n = count($this->documents); $i < $n; $i++) :
			$this->setDoc($i);
			echo $this->loadTemplate('doc');
		endfor; ?>

		<?php for ($i = 0, $n = count($this->images); $i < $n; $i++) :
			$this->setImage($i);
			echo $this->loadTemplate('img');
		endfor; ?>

	</tbody>
	</table>
	<input type="hidden" name="task" value="list" />
	<input type="hidden" name="username" value="" />
	<input type="hidden" name="password" value="" />
	<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
