<?php
/**
 * @package     Aesir.E-Commerce.Site
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Display pick-up location dropdown
 *
 * @since  1.6.55
 */
class JFormFieldPickuplocation extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	public $type = 'Pickuplocation';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	public function getOptions()
	{
		$options      = array();
		$formData     = $this->form->getData();
		$app          = Factory::getApplication();
		$customerId   = $formData->get('customer_id', $app->getUserState('shop.customer_id', 0));
		$customerType = $formData->get('customer_type', $app->getUserState('shop.customer_type', ''));
		$companyId    = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);
		$items        = RedshopbHelperStockroom::getPickUpStockroomList($companyId);

		if ($items)
		{
			foreach ($items as $stockroom)
			{
				$options[] = HTMLHelper::_(
					'select.option',
					$stockroom->id,
					$stockroom->name
				);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
