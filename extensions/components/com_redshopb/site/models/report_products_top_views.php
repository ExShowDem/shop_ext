<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * Report Products Top Views Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelReport_Products_Top_Views extends RedshopbModelReportReport
{
	/**
	 * @var  string
	 */
	public $reportName = 'products_top_views';

	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_report_products_top_views';

	/**
	 * Constructor
	 *
	 * @param   array  $config  Configuration array
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'product_category',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Function to get the active filters
	 *
	 * @return  array
	 *
	 * @since   3.2
	 */
	public function getActiveFilters()
	{
		$app = Factory::getApplication();

		if ($app->input->getMethod() == 'POST')
		{
			if ($app->input->post->get('product_category', array(), 'array') != array())
			{
				$this->setState('filter.product_category', '');
				$app->setUserState($this->context . '.filter.product_category', '');
			}
		}

		$activeFilters = parent::getActiveFilters();

		return $activeFilters;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @param   bool  $extended  Show extended columns or not
	 *
	 * @return  array
	 */
	public function getReportTableColumns($extended = false)
	{
		$columns = array(
			'product_name'    => array('title' => 'COM_REDSHOPB_REPORT_PRODUCT_NAME', 'noSum' => true),
			'product_sku'     => array('title' => 'COM_REDSHOPB_REPORT_PRODUCT_SKU', 'noSum' => true),
			'number_of_items' => array('title' => 'COM_REDSHOPB_REPORT_NUMBER_OF_VIEWS'),
			'retail_price'    => array('title' => 'COM_REDSHOPB_REPORT_RETAIL_PRICE', 'noSum' => true),
		);

		return $columns;
	}

	/**
	 * Get the formatted value
	 *
	 * @param   string  $key    Field key
	 * @param   mixed   $value  Value to parse
	 *
	 * @return  mixed  Formatted value
	 */
	public function getFormattedValue($key, $value)
	{
		switch ($key)
		{
			case 'retail_price':
				return RedshopbHelperProduct::getProductFormattedPrice($value, $this->currency);
			default:
				return $value;
		}
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select(
				array(
					'p.name AS product_name',
					'p.sku AS product_sku',
					'pp.retail_price AS retail_price',
					'p.hits AS number_of_items',
				)
			)
			->from($db->qn('#__redshopb_product', 'p'))
			->leftJoin(
				$db->qn('#__redshopb_product_price', 'pp') . ' ON pp.type_id = p.id AND pp.type = ' . $db->q('product')
				. ' AND pp.sales_type = ' . $db->q('all_customers')
				. ' AND pp.sales_code = ' . $db->q('')
				. ' AND pp.country_id IS NULL AND pp.currency_id IS NULL'
			);

		// Filter by category
		$this->addCategoryFilter($query);

		$query->order($db->qn('number_of_items') . ' DESC');
		$query->order($db->qn('p.name') . ' ASC');
		$query->group('p.id');

		return $query;
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
				foreach ($checkPoints as $checkPoint)
				{
					if (!isset($chartData['amounts'][$item->product_name][$checkPoint['startDate']]))
					{
						$chartData['amounts'][$item->product_name][$checkPoint['startDate']] = 0;
					}

					$chartData['amounts'][$item->product_name][$checkPoint['startDate']] += $item->{$sortItem};
				}

				unset($data[$key]);
			}
		}
		else
		{
			$usersString = Text::_('COM_REDSHOPB_REPORT_VIEW_TYPE_VIEWS');

			if (!isset($chartData['amounts'][$usersString]))
			{
				$chartData['amounts'][$usersString] = array();
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
						$dataValues = $value;
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
}
