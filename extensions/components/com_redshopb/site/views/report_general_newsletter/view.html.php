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
use Joomla\CMS\Pagination\Pagination;

/**
 * Report General Newsletter View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewReport_General_Newsletter extends RedshopbView
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
	 * @var  array
	 */
	public $chartData = array();

	/**
	 * @var  string
	 */
	public $chartType = 'Line';

	/**
	 * @var  string
	 */
	public $viewType;

	/**
	 * @var  array
	 */
	public $tableColumns = array();

	/**
	 * @var  object
	 */
	public $loadedModel = array();

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->loadedModel = $this->getModel('report_general_newsletter');

		$this->items         = $this->loadedModel->getItems();
		$this->pagination    = $this->loadedModel->getPagination();
		$this->filterForm    = $this->loadedModel->getForm();
		$this->activeFilters = $this->loadedModel->getActiveFilters();

		$this->tableColumns = $this->loadedModel->getReportTableColumns($this->loadedModel->getState('filter.show_extended', 0));

		$this->chartType = $this->loadedModel->getState('chart.report_chart_type', 'Pie');
		$this->viewType  = $this->loadedModel->getState('chart.report_view_type', 'number_of_users');
		$this->chartData = $this->loadedModel->getChartData($this->viewType, $this->chartType);

		$this->state = $this->loadedModel->getState();

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return Text::_('COM_REDSHOPB_REPORT_GENERAL_NEWSLETTER_TITLE');
	}

	/**
	 * Get the formatted value
	 *
	 * @param   string  $key    Field key
	 * @param   string  $value  Value to parse
	 *
	 * @return  string  Formatted value
	 */
	public function getFormattedValue($key, $value)
	{
		return $this->loadedModel->getFormattedValue($key, $value);
	}
}
