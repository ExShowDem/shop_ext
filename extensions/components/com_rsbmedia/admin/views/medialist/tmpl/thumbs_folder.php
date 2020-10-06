<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Rsmedia
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;

$user = Factory::getUser();
?>
		<li class="imgOutline thumbnail span2">
			<a class="close delete-item" target="_top" href="index.php?option=com_rsbmedia&amp;task=folder.delete&amp;tmpl=index&amp;<?php echo Session::getFormToken(); ?>=1&amp;folder=<?php echo $this->state->folder; ?>&amp;rm[]=<?php echo $this->_tmp_folder->name; ?>" rel="<?php echo $this->_tmp_folder->name; ?> :: <?php echo $this->_tmp_folder->files + $this->_tmp_folder->folders; ?>" title="<?php echo Text::_('JACTION_DELETE');?>">&#215;</a>
			<input class="pull-left" type="checkbox" name="rm[]" value="<?php echo $this->_tmp_folder->name; ?>" />
			<div class="clearfix"></div>
			<div class="height-50 pagination-centered">
				<a href="index.php?option=com_rsbmedia&amp;view=mediaList&amp;tmpl=component&amp;folder=<?php echo $this->_tmp_folder->path_relative; ?>" target="folderframe">
					<i class="icon-folder-close"></i>
				</a>
			</div>
			<div class="pagination-centered">
				<a href="index.php?option=com_rsbmedia&amp;view=mediaList&amp;tmpl=component&amp;folder=<?php echo $this->_tmp_folder->path_relative; ?>" target="folderframe"><?php echo HTMLHelper::_('string.truncate', $this->_tmp_folder->name, 10, false); ?></a>
			</div>
		</li>
