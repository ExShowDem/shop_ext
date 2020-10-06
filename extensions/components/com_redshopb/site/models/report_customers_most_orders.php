<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
/**
 * Report Customers Most Orders Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelReport_Customers_Most_Orders extends RedshopbModelReportReport
{
	/**
	 * @var  string
	 */
	public $reportName = 'customers_most_orders';

	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_report_customers_most_orders';

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
				'report_group', 'date_from', 'date_to', 'show_extended',
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
			'period' => array('title' => 'COM_REDSHOPB_REPORT_PERIOD', 'noSum' => true),
			'customer_type' => array('title' => 'COM_REDSHOPB_REPORT_CUSTOMER_TYPE', 'noSum' => true),
			'customer_name' => array('title' => 'COM_REDSHOPB_REPORT_CUSTOMER_NAME', 'noSum' => true),
			'number_of_orders' => array('title' => 'COM_REDSHOPB_REPORT_ORDERS'),
			'currency' => array('title' => 'COM_REDSHOPB_REPORT_CURRENCY', 'noSum' => true),
			'sales_total' => array('title' => 'COM_REDSHOPB_REPORT_ORDERS_SALES_TOTAL'),
		);

		if ($extended)
		{
			$columns = array_merge(
				array_splice($columns, 0, 5), array('sales_paid' => array('title' => 'COM_REDSHOPB_REPORT_ORDERS_SALES_TOTAL_PAID')), $columns
			);
			$columns = array_merge(
				array_splice($columns, 0, 5), array('sales_average' => array('title' => 'COM_REDSHOPB_REPORT_VIEW_TYPE_AMOUNT_AVERAGE')), $columns
			);
		}

		return $columns;
	}

	/**
	 * Get the formatted value
	 *
	 * @param   string  $key    Field key
	 * @param   string  $value  Value to parse
	 *
	 * @return  mixed  Formatted value
	 */
	public function getFormattedValue($key, $value)
	{
		switch ($key)
		{
			case 'period':
				return HTMLHelper::_('date', $value, $this->periodFormat, null);

			case 'sales_total':
			case 'sales_average':
			case 'sales_paid':
				return RedshopbHelperProduct::getProductFormattedPrice($value, $this->currency);

			case 'currency':
				if (!empty($value))
				{
					$this->currency = $value;
				}

				return $value;
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
					'DATE(o.created_date) AS period',
					'MIN(o.created_date) AS date_start',
					'MAX(o.created_date) AS date_end',
					'COUNT(*) AS number_of_orders',
					'SUM(o.total_price) AS sales_total',
					'AVG(o.total_price) AS sales_average',
					'SUM(o.total_price_paid) AS sales_paid',
					'o.currency',
					'o.customer_type',
					'o.customer_name',
				)
			)
			->from($db->qn('#__redshopb_order', 'o'));

		// Filter by order status.
		$orderStatus = $this->getState('filter.order_status', '-1');

		if (is_numeric($orderStatus) && $orderStatus >= 0)
		{
			$query->where($db->qn('o.status') . ' = ' . (int) $orderStatus);
		}

		$dateFrom = $this->getState('filter.date_from');

		if ($dateFrom)
		{
			$query->where('DATE(' . $db->qn('o.created_date') . ') >= ' . $db->q($dateFrom));
		}

		$dateTo = $this->getState('filter.date_to');

		if ($dateTo)
		{
			$query->where('DATE(' . $db->qn('o.created_date') . ') <= ' . $db->q($dateTo));
		}

		$viewType = $this->getState('chart.report_view_type', 'number_of_orders');
		$query->order($db->qn('o.created_date') . ' DESC');
		$query->order($db->qn($viewType) . ' DESC');

		// Grouping
		$grouping = $this->getState('filter.report_group', '1');

		switch ($grouping)
		{
			case '0':
				$query->group('YEAR(o.created_date), MONTH(o.created_date), DAY(o.created_date)');
				break;
			default:
			case '1':
				$query->group('YEAR(o.created_date), MONTH(o.created_date)');
				break;
			case '2':
				$query->group('YEAR(o.created_date)');
				break;
		}

		$query->group('o.customer_type');
		$query->group('o.customer_name');
		$query->group('o.currency');

		return $query;
	}
}
