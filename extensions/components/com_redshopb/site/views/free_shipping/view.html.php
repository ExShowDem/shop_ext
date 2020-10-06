<?php
/**
 * @package     Redshopb.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

/**
 * Free_Shipping View.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbViewFree_Shipping extends RedshopbView
{
	/**
	 * @var  JForm
	 */
	protected $form;

	/**
	 * @var  object
	 */
	protected $item;

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		if (!RedshopbHelperACL::getPermission('manage', 'product'))
		{
			$app = Factory::getApplication();

			// Enqueue the redirect message
			$app->enqueueMessage(Text::_('COM_REDSHOPB_ACTION_FORBIDDEN'), 'error');

			// Execute the redirect
			$app->redirect(Route::_('index.php?Itemid=' . Factory::getApplication()->getMenu()->getDefault()->id, false));

			return;
		}

		$this->form = $this->get('Form');
		$this->item = $this->get('Item');

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		$isNew = (int) $this->item->id <= 0;
		$title = Text::_('COM_REDSHOPB_FREE_SHIPPING_TITLE');
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
		$group = new RToolbarButtonGroup;

		if (RedshopbHelperACL::getPermission('manage', 'product', Array('edit','edit.own'), false))
		{
			$save         = RToolbarBuilder::createSaveButton('free_shipping.apply');
			$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('free_shipping.save');

			$group->addButton($save)
				->addButton($saveAndClose);

			if (RedshopbHelperACL::getPermission('manage', 'product', Array('create'), false))
			{
				$saveAndNew = RToolbarBuilder::createSaveAndNewButton('free_shipping.save2new');
				$group->addButton($saveAndNew);
			}
		}

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('free_shipping.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('free_shipping.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
