<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;

/**
 * Fee View
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewFee extends RedshopbViewAdmin
{
	/**
	 * Do we have to display a sidebar ?
	 *
	 * @var  boolean
	 */
	protected $displaySidebar = false;

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
		$title = Text::_('COM_REDSHOPB_FEE_DETAILS');
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

		$save         = RToolbarBuilder::createSaveButton('fee.apply');
		$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('fee.save');
		$saveAndNew   = RToolbarBuilder::createSaveAndNewButton('fee.save2new');

		$group->addButton($save)
			->addButton($saveAndClose)
			->addButton($saveAndNew);

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('fee.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('fee.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
