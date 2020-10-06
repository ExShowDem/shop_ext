<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Upgrade
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Custom upgrade of Redshop b2b.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Upgrade
 * @since       1.12.67
 */
class Com_RedshopbUpdateScript_1_12_67
{
	/**
	 * @var   array
	 */
	protected $appliedFields = array();

	/**
	 * @var   array
	 */
	protected $newFields = array();

	/**
	 * @var   array
	 */
	protected $extraFieldsOnSearchPriority = array();

	/**
	 * Performs the upgrade for this version
	 *
	 * @return  boolean
	 *
	 * @since   1.12.67
	 */
	public function execute()
	{
		// Update current config to new config table.
		$params = $this->getParams();

		/** @var RedshopbModelConfig $configModel */
		$configModel = RedshopbModel::getAdminInstance('Config');

		if (!$configModel->save($params))
		{
			return false;
		}

		// Clean up params from #__extensions
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->update($db->qn('#__extensions'))
			->set($db->qn('params') . ' = ' . $db->quote('{}'))
			->where($db->qn('element') . ' = ' . $db->quote('com_redshopb'))
			->where($db->qn('type') . ' = ' . $db->quote('component'))
			->where($db->qn('client_id') . ' = 1');

		$db->setQuery($query)->execute();

		// Remove old config.xml file
		$configFile = JPATH_ROOT . '/administrator/components/com_redshopb/config.xml';

		if (JFile::exists($configFile))
		{
			JFile::delete($configFile);
		}

		return true;
	}

	/**
	 * Method to check if the legacy configuration has already been processed
	 *
	 * @param	object  $params [description]
	 *
	 * @return boolean
	 */
	private function isLegacyConfig($params)
	{
		$params = $params->toArray();

		if (!array_key_exists('product_search_criterias_table', $params))
		{
			return true;
		}

		return false;
	}

	/**
	 * Method to convert legacy params to new param values
	 *
	 * @return array
	 */
	private function getParams()
	{
		$params = ComponentHelper::getParams('com_redshopb');

		if (!$this->isLegacyConfig($params))
		{
			return $params->toArray();
		}

		Factory::getApplication()->enqueueMessage('Converting Legacy Configuration');

		$redshopbConfig = $params;

		$searchCriteriaParams = $params->get('product_search_criterias',
			'exact_category_name,partially_category_name_description,partially_product_title_description_extra,
			exact_product_title,partially_product_description_category_extra,partially_product_description_extra,extra'
		);

		$productSearchCriterias = array_filter(explode(',', $searchCriteriaParams));
		$priority               = 1;

		if (!empty($productSearchCriterias))
		{
			$foundStandAloneExtraAdditional = (boolean) in_array('extra', $productSearchCriterias);

			foreach ($productSearchCriterias as $searchType)
			{
				$countPriorities = count($this->newFields);

				switch ($searchType)
				{
					case 'exact_product_title':
						$this->getFieldArray($priority, 'product_name', 'exact');
						break;
					case 'partially_product_title_description_extra':
						$this->getFieldArray($priority, 'product_name', 'exact_and_partial');
						$this->getFieldArray($priority, 'product_description', 'exact_and_partial');

						break;
					case 'partially_product_description_extra':
						$this->getFieldArray($priority, 'product_description', 'exact_and_partial');

						break;
					case 'partially_product_description_category_extra':
						$this->getFieldArray($priority, 'product_description', 'exact_and_partial');
						$this->getFieldArray($priority, 'category_name', 'exact_and_partial');

						break;
					case 'exact_category_name':
						$this->getFieldArray($priority, 'category_name', 'exact');
						break;
					case 'partially_category_name_description':
						$this->getFieldArray($priority, 'category_name', 'exact_and_partial');
						$this->getFieldArray($priority, 'category_description', 'exact_and_partial');
						break;
					case 'product_sku':
						$this->getFieldArray($priority, 'product_sku', 'exact_and_partial');
						break;
					case 'extra':
						$this->setAdditionalFields($priority);
						break;
				}

				if ($countPriorities != count($this->newFields))
				{
					$priority++;
				}
			}
		}

		$extraFieldsNotInPriority = array_diff($this->getSearchableExtraFields(), $this->extraFieldsOnSearchPriority);

		if (!empty($extraFieldsNotInPriority))
		{
			foreach ($extraFieldsNotInPriority as $item)
			{
				$this->getFieldArray($priority, $item);
			}
		}

		$redshopbConfig->set('product_search_criterias_table', $this->newFields);
		$redshopbConfig->set('product_search_criterias', null);
		$redshopbConfig->set('product_search_additional_fields', null);

		return $redshopbConfig->toArray();
	}

