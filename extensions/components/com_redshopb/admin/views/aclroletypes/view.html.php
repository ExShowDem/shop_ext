<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * ACL Role Types View
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Views
 * @since       1.6.107
 */
class RedshopbViewACLRoleTypes extends RedshopbViewAdmin
{
	/**
	 * Do we have to display a sidebar ?
	 *
	 * @var  boolean
	 */
	protected $displaySidebar = false;

	/**
	 * @var  array
	 */
	public $items;

	/**
	 * @var  array
	 */
	public $companyPermissions;

	/**
	 * @var array
	 */
	public $stoolsOptions = array();

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->items       = $this->get('Items');
		$this->permissions = $this->get('AllAccess');

		foreach ($this->permissions as $index => $permission)
		{
			$permission->group = explode('.', $permission->name);
			$permission->group = ucfirst($permission->group[1]);

			if ($permission->section == 'company')
			{
				$this->companyPermissions[] = $permission;
				unset($this->permissions[$index]);
			}
		}

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return Text::_('COM_REDSHOPB_ACL_TITLE');
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		return null;
	}
}
