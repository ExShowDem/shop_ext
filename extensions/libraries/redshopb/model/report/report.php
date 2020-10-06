<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\Utilities\ArrayHelper;

/**
 * Reporting Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelReportReport extends RedshopbModelList
{
	/**
	 * @var  string
	 */
	public $reportName = '';

	/**
	 * @var  string
	 */
	public $periodFormat = '';

	/**
	 * @var  string
	 */
	public $currency = '';

	/**
	 * @var  array
	 */
	public $items;

	/**
	 * Constructor
	 *
	 * @param   array  $config  Configuration array
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$app = Factory::getApplication();

		// Load default report filters
		if ($app->getUserState($this->context . '.filter.report_group', null) === null)
		{
			$this->loadReportFilters();
		}

		$periodGroup        = (int) $this->getState('filter.report_group', 1);
		$this->periodFormat = RedshopbHelperReport::getDatePeriodFormat($periodGroup);

		$config            = RedshopbEntityConfig::getInstance();
		$defaultCurrencyId = $config->getInt('default_currency', 38);
		$this->currency    = RedshopbHelperProduct::getCurrency($defaultCurrencyId)->alpha3;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState($ordering, $direction);
		$this->setState('list.limit', 0);
		$app = Factory::getApplication();

		$chartFilters = $app->getUserStateFromRequest($this->context . '.chart', 'chart', array(), 'array');

		// Receive & set filters
		if (!empty($chartFilters))
		{
			foreach ($chartFilters as $name => $value)
			{
				$this->setState('chart.' . $name, $value);
			}
		}
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function getItems()
	{
		$this->items = parent::getItems();

		// Save Posted filters as default filters
		if (Factory::getApplication()->input->getMethod() == 'POST')
		{
			$this->saveReportFilters($this->items);
		}

		return $this->items;
	}

	/**
	 * Save default Report filters
	 *
	 * @param   mixed  $items  Loaded items
	 *
	 * @return  void
	 */
	public function saveReportFilters($items)
	{
		$db     = $this->getDbo();
		$params = $this->getActiveFilters();

		foreach ($params as $key => $value)
		{
			unset($params[$key]);
			$params['filter.' . $key] = $value;
		}

		$params['chart.report_chart_type'] = $this->getState('chart.report_chart_type');
		$params['chart.report_view_type']  = $this->getState('chart.report_view_type');
		$params                            = json_encode($params);

		// Save new report request
		$query = $db->getQuery(true)
			->select('id')
			->from($db->qn('#__redshopb_reports', 'r'))
			->where($db->qn('name') . ' = ' . $db->q($this->reportName));

		$db->setQuery($query);
		$id = $db->loadResult();

		$reportModel = RedshopbModel::getFrontInstance('Report');
		$data        = array(
			'name' => $this->reportName,
			'rows' => $items !== false ? count($items) : 0,
			'params' => $params,
		);

		if (!empty($id))
		{
			$data['id'] = $id;
		}

		$reportModel->save($data);
	}

	/**
	 * Load default Report filters
	 *
	 * @return  void
	 */
	public function loadReportFilters()
	{
		$db  = $this->getDbo();
		$app = Factory::getApplication();

		// Getting default state
		$query = $db->getQuery(true)
			->select('params')
			->from($db->qn('#__redshopb_reports', 'r'))
			->where($db->qn('name') . ' = ' . $db->q($this->reportName));

		$db->setQuery($query);
		$params = $db->loadResult();

		if ($params)
		{
			$params = json_decode($params, true);

			foreach ($params as $paramKey => $paramValue)
			{
				$this->setState($paramKey, $paramValue);
				$app->setUserState($this->context . '.' . $paramKey, $paramValue);
			}
		}
	}

	/**
	 * Get data for chart
	 *
	 * @param   string  $viewType   On which field should it sort the values
	 * @param   string  $chartType  Type of chart
	 * @param   int     $interval   Chart interval points
	 *
	 * @return  array
	 */
	public function getChartData($viewType = 'number_of_orders', $chartType = 'Line', $interval = 6)
	{
		$chartData = $this->processChartData($interval, $viewType);

		return $this->prepareChartData($chartData, $chartType);
	}

	/**
	 * Get data for chart
	 *
	 * @param   int     $interval  Chart interval points
	 * @param   string  $sortItem  On which field should it sort the values
	 *
	 * @return  array
	 */
	public function processChartData($interval = 6, $sortItem = 'all')
	{
		if (!isset($this->items))
		{
			$this->getItems();
		}

		$data                  = $this->items;
		$numberOfItems         = $data ? count($data) : 0;
		$chartData             = array();
		$chartData['amounts']  = array();
		$chartData['labels']   = array();
		$chartData['currency'] = $this->currency;
		$dateField             = 'period';

		$startDate = $this->getState('filter.date_from');
		$endDate   = $this->getState('filter.date_to');

		if (empty($startDate))
		{
			if (isset($this->items[$numberOfItems - 1]) && isset($this->items[$numberOfItems - 1]->period))
			{
				$startDate = $this->items[$numberOfItems - 1]->period;
			}
			else
			{
				$startDate = Date::getInstance()->format('Y-m-d');
			}
		}

		if (empty($endDate))
		{
			if (isset($this->items[0]) && isset($this->items[0]->period))
			{
				$endDate = $this->items[0]->period;
			}
			else
			{
				$endDate = Date::getInstance()->format('Y-m-d');
			}
		}

		$startDateNumber   = strtotime($startDate);
		$endDateNumber     = strtotime($endDate);
		$checkPoints       = array();
		$chartData['days'] = round(($endDateNumber - $startDateNumber) / 86400);
		$point             = round($chartData['days'] / $interval);

		if ($point == 0)
		{
			$point = 1;
		}

		for ($i = $interval; $i >= 0; $i--)
		{
			$startDateNumber = strtotime($endDate . ' -' . ($i * $point) . ' days', Date::getInstance()->toUnix());
			$endDateNumber   = strtotime($endDate . ' -' . (($i - 1) * $point) . ' days', Date::getInstance()->toUnix());

			$startDatelabel        = date('Y-m-d', $startDateNumber);
			$endDateLabel          = date('Y-m-d', $endDateNumber);
			$chartData['labels'][] = $startDatelabel;

			$checkPoints[] = array(
				'startDate' => $startDatelabel,
				'endDate' => $endDateLabel,
			);
		}

		$chartData = $this->setReportDataToChartContainer($chartData, $data, $checkPoints, $dateField, $sortItem);

		return $chartData;
	}

	/**
	 * Prepare data for chart
	 *
	 * @param   array   $chartData    Container for chart data
	 * @param   array   $data         Data used for chart definition
	 * @param   array   $checkPoints  Breakpoints in graph
	 * @param   string  $dateField    Field name that is used for period
	 * @param   string  $sortItem     Field name that is used for calculating
	 *
	 * @return  array|stdClass[]
	 */
	public function setReportDataToChartContainer($chartData, $data, $checkPoints, $dateField, $sortItem)
	{
		if (!empty($data))
		{
			foreach ($data as $key => $item)
			{
				if (!isset($chartData['amounts'][$item->currency]))
				{
					$chartData['amounts'][$item->currency] = array();
				}

				$chartData['currency'] = $item->currency;

				foreach ($checkPoints as $checkPoint)
				{
					if ($item->{$dateField} >= $checkPoint['startDate'] && $item->{$dateField} < $checkPoint['endDate'])
					{
						if (!isset($chartData['amounts'][$item->currency][$checkPoint['startDate']]))
						{
							$chartData['amounts'][$item->currency][$checkPoint['startDate']] = 0;
						}

						$chartData['amounts'][$item->currency][$checkPoint['startDate']] += $item->{$sortItem};
					}
				}

				unset($data[$key]);
			}
		}

		return $chartData;
	}

	/**
	 * Prepare data for chart
	 *
	 * @param   array   $data       Data used for chart definition
	 * @param   string  $chartType  Chart types: Line, Bar, Radar, PolarArea, Pie, Doughnut
	 *
	 * @return  array|stdClass[]
	 */
	public function prepareChartData($data, $chartType = 'Line')
	{
		$chartType = RHtmlRchart::getChartType($chartType);
		$chartData = array();
		$amounts   = $data['amounts'];
		$labels    = $data['labels'];

		switch ($chartType)
		{
			case 'PolarArea':
			case 'Pie':
			case 'Doughnut':

				foreach ($amounts as $columnName => $amount)
				{
					$dataValues  = 0;
					$strokeColor = implode(',', RHtmlRchart::getColorFromHash($columnName, 'redshopb'));

					foreach ($amount as $value)
					{
						$dataValues += $value;
					}

					$dataSet            = new stdClass;
					$dataSet->value     = $dataValues;
					$dataSet->color     = 'rgba(' . $strokeColor . ',0.5)';
					$dataSet->highlight = 'rgba(' . $strokeColor . ',1)';
					$dataSet->label     = $columnName;

					$chartData[] = $dataSet;
				}

				break;

			case 'Line':
			case 'Radar':
			case 'Bar':
			default:
				$chartData['labels']   = $labels;
				$chartData['datasets'] = array();

				if (empty($amounts))
				{
					// Needed for proper chart display
					$chartData['datasets'] = array(array());
				}
				else
				{
					foreach ($amounts as $columnName => $amount)
					{
						$dataValues  = array();
						$strokeColor = implode(',', RHtmlRchart::getColorFromHash($columnName, 'redshopb'));

						foreach ($chartData['labels'] as $label)
						{
							$dataValues[] = !isset($amount[$label]) ? 0 : $amount[$label];
						}

						$dataSet = array(
							'label' => $columnName,
							'fillColor' => 'rgba(' . $strokeColor . ',0.2)',
							'strokeColor' => 'rgba(' . $strokeColor . ',1)',
							'data' => $dataValues,
						);

						if ($chartType == 'Bar')
						{
							$dataSet['highlightFill']   = 'rgba(' . $strokeColor . ',0.75)';
							$dataSet['highlightStroke'] = 'rgba(' . $strokeColor . ',1)';
						}
						else
						{
							$dataSet['pointColor']           = 'rgba(' . $strokeColor . ',1)';
							$dataSet['pointStrokeColor']     = '#fff';
							$dataSet['pointHighlightFill']   = '#fff';
							$dataSet['pointHighlightStroke'] = 'rgba(' . $strokeColor . ',1)';
						}

						$chartData['datasets'][] = $dataSet;
					}
				}

				break;
		}

		return $chartData;
	}

	/**
	 * Add Category filter
	 *
	 * @param   JDatabaseQuery  $query  Report query
	 *
	 * @return  void
	 */
	public function addCategoryFilter(&$query)
	{
		$category = $this->getState('filter.product_category');

		if ($category)
		{
			if (is_array($category))
			{
				$selectedCategories = ArrayHelper::toInteger($category);
				$categories         = array();

				foreach ($selectedCategories as $selectedCategory)
				{
					$categories[] = $selectedCategory;
					$categories   = array_merge($categories, RedshopbEntityCategory::load($selectedCategory)->getAllChildrenIds());
				}

				$category = implode(',', $categories);
			}
			else
			{
				$categories = array((int) $category);
				$categories = array_merge($categories, RedshopbEntityCategory::load((int) $category)->getAllChildrenIds());
				$category   = implode(',', $categories);
			}

			if (!empty($category))
			{
				$db = $this->getDbo();
				$query->leftJoin($db->qn('#__redshopb_product_category_xref', 'pcx') . ' ON pcx.product_id = p.id')
					->where('pcx.category_id IN (' . $category . ')');
			}
		}
	}
}
