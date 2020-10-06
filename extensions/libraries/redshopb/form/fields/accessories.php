<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

FormHelper::loadFieldClass('rlist');

/**
 * Department Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldAccessories extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Accessories';

	/**
	 * A static cache.
	 *
	 * @var  array
	 */
	protected $cache = array();

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options   = array();
		$options[] = HTMLHelper::_(
			'select.option', '', Text::_('COM_REDSHOPB_SHOP_SELECT_ACCESSORY')
		);

		// Get the addresses.
		$items = $this->getAccessories();

		// Build the field options.
		if (!empty($items))
		{
			foreach ($items as $accessory)
			{
				if ($accessory->price > 0)
				{
					$accessoryPrice = ' (' . RedshopbHelperProduct::getProductFormattedPrice($accessory->price) . ')';
				}

				else
				{
					$accessoryPrice = '';
				}

				$options[] = HTMLHelper::_(
					'select.option',
					$accessory->accessory_id,
					ucfirst(str_replace('_', ' ', $accessory->description)) . ($accessoryPrice > 0) ? ' (' . $accessoryPrice . ')' : ''
				);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Method to get the list of departments.
	 *
	 * @return  array  An array of addresses.
	 */
	protected function getAccessories()
	{
		$jinput             = Factory::getApplication()->input;
		$productId          = $jinput->get('product_id', 0, 'int');
		$clientDepartmentId = $jinput->get('client_department_id', 0, 'int');

		if (empty($this->cache) && $productId > 0 && $clientDepartmentId > 0)
		{
			$model = RModel::getAdminInstance('Product');

			$db = Factory::getDbo();

			$query = $model->getAccessoriesQuery(array($productId), $clientDepartmentId);

			$db->setQuery($query);

			$result = $db->loadObjectList();

			if (is_array($result))
			{
				$this->cache = $result;
			}
		}

		return $this->cache;
	}
}
