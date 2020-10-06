<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;


use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
/**
 * ACL Role Type Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.6.107
 */
class RedshopbControllerACLRoleType extends RedshopbControllerForm
{
	/**
	 * Method for get detail information of give role type id
	 *
	 * @return  void
	 */
	public function ajaxGetRole()
	{
		$app = Factory::getApplication();

		if (!Session::checkToken())
		{
			$app->setHeader('status', 500);
			$app->sendHeaders();
			echo Text::_('JINVALID_TOKEN');
			$app->close();
		}

		$id   = $app->input->getInt('id', 0);
		$data = $this->getModel()->getItem($id);

		$data->allowed_rules              = (!is_null($data->allowed_rules)) ? json_decode($data->allowed_rules) : null;
		$data->allowed_rules_main_company = (!is_null($data->allowed_rules_main_company)) ? json_decode($data->allowed_rules_main_company) : null;
		$data->allowed_rules_customers    = (!is_null($data->allowed_rules_customers)) ? json_decode($data->allowed_rules_customers) : null;
		$data->allowed_rules_company      = (!is_null($data->allowed_rules_company)) ? json_decode($data->allowed_rules_company) : null;
		$data->allowed_rules_own_company  = (!is_null($data->allowed_rules_own_company)) ? json_decode($data->allowed_rules_own_company) : null;
		$data->allowed_rules_department   = (!is_null($data->allowed_rules_department)) ? json_decode($data->allowed_rules_department) : null;

		$app->sendHeaders();
		echo json_encode($data);
		$app->close();
	}

	/**
	 * Method for get detail information of give role type id
	 *
	 * @return  void
	 */
	public function ajaxSaveRole()
	{
		$app = Factory::getApplication();

		if (!Session::checkToken())
		{
			$app->setHeader('status', 500);
			$app->sendHeaders();
			echo Text::_('JINVALID_TOKEN');
			$app->close();
		}

		$allowedRules = $app->input->get('allowed_rules', null, 'array');
		$allowedRules = !is_null($allowedRules) ? json_encode(array_unique($allowedRules)) : '';

		$allowedRulesMainCompany = $app->input->get('allowed_rules_main_company', null, 'array');
		$allowedRulesMainCompany = !is_null($allowedRulesMainCompany) ? json_encode(array_unique($allowedRulesMainCompany)) : '';

		$allowedRulesCustomers = $app->input->get('allowed_rules_customers', null, 'array');
		$allowedRulesCustomers = !is_null($allowedRulesCustomers) ? json_encode(array_unique($allowedRulesCustomers)) : '';

		$allowedRulesCompany = $app->input->get('allowed_rules_company', null, 'array');
		$allowedRulesCompany = !is_null($allowedRulesCompany) ? json_encode(array_unique($allowedRulesCompany)) : '';

		$allowedRulesDepartment = $app->input->get('allowed_rules_department', null, 'array');
		$allowedRulesDepartment = !is_null($allowedRulesDepartment) ? json_encode(array_unique($allowedRulesDepartment)) : '';

		$allowedRulesOwnCompany = $app->input->get('allowed_rules_own_company', null, 'array');
		$allowedRulesOwnCompany = !is_null($allowedRulesOwnCompany) ? json_encode(array_unique($allowedRulesOwnCompany)) : '';

		$data = array(
			'id'                         => $app->input->getInt('id', 0),
			'name'                       => $app->input->getString('name', ''),
			'hidden'                     => $app->input->getInt('hidden', 0),
			'allowed_rules'              => $allowedRules,
			'allowed_rules_main_company' => $allowedRulesMainCompany,
			'allowed_rules_customers'    => $allowedRulesCustomers,
			'allowed_rules_company'      => $allowedRulesCompany,
			'allowed_rules_department'   => $allowedRulesDepartment,
			'allowed_rules_own_company'  => $allowedRulesOwnCompany
		);

		if (!$this->getModel()->save($data))
		{
			$app->setHeader('status', 422);
			$app->sendHeaders();
			echo Text::_('COM_REDSHOPB_ACL_ROLETYPE_SAVE_ERROR');
			$app->close();
		}

		$app->sendHeaders();
		echo Text::_('COM_REDSHOPB_ACL_ROLETYPE_SAVE_SUCCESS');
		$app->close();
	}
}
