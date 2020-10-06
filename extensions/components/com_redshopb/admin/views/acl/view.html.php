<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;

/**
 * ACL View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewACL extends RedshopbViewAdmin
{
	/**
	 * @var  Form
	 */
	protected $form;

	/**
	 * Do we have to display a sidebar ?
	 *
	 * @var  boolean
	 */
	protected $displaySidebar = false;

	/**
	 * @var  array
	 */
	public $companies;

	/**
	 * @var  array
	 */
	public $departments;

	/**
	 * @var  object
	 */
	public $asset;

	/**
	 * @var  boolean
	 */
	public $rebuildACLBase = false;

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		/** @var RedshopbModelACL $model */
		$model = $this->getModel('ACL');

		$this->form        = $this->get('Form');
		$this->companies   = $model->getAllCompanies();
		$this->departments = $model->getAllDepartments();
		$this->asset       = Table::getInstance('asset');
		$this->asset->loadByName('com_redshopb');

		$app                  = Factory::getApplication();
		$this->rebuildACLBase = (bool) $app->getUserState('redshopb.rebuildACLBase', 0);
		$app->setUserState('redshopb.rebuildACLBase', 0);

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return Text::_('COM_REDSHOPB_ACL_REBUILD_TITLE');
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$group = new RToolbarButtonGroup;

		$rebuildACL = RToolbarBuilder::createStandardButton('acl.rebuildACL', 'COM_REDSHOPB_ACL_REBUILD', '', 'icon-refresh', false);
		$group->addButton($rebuildACL);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
