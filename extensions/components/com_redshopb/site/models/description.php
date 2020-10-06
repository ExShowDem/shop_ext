<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;

/**
 * Product Description Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelDescription extends RedshopbModelAdmin
{
	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'product';

	/**
	 * Get the associated Table
	 *
	 * @param   string  $name    Table name
	 * @param   string  $prefix  Table prefix
	 * @param   array   $config  Configuration array
	 *
	 * @return  Table
	 */
	public function getTable($name = '', $prefix = '', $config = array())
	{
		$name = empty($name) ? 'Product_Description' : $name;

		return parent::getTable($name, $prefix, $config);
	}

	/**
	 * Method for translate an product description
	 *
	 * @param   int     $productDescriptionId  ID of product description
	 * @param   string  $languageCode          Code of language
	 * @param   string  $descriptionIntro      Translation text of intro for description
	 * @param   string  $description           Translation text for description
	 *
	 * @return  integer                        Product description id
	 */
	public function translateLegacy($productDescriptionId, $languageCode, $descriptionIntro, $description)
	{
		$checkLang = RedshopbHelperTranslations::checkLanguageAvailable($languageCode);

		if (!$checkLang)
		{
			return false;
		}

		$translationTables = RTranslationHelper::getInstalledTranslationTables();

		$table = $this->getTable();

		if (!$table->load($productDescriptionId))
		{
			return false;
		}

		// Check existing translate table
		if (!isset($translationTables['#__redshopb_product_descriptions']))
		{
			return false;
		}

		$translationTable = $translationTables['#__redshopb_product_descriptions'];

		$result = RedshopbHelperTranslations::storeTranslation(
			$translationTable,
			$table,
			$languageCode,
			array (
				'id'                => (int) $table->id,
				'description_intro' => (string) $descriptionIntro,
				'description'       => (string) $description
			)
		);

		if ($result !== true)
		{
			return false;
		}
		else
		{
			return $productDescriptionId;
		}
	}

	/**
	 * Method for remove translation of an product description
	 *
	 * @param   int     $productDescriptionId  Product description id
	 * @param   string  $languageCode          Language code
	 *
	 * @return  integer                        Product description Id on success.
	 */
	public function translateRemoveLegacy($productDescriptionId, $languageCode)
	{
		$db = Factory::getDbo();

		$conditions = array(
			$db->qn('id') . ' = ' . $productDescriptionId,
			$db->qn('rctranslations_language') . ' = ' . $db->quote($languageCode)
		);

		$query = $db->getQuery(true)
			->delete($db->quoteName('#__redshopb_product_descriptions_rctranslations'))
			->where($conditions);
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}

		return $productDescriptionId;
	}
}
