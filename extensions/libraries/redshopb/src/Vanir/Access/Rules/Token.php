<?php
/**
 * @package     Vanir.Library
 * @subpackage  Access
 *
 * @copyright   Copyright (C) 2012 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

namespace Vanir\Access\Rules;

defined('_JEXEC') or die;

/**
 * Class Token
 *
 * @package  Vanir\Access\Rules
 * @since    2.0
 */
abstract class Token extends Rule
{
	/**
	 * Method to preform the rule specific checks on the user
	 *
	 * @param   \RedshopbEntityUser  $user   The user asking for permission
	 * @param   \JInput              $input  Input variables to be used by the rules
	 *
	 * @return boolean
	 */
	public function check($user, $input)
	{
		$scope  = $this->getLayoutScope();
		$layout = $input->get('layout', 'default');

		// Only check the token if the layout is in scope
		if (empty($scope) || !in_array($layout, $scope))
		{
			return true;
		}

		return $this->matchToken($user, $input);
	}

	/**
	 * Validates the token in the query string
	 *
	 * @param   \RedshopbEntityUser  $user   The user asking for permission
	 * @param   \JInput              $input  Input variables to be used by the rules
	 *
	 * @return   boolean
	 */
	abstract protected function matchToken(\RedshopbEntityUser $user,\JInput $input);
}
