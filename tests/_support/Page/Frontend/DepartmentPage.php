<?php
/**
 * Page departments
 */
namespace Page\Frontend;

class DepartmentPage extends Redshopb2bPage
{
//page department
	/**
	 * @var string
	 */
	public static $URLDepartments = '/index.php?option=com_redshopb&view=departments';

	/**
	 * @var string
	 */
	public static $searchDepartment = 'filter_search_departments';

	/**
	 * @var array
	 */
	public static $deleteDepartment = ['css' => '#departmentsModal button.btn-primary'];

	/**
	 * @var array
	 */
	public static $departmentModal = ['id' => 'departmentsModal'];

	// label detail
	/**
	 * @var string
	 */
	public static $labelNumber = 'Number';

	/**
	 * @var string
	 */
	public static $labelNameSecond = 'Name Second Line';

	/**
	 * @var string
	 */
	public static $labelRequisition = 'Requisition';

	/**
	 * @var string
	 */
	public static $labelParent = 'Parent';

	/**
	 * @var string
	 */
	public static $labelAddress = 'Address';

	/**
	 * @var string
	 */
	public static $labelAddressSecond = 'Address Second Line';

	/**
	 * @var string
	 */
	public static $labelZipCode = 'Zip Code';

	/**
	 * @var string
	 */
	public static $labelCity = 'City';

	/**
	 * @var string
	 */
	public static $labelPhone = 'Phone';

	/**
	 * @var string
	 */
	public static $labelCountry = 'Country';

	/**
	 * @var array
	 */
	//id
	public static $nameID = ['id' =>'jform_name'];

	/**
	 * @var array
	 */
	public static $searchDepartmentId = ['id' => 'filter_search_departments'];

	//message
	/**
	 * @var string
	 */
	public static $saveDepartmentSuccess = 'Department successfully submitted.';

	/**
	 * @var string
	 */
	public static $editDepartmentSuccess = 'Department successfully saved.';

	/**
	 * @var string
	 */
	public static $missingName = '';

	/**
	 * @var string
	 */
	public static $missingCompany = '';
}
