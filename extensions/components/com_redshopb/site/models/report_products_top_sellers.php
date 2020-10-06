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
 * Report Products Top Sellers Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelReport_Products_Top_Sellers extends RedshopbModelReportReport
{
	/**
	 * @var  string
	 */
	public $reportName = 'products_top_sellers';

	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_report_products_top_sellers';

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
				'report_group', 'date_from', 'date_to',
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
			'product_name' => array('title' => 'COM_REDSHOPB_REPORT_PRODUCT_NAME', 'noSum' => true),
			'product_sku' => array('title' => 'COM_REDSHOPB_REPORT_PRODUCT_SKU', 'noSum' => true),
			'number_of_items' => array('title' => 'COM_REDSHOPB_REPORT_QUANTITY'),
			'currency' => array('title' => 'COM_REDSHOPB_REPORT_CURRENCY', 'noSum' => true),
			'sales_total' => array('title' => 'COM_REDSHOPB_REPORT_ORDERS_SALES_TOTAL'),
		);

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
					'SUM(oi.quantity) AS number_of_items',
					'SUM(oi.price * oi.quantity) AS sales_total',
					'oi.currency',
					'oi.product_name',
					'oi.product_sku',
				)
			)
			->from($db->qn('#__redshopb_order', 'o'))
			->leftJoin($db->qn('#__redshopb_order_item', 'oi') . ' ON oi.order_id = o.id')
			->leftJoin($db->qn('#__redshopb_product', 'p') . ' ON p.id = oi.product_id');

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

		$viewType = $this->getState('chart.report_view_type', 'number_of_items');
		$query->order($db->qn('o.created_date') . ' DESC');
		$query->order($db->qn($viewType) . ' DESC');

		// Grouping
		$grouping = $this->getState('filter.report_group', '1');

		// Filter by category
		$this->addCategoryFilter($query);

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

		$query->group('oi.product_id');
		$query->group('oi.currency');

		return $query;
	}
}
