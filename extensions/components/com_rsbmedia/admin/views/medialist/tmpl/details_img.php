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
use Joomla\Registry\Registry;

HTMLHelper::_('bootstrap.tooltip');

$user   = Factory::getUser();
$app    = Factory::getApplication();
$params = new Registry;
$app->triggerEvent('onContentBeforeDisplay', array('com_rsbmedia.file', &$this->_tmp_img, &$params));
?>
		<tr>
			<td>
				<a class="img-preview" href="<?php echo COM_RSBMEDIA_BASEURL . '/' . $this->_tmp_img->path_relative; ?>" title="<?php echo $this->_tmp_img->name; ?>"><?php echo HTMLHelper::_('image', COM_RSBMEDIA_BASEURL . '/' . $this->_tmp_img->path_relative, Text::sprintf('COM_RSBMEDIA_IMAGE_TITLE', $this->_tmp_img->title, HTMLHelper::_('number.bytes', $this->_tmp_img->size)), array('width' => $this->_tmp_img->width_16, 'height' => $this->_tmp_img->height_16)); ?></a>
			</td>
			<td class="description">
				<a href="<?php echo  COM_RSBMEDIA_BASEURL . '/' . $this->_tmp_img->path_relative; ?>" title="<?php echo $this->_tmp_img->name; ?>" rel="preview"><?php echo $this->escape($this->_tmp_img->title); ?></a>
			</td>
			<td class="dimensions">
				<?php echo Text::sprintf('COM_RSBMEDIA_IMAGE_DIMENSIONS', $this->_tmp_img->width, $this->_tmp_img->height); ?>
			</td>
			<td class="filesize">
				<?php echo HTMLHelper::_('number.bytes', $this->_tmp_img->size); ?>
			</td>
			<td>
				<a class="delete-item" target="_top" href="index.php?option=com_rsbmedia&amp;task=file.delete&amp;tmpl=index&amp;<?php echo Session::getFormToken(); ?>=1&amp;folder=<?php echo $this->state->folder; ?>&amp;rm[]=<?php echo $this->_tmp_img->name; ?>" rel="<?php echo $this->_tmp_img->name; ?>"><i class="icon-remove hasTooltip" title="<?php echo HTMLHelper::tooltipText('JACTION_DELETE');?>"></i></a>
				<input type="checkbox" name="rm[]" value="<?php echo $this->_tmp_img->name; ?>" />
			</td>
		</tr>
<?php
$app->triggerEvent('onContentAfterDisplay', array('com_rsbmedia.file', &$this->_tmp_img, &$params));
