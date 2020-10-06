<?php
/**
 * @package     Webservices
 * @subpackage  Api
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Api Helper class for overriding default methods
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Api Helper
 * @since       1.2
 */
class RApiHalHelperSiteRedshopbDepartment
{
	/**
	 * Method for update department
	 *
	 * @param   array  $data  Array of post data
	 *
	 * @return  integer|false
	 */
	public function updateWSHelper($data)
	{
		// Sets the new department number if the erp_id is changed
		if (isset($data['erp_id']) && $data['erp_id'] != '')
		{
			$data['department_number'] = $data['erp_id'];
		}

		/** @var   RedshopbModelDepartment   $model */
		$model = RModelAdmin::getInstance('Department', 'RedshopbModel');

		$result = $model->updateWS($data);

		return $result;
	}

	/**
	 * Method for validating department update data
	 *
	 * @param   array  $data  Array of post data
	 *
	 * @return  array|boolean
	 */
	public function validateUpdateWSHelper($data)
	{
		// Sets the new department number if the erp_id is changed
		if (isset($data['erp_id']) && $data['erp_id'] != '')
		{
			$data['department_number'] = $data['erp_id'];
		}

		/** @var   RedshopbModelDepartment   $model */
		$model = RModelAdmin::getInstance('Department', 'RedshopbModel');

		$result = $model->validateUpdateWS($data);

		return $result;
	}

	/**
	 * Method for create department
	 *
	 * @param   array  $data  Array of post data
	 *
	 * @return  integer|false
	 */
	public function createWSHelper($data)
	{
		// Sets department_number to the same value as the ERP id to keep it as a visible reference in the views
		if (isset($data['id']))
		{
			$data['department_number'] = $data['id'];
		}

		/** @var   RedshopbModelDepartment   $model */
		$model = RModelAdmin::getInstance('Department', 'RedshopbModel');

		$result = $model->createWS($data);

		return $result;
	}

	/**
	 * Method for validating department create data
	 *
	 * @param   array  $data  Array of post data
	 *
	 * @return  false|array
	 */
	public function validateCreateWSHelper($data)
	{
		// Sets department_number to the same value as the ERP id to keep it as a visible reference in the views
		if (isset($data['id']))
		{
			$data['department_number'] = $data['id'];
		}

		/** @var   RedshopbModelDepartment   $model */
		$model = RModelAdmin::getInstance('Department', 'RedshopbModel');

		$result = $model->validateCreateWS($data);

		return $result;
	}
}
