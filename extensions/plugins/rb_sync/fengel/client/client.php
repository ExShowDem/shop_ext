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
class FEngelClient
{
	const LIVE_URL = 'http://195.215.70.63/nib/nib.asmx?wsdl';
	const TEST_URL = 'http://195.215.70.62/nib/nib.asmx?wsdl';

	/**
	 * The wsdl url.
	 *
	 * @var  string
	 */
	protected $url;

	/**
	 * The soap or folder client.
	 *
	 * @var  SoapClient/EngelFolderClient
	 */
	protected $client;

	/**
	 * An array of instances.
	 *
	 * @var  FEngelClient[]
	 */
	protected static $instance = array();

	/**
	 * Send parameters to the functions or not (just for SOAP)
	 *
	 * @var  string
	 */
	public $sendParameters = true;

	/**
	 * Constructor.
	 *
	 * @param   string  $source   Source for the current client
	 * @param   string  $url      WSDL URL of the current webservice when source = 'wsdl'
	 * @param   string  $folder   Folder to read files from when source = 'folder'
	 * @param   array   $options  The options for the soap client
	 */
	private function __construct($source = 'wsdl', $url = '', $folder = '', array $options = array())
	{
		// This is needed to avoid some issues when the server is hanging
		ini_set('default_socket_timeout', 60);

		switch ($source)
		{
			case 'wsdl':
				$this->client = new SoapClient(
					$url,
					$options
				);
				break;

			case 'folder':
				require_once __DIR__ . '/folderclient.php';
				$this->client         = FEngelFolderClient::getInstance($folder);
				$this->sendParameters = false;
				break;
		}
	}

	/**
	 * Get an instance or create it.
	 *
	 * @param   string  $source   Source for the current client
	 * @param   string  $url      WSDL URL of the current webservice when source = 'wsdl'
	 * @param   string  $folder   Folder to read files from when source = 'folder'
	 * @param   array   $options  The options for the soap client
	 *
	 * @return  FEngelClient
	 */
	public static function getInstance($source, $url, $folder, array $options = array())
	{
		$hash = md5($source . '-' . $url . '-' . $folder . '-' . serialize($options));

		if (!isset(self::$instance[$hash]))
		{
			self::$instance[$hash] = new static($source, $url, $folder, $options);
		}

		return self::$instance[$hash];
	}

