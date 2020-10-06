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
 * Report General Newsletter Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelReport_General_Newsletter extends RedshopbModelReportReport
{
	/**
	 * @var  string
	 */
	public $reportName = 'general_newsletter';

	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_report_general_newsletter';

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
			'newsletter_name' => array('title' => 'COM_REDSHOPB_REPORT_NEWSLETTER_NAME', 'noSum' => true),
			'number_of_users' => array('title' => 'COM_REDSHOPB_REPORT_NUMBER_OF_USERS'),
			'number_of_sent_mails' => array('title' => 'COM_REDSHOPB_REPORT_NUMBER_OF_USERS_SENT'),
			'number_of_failed_mails' => array('title' => 'COM_REDSHOPB_REPORT_NUMBER_OF_USERS_FAILED'),
		);

		if ($extended)
		{
			$columns = array_merge(
				array_splice($columns, 0, 4),
				array('number_of_open_mails' => array(
					'title' => 'COM_REDSHOPB_REPORT_NUMBER_OF_USERS_OPEN'
					)
				),
				$columns
			);

			$columns = array_merge(
				array_splice($columns, 0, 5),
				array(
					'number_of_bounced_mails' => array(
						'title' => 'COM_REDSHOPB_REPORT_NUMBER_OF_USERS_BOUNCED')
					),
				$columns
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
					'n.name AS newsletter_name',
					'DATE(n.created_date) AS period',
					'MIN(n.created_date) AS date_start',
					'MAX(n.created_date) AS date_end',
					'(SELECT COUNT(*) FROM '
						. $db->qn('#__redshopb_newsletter_user_xref', 'nux')
						. ' WHERE nux.newsletter_list_id = n.newsletter_list_id GROUP BY n.newsletter_list_id) AS number_of_users',
					'SUM(nus.sent) AS number_of_sent_mails',
					'SUM(nus.fail) AS number_of_failed_mails',
					'SUM(nus.open) AS number_of_open_mails',
					'SUM(nus.bounce) AS number_of_bounced_mails',
				)
			)
			->from($db->qn('#__redshopb_newsletter', 'n'))
			->leftJoin($db->qn('#__redshopb_newsletter_user_stats', 'nus') . ' ON nus.newsletter_id = n.id');

		$dateFrom = $this->getState('filter.date_from');

		if ($dateFrom)
		{
			$query->where('DATE(' . $db->qn('n.created_date') . ') >= ' . $db->q($dateFrom));
		}

		$dateTo = $this->getState('filter.date_to');

		if ($dateTo)
		{
			$query->where('DATE(' . $db->qn('n.created_date') . ') <= ' . $db->q($dateTo));
		}

		$query->order($db->qn('n.created_date') . ' DESC');
		$query->group('n.id');

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
					if (!isset($chartData['amounts'][$item->newsletter_name][$checkPoint['startDate']]))
					{
						$chartData['amounts'][$item->newsletter_name][$checkPoint['startDate']] = 0;
					}

					$chartData['amounts'][$item->newsletter_name][$checkPoint['startDate']] += $item->{$sortItem};
				}

				unset($data[$key]);
			}
		}
		else
		{
			$usersString = Text::_('COM_REDSHOPB_REPORT_NEWSLETTER_NAME');

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
