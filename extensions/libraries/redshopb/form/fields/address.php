<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Application\CMSApplication;

FormHelper::loadFieldClass('rlist');

/**
 * Department Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldAddress extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Address';

	/**
	 * Default selected value for shop view.
	 *
	 * @var array
	 */
	public $defaultAddress = null;

	/**
	 * Orders original address
	 *
	 * @var integer
	 */
	private $originalAddress = 0;

	/**
	 * @var integer
	 */
	private $customerId = 0;

	/**
	 * @var string
	 */
	private $customerType = '';

	/**
	 * @var CMSApplication
	 */
	private $app;

	/**
	 * @var string
	 */
	private $currentView;

	/**
	 * Method to attach a Joomla\CMS\Form\Form object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		$this->app         = Factory::getApplication();
		$this->currentView = $this->app->input->getString('view');

		switch ($this->currentView)
		{
			case 'orders':
				$this->customerId      = $this->app->getUserState('orders.customer_id', 0);
				$this->customerType    = $this->app->getUserState('orders.customer_type', '');
				$this->originalAddress = $this->app->getUserState('orders.address_id', 0);

				break;
			case 'order':
				$this->customerId      = $this->app->getUserState('order.customer_id', 0);
				$this->customerType    = $this->app->getUserState('order.customer_type', '');
				$this->originalAddress = $this->app->getUserState('order.address_id', 0);

				break;
			case 'shop':
				$this->customerId   = $this->app->getUserState('shop.collect_customer_id', $this->app->getUserState('shop.customer_id', 0));
				$this->customerType = $this->app->getUserState('shop.collect_customer_type',  $this->app->getUserState('shop.customer_type', ''));

				break;
			default:
				$this->customerId   = $this->app->getUserState('customer_id', 0);
				$this->customerType = $this->app->getUserState('customer_type', '');
		}

		if (!empty($this->element['customerId']) && (string) $this->element['customerId']
			&& !empty($this->element['customerType']) && (string) $this->element['customerType'])
		{
			$this->customerId   = (string) $this->element['customerId'];
			$this->customerType = (string) $this->element['customerType'];
		}

		if (isset($this->element['onlyFromSelectedCompany']) && (string) $this->element['onlyFromSelectedCompany'] === 'true')
		{
			$this->customerId   = (int) $this->form->getValue('company_id');
			$this->customerType = (string) $this->element['customerType'];
		}

		return $return;
	}

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multi select.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ((string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true')
		{
			$attr .= ' disabled="disabled"';
		}

		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';
		$attr .= $this->required ? ' required="required" aria-required="true"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		// Get the field options.
		$options = (array) $this->getOptions();

		// Create a read-only list (no name) with a hidden input to store the value.
		if ((string) $this->element['readonly'] == 'true')
		{
			$html[] = HTMLHelper::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $this->value, $this->id);
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '"/>';
		}
		// Create a regular list.
		else
		{
			$useIdentifierForDefaultValue = isset($this->element['useIdentifierForDefaultValue'])
				&& $this->element['useIdentifierForDefaultValue'] == 'false' ? false : true;
			$html[]                       = HTMLHelper::_(
				'select.genericlist',
				$options,
				$this->name,
				trim($attr),
				'value',
				'text',
				$useIdentifierForDefaultValue && isset($this->defaultAddress['identifier']) ? $this->defaultAddress['identifier'] : $this->value,
				$this->id
			);
		}

		return implode($html);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options   = array();
		$options[] = HTMLHelper::_('select.option', '', Text::_('JOPTION_SELECT_ADDRESS'));

		$empDefShip     = null;
		$deptDefShip    = null;
		$endCustDefShip = null;
		$custDefShip    = null;
		$deptAddress    = null;
		$endCustAddress = null;
		$custAddress    = null;
		$addresses      = array();
		$orgAddress     = null;

		for ($i = 1; $i <= 12; $i++)
		{
			$addresses[$i] = array();
		}

		// Get the addresses.
		$items = $this->getAddresses();

		// Build the field options.
		foreach ($items as $item)
		{
			// Missing data, pass this address
			if (empty($item['address']) || empty($item['city']) || empty($item['zip']) || empty($item['country_id']))
			{
				continue;
			}

			if ($item['identifier'] == $this->originalAddress)
			{
				$orgAddress = $item;

				continue;
			}

			switch ((int) $item['order'])
			{
				case 1:
					if (is_null($empDefShip))
					{
						$empDefShip = $item;
					}

					$addresses[1][] = $item;

					break;
				case 2:
					$addresses[2][] = $item;

					break;
				case 3:
					if (is_null($deptDefShip))
					{
						$deptDefShip = $item;
					}

					$addresses[3][] = $item;

					break;
				case 4:
					$addresses[4][] = $item;

					break;
				case 5:
					if (is_null($endCustDefShip))
					{
						$endCustDefShip = $item;
					}

					$addresses[5][] = $item;

					break;
				case 6:
					$addresses[6][] = $item;

					break;
				case 7:
					$addresses[7][] = $item;

					break;
				case 8:
					if (is_null($deptAddress))
					{
						$deptAddress = $item;
					}

					$addresses[8][] = $item;

					break;
				case 9:
					if (is_null($endCustAddress))
					{
						$endCustAddress = $item;
					}

					$addresses[9][] = $item;

					break;
				case 10:
					if (is_null($custDefShip))
					{
						$custDefShip = $item;
					}

					$addresses[10][] = $item;

					break;
				case 11:
					$addresses[11][] = $item;

					break;
				case 12:
					if (is_null($custAddress))
					{
						$custAddress = $item;
					}

					$addresses[12][] = $item;

					break;
			}
		}

		$addressGroups        = array();
		$employeeAddresses    = array();
		$departmentAddresses  = array();
		$endCustomerAddresses = array();
		$customerAddresses    = array();
		$customerCompany      = RedshopbHelperCompany::getCompanyByCustomer($this->customerId, $this->customerType, false);

		switch ($this->customerType)
		{
			case 'employee':
				if ($customerCompany->type == 'end_customer')
				{
					if (empty($addresses[1])
						&& empty($addresses[2])
						&& empty($addresses[3])
						&& empty($addresses[4])
						&& empty($addresses[5])
						&& empty($addresses[6]))
					{
						if (empty($addresses[8]))
						{
							if (empty($addresses[9]))
							{
								if (empty($addresses[10]))
								{
									$addressGroups     = array(12);
									$customerAddresses = $addresses[12];
								}
								else
								{
									$addressGroups     = array(10);
									$customerAddresses = $addresses[10];
								}
							}
							else
							{
								$addressGroups        = array(9);
								$endCustomerAddresses = $addresses[9];
							}
						}
						else
						{
							$addressGroups       = array(8);
							$departmentAddresses = $addresses[8];
						}
					}
					else
					{
						$addressGroups        = array(1,2,3,4,5,6);
						$employeeAddresses    = array_merge($addresses[1], $addresses[2]);
						$departmentAddresses  = array_merge($addresses[3], $addresses[4]);
						$endCustomerAddresses = array_merge($addresses[5], $addresses[6]);
					}
				}
				else
				{
					if (empty($addresses[1]) && empty($addresses[2]) && empty($addresses[3])
						&& empty($addresses[4]) && empty($addresses[10]) && empty($addresses[11]))
					{
						if (empty($addresses[8]))
						{
							$addressGroups     = array(12);
							$customerAddresses = $addresses[12];
						}
						else
						{
							$addressGroups       = array(8);
							$departmentAddresses = $addresses[8];
						}
					}
					else
					{
						$addressGroups       = array(1,2,3,4,10,11);
						$employeeAddresses   = array_merge($addresses[1], $addresses[2]);
						$departmentAddresses = array_merge($addresses[3], $addresses[4]);
						$customerAddresses   = array_merge($addresses[10], $addresses[11]);
					}
				}

				break;
			case 'department':
				if ($customerCompany->type == 'end_customer')
				{
					if (empty($addresses[3]) && empty($addresses[4]) && empty($addresses[5]) && empty($addresses[6]))
					{
						if (empty($addresses[8]))
						{
							if (empty($addresses[9]))
							{
								if (empty($addresses[10]))
								{
									$addressGroups     = array(12);
									$customerAddresses = $addresses[12];
								}
								else
								{
									$addressGroups     = array(10);
									$customerAddresses = $addresses[10];
								}
							}
							else
							{
								$addressGroups        = array(9);
								$endCustomerAddresses = $addresses[9];
							}
						}
						else
						{
							$addressGroups       = array(8);
							$departmentAddresses = $addresses[8];
						}
					}
					else
					{
						$addressGroups        = array(3,4,5,6);
						$departmentAddresses  = array_merge($addresses[3], $addresses[4]);
						$endCustomerAddresses = array_merge($addresses[5], $addresses[6]);
					}
				}
				else
				{
					if (empty($addresses[3]) && empty($addresses[4]) && empty($addresses[10]) && empty($addresses[11]))
					{
						if (empty($addresses[8]))
						{
							$addressGroups     = array(12);
							$customerAddresses = $addresses[12];
						}
						else
						{
							$addressGroups       = array(8);
							$departmentAddresses = $addresses[8];
						}
					}
					else
					{
						$addressGroups       = array(3,4,10,11);
						$departmentAddresses = array_merge($addresses[3], $addresses[4]);
						$customerAddresses   = array_merge($addresses[10], $addresses[11]);
					}
				}

				break;
			case 'company':
				if ($customerCompany->type == 'end_customer'
					|| (RedshopbHelperUser::getUserRSId() != 0 && RedshopbHelperUser::getUserCompany()->type == 'end_customer'))
				{
					if (empty($addresses[5]) && empty($addresses[6]))
					{
						if (empty($addresses[9]))
						{
							if (empty($addresses[10]))
							{
								$addressGroups     = array(12);
								$customerAddresses = $addresses[12];
							}
							else
							{
								$addressGroups     = array(10);
								$customerAddresses = $addresses[10];
							}
						}
						else
						{
							$addressGroups        = array(9);
							$endCustomerAddresses = $addresses[9];
						}
					}
					else
					{
						$addressGroups        = array(5,6);
						$endCustomerAddresses = array_merge($addresses[5], $addresses[6]);
					}
				}
				else
				{
					if (empty($addresses[10]) && empty($addresses[11]))
					{
						$addressGroups     = array(12);
						$customerAddresses = $addresses[12];
					}
					else
					{
						$addressGroups     = array(10,11);
						$customerAddresses = array_merge($addresses[10], $addresses[11]);
					}
				}

				break;
		}

		$customer   = RedshopbHelperCompany::getCustomerCompanyByCustomer($this->customerId, $this->customerType)->name;
		$company    = $customerCompany->name;
		$empGroups  = array(1,2,7);
		$dptGroups  = array(3,4,8);
		$endCGroups = array(5,6,9);
		$custGroups = array(10,11,12);

		if ($this->customerType == 'department')
		{
			$department = RedshopbHelperDepartment::getDepartmentById($this->customerId);

			if (!is_null($department))
			{
				$department = $department->name;
			}

			$employee = null;
		}
		elseif ($this->customerType == 'employee')
		{
			$department = RedshopbHelperUser::getUserDepartment($this->customerId);

			if (!is_null($department))
			{
				$department = $department->name;
			}

			$employee = RedshopbHelperUser::getUser($this->customerId)->name;
		}
		else
		{
			$department = null;
			$employee   = null;
		}

		$empAddresses  = array_intersect($addressGroups, $empGroups);
		$deptAddresses = array_intersect($addressGroups, $dptGroups);
		$compAddresses = array_intersect($addressGroups, $endCGroups);
		$custAddresses = array_intersect($addressGroups, $custGroups);
		$b2cCustomer   = false;

		if ($customerCompany && ($customerCompany->b2c || RedshopbEntityCompany::load($customerCompany->id)->get('hide_company')))
		{
			$b2cCustomer = true;
		}

		if (!empty($empAddresses) && !empty($employeeAddresses))
		{
			if (!$b2cCustomer && !is_null($employee))
			{
				$options[] = HTMLHelper::_(
					'select.optgroup',
					Text::_('COM_REDSHOPB_EMPLOYEE') . ' ' . $employee . ' ' . strtolower(Text::_('COM_REDSHOPB_ADDRESS_LIST_TITLE'))
				);
			}

			foreach ($employeeAddresses as $item)
			{
				// Get address name for showing it in dropdown
				if (!empty($item['name']))
				{
					$name = $item['name'] . ' - ' . $item['data'];
				}
				else
				{
					$name = $item['data'];
				}

				// Add option to list
				$options[] = HTMLHelper::_('select.option', $item['identifier'], $name);
			}

			if (!$b2cCustomer && !is_null($employee))
			{
				$options[] = HTMLHelper::_(
					'select.optgroup',
					Text::_('COM_REDSHOPB_EMPLOYEE') . ' ' . $employee . ' ' . strtolower(Text::_('COM_REDSHOPB_ADDRESS_LIST_TITLE'))
				);
			}
		}

		if (!$b2cCustomer && !empty($deptAddresses) && !empty($departmentAddresses))
		{
			if (!is_null($department))
			{
				$options[] = HTMLHelper::_(
					'select.optgroup',
					Text::_('COM_REDSHOPB_DEPARTMENT') . ' ' . $department . ' ' . strtolower(Text::_('COM_REDSHOPB_ADDRESS_LIST_TITLE'))
				);
			}

			foreach ($departmentAddresses as $item)
			{
				// Get address name for showing it in dropdown
				if (!empty($item['name']))
				{
					$name = $item['name'] . ' - ' . $item['data'];
				}
				else
				{
					$name = $item['data'];
				}

				// Add option to list
				$options[] = HTMLHelper::_('select.option', $item['identifier'], $name);
			}

			if (!is_null($department))
			{
				$options[] = HTMLHelper::_(
					'select.optgroup',
					Text::_('COM_REDSHOPB_DEPARTMENT') . ' ' . $department . ' ' . strtolower(Text::_('COM_REDSHOPB_ADDRESS_LIST_TITLE'))
				);
			}
		}

		if (!$b2cCustomer && !empty($compAddresses) && !empty($endCustomerAddresses))
		{
			if (!is_null($company))
			{
				$options[] = HTMLHelper::_(
					'select.optgroup',
					Text::_('COM_REDSHOPB_COMPANY') . ' ' . $company . ' ' . strtolower(Text::_('COM_REDSHOPB_ADDRESS_LIST_TITLE'))
				);
			}

			foreach ($endCustomerAddresses as $item)
			{
				// Get address name for showing it in dropdown
				if (!empty($item['name']))
				{
					$name = $item['name'] . ' - ' . $item['data'];
				}
				else
				{
					$name = $item['data'];
				}

				// Add option to list
				$options[] = HTMLHelper::_('select.option', $item['identifier'], $name);
			}

			if (!is_null($company))
			{
				$options[] = HTMLHelper::_(
					'select.optgroup',
					Text::_('COM_REDSHOPB_COMPANY') . ' ' . $company . ' ' . strtolower(Text::_('COM_REDSHOPB_ADDRESS_LIST_TITLE'))
				);
			}
		}

		if (!$b2cCustomer && !empty($custAddresses) && !empty($customerAddresses))
		{
			if (!is_null($customer))
			{
				$options[] = HTMLHelper::_(
					'select.optgroup',
					Text::_('COM_REDSHOPB_COMPANY') . ' ' . $customer . ' ' . strtolower(Text::_('COM_REDSHOPB_ADDRESS_LIST_TITLE'))
				);
			}

			foreach ($customerAddresses as $item)
			{
				// Get address name for showing it in dropdown
				if (!empty($item['name']))
				{
					$name = $item['name'] . ' - ' . $item['data'];
				}
				else
				{
					$name = $item['data'];
				}

				// Add option to list
				$options[] = HTMLHelper::_('select.option', $item['identifier'], $name);
			}

			if (!is_null($customer))
			{
				$options[] = HTMLHelper::_(
					'select.optgroup',
					Text::_('COM_REDSHOPB_COMPANY') . ' ' . $customer . ' ' . strtolower(Text::_('COM_REDSHOPB_ADDRESS_LIST_TITLE'))
				);
			}
		}

		// Auto select address if we are in shop view, setting a delivery address
		if (!is_null($empDefShip) && in_array(1, $addressGroups))
		{
			$this->defaultAddress = $empDefShip;
		}
		elseif (!is_null($deptDefShip) && in_array(3, $addressGroups))
		{
			$this->defaultAddress = $deptDefShip;
		}
		elseif (!is_null($endCustDefShip) && in_array(5, $addressGroups))
		{
			$this->defaultAddress = $endCustDefShip;
		}
		elseif (!is_null($deptAddress) && in_array(8, $addressGroups))
		{
			$this->defaultAddress = $deptAddress;
		}
		elseif (!is_null($endCustAddress) && in_array(9, $addressGroups))
		{
			$this->defaultAddress = $endCustAddress;
		}
		elseif (!is_null($custDefShip) && in_array(10, $addressGroups))
		{
			$this->defaultAddress = $custDefShip;
		}
		elseif (!is_null($custAddress) && in_array(12, $addressGroups))
		{
			$this->defaultAddress = $custAddress;
		}

		if (!is_null($orgAddress))
		{
			// An original address exists, then it shows it in the options

			$options[] = HTMLHelper::_(
				'select.optgroup',
				Text::_('COM_REDSHOPB_ORDERS_ORIGINAL_ADDRESS')
			);

			// Get address name for showing it in dropdown
			if (!empty($orgAddress['name']))
			{
				$name = $orgAddress['name'] . ' - ' . $orgAddress['data'];
			}
			else
			{
				$name = $orgAddress['data'];
			}

			$options[] = HTMLHelper::_('select.option', $orgAddress['identifier'], $name);

			$options[] = HTMLHelper::_(
				'select.optgroup',
				Text::_('COM_REDSHOPB_ORDERS_ORIGINAL_ADDRESS')
			);

			$this->defaultAddress = $orgAddress;
		}
		elseif ($this->currentView == 'orders')
		{
			// Several orders without a common original address (still the "original" option is given) - value 0

			$options[] = HTMLHelper::_(
				'select.optgroup',
				Text::_('COM_REDSHOPB_ORDERS_ORIGINAL_ADDRESS')
			);
			$options[] = HTMLHelper::_('select.option', -1, Text::_('COM_REDSHOPB_ORDERS_ORIGINAL_ADDRESS_EACH'));

			$options[] = HTMLHelper::_(
				'select.optgroup',
				Text::_('COM_REDSHOPB_ORDERS_ORIGINAL_ADDRESS')
			);

			$this->defaultAddress               = Array();
			$this->defaultAddress['identifier'] = -1;
		}

		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Method to get the list of addresses.
	 *
	 * @return  array  An array of addresses.
	 */
	protected function getAddresses()
	{
		$db    = Factory::getDbo();
		$where = $this->getWhere($this->customerId, $this->customerType);

		if ($this->currentView == 'order')
		{
			$expCustomerId   = $this->app->getUserState('order.expedite.customer_id', 0);
			$expCustomerType = $this->app->getUserState('order.expedite.customer_type', '');

			if ((int) $this->originalAddress > 0)
			{
				$where .= ' OR (' . $db->qn('a.id') . ' = ' . (int) $this->originalAddress . ')';
			}

			if ($expCustomerId != 0 && $expCustomerType != '')
			{
				$where = '(' . $where . ') OR (' . $this->getWhere($expCustomerId, $expCustomerType) . ')';
			}
		}
		elseif ($this->currentView == 'orders')
		{
			if ((int) $this->originalAddress > 0)
			{
				$where .= ' OR (' . $db->qn('a.id') . ' = ' . (int) $this->originalAddress . ')';
			}
		}

		if (!is_null($where))
		{
			$query  = $db->getQuery(true)
				->select(
					array (
						$db->qn('a.id', 'identifier'),
						$db->qn('a.name', 'name'),
						$db->qn('a.order'),
						$db->qn('a.address'),
						$db->qn('a.city'),
						$db->qn('a.country_id'),
						$db->qn('c.name', 'country_name'),
						$db->qn('a.zip'),
						'TRIM(CONCAT_WS(\' \', a.address, a.address2, a.zip, a.city)) AS ' . $db->qn('data')
					)
				)
				->from($db->qn('#__redshopb_address', 'a'))
				->where($where)
				->leftJoin($db->qn('#__redshopb_country', 'c') . ' ON c.id = a.country_id')
				->order($db->qn('a.order') . ' ASC, ' . $db->qn('a.name') . ' ASC, ' . $db->qn('data') . ' ASC');
			$result = $db->setQuery($query)->loadAssocList();

			foreach ($result as $i => $address)
			{
				$result[$i]['data'] .= ' ' . Text::_($address['country_name']);
			}
		}
		else
		{
			$result = array(
				array(
					'identifier' => 0,
					'name'       => '',
					'data'       => Text::_('COM_REDSHOPB_NO_RESULTS')
				)
			);
		}

		if (is_array($result))
		{
			return $result;
		}
		else
		{
			return array();
		}
	}

	/**
	 * Return hidden field for getting default address.
	 *
	 * @return string
	 */
	public function getHiddenField()
	{
		$addresses = $this->getOptions();

		if (is_null($this->defaultAddress))
		{
			if (is_array($addresses) && !empty($addresses))
			{
				foreach ($addresses as $address)
				{
					if (is_numeric($address->value))
					{
						$this->value = $address->value;
						break;
					}
				}
			}
			else
			{
				$this->value = 0;
			}
		}
		else
		{
			$this->value = $this->defaultAddress['identifier'];
		}

		return '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '" id="' . $this->id . '"/>';
	}

	/**
	 * Get address query WHERE clause.
	 *
	 * @param   int     $customerId    Customer id.
	 * @param   string  $customerType  Customer type.
	 *
	 * @return string WHERE clause for address listing.
	 */
	private function getWhere($customerId, $customerType)
	{
		$db    = Factory::getDbo();
		$where = null;

		switch ($customerType)
		{
			case 'employee':
				$user        = RedshopbHelperUser::getUser($customerId);
				$userCompany = RedshopbHelperUser::getUserCompany($user->id);

				// Get employee addresses
				$where = '(' . $db->qn('a.customer_type') . ' = ' . $db->q('employee') .
					' AND ' . $db->qn('a.customer_id') . ' = ' . (int) $user->id .
					' AND ' . $db->qn('a.type') . ' != 2)';

				if (!$userCompany->b2c)
				{
					$where .= ' OR (' . $db->qn('a.customer_type') . ' = ' . $db->q('company') .
						' AND ' . $db->qn('a.customer_id') . ' = ' . (int) $userCompany->id . ')' .
						' OR ' . $db->qn('a.id') . ' = ' . (int) $userCompany->addressId;

					if ($userCompany->type == 'end_customer')
					{
						$customerTypeCompany = RedshopbHelperCompany::getCustomerCompanyById($userCompany->id);
						$where              .= ' OR (' . $db->qn('a.customer_type') . ' = ' . $db->q('company') .
							' AND ' . $db->qn('a.customer_id') . ' = ' . (int) $customerTypeCompany->id . ')' .
							' OR ' . $db->qn('a.id') . ' = ' . (int) $customerTypeCompany->addressId;
					}

					$userDepartment = RedshopbHelperUser::getUserDepartment($user->id);

					if (!is_null($userDepartment))
					{
						$where .= ' OR (' . $db->qn('a.customer_type') . ' = ' . $db->q('department') .
							' AND ' . $db->qn('a.customer_id') . ' = ' . (int) $userDepartment->id . ')' .
							' OR ' . $db->qn('a.id') . ' = ' . (int) $userDepartment->addressId;
					}
				}

				break;
			case 'department':
				$departmentCompany = RedshopbHelperCompany::getCompanyByCustomer($customerId, $customerType);
				$department        = RedshopbHelperDepartment::getDepartmentById($customerId, false);
				$where             = '(' . $db->qn('a.customer_type') . ' = ' . $db->q('department') .
					' AND ' . $db->qn('a.customer_id') . ' = ' . (int) $department->id . ')' .
					' OR ' . $db->qn('a.id') . ' IN (' . (int) $department->addressId . ',' . (int) $departmentCompany->addressId . ')' .
					' OR (' . $db->qn('a.customer_type') . ' = ' . $db->q('company') .
					' AND ' . $db->qn('a.customer_id') . ' = ' . (int) $departmentCompany->id . ')';

				if ($departmentCompany->type == 'end_customer')
				{
					$customerTypeCompany = RedshopbHelperCompany::getCustomerCompanyById($departmentCompany->id);
					$where              .= ' OR (' . $db->qn('a.customer_type') . ' = ' . $db->q('company') .
						' AND ' . $db->qn('a.customer_id') . ' = ' . (int) $customerTypeCompany->id . ')' .
						' OR ' . $db->qn('a.id') . ' = ' . (int) $customerTypeCompany->addressId;
				}

				break;
			case 'company':
				$userCompany      = RedshopbHelperUser::getUserCompany();
				$purchaserCompany = RedshopbHelperCompany::getCompanyById($customerId, false);

				// Check level of user's (doing the expedite) company level
				if (RedshopbHelperUser::getUserRSId() == 0 || $userCompany->level <= 2)
				{
					$where = '(' . $db->qn('a.customer_type') . ' = ' . $db->q('company') .
						' AND ' . $db->qn('a.customer_id') . ' = ' . (int) $purchaserCompany->id . ')' .
						' OR ' . $db->qn('a.id') . ' = ' . (int) $purchaserCompany->addressId;

					// We don't need to add any other addresses if the company level <= 2 (Customer)
					break;
				}

				$where = '(' . $db->qn('a.customer_type') . ' = ' . $db->q('company') .
					' AND ' . $db->qn('a.customer_id') . ' = ' . (int) $userCompany->id . ')' .
					' OR ' . $db->qn('a.id') . ' = ' . (int) $userCompany->addressId;

				if ($purchaserCompany->type == 'end_customer')
				{
					$customerTypeCompany = RedshopbHelperCompany::getCustomerCompanyByCustomer($customerId, $customerType);
					$where              .= ' OR (' . $db->qn('a.customer_type') . ' = ' . $db->q('company') .
						' AND ' . $db->qn('a.customer_id') . ' = ' . (int) $customerTypeCompany->id . ')' .
						' OR ' . $db->qn('a.id') . ' = ' . (int) $customerTypeCompany->addressId;
				}

				break;
		}

		if (strcmp($this->currentView, 'shop') === 0)
		{
			$where = $db->qn('a.type') . ' != 2 AND (' . $where . ')';
		}

		return $where;
	}
}
