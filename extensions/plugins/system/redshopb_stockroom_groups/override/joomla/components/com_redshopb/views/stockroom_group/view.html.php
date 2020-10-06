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

/**
 * Stockroom Group View
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Views
 * @since       1.6
 */
class RedshopbViewStockroom_Group extends RedshopbView
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

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		$state = (empty($this->item->id)) ? Text::_('JNEW') : Text::_('JEDIT');

		return Text::_('COM_REDSHOPB_STOCKROOM_GROUP_DETAILS') . ' <small>' . $state . '</small>';
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$group = new RToolbarButtonGroup;

		$save         = RToolbarBuilder::createSaveButton('stockroom_group.apply');
		$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('stockroom_group.save');
		$saveAndNew   = RToolbarBuilder::createSaveAndNewButton('stockroom_group.save2new');

		$group->addButton($save)
			->addButton($saveAndClose)
			->addButton($saveAndNew);

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('stockroom_group.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('stockroom_group.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
