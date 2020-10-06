<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;

require_once __DIR__ . '/base.php';

/**
 * Get Sizes function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelGetSizes extends FengelFunctionBase
{
	/**
	 * Read and store the data.
	 *
	 * @param   RTable     $webserviceData   Webservice object
	 * @param   Registry   $params           Parameters of the plugin
	 *
	 * @return  boolean
	 *
	 * @throws  Exception
	 */
	public function read(&$webserviceData, $params)
	{
		$db = Factory::getDbo();

		try
		{
			$translationTables = RTranslationHelper::getInstalledTranslationTables();

			// Check existing translate table
			if (isset($translationTables['#__redshopb_product_attribute_value']))
			{
				$translationTable = $translationTables['#__redshopb_product_attribute_value'];
				$xml              = $this->client->getSizes();

				if (!is_object($xml))
				{
					throw new Exception(Text::_('PLG_RB_SYNC_FENGEL_FAILED_TO_FETCH_ITEMS'));
				}

				$db->transactionStart();

				$query = $db->getQuery(true)
					->select(array('serialize', 'remote_key'))
					->from($db->qn('#__redshopb_sync'))
					->where('reference = ' . $db->q('fengel.product'));
				$db->setQuery($query);
				$sizeTables = array();

				$products = $db->loadObjectList('remote_key');

				if ($products)
				{
					foreach ($products as $product)
					{
						$unSerialize = RedshopbHelperSync::mbUnserialize($product->serialize);

						if (isset($unSerialize['Size']))
						{
							$sizeTables[$unSerialize['Size']][] = $product->remote_key;
						}
					}
				}

				$lang  = RTranslationHelper::getSiteLanguage();
				$table = RTable::getInstance('Product_Attribute_Value', 'RedshopbTable')
					->setOption('forceWebserviceUpdate', true)
					->setOption('lockingMethod', 'Sync');

				foreach ($xml->Size as $obj)
				{
					if (!isset($sizeTables[(string) $obj->TableNo]))
					{
						continue;
					}

					if (isset($obj->Translations->Translation) && count($obj->Translations->Translation) > 0)
					{
						foreach ($sizeTables[(string) $obj->TableNo] as $productNo)
						{
							$id = $this->findSyncedId('fengel.attribute', 'Str._' . (string) $obj->Code, $productNo);

							if ($id)
							{
								if ($table->load($id))
								{
									foreach ($obj->Translations->Translation as $language)
									{
										if ((string) $language->LanguageCode != '')
										{
											$langCode = (string) $language->LanguageCode;

											$result = $this->storeTranslation(
												$translationTable,
												$table,
												$langCode,
												array(
													'id' => $table->id,
													'string_value' => (string) $language->Description
												)
											);

											if ($result !== true)
											{
												throw new Exception($result);
											}
										}
										elseif ((string) $language->LanguageCode == strtoupper($lang) && (string) $language->LanguageCode != '')
										{
											if (!$table->save(array('string_value' => (string) $language->Description)))
											{
												throw new Exception($table->getError());
											}
										}
									}
								}
							}
						}
					}

					foreach ($sizeTables[(string) $obj->TableNo] as $productNo)
					{
						$id = $this->findSyncedId('fengel.attribute', 'Str._' . (string) $obj->Code, $productNo);

						if ($id)
						{
							if ($table->load($id))
							{
								$result = $this->deleteNotSyncingLanguages($translationTable, $table);

								if ($result !== true)
								{
									throw new Exception($result);
								}
							}
						}
					}
				}

				$db->transactionCommit();
			}
		}
		catch (Exception $e)
		{
			$db->transactionRollback();
			RedshopbHelperSync::addMessage($e->getMessage(), 'error');

			return false;
		}

		RedshopbHelperSync::addMessage(Text::_('PLG_RB_SYNC_FENGEL_SYNCHRONIZE_SUCCESS'), 'success');

		return true;
	}
}
