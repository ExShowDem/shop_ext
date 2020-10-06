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
use Joomla\CMS\Pagination\Pagination;
/**
 * Taglist View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewTaglist extends RedshopbView
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
	 * @var string
	 */
	public $searchchar = '';

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$app     = Factory::getApplication();
		$session = Factory::getSession();

		$menuitem = $app->getMenu()->getActive();
		$params   = $menuitem->params;

		// Default value set for sidebar width
		$this->spanWidth = 2;

		$model = RedshopbModel::getAdminInstance('tags');

		if ($menuitem && $params->get('type', '') != '')
		{
			$model->setState('filter.type', $params->get('type'));
		}

		if ($app->input->get('reset_btn', '') != '')
		{
			$session->clear('searchchar');
			$model->setState('filter.search_char', '');
		}
		else
		{
			$this->searchchar = $app->input->get('searchchar', $session->get('searchchar', ''));

			$model->setState('filter.search_char', $this->searchchar);
			$session->set('searchchar', $this->searchchar);
		}

		if ($params->get('sidebar_width', '') != '')
		{
			$this->spanWidth = $params->get('sidebar_width');
		}

		$this->items                        = $model->getItems();
		$this->state                        = $model->getState();
		$this->pagination                   = $model->getPagination();
		$this->filterForm                   = $model->getForm();
		$this->activeFilters                = $model->getActiveFilters();
		$this->stoolsOptions['searchField'] = 'search_tags';
		$this->availableChars               = RedshopbHelperTag::getFirstCharProductAvailableTags($params->get('type'));
		RedshopbBrowser::getInstance(RedshopbBrowser::REDSHOPB_HISTORY)->browse();

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return Text::_('COM_REDSHOPB_TAG_LIST_TITLE');
	}
}
