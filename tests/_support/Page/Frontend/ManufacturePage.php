<?php
/**
 * ManufacturePage
 */

namespace Page\Frontend;
class ManufacturePage  extends Redshopb2bPage
{
	/**
	 * @var string
	 */
	public static $URL = 'index.php?option=com_redshopb&view=manufacturers';

	/**
	 * @var string
	 */
	public static $manufacturersSearch = 'filter_search_manufacturers';

	/**
	 * @var array
	 */
	public static $categoryId = ['id' => 'jform_category'];

	/**
	 * @var string
	 */
	public static $parentManufacturers = 'Parent manufacturer';

	/**
	 * @var string
	 */
	public static $statusButton = "//label[@for=\"jform_featured0\"]";
}