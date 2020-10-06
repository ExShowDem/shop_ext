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
 * collection View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewCollection extends RedshopbView
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
	 * @var  object
	 */
	protected $collectionProductItems;

	/**
	 * @var  object
	 */
	protected $collectionProducts;

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$layout                  = Factory::getApplication()->input->getCmd('layout', '');
		$model                   = $this->getModel();
		$this->state             = $this->get('State');
		$this->form              = $this->get('Form');
		$this->item              = $this->get('Item');
		$this->item->departments = $model->getDepartments($this->item->department_ids);
		$this->item->company     = $model->getCompany($this->item->company_id);
		$this->item->currency    = $model->getCurrency($this->item->currency_id);

		if ($layout == 'create_products')
		{
			$this->collectionProducts = RedshopbHelperCollection::getCollectionProducts($this->item->id);
		}
		elseif ($layout == 'create_product_items')
		{
			$this->collectionProductItems = $this->get('CollectionProductItems');
		}

		$companyId = RedshopbInput::getCompanyIdForm();

		if (!empty($companyId) && RedshopbHelperCompany::getDepartmentsCount($companyId) <= 0)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_DEPARTMENT_MISSING'), 'warning');
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
		$title = Text::_('COM_REDSHOPB_COLLECTION_FORM_TITLE');
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
		$group                  = new RToolbarButtonGroup;
		$collectionManagerGroup = new RToolbarButtonGroup;
		$user                   = Factory::getUser();

		// If we are in a create layout, we don't need the toolbar
		$layout = Factory::getApplication()->input->get('layout');

		if (false === strpos($layout, 'create'))
		{
			if (RedshopbHelperACL::getPermission('manage', 'collection', array('edit','edit.own'), true))
			{
				$save         = RToolbarBuilder::createSaveButton('collection.apply');
				$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('collection.save');

				$group->addButton($save)
					->addButton($saveAndClose);

				$newProduct = RToolbarBuilder::createStandardButton(
					'collection.addNewProduct',
					Text::_('COM_REDSHOPB_COLLECTION_ADD_EDIT_PRODUCT'),
					'btn-success',
					'icon-file-text-alt',
					false
				);
				$collectionManagerGroup->addButton($newProduct);

				if (RedshopbHelperACL::getPermission('manage', 'collection', array('create'), true))
				{
					$saveAndNew = RToolbarBuilder::createSaveAndNewButton('collection.save2new');
					$group->addButton($saveAndNew);
				}
			}
		}

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('collection.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('collection.cancel');

			if ($layout == 'create_products')
			{
				$group->addButton(
					RtoolbarBuilder::createStandardButton(
						'collection.createNext',
						Text::_('COM_REDSHOPB_COLLECTION_EDIT_VARIANTS'),
						'btn-success',
						'icon-file-text-alt',
						false
					)
				);
			}
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);
		$toolbar->addGroup($collectionManagerGroup);

		return $toolbar;
	}
}
