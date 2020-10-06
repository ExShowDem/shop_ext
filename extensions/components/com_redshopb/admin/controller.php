<?php
/**
 * @package    Redshopb.Admin
 *
 * @copyright  Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Front controller.
 *
 * @package  Redshopb.Admin
 * @since    1.0
 */
class RedshopbController extends BaseController
{
	/**
	 * Typical view method for MVC based architecture
	 *
	 * This function is provide as a default implementation, in most cases
	 * you will need to override it in your own controllers.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link FilterInput::clean()}.
	 *
	 * @return  BaseController  A BaseController object to support chaining.
	 */
	public function display($cachable = false, $urlparams = array())
	{
		$input = Factory::getApplication()->input;
		$input->set('view', $input->get('view', 'dashboard'));
		$input->set('task', $input->get('task', 'display'));

		return parent::display($cachable, $urlparams);
	}
}
