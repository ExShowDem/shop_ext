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
use Joomla\CMS\Plugin\PluginHelper;
/**
 * Newsletter List View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewNewsletter_List extends RedshopbView
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
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');

		Text::script('COM_REDSHOPB_NEWSLETTER_LIST_ERROR_LOAD_RECIPIENTS');

		if (!PluginHelper::isEnabled('redshopb_newsletter'))
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_NEWSLETTER_MAIL_ENGINE_NOT_FOUND'), 'warning');
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
		$isNew = (int) $this->item->id <= 0;
		$title = Text::_('COM_REDSHOPB_NEWSLETTER_LIST_FORM');
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
		$save         = RToolbarBuilder::createSaveButton('newsletter_list.apply');
		$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('newsletter_list.save');
		$saveAndNew   = RToolbarBuilder::createSaveAndNewButton('newsletter_list.save2new');

		$group->addButton($save)
			->addButton($saveAndClose)
			->addButton($saveAndNew);

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('newsletter_list.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('newsletter_list.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
