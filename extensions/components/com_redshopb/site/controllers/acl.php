<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * ACL Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerACL extends RedshopbControllerForm
{
	/**
	 * Ajax call to get an asset permission group (for input)
	 *
	 * @return  void
	 */
	public function ajaxassetgroups()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		// Parameters sent for the layout
		$companyId   = $input->getInt['company_id'];
		$assetId     = $input->getInt['asset_id'];
		$sectionName = $input->getString['section_name'];
		$inputId     = $input->getString['input_id'];
		$inputName   = $input->getString['input_name'];
		$simpleUX    = $input->getString['simple'];

		if ($companyId && $assetId && $inputId != '' && $inputName != '')
		{
			echo RedshopbLayoutHelper::render(
				'acl.assetgroups',
				array(
					'view' => $input->getString('view'),
					'options' => array (
						'company_id' => $companyId,
						'asset_id' => $assetId,
						'section' => $sectionName,
						'input_id' => $inputId,
						'input_name' => $inputName,
						'simple' => $simpleUX
					)
				),
				'',
				array('client' => 0)
			);
		}

		$app->close();
	}

	/**
	 * Ajax call to get a group permissions (for input)
	 *
	 * @return  void
	 */
	public function ajaxgrouppermissions()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$input = $app->input;

		// Parameters sent for the layout
		$assetId     = $input->getInt('asset_id');
		$sectionName = $input->getString('section_name');
		$inputId     = $input->getString('input_id');
		$inputName   = $input->getString('input_name');
		$groupId     = $input->getInt('group_id');
		$groupName   = $input->getString('group_name');
		$simpleUX    = $input->getString('simple');

		if ($assetId && $inputId != '' && $inputName != '' && $groupId != '' && $groupName != '')
		{
			echo RedshopbLayoutHelper::render(
				'acl.grouppermissions',
				array(
					'view' => $input->getString('view'),
					'options' => array (
						'asset_id' => $assetId,
						'section' => $sectionName,
						'input_id' => $inputId,
						'input_name' => $inputName,
						'group_id' => $groupId,
						'group_name' => $groupName,
						'active_group' => false,
						'simple' => $simpleUX
					)
				),
				'',
				array('client' => 0)
			);
		}

		$app->close();
	}
}