	/**
	 * Get the agent list.
	 *
	 * @param   string  $userId  User Id
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getAgentList($userId)
	{
		$object         = new stdClass;
		$object->UserID = (string) $userId;

		$result = $this->client->GetAgentList($object);

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetAgentListResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Red Get Item Variant Threshold
	 *
	 * @return boolean|SimpleXMLElement
	 */
	public function redGetItemVariantTreshold()
	{
		$result = $this->client->Red_GetItemVariantTreshold();

		if (is_object($result))
		{
			return new SimpleXMLElement($result->Red_GetItemVariantTresholdResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Red Get Item Price.
	 *
	 * @param   string  $itemNo  Item No
	 * @param   string  $type    Type
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function redGetItemPrice($itemNo, $type)
	{
		$object         = new stdClass;
		$object->ItemNo = (string) $itemNo;
		$object->Type   = (string) $type;

		if ($this->sendParameters)
		{
			$result = $this->client->Red_GetItemPrice($object);
		}
		else
		{
			$result = $this->client->Red_GetItemPrice();
		}

		if (is_object($result))
		{
			return new SimpleXMLElement($result->Red_GetItemPriceResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Red Get Item Discount.
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function redGetItemDiscount()
	{
		$result = $this->client->Red_GetItemDiscount();

		if (is_object($result))
		{
			return new SimpleXMLElement($result->Red_GetItemDiscountResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Red Get Customer Discount Group.
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function redGetCustomerDiscGroup()
	{
		$result = $this->client->Red_GetCustomerDiscGroup();

		if (is_object($result))
		{
			return new SimpleXMLElement($result->Red_GetCustomerDiscGroupResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Red Get Customer Discount Group Conn.
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function redGetCustomerDiscGroupConn()
	{
		$result = $this->client->Red_GetCustomerDiscGroupConn();

		if (is_object($result))
		{
			return new SimpleXMLElement($result->Red_GetCustomerDiscGroupConnResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Red Get Customer Price Group.
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function redGetCustomerPriceGroup()
	{
		$result = $this->client->Red_GetCustomerPriceGroup();

		if (is_object($result))
		{
			return new SimpleXMLElement($result->Red_GetCustomerPriceGroupResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Red Get Customer Price Group Conn.
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function redGetCustomerPriceGroupConn()
	{
		$result = $this->client->Red_GetCustomerPriceGroupConn();

		if (is_object($result))
		{
			return new SimpleXMLElement($result->Red_GetCustomerPriceGroupConnResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Red Get Item Discount Group.
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function redGetItemDiscGroup()
	{
		$result = $this->client->Red_GetItemDiscGroup();

		if (is_object($result))
		{
			return new SimpleXMLElement($result->Red_GetItemDiscGroupResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Red Get Item Discount Group Conn.
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function redGetItemDiscGroupConn()
	{
		$result = $this->client->Red_GetItemDiscGroupConn();

		if (is_object($result))
		{
			return new SimpleXMLElement($result->Red_GetItemDiscGroupConnResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Red Get Item Picture.
	 *
	 * @param   string  $type    Type
	 * @param   string  $itemNo  Item No
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function redGetItemPicture($type = '', $itemNo = '')
	{
		$object         = new stdClass;
		$object->Type   = (string) $type;
		$object->ItemNo = (string) $itemNo;

		if ($this->sendParameters)
		{
			$result = $this->client->Red_GetItemPicture($object);
		}
		else
		{
			$result = $this->client->Red_GetItemPicture();
		}

		if (is_object($result))
		{
			return new SimpleXMLElement($result->Red_GetItemPictureResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Red Get Item Variant Data.
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function redGetItemVariantData()
	{
		$result = $this->client->Red_GetItemVariantData();

		if (is_object($result))
		{
			return new SimpleXMLElement($result->Red_GetItemVariantDataResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Red Get Item Variant Relations.
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function redGetItemVariantRealations()
	{
		$result = $this->client->Red_GetItemVariantRealations();

		if (is_object($result))
		{
			return new SimpleXMLElement($result->Red_GetItemVariantRealationsResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Red Get Item Variant Type.
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function redGetItemVariantType()
	{
		$result = $this->client->Red_GetItemVariantType();

		if (is_object($result))
		{
			return new SimpleXMLElement($result->Red_GetItemVariantTypeResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Red Get Item Variant Type.
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function redGetItemVariantStock()
	{
		$result = $this->client->Red_GetItemVariantStock();

		if (is_object($result))
		{
			return new SimpleXMLElement($result->Red_GetItemVariantStockResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get colours.
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getColours()
	{
		$result = $this->client->GetColours();

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetColoursResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get the countries list.
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getCountry()
	{
		$result = $this->client->GetCountry();

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetCountryResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get the currencies list.
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getCurrency()
	{
		$result = $this->client->GetCurrency();

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetCurrencyResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get the agent list.
	 *
	 * @param   string  $customerNo  Customer Number
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getCustomer($customerNo)
	{
		$object             = new stdClass;
		$object->CustomerNo = (string) $customerNo;

		$result = $this->client->GetCustomer($object);

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetCustomerResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get the department data.
	 *
	 * @param   string  $customerNo     Customer number
	 * @param   string  $endCustomerNo  End customer number
	 * @param   string  $no             Department number
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getDepartment($customerNo, $endCustomerNo, $no)
	{
		$object                = new stdClass;
		$object->CustomerNo    = (string) $customerNo;
		$object->EndCustomerNo = (string) $endCustomerNo;
		$object->No            = (string) $no;

		$result = $this->client->GetDepartment($object);

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetDepartmentResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get the employee data.
	 *
	 * @param   string  $customerNo     Customer number
	 * @param   string  $endCustomerNo  End customer number
	 * @param   string  $no             Employee number
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getEmployee($customerNo, $endCustomerNo, $no)
	{
		$object                = new stdClass;
		$object->CustomerNo    = (string) $customerNo;
		$object->EndCustomerNo = (string) $endCustomerNo;
		$object->No            = (string) $no;

		$result = $this->client->GetEmployee($object);

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetEmployeeResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get the end customer data.
	 *
	 * @param   string  $customerNo  Customer number
	 * @param   string  $no          Employee number
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getEndCustomer($customerNo, $no)
	{
		$object             = new stdClass;
		$object->CustomerNo = (string) $customerNo;
		$object->No         = (string) $no;

		$result = $this->client->GetEndCustomer($object);

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetEndCustomerResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Set the end customer data.
	 *
	 * @param   string  $xml  set end customer
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function setEndCustomer($xml)
	{
		$object                    = new stdClass;
		$object->strXMLEndCustomer = (string) $xml;
		$result                    = $this->client->SetEndCustomer($object);

		if (is_object($result))
		{
			return new SimpleXMLElement($result->SetEndCustomerResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Set the department data.
	 *
	 * @param   string  $xml  set department
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function setDepartment($xml)
	{
		$object                   = new stdClass;
		$object->strXMLDepartment = (string) $xml;
		$result                   = $this->client->SetDepartment($object);

		if (is_object($result))
		{
			return new SimpleXMLElement($result->SetDepartmentResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Set the wardrobe data.
	 *
	 * @param   string  $xml  set wardrobe
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function setWardrobe($xml)
	{
		$object                 = new stdClass;
		$object->strXMLWardrobe = (string) $xml;
		$result                 = $this->client->SetWardrobe($object);

		if (is_object($result))
		{
			return new SimpleXMLElement($result->SetWardrobeResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Set the wardrobeLink data.
	 *
	 * @param   string  $xml  set wardrobeLink
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function setWardrobeLink($xml)
	{
		$object                     = new stdClass;
		$object->strXMLWardrobeLink = (string) $xml;
		$result                     = $this->client->SetWardrobeLink($object);

		if (is_object($result))
		{
			return new SimpleXMLElement($result->SetWardrobeLinkResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Set the item group data.
	 *
	 * @param   string  $xml  set item group
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function setItemGroup($xml)
	{
		$object                     = new stdClass;
		$object->strXMLSetItemGroup = (string) $xml;
		$result                     = $this->client->SetItemGroup($object);

		if (is_object($result))
		{
			return new SimpleXMLElement($result->SetItemGroupResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Set User.
	 *
	 * @param   string  $xml  set user
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function setUser($xml)
	{
		$object             = new stdClass;
		$object->strXMLUser = (string) $xml;
		$result             = $this->client->SetUser($object);

		if (is_object($result))
		{
			return new SimpleXMLElement($result->SetUserResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Set Ship to Address.
	 *
	 * @param   string  $xml  set user
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function setShipToAddress($xml)
	{
		$object                         = new stdClass;
		$object->strXMLSetShiptoAddress = (string) $xml;
		$result                         = $this->client->SetShiptoAddress($object);

		if (is_object($result))
		{
			return new SimpleXMLElement($result->SetShiptoAddressResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get the fee setup.
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getFeeSetup()
	{
		$result = $this->client->GetFeeSetup();

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetFeeSetupResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get the item.
	 *
	 * @param   string  $type    Type
	 * @param   string  $itemNo  Item number
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getItem($type, $itemNo)
	{
		$object         = new stdClass;
		$object->Type   = (string) $type;
		$object->ItemNo = (string) $itemNo;

		if ($this->sendParameters)
		{
			$result = $this->client->GetItem($object);
		}
		else
		{
			$result = $this->client->GetItem();
		}

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetItemResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get item changed.
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getItemChanged()
	{
		$result = $this->client->GetItemChanged();

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetItemChangedResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get the item disp.
	 *
	 * @param   string  $itemNo   Item number
	 * @param   string  $colour   Colour number
	 * @param   string  $size     Size
	 * @param   string  $style    Style number
	 * @param   string  $quality  Quality
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getItemDisp($itemNo, $colour, $size, $style, $quality)
	{
		$object          = new stdClass;
		$object->ItemNo  = (string) $itemNo;
		$object->Colour  = (string) $colour;
		$object->Size    = (string) $size;
		$object->Style   = (string) $style;
		$object->Quality = (string) $quality;

		$result = $this->client->GetItemDisp($object);

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetItemDispResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get item group.
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getItemGroup()
	{
		$result = $this->client->GetItemGroup();

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetItemGroupResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get Categories.
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getCategories()
	{
		$result = $this->client->GetCategories();

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetCategoriesResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get item price.
	 *
	 * @param   string  $itemNo  Item number
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getItemPrice($itemNo)
	{
		$object         = new stdClass;
		$object->ItemNo = (string) $itemNo;

		$result = $this->client->GetItemPrice($object);

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetItemPriceResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get item price.
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getItemStatus()
	{
		$result = $this->client->GetItemStatus();

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetItemStatusResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get item variant sort.
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getItemVariantSort()
	{
		$result = $this->client->GetItemVariantSort();

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetItemVariantSortResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get item variants.
	 *
	 * @param   string  $type    Type
	 * @param   string  $itemNo  Item number
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getItemVariants($type, $itemNo)
	{
		$object         = new stdClass;
		$object->Type   = (string) $type;
		$object->ItemNo = (string) $itemNo;

		$result = $this->client->GetItemVariants($object);

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetItemVariantsResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get item web group.
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getItemWebGroup()
	{
		$result = $this->client->GetItemWebGroup();

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetItemWebGroupResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get the permission.
	 *
	 * @param   string  $id  Permission id
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getPermission($id)
	{
		$object     = new stdClass;
		$object->ID = (string) $id;

		$result = $this->client->GetPermission($object);

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetPermissionResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get the retailer agent.
	 *
	 * @param   string  $customerNo  Customer number
	 * @param   string  $type        Type
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getRetailerAgent($customerNo, $type)
	{
		$object             = new stdClass;
		$object->CustomerNo = (string) $customerNo;
		$object->Type       = (string) $type;

		$result = $this->client->GetRetailerAgent($object);

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetRetailerAgentResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get the sales order.
	 *
	 * @param   string  $no  Sales order number
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getSalesOrder($no)
	{
		$object     = new stdClass;
		$object->No = (string) $no;

		$result = $this->client->GetSalesOrder($object);

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetSalesOrderResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get the ship to address.
	 *
	 * @param   string  $type                  Address Type
	 * @param   string  $customerNo            Customer number
	 * @param   string  $endCustomerNo         End customer number
	 * @param   string  $departmentEmployeeNo  Employee or department number, depending on type
	 * @param   string  $code                  Address code
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getShiptoAddress($type, $customerNo, $endCustomerNo, $departmentEmployeeNo, $code)
	{
		$object                       = new stdClass;
		$object->Type                 = (string) $type;
		$object->CustomerNo           = (string) $customerNo;
		$object->EndCustomerNo        = (string) $endCustomerNo;
		$object->DepartmentEmployeeNo = (string) $departmentEmployeeNo;
		$object->Code                 = (string) $code;

		$result = $this->client->GetShiptoAddress($object);

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetShiptoAddressResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get sizes.
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getSizes()
	{
		$result = $this->client->GetSizes();

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetSizesResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get styles.
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getStyles()
	{
		$result = $this->client->GetStyles();

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetStylesResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get the user.
	 *
	 * @param   string  $id        Find to id
	 * @param   string  $username  Find to username
	 * @param   string  $email     Find to e-mail
	 * @param   string  $dateTime  Last synced Date/Time
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getUser($id = '', $username = '', $email = '', $dateTime = '')
	{
		$object            = new stdClass;
		$object->ID        = (string) $id;
		$object->UserName  = (string) $username;
		$object->Email     = (string) $email;
		$object->Date_Time = (string) $dateTime;

		$result = $this->client->GetUser($object);

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetUserResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get the user permission.
	 *
	 * @param   string  $userId        User id
	 * @param   string  $permissionId  Permission id
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getUserPermission($userId, $permissionId)
	{
		$object               = new stdClass;
		$object->UserID       = (string) $userId;
		$object->PermissionID = (string) $permissionId;

		$result = $this->client->GetUserPermission($object);

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetUserPermissionResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get the vat.
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getVATPct()
	{
		$result = $this->client->GetVATPct();

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetVATPctResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get the version.
	 *
	 * @return  string|boolean  The result or FALSE.
	 */
	public function getVersion()
	{
		$result = $this->client->GetVersion();

		if (is_object($result))
		{
			return $result->GetVersionResult;
		}

		return false;
	}

	/**
	 * Get the wardrobe.
	 *
	 * @param   string  $customerNo     Customer number
	 * @param   string  $endCustomerNo  End customer number
	 * @param   string  $departmentNo   Department number
	 * @param   string  $wardrobeNo     Wardrobe number
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getWardrobe($customerNo, $endCustomerNo, $departmentNo, $wardrobeNo)
	{
		$object                = new stdClass;
		$object->CustomerNo    = (string) $customerNo;
		$object->EndCustomerNo = (string) $endCustomerNo;
		$object->DepartmentNo  = (string) $departmentNo;
		$object->WardrobeNo    = (string) $wardrobeNo;

		$result = $this->client->GetWardrobe($object);

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetWardrobeResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get the wardrobe link.
	 *
	 * @param   string  $customerNo     Customer number
	 * @param   string  $endCustomerNo  End customer number
	 * @param   string  $departmentNo   Department number
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getWardrobeLink($customerNo, $endCustomerNo, $departmentNo)
	{
		$object                = new stdClass;
		$object->CustomerNo    = (string) $customerNo;
		$object->EndCustomerNo = (string) $endCustomerNo;
		$object->DepartmentNo  = (string) $departmentNo;

		$result = $this->client->GetWardrobeLink($object);

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetWardrobeLinkResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get the wash care spec.
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getWashCareSpec()
	{
		$result = $this->client->GetWashCareSpec();

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetWashCareSpecResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Set sales order data.
	 *
	 * @param   string  $xml  set sales order
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function setSalesOrder($xml)
	{
		$object                      = new stdClass;
		$object->strXMLSetSalesOrder = (string) $xml;
		$result                      = $this->client->SetSalesOrder($object);

		if (is_object($result))
		{
			return new SimpleXMLElement($result->SetSalesOrderResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get Sales Persons
	 *
	 * @param   string  $id        Id sales person
	 * @param   string  $dateTime  Date/time last synchronization
	 *
	 * @return boolean|SimpleXMLElement
	 */
	public function getSalesPerson($id = '', $dateTime = '')
	{
		$object            = new stdClass;
		$object->ID        = (string) $id;
		$object->Date_Time = (string) $dateTime;
		$result            = $this->client->GetSalesPerson($object);

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetSalesPersonResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get Red Item Detail
	 *
	 * @param   string  $itemNo     Item No
	 * @param   string  $colorCode  Color Code
	 * @param   string  $userName   User Name
	 * @param   string  $password   Password
	 *
	 * @return boolean|array
	 */
	public function getRedItemDetail($itemNo = '', $colorCode = '', $userName = '', $password = '')
	{
		$result = $this->client->GetRedItemDetail($itemNo, $colorCode, $userName, $password);

		if (is_array($result))
		{
			return $result;
		}

		return false;
	}

	/**
	 * Get Logos.
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getLogos()
	{
		$result = $this->client->GetLogos();

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetLogosResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}

	/**
	 * Get Composition
	 *
	 * @param   string  $itemNo    Item No
	 * @param   string  $language  Language
	 *
	 * @return boolean|SimpleXMLElement  The result or FALSE.
	 */
	public function getCompositionB2B($itemNo = '', $language = '')
	{
		$object           = new stdClass;
		$object->ItemNo   = (string) $itemNo;
		$object->Language = (string) $language;

		$result = $this->client->GetCompositionB2B($object);

		if (is_object($result))
		{
			return new SimpleXMLElement($result->GetCompositionB2BResult->any, LIBXML_PARSEHUGE);
		}

		return false;
	}
}
