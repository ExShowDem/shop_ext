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
use Joomla\CMS\Factory;

$input = Factory::getApplication()->input;
?>
<li class="imgOutline thumbnail span2">
	<a href="index.php?option=com_rsbmedia&amp;view=imagesList&amp;tmpl=component&amp;folder=<?php echo $this->_tmp_folder->path_relative; ?>&amp;asset=<?php echo $input->getCmd('asset');?>&amp;author=<?php echo $input->getCmd('author');?>" target="imageframe">
		<div style="height: 50px" class="pagination-centered">
			<i class="icon-folder-close icon-3x"></i>
		</div>
		<div class="small pagination-centered">
			<?php echo HTMLHelper::_('string.truncate', $this->_tmp_folder->name, 10, false); ?>
		</div>
	</a>
</li>
