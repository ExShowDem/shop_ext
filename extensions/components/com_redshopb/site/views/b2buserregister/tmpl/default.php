<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

$input  = Factory::getApplication()->input;
$return = $input->getBase64('return');
$action = 'index.php?option=com_redshopb&view=b2buserregister';
$action = $input->getInt('Itemid', 0) !== 0 ? $action . '&Itemid=' . $input->getInt('Itemid', 0) : $action;

if ($this->company->id)
{
	?>
	<div class="panel-group" id="redshopb-signin-b2c-accordion">
	<div class="panel panel-default">
	<div class="panel-heading">
		<a class="accordion-toggle" data-toggle="collapse" data-parent="#redshopb-signin-b2c-accordion"
		   href="#collapseLogin">
			<h4><?php echo Text::_('COM_REDSHOPB_LOGIN_FORM_NAME') ?></h4>
		</a>
	</div>
	<div id="collapseLogin"
	class="panel-collapse collapse <?php echo $this->defaultOpen == 'login' ? 'in' : ''; ?>">
	<div class="panel-body">
	<?php
}

$registerModel = RedshopbModel::getInstance('B2BUserRegister', 'RedshopbModel');
$registerModel->set('context', 'com_redshopb.edit.b2buserregister.login');
$registerModel->set('formName', 'login');

echo RedshopbLayoutHelper::render(
	'user.login',
	array(
		'form' => $registerModel->getLoginForm(),
		'formName' => 'redshopLoginForm',
		'returnSuccess' => $return,
		'returnFail' => base64_encode(RedshopbHelperRoute::getRoute($action . '&active=login'))
	)
);

if ($this->company->id)
{
	?>
	</div>
	</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<a class="accordion-toggle" data-toggle="collapse"
			   data-parent="#redshopb-signin-b2c-accordion" href="#collapseRegister">
				<h4><?php echo Text::_('COM_REDSHOPB_B2BUSER') ?></h4>
			</a>
		</div>
		<div id="collapseRegister"
			 class="panel-collapse collapse <?php echo $this->defaultOpen == 'register' ? 'in' : ''; ?>">
			<div class="panel-body">
				<?php
				echo RedshopbLayoutHelper::render(
					'user.register',
					array(
						'form' => $this->form,
						'action' => RedshopbRoute::_($action),
						'return' => $return,
						'cancel' => Uri::root(),
						'returnFail' => base64_encode(RedshopbHelperRoute::getRoute($action . '&active=register'))
					)
				);
				?>
			</div>
		</div>
	</div>
	</div>
	<?php
}
