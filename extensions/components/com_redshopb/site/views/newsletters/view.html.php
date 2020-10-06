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
use Joomla\CMS\Pagination\Pagination;
/**
 * Newsletters View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.6
 */
class RedshopbViewNewsletters extends RedshopbView
{
	/**
	 * @var  array
	 */
	public $items;

	/**
	 * @var  object
	 */
	public $state;

	/**
	 * @var  Pagination
	 */
	public $pagination;

	/**
	 * @var  Form
	 */
	public $filterForm;

	/**
	 * @var array
	 */
	public $activeFilters;

	/**
	 * @var array
	 */
	public $stoolsOptions = array();

	/**
	 * Do we have to display a sidebar?
	 *
	 * @var  boolean
	 */
	protected $displaySidebar = false;

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$model = $this->getModel('Newsletters');

		$this->items         = $model->getItems();
		$this->state         = $model->getState();
		$this->pagination    = $model->getPagination();
		$this->filterForm    = $model->getForm();
		$this->activeFilters = $model->getActiveFilters();

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
		return Text::_('COM_REDSHOPB_NEWSLETTERS_VIEW_DEFAULT_TITLE');
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$firstGroup  = new RToolbarButtonGroup;
		$secondGroup = new RToolbarButtonGroup;

		// Add / edit
		// @TODO: Need add ACL permission for Newsletter
		if (RedshopbHelperACL::getPermission('manage', 'mainwarehouse', Array('create'), true))
		{
			$new = RToolbarBuilder::createNewButton('newsletter.add');
			$firstGroup->addButton($new);
		}

		if (RedshopbHelperACL::getPermission('manage', 'mainwarehouse', Array('edit'), true))
		{
			$edit = RToolbarBuilder::createEditButton('newsletter.edit');
			$firstGroup->addButton($edit);
		}

		// Publish / Unpublish
		if (RedshopbHelperACL::getPermission('manage', 'mainwarehouse', Array('edit.state'), true))
		{
			$publish   = RToolbarBuilder::createPublishButton('newsletters.publish');
			$unpublish = RToolbarBuilder::createUnpublishButton('newsletters.unpublish');

			$firstGroup->addButton($publish)->addButton($unpublish);
		}

		// Delete / Trash
		if (RedshopbHelperACL::getPermission('manage', 'mainwarehouse', Array('delete'), true))
		{
			$delete = RToolbarBuilder::createStandardButton('newsletters.delete', 'JTOOLBAR_DELETE', 'btn-danger', 'icon-trash');
			$secondGroup->addButton($delete);
		}

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup)
			->addGroup($secondGroup);

		return $toolbar;
	}
}
