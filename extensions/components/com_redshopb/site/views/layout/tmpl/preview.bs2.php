<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

jimport('joomla.application.module.helper');

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

$app = Factory::getApplication();

$layoutParts         = ModuleHelper::getModule('mod_redshopb_layoutparts');
$layoutPartsParams   = $layoutParts->params;
$layoutParts->params = "part=headLine\nwrapper=h1";
echo ModuleHelper::renderModule($layoutParts, array());
$layoutParts->params = "part=welcome\nwrapper=h3";
echo ModuleHelper::renderModule($layoutParts, array());

$layoutParts->params = $layoutPartsParams;

$companyName = 'redSHOP';

$mainCompany = RedshopbApp::getMainCompany();

if ($mainCompany)
{
	$companyName = $mainCompany->name;
}

?>
<div class="redshopb-layout-preview">
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span12 text-center">
				<strong>
					<?php echo $companyName ?> B2B
				</strong>
			</div>
		</div>
	</div>
</div>
