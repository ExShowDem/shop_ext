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
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;

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
	 * Rebuild Company ACL
	 *
	 * @return void
	 */
	public function ajaxRebuildCompany()
	{
		$app   = Factory::getApplication();
		$input = $app->input;
		$id    = $input->get('id', 'int');

		if (RedshopbHelperACL::rebuildCompanyACL($id))
		{
			echo Text::_('COM_REDSHOPB_COMPANY_LABEL') . ' ' . $id . ': Ok';
		}
		else
		{
			echo Text::_('COM_REDSHOPB_COMPANY_LABEL') . ' ' . $id . ': Error';
		}

		$app->close();
	}

	/**
	 * Rebuild Department ACL
	 *
	 * @return void
	 */
	public function ajaxRebuildDepartment()
	{
		$app   = Factory::getApplication();
		$input = $app->input;
		$id    = $input->get('id', 'int');

		if (RedshopbHelperACL::rebuildDepartmentACL($id))
		{
			echo Text::_('COM_REDSHOPB_DEPARTMENT_LABEL') . ' ' . $id . ': Ok';
		}
		else
		{
			echo Text::_('COM_REDSHOPB_DEPARTMENT_LABEL') . ' ' . $id . ': Error';
		}

		$app->close();
	}

	/**
	 * Ajax call to get the company permissions set
	 *
	 * @return  void
	 */
	public function ajaxpermissions()
	{
		$app = Factory::getApplication();

		// Loads the form to get the input with the permission set
		$asset = Table::getInstance('asset');

		if ($asset->loadByName('com_redshopb'))
		{
			// Load common and local language files.
			$lang = Factory::getLanguage();

			// Load language file
			$lang->load('com_redshopb', JPATH_SITE, null, false, false)
			|| $lang->load('com_redshopb', JPATH_SITE . "/components/com_redshopb", null, false, false)
			|| $lang->load('com_redshopb', JPATH_SITE, $lang->getDefault(), false, false)
			|| $lang->load('com_redshopb', JPATH_SITE . "/components/com_redshopb", $lang->getDefault(), false, false);

			$model = $this->getModel();
			$form  = $model->getForm();
			$form->setValue('asset_id', null, $asset->id);

			echo $form->getInput('acl_rules');
		}

		$app->close();
	}

	/**
	 * Rebuild ACL permissions
	 *
	 * @return  void
	 */
	public function rebuildACL()
	{
		RedshopbHelperACL::rebuildACLBase();
		$app = Factory::getApplication();
		$app->setUserState('redshopb.rebuildACLBase', 1);
		$this->setRedirect('index.php?option=com_redshopb&view=acl');
	}

	/**
	 * Ajax call to get a group permissions (for input)
	 *
	 * @return  void
	 */
	public function ajaxgrouppermissions()
	{
		$app   = Factory::getApplication();
		$input = $app->input;

		// Parameters sent for the layout
		$assetId     = $input->getInt('asset_id');
		$sectionName = $input->getString('section_name');
		$inputId     = $input->getString('input_id');
		$inputName   = $input->getString('input_name');
		$groupId     = $input->getInt('group_id');
		$groupName   = $input->getString('group_name');

		if ($assetId && $sectionName != '' && $inputId != '' && $inputName != '' && $groupId != '' && $groupName != '')
		{
			// Load common and local language files.
			$lang = Factory::getLanguage();

			// Load language file
			$lang->load('com_redshopb', JPATH_SITE, null, false, false)
			|| $lang->load('com_redshopb', JPATH_SITE . "/components/com_redshopb", null, false, false)
			|| $lang->load('com_redshopb', JPATH_SITE, $lang->getDefault(), false, false)
			|| $lang->load('com_redshopb', JPATH_SITE . "/components/com_redshopb", $lang->getDefault(), false, false);

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
						'active_group' => false
					)
				),
				'',
				array('client' => 0)
			);
		}

		$app->close();
	}

	/**
	 * Function that handles the ajax call to get the new pages
	 * Prints html rules field
	 * @return void
	 */
	public function ajaxGetNewGroupsPage()
	{
		$app      = Factory::getApplication();
		$nextPage = $app->input->getInt('page', 0);
		$model    = $this->getModel();
		$form     = $model->getForm();
		$fields   = $form->getFieldset();

		foreach ($fields as $field)
		{
			if ($field->__get('fieldname') == 'rules')
			{
				require_once JPATH_ROOT . '/libraries/redshopb/form/fields/rules.php';
				$field = new JFormFieldRulesB2B($field);
				$field->setCurrentPage($nextPage);

				echo $field->getInput();
			}
		}

		$app->close();
	}

	/**
	 * Ajax function for getting list of departments.
	 *
	 * @return void
	 */
	public function ajaxGetDepartments()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app       = Factory::getApplication();
		$input     = $app->input;
		$companyId = $input->getInt('companyId', 0);
		$fieldName = $input->getString('fieldName', '');
		$fieldId   = $input->getString('fieldId', '');
		$userId    = $input->getInt('userId', 0);

		/** @var RedshopbModelUser $model */
		$model = RedshopbModel::getFrontInstance('User');
		echo $model->getDepartmentList($companyId, $fieldName, $fieldId, $userId);

		$app->close();
	}
}
