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
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

/**
 * Report Sales Shipping Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelReport_Sales_Shipping extends RedshopbModelReportReport
{
	/**
	 * @var  string
	 */
	public $reportName = 'sales_shipping';

	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_report_sales_shipping';

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
				'report_group', 'date_from', 'date_to', 'show_extended'
			);
		}

		parent::__construct($config);
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
		$this->items          = parent::getItems();
		$shippingConfigParams = array();

		if ($this->items)
		{
			foreach ($this->items as $item)
			{
				if (!isset($shippingConfigParams[$item->shipping_configuration_id]))
				{
					$shippingConfigParams[$item->shipping_configuration_id] = new Registry;
					$shippingConfigParams[$item->shipping_configuration_id]->loadString($item->shipping_configuration_params);
				}

				$item->shipping_rate_name = $shippingConfigParams[$item->shipping_configuration_id]->get('shipping_title')
											. ' - ' . $item->shipping_rate_name;
			}
		}

		// Save Posted filters as default filters
		if (Factory::getApplication()->input->getMethod() == 'POST')
		{
			$this->saveReportFilters($this->items);
		}

		return $this->items;
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
			'shipping_rate_name' => array('title' => 'COM_REDSHOPB_REPORT_SHIPPING_RATE_NAME', 'noSum' => true),
			'number_of_orders' => array('title' => 'COM_REDSHOPB_REPORT_ORDERS'),
			'currency' => array('title' => 'COM_REDSHOPB_REPORT_CURRENCY', 'noSum' => true),
			'sales_shipping' => array('title' => 'COM_REDSHOPB_REPORT_ORDERS_SALES_SHIPPING'),
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

			case 'sales_shipping':
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
					'SUM(o.shipping_price) AS sales_shipping',
					'o.currency',
					'sr.shipping_configuration_id',
					'sr.name AS shipping_rate_name',
					'sc.params AS shipping_configuration_params',
				)
			)
			->from($db->qn('#__redshopb_order', 'o'))
			->leftJoin($db->qn('#__redshopb_shipping_rates', 'sr') . ' ON sr.id = o.shipping_rate_id')
			->leftJoin($db->qn('#__redshopb_shipping_configuration', 'sc') . ' ON sc.id = sr.shipping_configuration_id')
			->where($db->qn('o.shipping_rate_id') . ' IS NOT NULL ');

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

		$query->group('o.shipping_rate_id');
		$query->group('o.currency');

		return $query;
	}
}
