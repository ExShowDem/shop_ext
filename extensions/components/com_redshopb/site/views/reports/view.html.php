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
/**
 * Reports View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewReports extends RedshopbView
{
	/**
	 * @var  array
	 */
	public $items;

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$model = $this->getModel('reports');

		$this->items = $model->getItems();

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return Text::_('COM_REDSHOPB_REPORTS_TITLE');
	}

	/**
	 * Get the report item
	 *
	 * @param   string  $itemName  The report name
	 *
	 * @return  object  Report item
	 */
	public function getReportItem($itemName)
	{
		if (!empty($this->items))
		{
			foreach ($this->items as $item)
			{
				if ($item->name == $itemName)
				{
					return $item;
				}
			}
		}

		return (object) array(
			'modified_date' => '0000-00-00',
			'rows' => '-',
		);
	}
}
