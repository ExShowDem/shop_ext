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
use Joomla\CMS\Router\Route;
/**
 * Holiday View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewHoliday extends RedshopbView
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
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		if (!RedshopbHelperACL::getPermission('manage', 'mainwarehouse'))
		{
			$app = Factory::getApplication();

			// Enqueue the redirect message
			$app->enqueueMessage(Text::_('COM_REDSHOPB_ACTION_FORBIDDEN'), 'error');

			// Execute the redirect
			$app->redirect(Route::_('index.php?Itemid=' . Factory::getApplication()->getMenu()->getDefault()->id, false));

			return;
		}

		$this->form	= $this->get('Form');
		$this->item	= $this->get('Item');

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
		$title = Text::_('COM_REDSHOPB_HOLIDAY_FORM_TITLE');
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
			$save         = RToolbarBuilder::createSaveButton('holiday.apply');
			$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('holiday.save');

			$group->addButton($save)
				->addButton($saveAndClose);

			if (RedshopbHelperACL::getPermission('manage', 'product', Array('create'), false))
			{
				$saveAndNew = RToolbarBuilder::createSaveAndNewButton('holiday.save2new');
				$group->addButton($saveAndNew);
			}
		}

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('holiday.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('holiday.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
