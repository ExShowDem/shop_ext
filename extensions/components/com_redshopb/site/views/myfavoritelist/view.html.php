<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
/**
 * Tag View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewMyfavoriteList extends RedshopbView
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
	 * Array of products
	 *
	 * @var  array
	 */
	protected $products = array();

	/**
	 * @var boolean
	 */
	protected $isManage = false;

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$layout     = $this->getLayout();
		$app        = Factory::getApplication();
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$isNew      = $app->input->getInt('id', 0) ? false : true;
		$canEdit    = false;
		HTMLHelper::script('com_redshopb/redshopb.shop.js', array('framework' => false, 'relative' => true));
		RedshopbHelperCommon::initCartScript();

		switch ($layout)
		{
			case 'item':
				if (empty($this->item->id))
				{
					$id = Factory::getApplication()->input->get('id', 0);
					$app->enqueueMessage(Text::sprintf('COM_REDSHOPB_MYFAVORITELIST_ERROR_NO_RECORD_FOUND', $id), 'error');
					$app->redirect(RedshopbRoute::_('index.php?option=com_redshopb&view=myfavoritelists', false));
				}

				$myFavoriteProductsModel = $this->getMyFavoriteListProductModel($this->item->id);
				$this->products          = $myFavoriteProductsModel->getItems();

			break;
			case 'edit':

				if (empty($this->item))
				{
					$app->enqueueMessage(Text::_('COM_REDSHOPB_MYFAVORITELIST_ERROR_NOT_HAVE_PERMISSION_FOR_EDIT'), 'error');
					$app->redirect(RedshopbRoute::_('index.php?option=com_redshopb&view=myfavoritelists', false));
				}

				$myFavoriteProductsModel = $this->getMyFavoriteListProductModel($this->item->id);

				if ($myFavoriteProductsModel->removeNotAvailableProducts())
				{
					$app->enqueueMessage(Text::_('COM_REDSHOPB_MYFAVORITELIST_WARNING_SOME_PRODUCTS_ARE_NOT_AVAILABLE'), 'warning');
				}

				$this->products = $myFavoriteProductsModel->getItems();

				// If current user is Super Admin
				if (RedshopbHelperACL::isSuperAdmin())
				{
					$this->isManage = true;
					$canEdit        = true;
				}
				// Make sure current user has same company with owner of favourite list
				elseif (RedshopbHelperUser::getUserCompanyId() == $this->item->company_id)
				{
					// If current user is Administrator of this company
					if (RedshopbHelperACL::getPermission('manage', 'company'))
					{
						$this->isManage = true;
						$canEdit        = true;
					}
					// If current user is Head of Department of this department
					elseif (RedshopbHelperACL::getPermission('manage', 'department')
						&& (RedshopbHelperUser::getUserDepartmentId() == $this->item->department_id || is_null($this->item->department_id)))
					{
						$this->isManage = true;
						$canEdit        = true;
					}
					else
					{
						$user = RedshopbHelperUser::getUser(Factory::getUser()->id, 'joomla');

						// Check if current user is B2B user / Favourite list exist / And this user is owner
						if ($user && $this->item->user_id == $user->id)
						{
							$canEdit = true;
						}
					}
				}

				if (!$canEdit && !$isNew)
				{
					$app->enqueueMessage(Text::_('COM_REDSHOPB_MYFAVORITELIST_ERROR_NOT_HAVE_PERMISSION_FOR_EDIT'), 'error');
					$app->redirect(RedshopbRoute::_('index.php?option=com_redshopb&view=myfavoritelists', false));
				}

				break;

			case 'addproduct':
				$this->favlistId = $app->input->get('id', '', 'int');
				break;

			default:
				break;
		}

		parent::display($tpl);
	}

	/**
	 * Method to get the favorite list products model
	 *
	 * @param   int   $itemId  favorite list id
	 *
	 * @return RedshopbModelMyfavoriteproducts
	 */
	private function getMyFavoriteListProductModel($itemId = 0)
	{
		/** @var RedshopbModelMyfavoriteproducts $myFavoriteProductsModel */
		$myFavoriteProductsModel = RedshopbModel::getFrontInstance('Myfavoriteproducts', array('ignore_request' => true));

		// Prepare the model state
		$myFavoriteProductsModel->setState('filter.favorite_list_id_exclude', false);
		$myFavoriteProductsModel->setState('filter.favorite_list_id', $itemId);
		$myFavoriteProductsModel->setState('disable_user_states', true);
		$myFavoriteProductsModel->setState('filter.include_available_collections', true);

		return $myFavoriteProductsModel;
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		$isNew = (int) $this->item->id <= 0;
		$title = Text::_('COM_REDSHOPB_MYFAVORITELIST_FORM');
		$state = $isNew ? Text::_('JNEW') : Text::_('JEDIT');

		return $title . ' <small>' . $state . '</small>';
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$group        = new RToolbarButtonGroup;
		$save         = RToolbarBuilder::createSaveButton('myfavoritelist.apply');
		$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('myfavoritelist.save');
		$saveAndNew   = RToolbarBuilder::createSaveAndNewButton('myfavoritelist.save2new');
		$layout       = Factory::getApplication()->input->get('layout');
		$toolbar      = new RToolbar;

		if ($layout == 'edit')
		{
			$group->addButton($save)
				->addButton($saveAndClose)
				->addButton($saveAndNew);

			if (empty($this->item->id))
			{
				$group->addButton(RToolbarBuilder::createCancelButton('myfavoritelist.cancel'));
				$toolbar->addGroup($group);
			}
			else
			{
				$group->addButton(RToolbarBuilder::createCloseButton('myfavoritelist.cancel'));
				$deleteGroup = new RToolbarButtonGroup('pull-right');

				$delUrl  = RedshopbRoute::_('index.php?option=com_redshopb&task=myfavoritelists.delete&id=' . $this->item->id);
				$delText = Text::_('COM_REDSHOPB_MYFAVORITELIST_DELETE_MYFAVORITE_LIST');

				$deleteButton = RToolbarBuilder::createLinkButton($delUrl, $delText, 'icon-trash');

				$deleteGroup->addButton($deleteButton);
				$toolbar->addGroup($group);
				$toolbar->addGroup($deleteGroup);
			}
		}

		$app          = Factory::getApplication();
		$customerId   = $app->getUserState('shop.customer_id', 0);
		$customerType = $app->getUserState('shop.customer_type', '');

		if ($customerId && $customerType)
		{
			$group2   = new RToolbarButtonGroup('pull-right clear btn-toolbar');
			$checkout = RToolbarBuilder::createStandardButton(
				'myfavoritelist.checkout', Text::_('COM_REDSHOPB_MYFAVORITELIST_CHECK_OUT'),
				'btn-checkout-myfavoritelist', '', false
			);
			$group2->addButton($checkout);
			$toolbar->addGroup($group2);
		}

		$group3 = new RToolbarButtonGroup('pull-right clear btn-toolbar');
		$toPdf  = RToolbarBuilder::createStandardButton(
			'myfavoritelist.toPdf', Text::_('COM_REDSHOPB_MYFAVORITELIST_TO_PDF'),
			'btn-to-pdf-myfavoritelist', '', false
		);

		$group3->addButton($toPdf);
		$toolbar->addGroup($group3);

		return $toolbar;
	}
}
