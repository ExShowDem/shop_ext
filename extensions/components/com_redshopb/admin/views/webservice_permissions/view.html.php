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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Pagination\Pagination;

/**
 * Webservice permissions View
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewWebservice_Permissions extends RedshopbViewAdmin
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
	 * @var  object
	 */
	public $state;

	/**
	 * @var  Pagination
	 */
	public $pagination;

	/**
	 * @var  Form
	 */
	public $filterForm;

	/**
	 * @var array
	 */
	public $activeFilters;

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
		$model = $this->getModel();

		$this->items         = $model->getItems();
		$this->state         = $model->getState();
		$this->pagination    = $model->getPagination();
		$this->filterForm    = $model->getForm();
		$this->activeFilters = $model->getActiveFilters();

		// Check if option is enabled
		if (RedshopbApp::getConfig()->get('use_webservice_permission_restriction', 0) == 0)
		{
			$return = base64_encode('index.php?option=com_redshopb&view=webservice_permissions');
			Factory::getApplication()->enqueueMessage(
				Text::sprintf(
					'COM_REDSHOPB_WEBSERVICE_PERMISSIONS_NOT_ENABLED_TITLE',
					'<a href="index.php?option=com_redcore&view=config&layout=edit&component=com_redshopb&return=' . $return . '">'
					. Text::_('COM_REDSHOPB_CONFIG_FORM_TITLE')
					. '</a>'
				),
				'warning'
			);
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
		return Text::_('COM_REDSHOPB_WEBSERVICE_PERMISSIONS_TITLE');
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$user = Factory::getUser();

		$firstGroup  = new RToolbarButtonGroup;
		$secondGroup = new RToolbarButtonGroup;
		$thirdGroup  = new RToolbarButtonGroup;

		if ($user->authorise('core.create', 'com_redshopb'))
		{
			$new = RToolbarBuilder::createNewButton('webservice_permission.add');
			$firstGroup->addButton($new);
		}

		if ($user->authorise('core.edit', 'com_redshopb'))
		{
			$edit = RToolbarBuilder::createEditButton('webservice_permission.edit');
			$firstGroup->addButton($edit);
		}

		if ($user->authorise('core.delete', 'com_redshopb'))
		{
			$delete = RToolbarBuilder::createStandardButton('webservice_permissions.delete', 'JTOOLBAR_DELETE', 'btn-danger', 'icon-trash');
			$secondGroup->addButton($delete);
		}

		$users = RToolbarBuilder::createLinkButton(
			'index.php?option=com_redshopb&view=webservice_permission_users',
			'COM_REDSHOPB_WEBSERVICE_PERMISSION_ADD_NEW_USERS_TITLE',
			'icon-user',
			'btn-primary'
		);
		$thirdGroup->addButton($users);

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup)
			->addGroup($secondGroup)
			->addGroup($thirdGroup);

		return $toolbar;
	}
}
