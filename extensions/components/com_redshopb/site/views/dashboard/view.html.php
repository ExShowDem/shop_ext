<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Dashboard View.
 *
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewDashboard extends RedshopbView
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
		/** @var RedshopbModelDashboard $model */
		$model = $this->getModel();
		$items = $model->getMenuItems('others');

		RFactory::getDispatcher()->trigger('onAfterRedshopbViewDashboardDisplayAccessButtons', array(&$items, RedshopbHelperPrices::displayPrices()));
		$flattened = $model->flattenMenu($items);

		if (count($flattened) == 0)
		{
			Factory::getApplication()->redirect(RedshopbRoute::_('index.php?option=com_redshopb&view=shop', false));
			Factory::getApplication()->close();
		}

		$this->items = $flattened;
		parent::display($tpl);
	}
}
