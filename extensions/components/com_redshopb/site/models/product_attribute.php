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
use Joomla\CMS\Plugin\PluginHelper;
/**
 * Product Attribute Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelProduct_Attribute extends RedshopbModelAdmin
{
	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'product';

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return boolean True on success, False on error.
	 */
	public function save($data)
	{
		/** @var RedshopbTableProduct_Attribute $table */
		$table = $this->getTable();
		$key   = $table->getKeyName();
		$pk    = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		if ($table->load($pk))
		{
			$isNew  = false;
			$oldRow = $table->getProperties();
		}

		if (isset($data['value']))
		{
			$typeId    = RedshopbEntityProduct_Attribute::getInstance($data['product_attribute_id'])->get('type_id');
			$valueType = RedshopbEntityType::getInstance($typeId)->get('value_type');

			$data[$valueType] = $data['value'];
		}

		// Bind the data.
		if (!$table->bind($data))
		{
			$this->setError($table->getError());

			return false;
		}

		// Prepare the row for saving
		$this->prepareTable($table);

		// Check the data.
		if (!$table->check())
		{
			$this->setError($table->getError());

			return false;
		}

		// Include the content plugins for the on save events.
		PluginHelper::importPlugin('content');
		$dispatcher = RFactory::getDispatcher();

		// Trigger the onContentBeforeSave event.
		$result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, $table, $isNew));

		if (in_array(false, $result, true))
		{
			$this->setError($table->getError());

			return false;
		}

		// Store the data.
		if (!$table->store())
		{
			$this->setError($table->getError());

			return false;
		}

		// Trigger the onContentAfterSave event.
		$dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, $table, $isNew));

		// Conversion sets process
		if (!$table->conversion_sets)
		{
			RedshopbHelperConversion::removeProductAttributeConversionSet($table->id);
		}

		if (!$isNew
			&& !empty($oldRow)
			&& ($oldRow['state'] != $table->state || $oldRow['ordering'] != $table->ordering))
		{
			$table->reorder($this->getReorderConditions($table));
		}

		$this->setState($this->getName() . '.id', $table->id);
		$this->setState($this->getName() . '.new', $isNew);

		// Image loading and thumbnail creation from web service file
		if (!$this->saveImageFile($table, $data, 'product_attribute'))
		{
			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   RedshopbTableProduct_Attribute  $table  A record object.
	 *
	 * @return  array  An array of conditions to add to add to ordering queries.
	 */
	protected function getReorderConditions($table)
	{
		$condition   = array();
		$condition[] = 'product_id = ' . (int) $table->product_id;
		$condition[] = 'state >= 0';

		return $condition;
	}

	/**
	 * unpublish a product attribute
	 *
	 * @param   integer  $id  The product attribute id
	 *
	 * @return  integer  product id
	 */
	public function unpublish($id)
	{
		/** @var RedshopbTableProduct_Attribute $table */
		$table = $this->getTable();

		if (!$table->load($id))
		{
			return false;
		}

		$table->id    = $id;
		$table->state = 0;

		if (!$table->store())
		{
			return false;
		}

		return true;
	}

	/**
	 * reordering a product attribute
	 *
	 * @param   integer  $id        The product attribute id
	 * @param   integer  $ordering  The product id
	 *
	 * @return  integer  product id
	 */
	public function webserviceReorder($id, $ordering)
	{
		$this->operationWS = true;

		/** @var RedshopbTableProduct_Attribute $table */
		$table = $this->getTable();

		if (!$table->load($id))
		{
			return false;
		}

		$table->id       = $id;
		$table->ordering = $ordering;

		if (!$table->store())
		{
			return false;
		}

		return true;
	}

	/**
	 * translation insert of a product attribute element
	 *
	 * @param   integer  $productAttributeId    product attribute id
	 * @param   string   $langCode              language code
	 * @param   string   $name                  name
	 * @param   string   $alias                 alias
	 *
	 * @return  integer  $productAttributeId
	 */
	public function translateLegacy($productAttributeId, $langCode, $name, $alias = '')
	{
		/** @var RedshopbTableProduct_Attribute $table */
		$table = $this->getTable();
		$table->load($productAttributeId);
		$translationTables = RTranslationHelper::getInstalledTranslationTables();
		$checkLang         = RedshopbHelperTranslations::checkLanguageAvailable($langCode);

		if (!$checkLang)
		{
			return false;
		}

		// Check existing translate table
		if (!isset($translationTables['#__redshopb_product_attribute']))
		{
			return false;
		}

		$translationTable = $translationTables['#__redshopb_product_attribute'];
		$result           = RedshopbHelperTranslations::storeTranslation(
			$translationTable,
			$table,
			$langCode,
			array(
				'id' => (int) $table->id,
				'name' => (string) $name
			)
		);

		if ($result !== true)
		{
			return false;
		}

		return $productAttributeId;
	}

	/**
	 * translation remove of a product attribute element
	 *
	 * @param   integer  $productAttributeId   product attribute id
	 * @param   string   $langCode             language code
	 *
	 * @return  integer  $productAttributeId
	 */
	public function translateRemoveLegacy($productAttributeId, $langCode)
	{
		$db         = Factory::getDbo();
		$query      = $db->getQuery(true);
		$conditions = array(
			$db->quoteName('id') . ' = ' . $productAttributeId,
			$db->quoteName('rctranslations_language') . ' = ' . $db->quote($langCode)
		);

		$query->delete($db->quoteName('#__redshopb_product_attribute_rctranslations'));
		$query->where($conditions);
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}

		return true;
	}

	/**
	 * Method to get the row form.
	 *
	 * @param   int  $pk  Primary key
	 *
	 * @return	object
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		// Gets the image URL
		if (empty($item->id) || empty($item->image))
		{
			return $item;
		}

		$item->imageurl = $this->getImageUrl($item->image, 'product_attribute');

		return $item;
	}
}
