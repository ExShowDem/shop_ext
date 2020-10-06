<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Pagination\Pagination;
/**
 * Layouts View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.13.0
 */
class RedshopbViewLayout_List extends RedshopbView
{
	/**
	 * @var  array
	 *
	 * @since   1.13.0
	 */
	public $items;

	/**
	 * @var  object
	 *
	 * @since   1.13.0
	 */
	public $state;

	/**
	 * @var  Pagination
	 *
	 * @since   1.13.0
	 */
	public $pagination;

	/**
	 * @var  Form
	 *
	 * @since   1.13.0
	 */
	public $filterForm;

	/**
	 * @var array
	 *
	 * @since   1.13.0
	 */
	public $activeFilters;

	/**
	 * @var array
	 *
	 * @since   1.13.0
	 */
	public $stoolsOptions = array();

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 *
	 * @since   1.13.0
	 */
	public function display($tpl = null)
	{
		$model = $this->getModel('Layout_List');

		$this->items                        = $model->getItems();
		$this->state                        = $model->getState();
		$this->pagination                   = $model->getPagination();
		$this->filterForm                   = $model->getForm();
		$this->activeFilters                = $model->getActiveFilters();
		$this->stoolsOptions['searchField'] = 'search_layout_list';

		parent::display($tpl);
	}
}
