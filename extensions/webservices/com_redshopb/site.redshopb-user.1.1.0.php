<?php
/**
 * @package     Webservices
 * @subpackage  Api
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;

/**
 * Api Helper class for overriding default methods
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Api Helper
 * @since       1.2
 */
class RApiHalHelperSiteRedshopbUser
{
	/**
	 * Method for update user
	 *
	 * @param   array  $data  Array of post data
	 *
	 * @return  integer|false record id | false
	 */
	public function updateWSHelper($data)
	{
		// Sets the new customer number if the erp_id is changed
		if (isset($data['erp_id']) && $data['erp_id'] != '')
		{
			$data['customer_number'] = $data['erp_id'];
		}

		/** @var   RedshopbModelUser   $model */
		$model = RModelAdmin::getInstance('User', 'RedshopbModel');

		$result = $model->updateWS($data);
		$this->displayErrors($model);

		return $result;
	}

	/**
	 * Method for validating user update data
	 *
	 * @param   array  $data  Array of post data
	 *
	 * @return  false|array
	 */
	public function validateUpdateWSHelper($data)
	{
		if (isset($data['erp_id']) && $data['erp_id'] != '')
		{
			$data['customer_number'] = $data['erp_id'];
		}

		/** @var   RedshopbModelUser   $model */
		$model = RModelAdmin::getInstance('User', 'RedshopbModel');

		$result = $model->validateUpdateWS($data);
		$this->displayErrors($model);

		return $result;
	}

	/**
	 * Method for create user
	 *
	 * @param   array  $data  Array of post data
	 *
	 * @return  integer|false  record id | false
	 */
	public function createWSHelper($data)
	{
		// Sets customer_number to the same value as the ERP id to keep it as a visible reference in the views
		if (isset($data['id']))
		{
			$data['employee_number'] = $data['id'];
		}

		/** @var   RedshopbModelUser   $model */
		$model = RModelAdmin::getInstance('User', 'RedshopbModel');

		$result = $model->createWS($data);
		$this->displayErrors($model);

		return $result;
	}

	/**
	 * Method for validating user create data
	 *
	 * @param   array  $data  Array of post data
	 *
	 * @return  false|array
	 */
	public function validateCreateWSHelper($data)
	{
		// Sets customer_number to the same value as the ERP id to keep it as a visible reference in the views
		if (isset($data['id']))
		{
			$data['employee_number'] = $data['id'];
		}

		/** @var   RedshopbModelUser   $model */
		$model = RModelAdmin::getInstance('User', 'RedshopbModel');

		$result = $model->validateCreateWS($data);
		$this->displayErrors($model);

		return $result;
	}

	/**
	 * Gets errors from model and places it into Application message queue
	 *
	 * @param   RedshopbModelUser $model Model
	 *
	 * @throws Exception  If Factory::getApplication() fails
	 *
	 * @return void
	 */
	public function displayErrors($model)
	{
		if (method_exists($model, 'getErrors'))
		{
			$app = Factory::getApplication();

			// Get the validation messages.
			$errors = $model->getErrors();
			$n      = count($errors);

			// Push up all validation messages out to the user.
			for ($i = 0; $i < $n; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'error');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'error');
				}
			}
		}
	}
}
