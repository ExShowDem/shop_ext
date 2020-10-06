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
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
/**
 * User View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewUser extends RedshopbView
{
	/**
	 * @var  Form
	 */
	protected $form;

	/**
	 * @var  object
	 */
	protected $item;

	/**
	 * @var object User credit status
	 */
	protected $credit;

	/**
	 * @var object User Wallet
	 */
	protected $wallet = null;

	/**
	 * @var bool Check if user is new.
	 */
	protected $isNew;

	/**
	 * @var bool Check if user is activeted
	 */
	protected $isActive = true;

	/**
	 * @var bool Check if user is editing its own profile
	 */
	protected $isOwn = false;

	/**
	 * @var array  Array of joomla usergroups
	 */
	protected $jusergroups = array();

	/**
	 * @var  boolean
	 */
	protected $anyRequired = false;

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$model             = $this->getModel('User');
		$this->form        = $this->get('Form');
		$this->item        = $this->get('Item');
		$this->jusergroups = $model->getJoomlaUserGroups();
		$this->anyRequired = $model->areThereAnyRequiredFields();

		if (strlen((string) $this->item->activation) > 1)
		{
			$this->isActive = false;
		}

		if ($this->getLayout() == 'own')
		{
			$user      = Factory::getUser();
			$rsbUserId = RedshopbHelperUser::getUserRSid($user->id);

			if ($this->item->id != $rsbUserId)
			{
				$app = Factory::getApplication();
				$app->redirect(RedshopbRoute::_('index.php?option=com_redshopb&view=dashboard'));
				$app->close();
			}

			$this->isOwn = true;
		}

		if (!isset($this->item->id) || $this->item->id <= 0)
		{
			$this->isNew = true;
		}
		else
		{
			$this->isNew       = false;
			$departmentAssetId = RedshopbHelperUser::getUserDepartmentAssetId();

			if ($departmentAssetId)
			{
				// Checks if the user can manage the department wallets
				if (RedshopbHelperACL::getPermission('points', 'user', Array(), true, $departmentAssetId))
				{
					$this->wallet = RedshopbHelperWallet::getUserWallet($this->item->id, 'redshopb', true);
				}
			}
			else
			{
				$companyAssetId = RedshopbHelperUser::getUserCompanyAssetId();

				if ($companyAssetId)
				{
					// Checks if the user can manage the company wallets
					if (RedshopbHelperACL::getPermission('points', 'user', Array(), true, $companyAssetId))
					{
						$this->wallet = RedshopbHelperWallet::getUserWallet($this->item->id, 'redshopb', true);
					}
				}
				else
				{
					if (RedshopbHelperACL::isSuperAdmin())
					{
						$this->wallet = RedshopbHelperWallet::getUserWallet($this->item->id, 'redshopb', true);
					}
				}
			}
		}

		// Passwords fields are required when mail to user is set to No in joomla user plugin
		$userId = $this->form->getValue('id');

		if ($userId === 0)
		{
			$this->form->setFieldAttribute('password', 'required', 'true');
			$this->form->setFieldAttribute('password2', 'required', 'true');
		}

		$this->form->setValue('password', null);
		$this->form->setValue('password2',	null);

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		$title = Text::_('COM_REDSHOPB_USER_FORM_TITLE');
		$state = $this->isNew ? Text::_('JNEW') : Text::_('JEDIT');

		return $title . ' <small>' . $state . '</small>';
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$group = new RToolbarButtonGroup;

		if ($this->get('IsLockedByWebservice'))
		{
			$locked = RedshopbToolbarBuilder::createAlertButton('JTOOLBAR_APPLY', 'COM_REDSHOPB_BUTTON_ALERT_LOCKED_BY_WEBSERVICE', 'icon-lock');
			$group->addButton($locked);
		}
		else
		{
			if ($this->isOwn)
			{
				$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('user.save');
				$group->addButton($saveAndClose);
			}
			else
			{
				if (RedshopbHelperACL::getPermission('manage', 'user', Array('edit', 'edit.own'), true))
				{
					$save         = RToolbarBuilder::createSaveButton('user.apply');
					$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('user.save');

					$group->addButton($save)->addButton($saveAndClose);

					if (!$this->isActive)
					{
						$active = RToolbarBuilder::createStandardButton('user.activate', 'COM_REDSHOPB_USERS_ACTIVATE', '', 'icon-publish', false);
						$group->addButton($active);
					}
				}

				if (RedshopbHelperACL::getPermission('manage', 'user', Array('create'), true)
					&& (RedshopbHelperACL::getPermission('manage', 'company', Array('create'), true)
					|| RedshopbHelperACL::getPermission('manage', 'department', Array('create'), true)))
				{
					$saveAndNew = RToolbarBuilder::createSaveAndNewButton('user.save2new');

					$group->addButton($saveAndNew);
				}
			}
		}

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('user.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('user.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