	/**
	 * setAdditionalFields
	 *
	 * @param   int  $priority  Priority
	 *
	 * @return  void
	 *
	 * @since 1.12.32
	 */
	protected function setAdditionalFields($priority)
	{
		$redshopbConfig                = RedshopbApp::getConfig();
		$productSearchAdditionalFields = (array) $redshopbConfig->get('product_search_additional_fields', array());

		if (!empty($productSearchAdditionalFields))
		{
			foreach ($productSearchAdditionalFields as $additionalField)
			{
				switch ($additionalField)
				{
					case 'related_sku':
						$this->getFieldArray($priority, 'related_sku');
						break;
					case 'manufacturer_sku':
						$this->getFieldArray($priority, 'manufacturer_sku');
						break;
					case 'tags':
						$this->getFieldArray($priority, 'tags');
						break;
					case 'image_alt_text':
						$this->getFieldArray($priority, 'image_alt_text');
						break;
					case 'manufacturer_name':
						$this->getFieldArray($priority, 'manufacturer_name');
						break;
					case 'sku':
						$this->getFieldArray($priority, 'product_sku');
						break;
					case is_numeric($additionalField) && in_array($additionalField, $this->getSearchableExtraFields()):
						$this->extraFieldsOnSearchPriority[] = $additionalField;
						$this->getFieldArray($priority, $additionalField);
						break;
				}
			}
		}
	}

	/**
	 * Get Searchable ExtraFields
	 *
	 * @return  array
	 *
	 * @since 1.12.32
	 */
	protected function getSearchableExtraFields()
	{
		static $extraFields = null;

		if (is_null($extraFields))
		{
			$db          = Factory::getDbo();
			$fieldQuery  = $db->getQuery(true)
				->select('f.id')
				->from($db->qn('#__redshopb_field', 'f'))
				->where('f.scope = ' . $db->q('product'))
				->where('f.searchable_frontend = 1');
			$extraFields = $db->setQuery($fieldQuery)
				->loadColumn();

			if (!$extraFields)
			{
				$extraFields = array();
			}
		}

		return $extraFields;
	}

	/**
	 * getFieldArray
	 *
	 * @param   int     $priority   Priority
	 * @param   string  $fieldName  Field name
	 * @param   string  $method     Method
	 *
	 * @return void
	 *
	 * @since 1.12.32
	 */
	protected function getFieldArray($priority, $fieldName, $method = '-1')
	{
		if (array_key_exists($fieldName, $this->appliedFields))
		{
			if ($this->appliedFields[$fieldName] == 'exact')
			{
				if ($method == 'exact')
				{
					return;
				}

				foreach ($this->newFields as $newPriority => $fields)
				{
					if (array_key_exists($fieldName, $fields))
					{
						$priority = $newPriority;
						break;
					}
				}
			}
			else
			{
				return;
			}
		}

		if (!array_key_exists($priority, $this->newFields))
		{
			$this->newFields[$priority] = array();
		}

		$this->appliedFields[$fieldName]        = $method;
		$this->newFields[$priority][$fieldName] = array(
			'name' => $fieldName,
			'synonym' => '-1',
			'stem' => '-1',
			'method' => $method
		);
	}
}
