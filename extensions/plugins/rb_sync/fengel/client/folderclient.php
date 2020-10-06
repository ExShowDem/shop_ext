<?php
/**
 * @package     Engel
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Engel Soap Client.
 *
 * @package     Engel
 * @subpackage  Client
 * @since       1.0
 */
class FEngelFolderClient
{
	/**
	 * Folder path
	 *
	 * @var  string
	 */
	protected $folder;

	/**
	 * An array of instances.
	 *
	 * @var  EngelClient[]
	 */
	protected static $instance = array();

	/**
	 * Constructor.
	 *
	 * @param   string  $folder  Folder to read files from
	 */
	private function __construct($folder = '')
	{
		$this->folder = $folder;
	}

	/**
	 * Get an instance or create it.
	 *
	 * @param   string  $folder  Folder to read files from
	 *
	 * @return  EngelClient
	 */
	public static function getInstance($folder)
	{
		$hash = md5($folder);

		if (!isset(self::$instance[$hash]))
		{
			self::$instance[$hash] = new static($folder);
		}

		return self::$instance[$hash];
	}

	/**
	 * Reads the specified file name and returns the complete content in an object with two wrappers - for compatibility with the SOAP results
	 *
	 * @param   string  $fileName        File name, relative to the $this->folder folder
	 * @param   string  $objectWrapper   Wrapper of the object to create with the results
	 * @param   string  $objectWrapper2  Second wrapper of the object to create with the results
	 *
	 * @return  object|boolean  The result in a wrapper or FALSE.
	 */
	protected function readXMLFile($fileName, $objectWrapper, $objectWrapper2 = 'any')
	{
		$fullFile = JPATH_SITE . '/media/com_redshopb/ftpsync/' . $this->folder . '/' . $fileName;

		if (file_exists($fullFile))
		{
			$object                                    = new stdClass;
			$object->$objectWrapper                    = new stdClass;
			$object->$objectWrapper->{$objectWrapper2} = file_get_contents($fullFile);

			return $object;
		}

		return false;
	}

	/**
	 * Red Get Item Price.
	 *
	 * @return  object|boolean  The wrapped result or FALSE.
	 */
	public function redGetItemPrice()
	{
		return $this->readXMLFile('Red_GetItemPrice.XML', 'Red_GetItemPriceResult');
	}

	/**
	 * Red Get Item Picture.
	 *
	 * @return  object|boolean  The wrapped result or FALSE.
	 */
	public function redGetItemPicture()
	{
		return $this->readXMLFile('Red_GetItemPicture.XML', 'Red_GetItemPictureResult');
	}

	/**
	 * Red Get Item Variant Data.
	 *
	 * @return  object|boolean  The wrapped result or FALSE.
	 */
	public function redGetItemVariantData()
	{
		return $this->readXMLFile('Red_GetItemVariantData.XML', 'Red_GetItemVariantDataResult');
	}

	/**
	 * Red Get Item Variant Relations.
	 *
	 * @return  object|boolean  The wrapped result or FALSE.
	 */
	public function redGetItemVariantRealations()
	{
		return $this->readXMLFile('Red_GetItemVariantRealations.XML', 'Red_GetItemVariantRealationsResult');
	}

	/**
	 * Red Get Item Variant Type.
	 *
	 * @return  object|boolean  The wrapped result or FALSE.
	 */
	public function redGetItemVariantType()
	{
		return $this->readXMLFile('Red_GetItemVariantType.XML', 'Red_GetItemVariantTypeResult');
	}

	/**
	 * Get colours.
	 *
	 * @return  object|boolean  The wrapped result or FALSE.
	 */
	public function getColours()
	{
		return $this->readXMLFile('getColours.XML', 'GetColoursResult');
	}

	/**
	 * Get styles.
	 *
	 * @return  object|boolean  The wrapped result or FALSE.
	 */
	public function getStyles()
	{
		return false;
	}

	/**
	 * Get the item.
	 *
	 * @return  object|boolean  The wrapped result or FALSE.
	 */
	public function getItem()
	{
		return $this->readXMLFile('GetItem.XML', 'GetItemResult');
	}

	/**
	 * Get item group.
	 *
	 * @return  object|boolean  The wrapped result or FALSE.
	 */
	public function getItemGroup()
	{
		return $this->readXMLFile('GetItemGroup.XML', 'GetItemGroupResult');
	}

	/**
	 * Get Categories.
	 *
	 * @return  object|boolean  The wrapped result or FALSE.
	 */
	public function getCategories()
	{
		return $this->readXMLFile('GetCategories.XML', 'GetCategoriesResult');
	}

	/**
	 * Get sizes.
	 *
	 * @return  object|boolean  The wrapped result or FALSE.
	 */
	public function getSizes()
	{
		return $this->readXMLFile('getSizes.XML', 'GetSizesResult');
	}

	/**
	 * Get the wash care spec.
	 *
	 * @return  object|boolean  The wrapped result or FALSE.
	 */
	public function getWashCareSpec()
	{
		return $this->readXMLFile('GetWashCareSpec.XML', 'GetWashCareSpecResult');
	}

	/**
	 * Get Logos.
	 *
	 * @return  object|boolean  The wrapped result or FALSE.
	 */
	public function getLogos()
	{
		return false;
	}

	/**
	 * Get Composition
	 *
	 * @param   string  $itemNo    Item No
	 * @param   string  $language  Language
	 *
	 * @return  object|boolean  The wrapped result or FALSE.
	 */
	public function getCompositionB2B($itemNo = '', $language = '')
	{
		return $this->readXMLFile('getCompositionB2B.XML', 'GetCompositionB2BResult');
	}
}
