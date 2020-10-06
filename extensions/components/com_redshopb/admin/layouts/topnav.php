<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$active = null;

if (isset($data['active']))
{
	$active = $data['active'];
}

$configClass = ($active === 'config') ? 'active' : '';
$aclClass    = ($active === 'acl') ? 'active' : '';
$syncClass   = ($active === 'sync') ? 'active' : '';
$toolsClass  = ($active === 'tools') ? 'active' : '';
?>
<span class="divider-vertical pull-left"></span>
<ul class="nav">
	<li>
		<a class="<?php echo $configClass ?>"
		   href="<?php echo Route::_('index.php?option=com_redshopb&task=config.edit') ?>">
			<i class="icon-cogs"></i>
			<?php echo Text::_('COM_REDSHOPB_CONFIG_FORM_TITLE') ?>
		</a>
	</li>
	<li>
		<a class="<?php echo $aclClass ?>"
		   href="<?php echo Route::_('index.php?option=com_redshopb&view=aclroletypes') ?>">
			<i class="icon-unlock"></i>
			<?php echo Text::_('COM_REDSHOPB_ACL_TITLE') ?>
		</a>
	</li>
	<li>
		<a class="<?php echo $syncClass ?>"
		   href="<?php echo Route::_('index.php?option=com_redshopb&view=sync') ?>">
			<i class="icon-cog"></i>
			<?php echo Text::_('COM_REDSHOPB_SYNCRONIZATION') ?>
		</a>
	</li>
	<li>
		<a class="<?php echo $toolsClass ?>"
		   href="<?php echo Route::_('index.php?option=com_redshopb&view=fees') ?>">
			<i class="icon-wrench"></i>
			<?php echo Text::_('COM_REDSHOPB_FEES') ?>
		</a>
	</li>
	<li>
		<a class="<?php echo $toolsClass ?>"
		   href="<?php echo Route::_('index.php?option=com_redshopb&view=webservice_permissions') ?>">
			<i class="icon-unlock-alt"></i>
			<?php echo Text::_('COM_REDSHOPB_WEBSERVICE_PERMISSIONS_TITLE') ?>
		</a>
	</li>
	<li>
		<a class="<?php echo $toolsClass ?>"
		   href="<?php echo Route::_('index.php?option=com_redshopb&view=tools') ?>">
			<i class="icon-wrench"></i>
			<?php echo Text::_('COM_REDSHOPB_TOOLS_TITLE') ?>
		</a>
	</li>
</ul>
