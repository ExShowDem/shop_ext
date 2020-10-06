<?php
/**
 * @package     GroupDeliveryTime
 * @subpackage  Plugin
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Plugin\CMSPlugin;

JLoader::import('redshopb.library');
JImport("plugins.vanir.group_delivery_time.helper.helper", JPATH_ROOT);

/**
 * Vanir - Group Delivery Time Plugin
 *
 * @package     GroupDeliveryTime
 * @subpackage  Vanir
 * @since       1.0
 */
class PlgVanirGroup_Delivery_Time extends CMSPlugin
{
	/**
	 * Auto load language
	 *
	 * @var boolean
	 */
	protected $autoloadLanguage = true;

	/**
	 * Constructor
	 *
	 * @param   object  $subject   The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @since   1.5
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);

		Table::addIncludePath(__DIR__ . '/tables');
	}

	/**
	 * Method for add group delivery
	 *
	 * @return void
	 */
	public function onAjaxVanirAddGroupDelivery()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app = Factory::getApplication();

		$color = $app->input->getString('color', '');
		$min   = $app->input->getInt('min', null);
		$max   = $app->input->getInt('max', null);
		$label = $app->input->getString('label', '');
		$id    = $app->input->getInt('id', 0);

		$table = Table::getInstance('Delivery_Time_Group', 'RedshopbTable');

		// Check: Min and max is missing
		if (!$min && !$max)
		{
			$app->setHeader('status', 422);
			$app->sendHeaders();
			echo Text::_('PLG_VANIR_GROUP_DELIVERY_TIME_ERROR_MISSING_MIN_MAX');
			$app->close();
		}

		// Check: Min must be lower than Max
		if ($min && $max && ($min > $max))
		{
			$app->setHeader('status', 422);
			$app->sendHeaders();
			echo Text::_('PLG_VANIR_GROUP_DELIVERY_TIME_ERROR_MIN_HIGHER_MAX');
			$app->close();
		}

		if ($id && !$table->load($id))
		{
			$app->setHeader('status', 422);
			$app->sendHeaders();
			echo Text::_('PLG_VANIR_GROUP_DELIVERY_TIME_ERROR_COULD_NOT_LOAD_DATA');
			$app->close();
		}

		$table->color    = $color;
		$table->min_time = $min;
		$table->max_time = $max;
		$table->label    = $label;

		if (!$table->store())
		{
			$app->setHeader('status', 422);
			$app->sendHeaders();
			echo Text::_('PLG_VANIR_GROUP_DELIVERY_TIME_ERROR_CAN_NOT_SAVE');
			$app->close();
		}

		echo $table->id;

		$app->close();
	}

	/**
	 * Method for add group delivery
	 *
	 * @return void
	 */
	public function onAjaxVanirDeleteGroup()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app = Factory::getApplication();

		$id = $app->input->getInt('id', 0);

		if (!$id)
		{
			$app->setHeader('status', 422);
			$app->sendHeaders();
			echo Text::_('PLG_VANIR_GROUP_DELIVERY_TIME_ERROR_MISSING_ID');
			$app->close();
		}

		$table = Table::getInstance('Delivery_Time_Group', 'RedshopbTable');

		if (!$table->load($id))
		{
			$app->setHeader('status', 422);
			$app->sendHeaders();
			echo Text::_('PLG_VANIR_GROUP_DELIVERY_TIME_ERROR_COULD_NOT_LOAD_DATA');
			$app->close();
		}

		$table->delete();

		$app->close();
	}

	/**
	 * Method for load group information from specific stockroom
	 *
	 * @return void
	 */
	public function onAjaxVanirLoadGroupFromStockRoom()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app = Factory::getApplication();

		$id = $app->input->getInt('id', 0);

		if (!$id)
		{
			$app->setHeader('status', 422);
			$app->sendHeaders();
			echo Text::_('PLG_VANIR_GROUP_DELIVERY_TIME_ERROR_MISSING_ID');
			$app->close();
		}

		$vanirGroup = PlgVanirGroupDeliveryTimeHelper::getDeliveryTime($id);

		if (!$vanirGroup)
		{
			$app->setHeader('status', 422);
			$app->sendHeaders();
			echo Text::_('PLG_VANIR_GROUP_DELIVERY_TIME_ERROR_COULD_NOT_LOAD_DATA');
			$app->close();
		}

		echo json_encode($vanirGroup);

		$app->close();
	}

	/**
	 * Method for load group information from specific group Id
	 *
	 * @return void
	 */
	public function onAjaxVanirLoadGroup()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$app = Factory::getApplication();

		$id = $app->input->getInt('id', 0);

		if (!$id)
		{
			$app->setHeader('status', 422);
			$app->sendHeaders();
			echo Text::_('PLG_VANIR_GROUP_DELIVERY_TIME_ERROR_MISSING_ID');
			$app->close();
		}

		$table = Table::getInstance('Delivery_Time_Group', 'RedshopbTable');

		if (!$table->load($id))
		{
			$app->setHeader('status', 422);
			$app->sendHeaders();
			echo Text::_('PLG_VANIR_GROUP_DELIVERY_TIME_ERROR_COULD_NOT_LOAD_DATA');
			$app->close();
		}

		echo json_encode($table->getProperties(true));

		$app->close();
	}

	/**
	 * onRedshopbSetProductRelates
	 *
	 * @param   array  $products   Products data
	 * @param   array  $keys       Products array ids
	 *
	 * @return   void
	 */
	public function onRedshopbSetProductRelates(&$products, $keys)
	{
		PlgVanirGroupDeliveryTimeHelper::getMinDeliveryStocks($keys);
	}

	/**
	 * onRedshopbAddToCartValidation
	 *
	 * @param   array  $data     All cart data
	 * @param   null   $return   Array variables for override result
	 *
	 * @return array|boolean
	 */
	public function onRedshopbAddToCartValidation($data, &$return = null)
	{
		extract($data);

		/**
		 * @var  int    $stockroomId
		 * @var  int    $productItem
		 * @var  object $userCompany
		 * @var  int    $productId
		 */

		if (!$productItem && !$stockroomId)
		{
			if ($userCompany && $userCompany->stockroom_verification
				&& !RedshopbHelperStockroom::checkProductHasAvailableStockroom($productId))
			{
				return array(
					'items' => array(),
					'msg' => Text::_('COM_REDSHOPB_ADD_TO_CART_ERROR_STOCK_AMOUNT'),
					'msgStatus' => 'alert-error'
				);
			}

			$results = array();

			// Get available stockroom for product if available
			$results['stockroomId']     = PlgVanirGroupDeliveryTimeHelper::getMinDeliveryStock($productId);
			$results['findStockroomId'] = false;

			return $results;
		}
		else
		{
			return true;
		}
	}
}
