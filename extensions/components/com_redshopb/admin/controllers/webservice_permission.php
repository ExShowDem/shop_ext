<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Webservice Permission Controller
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerWebservice_Permission extends RedshopbControllerForm
{
	/**
	 * Ajax call to get departments based on selected Company.
	 *
	 * @return  void
	 */
	public function ajaxGetScopeItems()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app   = Factory::getApplication();
		$model = $this->getModel('Webservice_Permission', 'RedshopbModel', array('ignore_request' => false));
		$input = $app->input;

		$webservicePermissionId = $input->getInt('webservice_permission_id', 0);
		$scope                  = $input->getString('scope', 'product');

		$permission = $model->getPermissionScopeItems($scope, $webservicePermissionId);

		echo RedshopbLayoutHelper::render(
			'permissions.options',
			array(
				'permission' => $permission,
				'scope' => $scope,
				'webservicePermissionId' => $webservicePermissionId,
			)
		);

		$app->close();
	}
}
