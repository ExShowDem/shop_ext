<?php
/**
 * @package     Vanir.Library
 * @subpackage  Access
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

namespace Vanir\Access\Rules;


defined('_JEXEC') or die;

use Joomla\CMS\Input\Input;

/**
 * Interface RulesInterface
 *
 * @package  Vanir\Access\Rules
 * @since    2.0
 */
interface RulesInterface
{
	/**
	 * Method to grant or deny permission to the user
	 *
	 * @param   \RedshopbEntityUser  $user   the user asking for permission
	 * @param   Input                $input  input variables to be used by the rules
	 *
	 * @return boolean
	 */
	public function grant($user, $input);

	/**
	 * Method to build a chain of rules
	 *
	 * @param   array  $rules  FIFO array of rules to apply
	 *
	 * @return void
	 */
	public function buildChain($rules = array());
}
