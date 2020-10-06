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
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\Registry\Registry;

HTMLHelper::_('bootstrap.tooltip');

$user   = Factory::getUser();
$app    = Factory::getApplication();
$params = new Registry;
$app->triggerEvent('onContentBeforeDisplay', array('com_rsbmedia.file', &$this->_tmp_doc, &$params));
?>
		<tr>
			<td>
				<a  title="<?php echo $this->_tmp_doc->name; ?>">
					<?php  echo HTMLHelper::_('image', $this->_tmp_doc->icon_16, $this->_tmp_doc->title, null, true, true) ? HTMLHelper::_('image', $this->_tmp_doc->icon_16, $this->_tmp_doc->title, array('width' => 16, 'height' => 16), true) : HTMLHelper::_('image', 'rsbmedia/con_info.png', $this->_tmp_doc->title, array('width' => 16, 'height' => 16), true);?> </a>
			</td>
			<td class="description"  title="<?php echo $this->_tmp_doc->name; ?>">
				<?php echo $this->_tmp_doc->title; ?>
			</td>
			<td>&#160;

			</td>
			<td class="filesize">
				<?php echo HTMLHelper::_('number.bytes', $this->_tmp_doc->size); ?>
			</td>
			<td>
				<a class="delete-item" target="_top" href="index.php?option=com_rsbmedia&amp;task=file.delete&amp;tmpl=index&amp;<?php echo Session::getFormToken(); ?>=1&amp;folder=<?php echo $this->state->folder; ?>&amp;rm[]=<?php echo $this->_tmp_doc->name; ?>" rel="<?php echo $this->_tmp_doc->name; ?>"><i class="icon-remove hasTooltip" title="<?php echo HTMLHelper::tooltipText('JACTION_DELETE');?>"></i></a>
				<input type="checkbox" name="rm[]" value="<?php echo $this->_tmp_doc->name; ?>" />
			</td>
		</tr>
<?php
$app->triggerEvent('onContentAfterDisplay', array('com_rsbmedia.file', &$this->_tmp_doc, &$params));
