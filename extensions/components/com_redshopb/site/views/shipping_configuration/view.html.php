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
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
/**
 * Shipping Configuration View
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewShipping_Configuration extends RedshopbView
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
		$this->form	= $this->get('Form');
		$this->item	= $this->get('Item');

		// Load redCORE form field for shipping plugins
		FormHelper::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_redcore/models/fields');

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
		$title = Text::_('COM_REDSHOPB_SHIPPING_CONFIGURATION');
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

		if (RedshopbHelperACL::getPermission('manage', 'product', array('edit', 'edit.own'), true))
		{
			if ($this->get('IsLockedByWebservice'))
			{
				$locked = RedshopbToolbarBuilder::createAlertButton('JTOOLBAR_APPLY', 'COM_REDSHOPB_BUTTON_ALERT_LOCKED_BY_WEBSERVICE', 'icon-lock');
				$group->addButton($locked);
			}
			else
			{
				$save         = RToolbarBuilder::createSaveButton('shipping_configuration.apply');
				$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('shipping_configuration.save');

				$group->addButton($save)
					->addButton($saveAndClose);

				if (RedshopbHelperACL::getPermission('manage', 'product', array('create'), true))
				{
					$saveAndNew = RToolbarBuilder::createSaveAndNewButton('shipping_configuration.save2new');

					$group->addButton($saveAndNew);
				}
			}
		}

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('shipping_configuration.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('shipping_configuration.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
