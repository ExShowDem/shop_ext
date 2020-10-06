<?php
/**
 * @package     Aesir.E-Commerce.Plugin.Custom_Field_Ean
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Language\Text;

/**
 * Aesir E-Commerce - EAN Custom Field
 *
 * @since       1.0.0
 */
class PlgVanirCustom_Field_Ean extends CMSPlugin
{
	/**
	 * Auto load language
	 *
	 * @var boolean
	 */
	protected $autoloadLanguage = true;

	/**
	 * Returns the Field entity
	 *
	 * @return   RedshopbEntityField
	 */
	protected function getField()
	{
		return RedshopbEntityField::getInstance($this->params->get('customFieldEan'));
	}

	/**
	 * Adds the EAN custom field to the registration form
	 *
	 * @param   Form   $form          Registration form
	 *
	 * @return   false|null
	 */
	public function onVanirPrepareRegistrationForm(Form $form)
	{
		$doc = Factory::getDocument();

		$js = file_get_contents('plugins/vanir/custom_field_ean/js/ean_toggle.js');

		$doc->addScriptDeclaration($js);

		$append = $this->appendRadioButton($form);

		if (false === $append)
		{
			return false;
		}

		$this->addCustomField($form);
	}

	/**
	 * Adds the EAN field to the registration form
	 *
	 * @param   JForm   $form   Registration form
	 *
	 * @return  void
	 */
	protected function addCustomField(JForm $form)
	{
		$field = $this->getField();
		$item  = $field->getItem();

		$field = new SimpleXMLElement(
			'<field name="ean" label="' . $item->title . '" type="text" />'
		);

		if (1 == $item->required)
		{
			$field->addAttribute('required', 'true');
		}

		$form->setField($field);
	}

	/**
	 * Output HTML for the EAN field
	 *
	 * @param   JForm         $form    Registration form
	 * @param   string|null   $field   Either empty or an HTML text string
	 *
	 * @return  void
	 */
	public function AECRegisterPrintFieldAfterVAT(JForm $form, &$field)
	{
		$eanField = "<div class=\"control-group custom-field-ean\">
			<div class=\"control-label\">
				{$form->getLabel('ean')}
			</div>
			<div class=\"controls\">
				{$form->getInput('ean')}
			</div>
		</div>";

		isset($field)
			? $field .= $eanField
			: $field  = $eanField;
	}

	/**
	 * Outputs the EAN number
	 *
	 * @param   RedshopbEntityCompany    $company   Used to get the EAN
	 * @param   mixed                    $output    Empty variable that we replace with the EAN number
	 *
	 * @return  void
	 */
	public function AECPrintEANOnOrder($company, &$output)
	{
		$field     = $this->getField();
		$keys      = array('field_id', 'item_id');
		$values    = array($field->getId(), $company->getId());
		$fieldData = RedshopbEntityField_Data::getInstanceByField($keys, $values);

		if ($fieldData->isLoaded())
		{
			$output = $fieldData->getItem()->string_value;
		}
	}

	/**
	 * Adds a EAN option to the registration type radio button
	 *
	 * @param   JForm   $form   Registration form
	 *
	 * @return   false|null
	 */
	protected function appendRadioButton(JForm $form)
	{
		$field = $form->getFieldXml('register_type');

		if (false === $field)
		{
			return false;
		}

		$child = $field->addChild('option', Text::_('PLG_VANIR_CUSTOM_FIELD_EAN_RADIO_BUTTON_TITLE'));
		$child->addAttribute('value', 'ean');

		$form->setField($field);
	}

	/**
	 * Saves the custom field data
	 *
	 * @param   integer   $companyId   Company we're saving the EAN to
	 * @param   string    $ean         EAN number entered in the registration form
	 *
	 * @return   void
	 */
	protected function saveCustomFieldData($companyId, $ean)
	{
		$field     = $this->getField();
		$fieldData = RedshopbEntityField_Data::getInstance();

		$item = array(
			'field_id'     => $field->getId(),
			'item_id'      => $companyId,
			'string_value' => $ean,
		);

		$fieldData->save($item);
	}

	/**
	 * Saves the ean number if the registration type is set to `ean`
	 *
	 * @param   integer   $companyId   Company being saved
	 * @param   array     $data        Data being saved by {@see RedshopbModelB2BUserRegister}
	 *
	 * @return   void
	 */
	public function onAECB2BUserRegisterAfterCompanyRegister(&$companyId, &$data)
	{
		$jform = Factory::getApplication()->input->get('jform', array(), 'array');

		if ('business' === $data['register_type'] && array_key_exists('ean', $jform) && !empty($jform['ean']))
		{
			$this->saveCustomFieldData($companyId, $jform['ean']);
			$this->setPriceGroup($companyId);
		}
	}

	/**
	 * Set the price group for the company
	 *
	 * @param   integer   $companyId   ID of the company that was just registered
	 *
	 * @return  null
	 */
	protected function setPriceGroup($companyId)
	{
		if (empty($this->params->get('priceGroup')))
		{
			return null;
		}

		$companyPriceGroups = $this->getCompanyPriceGroups($companyId);

		if (null === $companyPriceGroups)
		{
			return null;
		}

		$this->removeDefaultPriceGroups($companyPriceGroups, $companyId);

		$this->setNewDefaultPriceGroups($companyId);
	}

	/**
	 * Set the new price groups defined in the plugin
	 *
	 * @param   integer   $companyId   Company to set the price groups for
	 *
	 * @return  void
	 */
	protected function setNewDefaultPriceGroups($companyId)
	{
		/** @var   RedshopbTableCompany   $table */
		$table = RedshopbTable::getAutoInstance('Company');

		$table->load($companyId);

		$table->set('price_group_ids', $this->params->get('priceGroup'));

		$table->store();
	}

	/**
	 * Removes the old default price groups so we can set our own default
	 *
	 * @param   array     $priceGroups   List of price groups to remove
	 * @param   integer   $companyId     Company to remove them from
	 *
	 * @return   void
	 */
	protected function removeDefaultPriceGroups($priceGroups, $companyId)
	{
		/** @var   RedshopbTableCustomer_Price_Group_Xref   $table */
		$table = RedshopbTable::getAutoInstance('Customer_Price_Group_Xref');

		foreach ($priceGroups as $group)
		{
			$table->load(array('customer_id' => $companyId, 'price_group_id' => $group), true);
			$table->delete();
		}
	}

	/**
	 * Get price groups from a company
	 *
	 * @param   integer   $companyId   Company to get price groups for
	 *
	 * @return   array|null
	 */
	protected function getCompanyPriceGroups($companyId)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);

		$query->select($db->qn('price_group_id'))
			->from($db->qn('#__redshopb_customer_price_group_xref'))
			->where("{$db->qn('customer_id')} = {$db->q($companyId)}");

		return $db->setQuery($query)->loadAssocList('', 'price_group_id');
	}
}
