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
use Joomla\CMS\Form\Form;

/**
 * Category View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewCategory extends RedshopbView
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
	 * @var  object
	 */
	protected $state;

	/**
	 * @var  array
	 */
	protected $fields;

	/**
	 * @var  array
	 */
	protected $unassociatedFields;

	/**
	 * @var  string
	 */
	protected $syncReference;

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
		$model = $this->getModel('Category');

		$this->form	              = $model->getForm();
		$this->item	              = $model->getItem();
		$this->fields             = $model->getFields($this->item->id);
		$this->unassociatedFields = $model->getUnassociatedFields($this->item->id);
		$this->state              = $model->getState();
		$this->anyRequired        = $model->areThereAnyRequiredFields();

		// Gets sync reference
		$this->syncReference = RedshopbHelperSync::getEnrichmentBase($model);

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
		$title = Text::_('COM_REDSHOPB_CATEGORY_FORM_TITLE');
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

		if (RedshopbHelperACL::getPermission('manage', 'category', array('edit','edit.own'), true))
		{
			if ($this->get('IsLockedByWebservice'))
			{
				$locked = RedshopbToolbarBuilder::createAlertButton('JTOOLBAR_APPLY', 'COM_REDSHOPB_BUTTON_ALERT_LOCKED_BY_WEBSERVICE', 'icon-lock');
				$group->addButton($locked);
			}
			else
			{
				$save         = RToolbarBuilder::createSaveButton('category.apply');
				$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('category.save');

				$group->addButton($save)
					->addButton($saveAndClose);

				if (RedshopbHelperACL::getPermission('manage', 'category', array('create'), true))
				{
					$saveAndNew = RToolbarBuilder::createSaveAndNewButton('category.save2new');

					$group->addButton($saveAndNew);
				}
			}
		}

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('category.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('category.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
