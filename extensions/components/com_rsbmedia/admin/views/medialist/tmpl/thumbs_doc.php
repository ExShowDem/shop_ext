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
use Joomla\Registry\Registry;
use Joomla\CMS\Session\Session;

$user   = Factory::getUser();
$app    = Factory::getApplication();
$params = new Registry;
$app->triggerEvent('onContentBeforeDisplay', array('com_rsbmedia.file', &$this->_tmp_doc, &$params));
?>
		<li class="imgOutline thumbnail span2">
			<a class="close delete-item" target="_top" href="index.php?option=com_rsbmedia&amp;task=file.delete&amp;tmpl=index&amp;<?php echo Session::getFormToken(); ?>=1&amp;folder=<?php echo $this->state->folder; ?>&amp;rm[]=<?php echo $this->_tmp_doc->name; ?>" rel="<?php echo $this->_tmp_doc->name; ?>" title="<?php echo Text::_('JACTION_DELETE');?>">&#215;</a>
			<input class="pull-left" type="checkbox" name="rm[]" value="<?php echo $this->_tmp_doc->name; ?>" />
			<div class="clearfix"></div>
			<div class="height-50 pagination-centered">
				<a style="display: block; width: 100%; height: 100%" title="<?php echo $this->_tmp_doc->name; ?>" >
					<?php echo HTMLHelper::_('image', $this->_tmp_doc->icon_32, $this->_tmp_doc->name, null, true, true) ? HTMLHelper::_('image', $this->_tmp_doc->icon_32, $this->_tmp_doc->title, null, true) : HTMLHelper::_('image', 'rsbmedia/con_info.png', $this->_tmp_doc->name, null, true); ?></a>
			</div>
			<div class="pagination-centered" title="<?php echo $this->_tmp_doc->name; ?>" >
				<?php echo HTMLHelper::_('string.truncate', $this->_tmp_doc->name, 10, false); ?>
			</div>
		</li>
<?php
$app->triggerEvent('onContentAfterDisplay', array('com_rsbmedia.file', &$this->_tmp_doc, &$params));
