<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

jimport('redcore.form.fields.rrules');

/**
 * CompanyPermissions Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldCompanyPermissions extends JFormFieldRrules
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'CompanyPermissions';

	/**
	 * Method to get the field input markup for Access Control Lists.
	 * Optionally can be associated with a specific component and section.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		$assetField = 'asset_id';
		$companyId  = $this->form->getValue('id');

		// Find the asset id of the content (company)
		$assetId                      = $this->form->getValue($assetField);
		$allowAdministratorRoleChange = $this->getAttribute('allowAdministratorRoleChange');

		return RedshopbLayoutHelper::render(
			'acl.assetgroups',
			array(
				'view' => $this,
				'options' => array (
					'company_id' => $companyId,
					'asset_id' => $assetId,
					'section' => $this->getAttribute('section'),
					'input_id' => $this->id,
					'input_name' => $this->name,
					'simple' => $this->getAttribute('simple'),
					'allowAdministratorRoleChange' => $allowAdministratorRoleChange,
				)
			),
			'',
			array('client' => 0)
		);
	}
}
