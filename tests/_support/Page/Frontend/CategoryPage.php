<?php
/**
 * Page Categories
 **/

namespace Page\Frontend;

class CategoryPage extends Redshopb2bPage
{
	//page categories
	/**
	 * @var string
	 */
	public static $URLCategories = 'index.php?option=com_redshopb&view=categories';

	/**
	 * @var string
	 */
	public static $categoryCompanyJform = "jform_company_id_chzn";
	/**
	 * @var array
	 */
	public static $editor = ['link' => 'Toggle editor'];
	
	/**
	 * @var array
	 */
	public static $description = ['id' => 'jform_description'];
	
	/**
	 * @var array
	 */
	public static $categoryStatePath = ['xpath'=>'//td[@class=\'footable-visible\']/a'];
	
	/**
	 * @var string
	 */
	public static $saveCategorySuccess = 'Category successfully submitted.';
	
	/**
	 * @var string
	 */
	public static $categorySaveSuccess = "Category successfully saved.";
	
	/**
	 * @var string
	 */
	public static $searchCategory = 'filter_search_categories';
	
	/**
	 * @var array
	 */
	public static $searchCategoryId = ['id'=>'filter_search_categories'];

	//missing message
	public static $missingTitle = "Invalid field:  Title ";

}