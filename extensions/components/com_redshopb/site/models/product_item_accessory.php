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
/**
 * Product Data Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelProduct_Item_Accessory extends RedshopbModelAdmin
{
	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'product';
	/**
	 * unpublish a product item accessory element
	 *
	 * @param   integer  $id  The product item accessory id
	 *
	 * @return  boolean True on success. False otherwise.
	 */
	public function unpublish($id)
	{
		$table = $this->getTable();
		$table->load($id);
		$table->id    = $id;
		$table->state = 0;

		if (!$table->store())
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Setprice for a product item accessory element
	 *
	 * @param   integer  $id     The product item accessory id
	 * @param   integer  $price  The price
	 *
	 * @return  boolean True on success. False otherwise.
	 */
	public function setPrice($id, $price)
	{
		// This method is only used in Webservice so we will set it like that
		$this->operationWS = true;

		$table = $this->getTable();
		$table->load($id);
		$table->id    = $id;
		$table->price = $price;

		if (!$table->store())
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * transaltion insert of a product item accessory element
	 *
	 * @param   integer  $prodItemAccId  product item accessory id
	 * @param   string   $langCode       language code
	 * @param   string   $desc           Description
	 *
	 * @return  boolean True on success. False otherwise.
	 */
	public function translateLegacy($prodItemAccId, $langCode, $desc)
	{
		$table = $this->getTable();
		$table->load($prodItemAccId);
		$translationTables = RTranslationHelper::getInstalledTranslationTables();
		$checkLang         = RedshopbHelperTranslations::checkLanguageAvailable($langCode);

		if (!$checkLang)
		{
			return false;
		}

		// Check existing translate table
		if (isset($translationTables['#__redshopb_product_item_accessory']))
		{
			$translationTable = $translationTables['#__redshopb_product_item_accessory'];
			$result           = RedshopbHelperTranslations::storeTranslation(
				$translationTable,
				$table,
				$langCode,
				array(
					'id' => (int) $table->id,
					'description' => $desc
				)
			);

			if ($result !== true)
			{
				return false;
			}
			else
			{
				return true;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * transaltion remove of a product item accessory elementt
	 *
	 * @param   integer  $prodItemAccId  product item accessory id
	 * @param   string   $langCode       language code
	 *
	 * @return  integer  boolean True on success. False otherwise.
	 */
	public function translateRemoveLegacy($prodItemAccId, $langCode)
	{
		$db         = Factory::getDbo();
		$query      = $db->getQuery(true);
		$conditions = array(
			$db->quoteName('id') . ' = ' . $prodItemAccId,
			$db->quoteName('rctranslations_language') . ' = ' . $db->quote($langCode)
		);

		$query->delete($db->quoteName('#__redshopb_product_item_accessory_rctranslations'));
		$query->where($conditions);
		$db->setQuery($query);

		if (!$db->execute())
		{
			return false;
		}
		else
		{
			return true;
		}
	}
}
