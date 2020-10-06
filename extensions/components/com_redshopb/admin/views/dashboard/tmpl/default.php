<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Templates
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>

<div class="container-fluid">
	<div class="row">
		<div class="col-md-12 text-center">
			<h1 class="text-center">
				Aesir E-Commerce
			</h1>
		</div>
	</div>
	<p>&nbsp;</p>
	<div class="row">
		<div class="col-md-3">
			<a href="<?php echo Route::_('index.php?option=com_redshopb&task=config.edit') ?>">
				<div class="row pagination-centered">
					<span class="dashboard-icon-link-icon">
						<i class="icon-cogs icon-4x"></i>
					</span>
				</div>
				<div class="row pagination-centered">
					<span class="dashboard-icon-link-text">
						<?php echo Text::_('COM_REDSHOPB_CONFIG_FORM_TITLE') ?>
					</span>
				</div>
			</a>
		</div>
		<div class="col-md-3">
			<a href="<?php echo Route::_('index.php?option=com_redshopb&view=aclroletypes') ?>">
				<div class="row pagination-centered">
					<span class="dashboard-icon-link-icon">
						<i class="icon-unlock icon-4x"></i>
					</span>
				</div>
				<div class="row pagination-centered">
					<span class="dashboard-icon-link-text">
						<?php echo Text::_('COM_REDSHOPB_ACL_TITLE') ?>
					</span>
				</div>
			</a>
		</div>
		<div class="col-md-3">
			<a href="<?php echo Route::_('index.php?option=com_redshopb&view=sync') ?>">
				<div class="row pagination-centered">
					<span class="dashboard-icon-link-icon">
						<i class="icon-cog icon-4x"></i>
					</span>
				</div>
				<div class="row pagination-centered">
					<span class="dashboard-icon-link-text">
						<?php echo Text::_('COM_REDSHOPB_SYNCRONIZATION') ?>
					</span>
				</div>
			</a>
		</div>
		<div class="col-md-3">
			<a href="<?php echo Route::_('index.php?option=com_redshopb&view=fees') ?>">
				<div class="row pagination-centered">
					<span class="dashboard-icon-link-icon">
						<i class="icon-tag icon-4x"></i>
					</span>
				</div>
				<div class="row pagination-centered">
					<span class="dashboard-icon-link-text">
						<?php echo Text::_('COM_REDSHOPB_FEES') ?>
					</span>
				</div>
			</a>
		</div>
	</div>
	<p>&nbsp;</p>
	<div class="row">
		<div class="col-md-3">
			<a href="<?php echo Route::_('index.php?option=com_redshopb&view=webservice_permissions') ?>">
				<div class="row pagination-centered">
					<span class="dashboard-icon-link-icon">
						<i class="icon-unlock-alt icon-4x"></i>
					</span>
				</div>
				<div class="row pagination-centered">
					<span class="dashboard-icon-link-text">
						<?php echo Text::_('COM_REDSHOPB_WEBSERVICE_PERMISSIONS_TITLE') ?>
					</span>
				</div>
			</a>
		</div>
		<div class="col-md-3">
			<a href="<?php echo Route::_('index.php?option=com_redshopb&view=webservice_permission_users') ?>">
				<div class="row pagination-centered">
					<span class="dashboard-icon-link-icon">
						<i class="icon-user icon-4x"></i>
					</span>
				</div>
				<div class="row pagination-centered">
					<span class="dashboard-icon-link-text">
						<?php echo Text::_('COM_REDSHOPB_WEBSERVICE_PERMISSION_USERS_TITLE') ?>
					</span>
				</div>
			</a>
		</div>
		<div class="col-md-3">
			<a href="<?php echo Route::_('index.php?option=com_redshopb&view=tools') ?>">
				<div class="row pagination-centered">
					<span class="dashboard-icon-link-icon">
						<i class="icon-wrench icon-4x"></i>
					</span>
				</div>
				<div class="row pagination-centered">
					<span class="dashboard-icon-link-text">
						<?php echo Text::_('COM_REDSHOPB_TOOLS_TITLE') ?>
					</span>
				</div>
			</a>
		</div>
	</div>
</div>
