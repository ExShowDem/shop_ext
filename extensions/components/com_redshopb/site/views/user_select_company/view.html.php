<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
/**
 * User Select Company View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewUser_Select_Company extends RedshopbView
{
	/**
	 * @var  object
	 */
	protected $userCompanies;

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$user                = RedshopbEntityUser::loadFromJoomlaUser();
		$this->userCompanies = $user->getUserMultiCompanies();

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return Text::_('COM_REDSHOPB_USER_SELECT_COMPANY_FORM_TITLE');
	}
}
