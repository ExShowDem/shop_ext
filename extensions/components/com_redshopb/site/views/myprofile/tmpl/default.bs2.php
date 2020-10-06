<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('rjquery.chosen', 'select');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder == 'ordering';
$url       = RedshopbRoute::_('index.php?option=com_redshopb&view=address');

$passwordOptions = array('form' => $this->form);

$companyId = RedshopbHelperUser::getUserCompanyId();
$company   = RedshopbEntityCompany::getInstance($companyId)->getItem();
$rsUser    = RedshopbHelperUser::getUser();

if (!isset($this->billingAddressDetails))
{
	$this->billingAddressDetails = new stdClass;
}

if ($companyId && !$company->hide_company)
{
	$this->billingAddressDetails->company = $company->name;

	if ($company->vat_number)
	{
		$this->billingAddressDetails->vatNumber = $company->vat_number;
	}
}

if ($rsUser)
{
	$this->billingAddressDetails->userName = $rsUser->username;

	if ($rsUser->number)
	{
		$this->billingAddressDetails->userNumber = $rsUser->number;
	}

	if ($rsUser->phone)
	{
		$this->billingAddressDetails->userPhone = $rsUser->phone;
	}

	if ($rsUser->email && !$rsUser->use_company_email)
	{
		$this->billingAddressDetails->userEmail = $rsUser->email;
	}
}

$billingOptions = array(
	'header' => 'COM_REDSHOPB_BILLINGADDRESS',
	'address' => $this->billingAddressDetails,
	'address_type' => 'billing',
);

$shippingOptions = array(
	'header' => 'COM_REDSHOPB_SHIPPINGADDRESS',
	'address' => $this->defaultAddressDetails,
	'address_type' => 'shipping',
);

?>

<div class="redshopb-myprofile">
	<h3><?php echo Text::_('COM_REDSHOPB_MYPROFILE'); ?></h3>
	<div class="row-fluid">
		<div class="span4">
			<?php echo RedshopbLayoutHelper::render('myprofile.password', $passwordOptions);?>
		</div>
		<div class="span4">
			<?php echo RedshopbLayoutHelper::render('myprofile.address.form', $billingOptions);?>
		</div>
		<div class="span4">
			<?php echo RedshopbLayoutHelper::render('myprofile.address.form', $shippingOptions);?>
		</div>
	</div>
	<?php if (!empty($this->fieldsData)): ?>
		<script type="text/javascript">
			(function($){
				$(document).ready(function(){
					$("#userFieldsData a:first").tab("show");
				});
			})(jQuery);
		</script>
		<div class="row-fluid">
			<div class="span12">
				<?php
				$dataFields     = array();
				$documentFields = array();
				$imageFields    = array();
				$videoFields    = array();

				foreach ($this->fieldsData as $field)
				{
					switch ($field->type_alias)
					{
						case 'documents':
							$documentFields[] = $field;
							break;

						case 'field-images':
							$imageFields[] = $field;
							break;

						case 'videos':
							$videoFields[] = $field;
							break;

						case 'files':
							$fileFields[] = $field;
							break;

						default:
							$dataFields[] = $field;
							break;
					}
				}
				?>
				<ul class="nav nav-tabs" id="userFieldsData">
					<?php if (!empty($dataFields)): ?>
						<li>
							<a href="#tabFieldsData" data-toggle="tab">
								<?php echo Text::_('COM_REDSHOPB_B2B_USER_FIELD_DATA'); ?>
							</a>
						</li>
					<?php endif; ?>

					<?php if (!empty($documentFields)): ?>
						<li>
							<a href="#tabFieldsDocument" data-toggle="tab">
								<?php echo Text::_('COM_REDSHOPB_B2B_USER_FIELD_DOCUMENT'); ?>
							</a>
						</li>
					<?php endif; ?>

					<?php if (!empty($videoFields)): ?>
						<li>
							<a href="#tabFieldsVideo" data-toggle="tab">
								<?php echo Text::_('COM_REDSHOPB_B2B_USER_FIELD_VIDEO'); ?>
							</a>
						</li>
					<?php endif; ?>

					<?php if (!empty($imageFields)): ?>
						<li>
							<a href="#tabFieldsImage" data-toggle="tab">
								<?php echo Text::_('COM_REDSHOPB_B2B_USER_FIELD_IMAGE'); ?>
							</a>
						</li>
					<?php endif; ?>
				</ul>
				<div class="tab-content">
					<?php if (!empty($dataFields)): ?>
						<div class="tab-pane" id="tabFieldsData">
							<div class="userFieldsData">
								<?php echo RedshopbLayoutHelper::render('user.fields.data', array('fields' => $dataFields)) ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if (!empty($documentFields)): ?>
						<div class="tab-pane" id="tabFieldsDocument">
							<div class="userFieldsDocument">
								<?php echo RedshopbLayoutHelper::render('user.fields.documents', array('fields' => $dataFields)) ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if (!empty($fileFields)): ?>
						<div class="tab-pane" id="tabFieldsFile">
							<div class="userFieldsFile">
								<?php echo RedshopbLayoutHelper::render('user.fields.files', array('fields' => $dataFields)) ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if (!empty($videoFields)): ?>
						<div class="tab-pane" id="tabFieldsVideo">
							<div class="userFieldsVideo">
								<?php echo RedshopbLayoutHelper::render('user.fields.videos', array('fields' => $dataFields)) ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if (!empty($imageFields)): ?>
						<div class="tab-pane" id="tabFieldsImage">
							<div class="userFieldsImage">
								<?php echo RedshopbLayoutHelper::render('user.fields.images', array('fields' => $dataFields)) ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>
<div class="redshopbMyprofileShippingAddresses">
	<hr>
	<h5><?php echo Text::_('COM_REDSHOPB_ALLSHIPPINGADDRESS') ?></h5>
	<div class="redshopb-addresses">
		<form action="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=myprofile'); ?>" name="adminForm"
			  class="adminForm" id="adminForm" method="post">
			<?php
			echo RedshopbLayoutHelper::render(
				'addresses.default',
				array(
					'listOrder' => $listOrder,
					'listDirn' => $listDirn,
					'items' => $this->items,
					'pagination' => $this->pagination,
					'formName' => 'adminForm'
				)
			);
			?>
		</form>
	</div>
</div>
