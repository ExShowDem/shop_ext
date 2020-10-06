<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  com_rsbmedia
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

$app    = Factory::getApplication();
$params = new Registry;
$app->triggerEvent('onContentBeforeDisplay', array('com_rsbmedia.file', &$this->_tmp_img, &$params));
?>
<li class="imgOutline thumbnail span2">
	<a class="img-preview" href="javascript:ImageManager.populateFields('<?php echo $this->_tmp_img->path_relative; ?>')" title="<?php echo $this->_tmp_img->name; ?>" >
		<div style="height: 50px" class="pagination-centered">
			<?php echo HTMLHelper::_('image', $this->baseURL . '/' . $this->_tmp_img->path_relative, Text::sprintf('COM_RSBMEDIA_IMAGE_TITLE', $this->_tmp_img->title, HTMLHelper::_('number.bytes', $this->_tmp_img->size)), array('width' => $this->_tmp_img->width_60, 'height' => $this->_tmp_img->height_60)); ?>
		</div>
		<div class="pagination-centered">
			<?php echo HTMLHelper::_('string.truncate', $this->_tmp_img->name, 10, false); ?>
		</div>
	</a>
</li>
<?php
$app->triggerEvent('onContentAfterDisplay', array('com_rsbmedia.file', &$this->_tmp_img, &$params));
