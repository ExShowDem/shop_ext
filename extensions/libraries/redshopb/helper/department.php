<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
/**
 * A Department helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperDepartment
{
	/**
	 * Array ids parent departments from current department
	 *
	 * @var array
	 */
	protected static $parentDepartments = array();

	/**
	 * Array ids child departments from current department
	 *
	 * @var array
	 */
	protected static $childDepartments = array();

	/**
	 * Check if the department is from the given company.
	 *
	 * @param   integer  $departmentId  The department id.
	 * @param   integer  $companyId     The company id.
	 * @param   boolean  $hideDeleted   Hide deleted departments.
	 *
	 * @return  boolean  True if from the given company, false otherwise.
	 */
	public static function isFromCompany($departmentId, $companyId, $hideDeleted = true)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('id')
			->from($db->qn('#__redshopb_department'))
			->where('id = ' . (int) $departmentId)
			->where('company_id = ' . (int) $companyId);

		if ($hideDeleted)
		{
			$query->where($db->qn('deleted') . ' = 0 AND ' . $db->qn('state') . ' = 1');
		}

		$db->setQuery($query);

		$result = $db->loadResult();

		if (empty($result))
		{
			return false;
		}

		return true;
	}

	/**
	 * Get the company id of this department.
	 *
	 * @param   integer  $departmentId  The department id.
	 * @param   boolean  $hideDeleted   Hide deleted departments.
	 *
	 * @return  mixed  The company id or null.
	 */
	public static function getCompanyId($departmentId, $hideDeleted = true)
	{
		static $departments = array();
		$key                = $departmentId . '_' . (int) $hideDeleted;

		if (!array_key_exists($key, $departments))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('company_id')
				->from($db->qn('#__redshopb_department'))
				->where('id = ' . (int) $departmentId);

			if ($hideDeleted)
			{
				$query->where($db->qn('deleted') . ' = 0 AND ' . $db->qn('state') . ' = 1');
			}

			$companyId = $db->setQuery($query)->loadResult();

			if (!empty($companyId))
			{
				$departments[$key] = (int) $companyId;
			}
			else
			{
				$departments[$key] = null;
			}
		}

		return $departments[$key];
	}

	/**
	 * Get the department id from the department name.
	 *
	 * @param   string   $departmentName  The name of the department
	 * @param   boolean  $hideDeleted     Hide deleted departments.
	 *
	 * @return mixed The department ID or null.
	 */
	public static function getDepartmentId($departmentName, $hideDeleted = true)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id')
			->from($db->qn('#__redshopb_department'))
			->where('LOWER(' . ($db->qn('name')) . ') = ' . mb_strtolower($db->q($departmentName)));

		if ($hideDeleted)
		{
			$query->where($db->qn('deleted') . ' = 0 AND ' . $db->qn('state') . ' = 1');
		}

		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Get the department id from the department number.
	 *
	 * @param   string   $departmentNumber  The number of the department.
	 * @param   boolean  $hideDeleted       Hide deleted departments.
	 *
	 * @return mixed The department ID or null.
	 */
	public static function getDepartmentIdByDepartmentNumber($departmentNumber, $hideDeleted = true)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id')
			->from($db->qn('#__redshopb_department'))
			->where($db->qn('department_number') . ' = ' . $db->q($departmentNumber));

		if ($hideDeleted)
		{
			$query->where($db->qn('deleted') . ' = 0 AND ' . $db->qn('state') . ' = 1');
		}

		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Get all department IDs and parents
	 *
	 * @param   boolean  $hideDeleted  Hide deleted departments.
	 *
	 * @return array The departments
	 */
	public static function getAllDepartments($hideDeleted = true)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('d.id, d.parent_id')
			->from($db->qn('#__redshopb_department', 'd'));

		if ($hideDeleted)
		{
			$query->where($db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' = 1');
		}

		$db->setQuery($query);
		$departments = $db->loadAssocList();

		$deps = array();

		foreach ($departments as $department)
		{
			$deps[$department['parent_id']][] = $department['id'];
		}

		return $deps;
	}

	/**
	 * Get the department name for department id.
	 *
	 * @param   integer  $departmentId  The department id.
	 * @param   boolean  $webSafe       Use URL safe name.
	 * @param   boolean  $hideDeleted   Hide deleted departments.
	 *
	 * @return  mixed  The department name or null.
	 */
	public static function getName($departmentId, $webSafe = false, $hideDeleted = true)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('name')
			->from('#__redshopb_department')
			->where('id =' . (int) $departmentId);

		if ($hideDeleted)
		{
			$query->where($db->qn('deleted') . ' = 0 AND ' . $db->qn('state') . ' = 1');
		}

		$db->setQuery($query);

		$name = $db->loadResult();

		if (empty($name))
		{
			return null;
		}

		if ($webSafe)
		{
			setlocale(LC_ALL, 'en_US.UTF8');

			$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $name);
			$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
			$clean = strtolower(trim($clean, '-'));
			$clean = preg_replace("/[\/_|+ -]+/", '-', $clean);

			return urlencode($clean);
		}

		return $name;
	}

	/**
	 * Get the department name2 for department id.
	 *
	 * @param   integer  $departmentId  The department id.
	 * @param   boolean  $hideDeleted   Hide deleted departments.
	 *
	 * @return  mixed  The department name2 or empty string.
	 */
	public static function getName2($departmentId, $hideDeleted = true)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('name2')
			->from('#__redshopb_department')
			->where('id =' . (int) $departmentId);

		if ($hideDeleted)
		{
			$query->where($db->qn('deleted') . ' = 0 AND ' . $db->qn('state') . ' = 1');
		}

		$db->setQuery($query);

		$name = $db->loadResult();

		if (empty($name))
		{
			return '';
		}

		return $name;
	}

	/**
	 * Get departments by user.
	 *
	 * @param   integer  $userId       RedshopB User id
	 * @param   integer  $companyId    (optional) Company Id to filter departments from
	 * @param   boolean  $hideDeleted  Hide deleted departments.
	 *
	 * @return mixed|null
	 */
	public static function getDepartmentsByUser($userId = 0, $companyId = 0, $hideDeleted = true)
	{
		$db   = Factory::getDbo();
		$user = Factory::getUser();

		$query = $db->getQuery(true);
		$query->select('joomla_user_id')
			->from($db->qn('#__redshopb_user'));

		if ($userId)
		{
			$query->where('id = ' . (int) $userId);
		}

		$db->setQuery($query);

		$result = $db->loadObject();

		$userDepartments = RedshopbHelperACL::listAvailableDepartments((int) $result->joomla_user_id, 'comma', $companyId, true);
		$ownDepartments  = RedshopbHelperACL::listAvailableDepartments($user->id, 'comma', $companyId);

		$query->clear()
			->select('*')
			->from($db->qn('#__redshopb_department', 'd'))
			->order('d.lft');

		if ($hideDeleted)
		{
			$query->where($db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' = 1');
		}

		if ($userDepartments == '' || $ownDepartments == '')
		{
			$query->where('0 = 1');
		}
		else
		{
			$query->where($db->qn('d.id') . ' IN (' . $userDepartments . ')')
				->where($db->qn('d.id') . ' IN (' . $ownDepartments . ')');
		}

		$db->setQuery($query);

		$departments = $db->loadObjectList();

		if (empty($departments))
		{
			return null;
		}

		return $departments;
	}

	/**
	 * Get list ids current and parents departments
	 *
	 * @param   int      $departmentId  Department id.
	 * @param   boolean  $hideDeleted   Hide deleted departments.
	 *
	 * @return mixed
	 */
	public static function getParentDepartments($departmentId = 0, $hideDeleted = true)
	{
		$app = Factory::getApplication();

		if ($departmentId <= 0)
		{
			$departmentId = $app->getUserStateFromRequest('list.department_id', 'department_id', 0, 'int');
		}

		if (!isset(self::$parentDepartments[$departmentId]))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('d_parent.id')
				->from($db->qn('#__redshopb_department', 'd_node'));

			if ($hideDeleted)
			{
				$query->leftJoin(
					$db->qn('#__redshopb_department', 'd_parent') . ' ON d_node.lft BETWEEN d_parent.lft AND d_parent.rgt AND ' .
					$db->qn('d_parent.deleted') . ' = 0 AND ' . $db->qn('d_parent.state') . ' = 1'
				)
					->where($db->qn('d_node.deleted') . ' = 0');
			}
			else
			{
				$query->leftJoin($db->qn('#__redshopb_department', 'd_parent') . ' ON d_node.lft BETWEEN d_parent.lft AND d_parent.rgt');
			}

			$query->where('d_node.id = ' . (int) $departmentId)
				->where('d_parent.level > 0');

			$db->setQuery($query);
			self::$parentDepartments[$departmentId] = $db->loadColumn();

			if (!self::$parentDepartments[$departmentId])
			{
				self::$parentDepartments[$departmentId] = array($departmentId);
			}
		}

		return self::$parentDepartments[$departmentId];
	}

	/**
	 * Get number of sub departments for given departments.
	 *
	 * @param   array  $departments     Parent department ids.
	 * @param   bool   $hideDeleted     Hide deleted departments.
	 * @param   bool   $getDeeperCount  Get count all deeper departments
	 *
	 * @return array Number of departments for given departments.
	 */
	public static function getSubDepartmentsCount($departments = array(), $hideDeleted = true, $getDeeperCount = true)
	{
		$departments = ArrayHelper::toInteger($departments, array());

		if (empty($departments) || !is_array($departments))
		{
			return array();
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(
				array (
					$db->qn('d_parent.id', 'key'),
					'COUNT(' . $db->qn('d_node.id') . ') AS counter'
				)
			)
			->from($db->qn('#__redshopb_department', 'd_node'));

		if ($hideDeleted)
		{
			$query->leftJoin(
				$db->qn('#__redshopb_department', 'd_parent') . ' ON d_node.lft > d_parent.lft AND d_node.rgt < d_parent.rgt AND ' .
				$db->qn('d_parent.deleted') . ' = 0 AND ' . $db->qn('d_parent.state') . ' = 1'
			)
				->where($db->qn('d_node.deleted') . ' = 0');
		}
		else
		{
			$query->leftJoin($db->qn('#__redshopb_department', 'd_parent') . ' ON d_node.lft > d_parent.lft AND d_node.rgt < d_parent.rgt');
		}

		$query->where($db->qn('d_parent.id') . ' IN (' . implode(',', $departments) . ')')
			->where($db->qn('d_node.level') . ' > 0')
			->group($db->qn('d_parent.id'));

		if (!$getDeeperCount)
		{
			$query->where('d_node.level = d_parent.level + 1');
		}

		$db->setQuery($query);

		return $db->loadAssocList('key', 'counter');
	}

	/**
	 * Get number of employees for given departments.
	 *
	 * @param   array  $departments     Department ids.
	 * @param   bool   $hideDeleted     Hide deleted departments.
	 * @param   bool   $getDeeperCount  Get count all deeper departments
	 *
	 * @return array Array of number of employees per department.
	 */
	public static function getEmployeesCount($departments = array(), $hideDeleted = true, $getDeeperCount = true)
	{
		$departments = ArrayHelper::toInteger($departments, array());

		if (empty($departments) || !is_array($departments))
		{
			return array();
		}

		$childDepartments = array();
		$allDepartments   = $departments;

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(
				array(
					$db->qn('d_parent.id', 'parent'),
					$db->qn('d_node.id', 'child')
				)
			)
			->from($db->qn('#__redshopb_department', 'd_node'));

		if ($hideDeleted)
		{
			$query->leftJoin(
				$db->qn('#__redshopb_department', 'd_parent') . ' ON d_node.lft > d_parent.lft AND d_node.rgt < d_parent.rgt AND ' .
				$db->qn('d_parent.deleted') . ' = 0 AND ' . $db->qn('d_parent.state') . ' = 1'
			)
				->where($db->qn('d_node.deleted') . ' = 0');
		}
		else
		{
			$query->leftJoin($db->qn('#__redshopb_department', 'd_parent') . ' ON d_node.lft > d_parent.lft AND d_node.rgt < d_parent.rgt');
		}

		$query->where('d_parent.id IN (' . implode(',', $departments) . ')')
			->where('d_node.level > 0');
		$db->setQuery($query);

		$resultDepartments = $db->loadObjectList();

		foreach ($resultDepartments as $department)
		{
			if (!isset($childDepartments[$department->parent]))
			{
				if (is_null($department->child))
				{
					$childDepartments[$department->parent] = array();
				}
				else
				{
					$childDepartments[$department->parent] = array($department->child);
				}
			}
			else
			{
				$childDepartments[$department->parent][] = $department->child;
			}
		}

		foreach ($childDepartments as $chDepartments)
		{
			$chDepartments  = ArrayHelper::toInteger($chDepartments);
			$allDepartments = array_merge($allDepartments, $chDepartments);
		}

		$query->clear()
			->select(
				array (
					$db->qn('ru.department_id', 'key'),
					'COUNT(' . $db->qn('ru.id') . ') AS counter'
				)
			)
			->from($db->qn('#__redshopb_user', 'ru'))
			->where($db->qn('ru.department_id') . ' IN (' . implode(',', $allDepartments) . ')')
			->group($db->qn('ru.department_id'));
		$db->setQuery($query);

		$resultList = $db->loadAssocList('key', 'counter');
		$final      = array();

		foreach ($resultList as $key => $counter)
		{
			if (in_array($key, $departments))
			{
				if (!isset($final[$key]))
				{
					$final[$key] = $counter;
				}
				else
				{
					$final[$key] += $counter;
				}
			}

			if ($getDeeperCount)
			{
				foreach ($childDepartments as $cKey => $cKeyDepartments)
				{
					if (in_array($key, $cKeyDepartments))
					{
						if (!isset($final[$cKey]))
						{
							$final[$cKey] = $counter;
						}
						else
						{
							$final[$cKey] += $counter;
						}
					}
				}
			}
		}

		return $final;
	}

	/**
	 * Get employees list.
	 *
	 * @param   int  $departmentId  Department id.
	 *
	 * @return array List of employees under given department.
	 */
	public static function getEmployees($departmentId)
	{
		$departments = self::getChildDepartments($departmentId);
		$departments = array_unique(array_merge($departments, array($departmentId)));
		$db          = Factory::getDbo();
		$query       = $db->getQuery(true)
			->select('DISTINCT ' . $db->qn('id'))
			->from($db->qn('#__redshopb_user'))
			->where('department_id IN (' . implode(',', $departments) . ')');
		$db->setQuery($query);

		return $db->loadColumn();
	}

	/**
	 * Get child departments for given department.
	 *
	 * @param   int      $departmentId  Department id.
	 * @param   boolean  $hideDeleted   Hide deleted departments.
	 *
	 * @return mixed
	 */
	public static function getChildDepartments($departmentId = 0, $hideDeleted = true)
	{
		$app = Factory::getApplication();

		if ($departmentId <= 0)
		{
			$departmentId = $app->getUserStateFromRequest('list.department_id', 'department_id', 0, 'int');
		}

		if (!isset(self::$childDepartments[$departmentId]))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('d_node.id')
				->from($db->qn('#__redshopb_department', 'd_node'));

			if ($hideDeleted)
			{
				$query->leftJoin(
					$db->qn('#__redshopb_department', 'd_parent') . ' ON d_node.lft > d_parent.lft AND d_node.rgt < d_parent.rgt AND ' .
					$db->qn('d_parent.deleted') . ' = 0 AND ' . $db->qn('d_parent.state') . ' = 1'
				)
					->where($db->qn('d_node.deleted') . ' = 0');
			}
			else
			{
				$query->leftJoin($db->qn('#__redshopb_department', 'd_parent') . ' ON d_node.lft > d_parent.lft AND d_node.rgt < d_parent.rgt');
			}

			$query->where('d_parent.id = ' . (int) $departmentId)
				->where('d_node.level > 0');
			$db->setQuery($query);

			self::$childDepartments[$departmentId] = $db->loadColumn();
		}

		return self::$childDepartments[$departmentId];
	}

	/**
	 * Get department info.
	 *
	 * @param   int      $id           Department id.
	 * @param   boolean  $hideDeleted  Hide deleted departments.
	 *
	 * @return object Department object
	 */
	public static function getDepartmentById($id, $hideDeleted = true)
	{
		if ($id == 0)
		{
			return null;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select(
			array(
				$db->qn('d.id', 'id'),
				$db->qn('d.parent_id', 'parent'),
				$db->qn('d.level', 'level'),
				$db->qn('d.name', 'name'),
				$db->qn('d.name2', 'name2'),
				$db->qn('d.company_id', 'company_id'),
				$db->qn('d.requisition', 'requisition'),
				$db->qn('d.asset_id', 'asset_id'),
				$db->qn('a.name', 'addressName'),
				$db->qn('a.id', 'addressId'),
				$db->qn('a.address', 'address'),
				$db->qn('a.address2', 'address2'),
				$db->qn('a.city', 'city'),
				$db->qn('a.zip', 'zip'),
				$db->qn('cont.name', 'country'),
				$db->qn('state.name', 'state_name'),
				$db->qn('d.department_number', 'number')
			)
		)
			->from($db->qn('#__redshopb_department', 'd'))
			->leftJoin($db->qn('#__redshopb_address', 'a') . ' ON a.id = d.address_id')
			->leftJoin($db->qn('#__redshopb_country', 'cont') . ' ON cont.id = a.country_id')
			->leftJoin($db->qn('#__redshopb_state', 'state') . ' ON state.id = a.state_id')
			->where('d.id = ' . (int) $id);

		if ($hideDeleted)
		{
			$query->where($db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' = 1');
		}

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Returns list of customer departments.
	 *
	 * @param   int      $customerId    Customer id.
	 * @param   string   $customerType  Customer type.
	 * @param   boolean  $hideDeleted   Hide deleted departments.
	 * @param   boolean  $forShop       If it's an employee, defines if they need to see the accessible departments to shop on behalf of
	 *
	 * @return array List of customer departments.
	 */
	public static function getCustomerDepartments($customerId, $customerType, $hideDeleted = true, $forShop = false)
	{
		switch ($customerType)
		{
			case 'employee':
				$user = RedshopbHelperUser::getUser($customerId);

				$departments = explode(',',
					RedshopbHelperACL::listAvailableDepartments(
						$user->joomla_user_id, 'comma', 0, true, 0, '', ($forShop ? 'redshopb.order.impersonate' : 'redshopb.department.view')
					)
				);

				if ($forShop && !empty($user->department))
				{
					$departments = array_unique(array_merge($departments, Array($user->department)));
				}
				break;
			case 'department':
				$departments = array($customerId);
				$departments = array_merge($departments, self::getChildDepartments($customerId, $hideDeleted));
				break;
			case 'company':
				$company = RedshopbEntityCompany::getInstance(
					RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType)
				);

				$departments = $company->getDescendantDepartments()->ids();
				break;
			default:
				$departments = array();
		}

		return $departments;
	}

	/**
	 * Get image thumb html
	 *
	 * @param   int      $id             ID of manufacturer
	 * @param   boolean  $setDimensions  Set image dimensions?
	 * @param   int      $width          If setDimensions this will be used for width
	 * @param   int      $height         If setDimensions this will be used for height
	 *
	 * @return  string  Thumbnail html
	 */
	public static function getImageThumbHtml($id, $setDimensions = false, $width = 144, $height = 144)
	{
		if (!$id)
		{
			return false;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select(
			array(
				$db->qn('name'),
				$db->qn('image')
			)
		)
			->from($db->qn('#__redshopb_department'))
			->where($db->qn('id') . ' = ' . (int) $id);
		$image = $db->setQuery($query)->loadObject();

		if ($setDimensions)
		{
			if (empty($width) || empty($height))
			{
				// Thumbnail preparation
				$config = RedshopbEntityConfig::getInstance();
				$width  = empty($width) ? $config->getThumbnailWidth() : $width;
				$height = empty($height) ? $config->getThumbnailHeight() : $height;
			}
		}

		if (!empty($image))
		{
			$alt = $image->name;

			$image = RedshopbHelperThumbnail::originalToResize($image->image, $width, $height, 100, 0, 'departments');

			if ($image === false)
			{
				$thumb = RedshopbHelperMedia::drawDefaultImg($width, $height, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL'), '#999999', '#dfdfdf');
			}
			else
			{
				$thumb = HTMLHelper::_('image', $image, $alt);
			}
		}
		else
		{
			$thumb = RedshopbHelperMedia::drawDefaultImg($width, $height, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL'), '#999999', '#dfdfdf');
		}

		return $thumb;
	}

	/**
	 * Get department id for given shop customer.
	 *
	 * @param   int     $customerId    Customer id.
	 * @param   string  $customerType  Customer type.
	 *
	 * @return integer Customer type company.
	 */
	public static function getDepartmentIdByCustomer($customerId, $customerType)
	{
		switch ($customerType)
		{
			case 'employee':
				$departmentId = RedshopbHelperUser::getUserDepartmentId($customerId);
				break;
			case 'department':
				$departmentId = $customerId;
				break;
			default:
				$departmentId = 0;
		}

		return $departmentId;
	}

	/**
	 * Get department for given shop customer.
	 *
	 * @param   int     $customerId    Customer id.
	 * @param   string  $customerType  Customer type.
	 *
	 * @return object Customer type company.
	 */
	public static function getDepartmentByCustomer($customerId, $customerType)
	{
		$departmentId = self::getDepartmentIdByCustomer($customerId, $customerType);

		return self::getDepartmentById($departmentId);
	}
}
