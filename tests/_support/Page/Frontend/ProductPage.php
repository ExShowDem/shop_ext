<?php
/**
 * Page products
 */
namespace Page\Frontend;
class ProductPage extends Redshopb2bPage
{
	//page product
	/**
	 * @var string
	 */
	public static $URLProducts = 'index.php?option=com_redshopb&view=products';

	/**
	 * @var array
	 */
	public static $productTag = ['id' => 'redshopb-tags'];

	/**
	 * @var array
	 */
	public static $selectCategory = ['xpath' => "//div[@id='jform_category_id_chzn']/a[@class='chzn-single']/span[contains(normalize-space(), '- Select Category -')]"];

	/**
	 * @var array
	 */
	public static $sku = ['id' => 'jform_sku'];

	/**
	 * @var string
	 */
	public static $priceRetail = '#jform_retail_price';

	/**
	 * @var array
	 */
	public static $price = ['id' => 'jform_price'];

	/**
	 * @var array
	 */
	public static $idCategory = ['id' => 'jform_category_id_chzn'];

	/**
	 * @var string
	 */
	public static $nameCategory = 'Main Category';

	/**
	 * @var string
	 */
	public static $productSaveSuccess = 'Product successfully submitted.';

	/**
	 * @var array
	 */
	public static $searchProductID = ['id' => 'filter_search_products'];

	/**
	 * @var string
	 */
	public static $searchProduct ='filter_search_products';

	/**
	 * @var string
	 */
	public static $editProductSuccess = "Product successfully saved.";

	//labels
	/**
	 * @var string
	 */
	public static $ownerCompanyLabel = "Owner Company";

	/**
	 * @var string
	 */
	public static $mainCategoryLabel = "Categories";

	/**
	 * @var string
	 */
	public static $manufacturerLabel = "Manufacturer";

	/**
	 * @var string
	 */
	public static $UnitOfMeasureLabel = "Unit Of Measure";

	/**
	 * @var string
	 */
	public static $filterFieldsetLabel = "Filter Fieldset";

	/**
	 * @var string
	 */
	public static $vatTaxLabel = "VAT/Tax group";

	/**
	 * @var string
	 */
	public static $ownerCompanyId = "//div[@id='jform_company_id_chzn']/a";
	
	/**
	 * @var string
	 */
	public static $ownerCompanyJform = "jform_company_id_chzn";
	
	/**
	 * @var string
	 */
	public static $mainCategoryJform = "jform_category_id_chzn";
	/**
	 * @var string
	 */
	public static $mainCategoryId = "//div[@id='jform_category_id_chzn']/a";

	/**
	 * @var string
	 */
	public static $vatTaxGroupJform = "jform_tax_group_id_chzn";

	/**
	 * @var string
	 */
	public static $vatTaxGroupId = "//div[@id='jform_tax_group_id_chzn']/a";
	
	//attribute tabs
	public static $attributeType = "//a[contains(text(),\"Attribute Types\")]";

	/**
	 * @var array
	 */
	public static $newAttribute = "//a[@data-task='product_attribute.add']";

	/**
	 * @var string
	 */
	public static $attributeTypeLabel = "Attribute Type";

	/**
	 * @var array
	 */
	public static $nameAttribute = "#jform_name";

	/**
	 * @var array
	 */
	public static $attributeTab = "#productAttributesTab";

	/**
	 * @var array
	 */
	public static $attributeTypeChoice = "#jform_type_id_chzn";

	/**
	 * @var array
	 */
	public static  $attributeTypeSearch = "#jform_type_id_chzn";

	/**
	 * @var string
	 */
	public static $labelStatus = 'Status';

	/**
	 * @var string
	 */
	public static $saveAttributeSuccess = 'The attribute test has been set as main attribute, as there must be at least one';

	/**
	 * @var string
	 */
	public static $saveAttributeValueSuccess = 'Product attribute successfully saved.';

	/**
	 * @var string
	 */
	public static $attributeValueValueSubmitted = 'Product attribute successfully submitted.';

	/**
	 * @var string
	 */
	public static $saveCloseAttributeValueSuccess = "Product item successfully saved.";

	/**
	 * @var string
	 */
	public static $saveAttributeSuccessMessage = "Product attribute type successfully submitted.";

	/**
	 * @var array
	 */
	public static $buttonDeleteAttribute = "(//a[@data-task='product_attributes.delete'])[1]";

	/**
	 * @var array
	 */
	public static $valueAttribute = "#jform_value";

	/**
	 * @var string
	 */
	public static $labelDefaultSelect = 'Default Selected';

	/**
	 * @var string
	 */
	public static $combinations = 'Combinations';

	/**
	 * @var array
	 */
	public static $combinationsPrices = "//a[contains(text(),\"Combinations Prices\")]";

	/**
	 * @var array
	 */
	public static $generateItems = "//button[contains(@onclick, \"Joomla.submitbutton('product.generate')\")]";

	/**
	 * @var string
	 */
	public static $generateItemSuccess = 'Items successfully generated';

	//Stock tab
	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $stock = "//a[contains(text(),\"Stock\")]";

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $quantityProductStock = "//input[@data-volume_pricing_prefix=\".js-volume_pricing\"]";

	/**
	 * @param $name
	 *
	 * @return string
	 * @since 2.8.0
	 */
	public static function getStock($name)
	{
		$pathStock = "//a[contains(text(),'$name')]";

		return $pathStock;
	}

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $stockNumber = "//div[@class='input-append']//input[@type='number']";

	/**
	 * @param $idAttribute
	 * @return array
	 */
	public static function getValueAttribute($idAttribute)
	{
		$pathAttribute = ['xpath'=> "(//a[@class='btn btn-default pull-right'])[" . $idAttribute . "]"];

		return $pathAttribute;
	}

	/**
	 * @param $value
	 * @return array
	 */
	public static function getValueCombinations($value)
	{
		$path = ['xpath' => "(//input[contains(@id, 'jform_price') and @class='input-mini'])[" . $value . "]"];

		return $path;
	}

	/**
	 * @param $name
	 * @return array
	 */
	public static function getNameAttribute($name)
	{
		$valueAttribute = ['xpath'=>'//a[contains(normalize-space(), ' . $name . ')]'];

		return $valueAttribute;
	}
}
