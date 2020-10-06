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
/**
 * Report Products Low Stock Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelReport_Products_Low_Stock extends RedshopbModelReportReport
{
	/**
	 * @var  string
	 */
	public $reportName = 'products_low_stock';

	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_report_products_low_stock';

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
				'report_group', 'items_from', 'items_to', 'limit_type',
			);
		}

		parent::__construct($config);
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
			'product_name' => array('title' => 'COM_REDSHOPB_REPORT_PRODUCT_NAME', 'noSum' => true),
			'product_sku' => array('title' => 'COM_REDSHOPB_REPORT_PRODUCT_SKU', 'noSum' => true),
			'stock_upper_level' => array('title' => 'COM_REDSHOPB_REPORT_STOCK_UPPER_LEVEL', 'noSum' => true),
			'stock_lower_level' => array('title' => 'COM_REDSHOPB_REPORT_STOCK_LOWER_LEVEL', 'noSum' => true),
			'number_of_items' => array('title' => 'COM_REDSHOPB_REPORT_STOCK_QUANTITY'),
		);

		return $columns;
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
		switch ($key)
		{
			default :
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
					's.*',
					's.amount as number_of_items',
					'p.name AS product_name',
					'p.sku AS product_sku',
				)
			)
			->select('s.*')
			->from($db->qn('#__redshopb_stockroom_product_xref', 's'))
			->leftJoin($db->qn('#__redshopb_product', 'p') . ' ON p.id = s.product_id')
			->where('s.unlimited = 0');

		$itemsFrom = $this->getState('filter.items_from');
		$itemsTo   = $this->getState('filter.items_to');
		$limitType = $this->getState('filter.limit_type', 'upper_limit') == 'upper_limit' ? 's.stock_upper_level' : 's.stock_lower_level';

		if ($itemsFrom)
		{
			$query->where('s.amount >= ' . (int) $itemsFrom);
		}

		if ($itemsTo)
		{
			$query->where('s.amount <= ' . (int) $itemsTo);
		}

		if (!$itemsFrom && !$itemsTo)
		{
			$stockLimit = array(
				's.amount <= ' . $db->qn($limitType),
				's.amount <= 0'
			);

			$query->where('(' . implode(' OR ', $stockLimit) . ')');
		}

		// Filter by category
		$this->addCategoryFilter($query);

		$query->order($db->qn('number_of_items') . ' ASC');

		$query->group('s.product_id');

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
			$usersString = Text::_('COM_REDSHOPB_REPORT_VIEW_TYPE_STOCK_QUANTITY');

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
