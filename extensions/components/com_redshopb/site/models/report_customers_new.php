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
use Joomla\CMS\Language\Text;

/**
 * Report Customers New Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelReport_Customers_New extends RedshopbModelReportReport
{
	/**
	 * @var  string
	 */
	public $reportName = 'customers_new';

	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_report_customers_new';

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
			'number_of_users' => array('title' => 'COM_REDSHOPB_REPORT_NEW_ACCOUNTS'),
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
			case 'period':
				return HTMLHelper::_('date', $value, $this->periodFormat, null);

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
					'DATE(u.created_date) AS period',
					'MIN(u.created_date) AS date_start',
					'MAX(u.created_date) AS date_end',
					'COUNT(*) AS number_of_users',
				)
			)
			->from($db->qn('#__redshopb_user', 'u'));

		$dateFrom = $this->getState('filter.date_from');

		if ($dateFrom)
		{
			$query->where('DATE(' . $db->qn('u.created_date') . ') >= ' . $db->q($dateFrom));
		}

		$dateTo = $this->getState('filter.date_to');

		if ($dateTo)
		{
			$query->where('DATE(' . $db->qn('u.created_date') . ') <= ' . $db->q($dateTo));
		}

		$viewType = $this->getState('chart.report_view_type', 'number_of_users');
		$query->order($db->qn('u.created_date') . ' DESC');
		$query->order($db->qn($viewType) . ' DESC');

		// Grouping
		$grouping = $this->getState('filter.report_group', '1');

		switch ($grouping)
		{
			case '0':
				$query->group('YEAR(u.created_date), MONTH(u.created_date), DAY(u.created_date)');
				break;
			default:
			case '1':
				$query->group('YEAR(u.created_date), MONTH(u.created_date)');
				break;
			case '2':
				$query->group('YEAR(u.created_date)');
				break;
		}

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
		$usersString = Text::_('COM_REDSHOPB_REPORT_NEW_ACCOUNTS');

		if (!isset($chartData['amounts'][$usersString]))
		{
			$chartData['amounts'][$usersString] = array();
		}

		if (!empty($data))
		{
			foreach ($data as $key => $item)
			{
				foreach ($checkPoints as $checkPoint)
				{
					if ($item->{$dateField} >= $checkPoint['startDate'] && $item->{$dateField} < $checkPoint['endDate'])
					{
						if (!isset($chartData['amounts'][$usersString][$checkPoint['startDate']]))
						{
							$chartData['amounts'][$usersString][$checkPoint['startDate']] = 0;
						}

						$chartData['amounts'][$usersString][$checkPoint['startDate']] += $item->{$sortItem};
					}
				}

				unset($data[$key]);
			}
		}

		return $chartData;
	}
}
