<?php
/**
 * @package     GroupDeliveryTime
 * @subpackage  Plugin
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;

JLoader::import('redshopb.library');

/**
 * Vanir - Group Delivery Time Plugin
 *
 * @package     GroupDeliveryTime
 * @subpackage  Vanir
 * @since       1.0
 */
class PlgSystemRedshopb_Self_Shipping extends RedshopbShippingPluginBase
{
	/**
	 * @var string
	 */
	protected $shippingName = 'redshopb_self_shipping';

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $pluginPath = JPATH_PLUGINS . '/system/redshopb_self_shipping';

	/**
	 * Constructor
	 *
	 * @param   object $subject    The object to observe
	 * @param   array  $config     An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @since   1.5
	 * @throws Exception
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);

		RTable::addIncludePath(JPATH_PLUGINS . '/system/redshopb_self_shipping/extensions/components/com_redshopb/admin/tables');
		RModel::addIncludePath(JPATH_PLUGINS . '/system/redshopb_self_shipping/extensions/components/com_redshopb/site/models');

		if (Factory::getApplication()->isClient('site'))
		{
			JLoader::registerPrefix('Redshopb', JPATH_PLUGINS . '/system/redshopb_self_shipping/extensions/components/com_redshopb/site');
			JLoader::registerPrefix('Redshopb', JPATH_PLUGINS . '/system/redshopb_self_shipping/extensions/libraries/redshopb');
		}
	}

	/**
	 * onRedshopbSiteController
	 *
	 * @param   JControllerLegacy  $controller  Controller
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function onRedshopbSiteController($controller)
	{
		$controller->addViewPath($this->pluginPath . '/extensions/components/com_redshopb/site/views');
	}

	/**
	 * onRedshopbBreadcrumbEntity
	 *
	 * @param   string  $text     Text
	 * @param   JUri    $linkUri  JURI object
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function onRedshopbBreadcrumbEntity(&$text, $linkUri)
	{
		$linkView = $linkUri->getVar('view');
		$linkId   = (int) $linkUri->getVar('id');

		if ($linkView == 'shipping_route')
		{
			$text = Text::_('COM_REDSHOPB_BREADCRUMB_SHIPPING_ROUTE')
				. ': ' . RedshopbHelperBreadcrumb::getItemName($linkId, '#__redshopb_shipping_route');
		}
	}

	/**
	 * @param   array                $list       List of menu types
	 * @param   MenusModelMenutypes  $menuTypes  MenusModelMenutypes
	 *
	 * @return  void
	 * @since 1.0.0
	 */
	public function onAfterGetMenuTypeOptions(&$list, $menuTypes)
	{
		$object              = new Joomla\CMS\Object\CMSObject;
		$object->title       = 'COM_REDSHOPB_SHIPPING_ROUTE_VIEW_DEFAULT_TITLE';
		$object->description = 'COM_REDSHOPB_SHIPPING_ROUTE_VIEW_DEFAULT_DESC';
		$object->request     = array(
			'option' => 'com_redshopb',
			'view' => 'shipping_routes'
		);

		$menuTypes->addReverseLookupUrl($object);

		$list['COM_REDSHOPB'][] = $object;
	}

	/**
	 * onRedshopbAddToCartValidation
	 *
	 * @param   SimpleXMLElement|array  $menuItems  All cart data
	 *
	 * @return void
	 */
	public function onRedshopbAddMenuItem(&$menuItems)
	{
		$routesGroup = array(
			array(
				'view' => 'shipping_routes',
				'icon' => 'icon-globe',
				'text' => Text::_('COM_REDSHOPB_SHIPPING_ROUTES_LIST_TITLE'),
				'class' => 'shipping_routes',
				'query' => ''
			)
		);

		foreach ($menuItems as $key => $menuItem)
		{
			// We will insert it after shipping rates
			if (isset($menuItem['view'])
				&& $menuItem['view'] == 'shipping_rates')
			{
				array_splice($menuItems, $key + 1, 0, $routesGroup);

				break;
			}
		}
	}

	/**
	 * Add additional views for permission checking
	 *
	 * @param   array  $views  Views
	 *
	 * @return  void
	 */
	public function onAfterComRedshopbGetViewsACL(&$views)
	{
		$views['shipping_routes'] = 'company';
	}

	/**
	 * Prepare form and add my field.
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public function onContentPrepareForm($form, $data)
	{
		$app   = Factory::getApplication();
		$input = $app->input;

		if ($input->get('option') == 'com_redshopb'
			&& $input->get('view') == 'shipping_route'
			&& $app->isClient('site'))
		{
			Form::addFieldPath($this->pluginPath . '/extensions/libraries/redshopb/form/fields');
			$form->loadFile($this->pluginPath . '/extensions/components/com_redshopb/site/models/forms/shipping_route.xml', false);
		}

		return true;
	}

	/**
	 * onBeforeStoreRedshopb event
	 *
	 * @param   RTable  $table   Table Class
	 * @param   array   $src     True to update null values as well.
	 * @param   array   $ignore  True to update null values as well.
	 *
	 * @return  void
	 */
	public function onBeforeBindRedshopb($table, $src, $ignore)
	{
		if ($table->get('_tbl') == '#__redshopb_address')
		{
			// We initialise shipping route ids property so we can bind it before store
			$table->shipping_route_ids = null;
		}
	}

	/**
	 * onBeforeStoreRedshopb event
	 *
	 * @param   RTable  $table        Table Class
	 * @param   bool    $updateNulls  True to update null values as well.
	 *
	 * @return  boolean
	 */
	public function onBeforeStoreRedshopb($table, $updateNulls = false)
	{
		// We store shipping route ids in order items for history purposes
		if ($table->get('_tbl') == '#__redshopb_order')
		{
			if (strpos($table->shipping_rate_id, '_shipping_route_day_') !== false)
			{
				$shippingRateId          = explode('_shipping_route_day_', $table->shipping_rate_id);
				$selectedDay             = $shippingRateId[2];
				$shippingRouteId         = $shippingRateId[1];
				$table->shipping_rate_id = $shippingRateId[0];
				$shippingRoute           = RedshopbEntityShipping_Route::getInstance($shippingRouteId)->loadItem();
				$shippingDetails         = $table->shipping_details;

				if (!is_object($shippingDetails))
				{
					$shippingDetails = new Registry($shippingDetails);
				}

				$shippingDate = $this->shippingHelper->getShippingDateFromDay($selectedDay);
				$table->set('shipping_date', $shippingDate->format('Y-m-d'));
				$shippingDetails->set('shipping_date', $shippingDate->format('Y-m-d'));
				$shippingDetails->set('shipping_route', $shippingRouteId);

				if ($shippingRoute)
				{
					$shippingDetails->set('shipping_route_name', $shippingRoute->get('name'));
				}

				$table->shipping_details = $shippingDetails;
			}
		}
		elseif ($table->get('_tbl') == '#__redshopb_address')
		{
			if (property_exists($table, 'shipping_route_ids'))
			{
				// Move shipping route ids to non store property for avoid error
				$table->set('_shipping_route_ids', $table->get('shipping_route_ids'));
				unset($table->shipping_route_ids);
			}
		}

		return true;
	}

	/**
	 * Set Method for Api to be performed
	 *
	 * @param   string      $functionName  Function name
	 * @param   RApiHalHal  $apiHal        Webservice Hal object
	 *
	 * @return  mixed
	 */
	public function RApiHalBeforeSetApiOperation($functionName, $apiHal)
	{
		if (is_array($apiHal))
		{
			$apiHal = $apiHal[0];
		}

		// We check if this is our wanted webservice
		if ($apiHal->webserviceName == 'redshopb-delivery_address')
		{
			// We add our additional resources here
			$this->addResourceToTheConfiguration($apiHal->configuration->operations->read->list->resources, true);
			$this->addResourceToTheConfiguration($apiHal->configuration->operations->read->item->resources);

			// We add our additional fields here
			$this->addFieldToTheConfiguration($apiHal->configuration->operations->create->fields);
			$this->addFieldToTheConfiguration($apiHal->configuration->operations->update->fields);
		}

		// Returning null will let normal method to execute
		return null;
	}

	/**
	 * Add additional resource to the resource XML
	 *
	 * @param   SimpleXMLElement  $xml        Webservice xml object
	 * @param   bool              $isList     Is this XML configuration list
	 * @param   string            $fieldName  Field name to insert
	 *
	 * @return  void
	 */
	private function addResourceToTheConfiguration($xml, $isList = false, $fieldName = 'shipping_route_ids')
	{
		switch ($fieldName)
		{
			case 'shipping_route_ids':
				$xmlResource = $xml->addChild('resource');
				$xmlResource->addAttribute('displayName', 'shipping_route_ids');
				$xmlResource->addAttribute('transform', 'array');
				$xmlResource->addAttribute('fieldFormat', '{shipping_route_ids}');
				$xmlResource->addAttribute('displayGroup', '');
				$xmlResource->addAttribute('resourceSpecific', $isList ? 'listItem' : 'rcwsGlobal');
				$description = $xmlResource->addChild('description', Text::_('PLG_SYSTEM_REDSHOPB_SHIPPING_ROUTE_SHIPPING_ROUTE_ID'));

				break;
		}
	}

	/**
	 * Add additional resource to the resource XML
	 *
	 * @param   SimpleXMLElement  $xml        Webservice xml object
	 * @param   bool              $isList     Is this XML configuration list
	 * @param   string            $fieldName  Field name to insert
	 *
	 * @return  void
	 */
	private function addFieldToTheConfiguration($xml, $isList = false, $fieldName = 'shipping_route_ids')
	{
		switch ($fieldName)
		{
			case 'shipping_route_ids':
				$xmlResource = $xml->addChild('field');
				$xmlResource->addAttribute('name', 'shipping_route_ids');
				$xmlResource->addAttribute('transform', 'array');
				$xmlResource->addAttribute('defaultValue', '');
				$xmlResource->addAttribute('isRequiredField', 'false');
				$xmlResource->addAttribute('isFilterField', 'false');
				$xmlResource->addAttribute('isSearchableField', 'false');
				$xmlResource->addAttribute('isPrimaryField', 'false');
				$description = $xmlResource->addChild('description', Text::_('PLG_SYSTEM_REDSHOPB_SHIPPING_ROUTE_SHIPPING_ROUTE_ID'));

				break;
		}
	}

	/**
	 * Set document content for List view
	 *
	 * @param   string            $functionName   Function name
	 * @param   array             $items          List of items
	 * @param   SimpleXMLElement  $configuration  Configuration for displaying object
	 * @param   RApiHalHal        $apiHal         Hal main object
	 *
	 * @return void
	 */
	public function RApiHalBeforeSetForRenderList($functionName, $items = null, $configuration = null, RApiHalHal $apiHal = null)
	{
		if (is_string($functionName))
		{
			$configuration = $items[1];
			$apiHal        = $items[2];
			$items         = $items[0];
		}

		// We check if this is our wanted webservice
		if ($apiHal->webserviceName == 'redshopb-delivery_address')
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('address_id, GROUP_CONCAT(shipping_route_id) AS shipping_route_ids')
				->from('#__redshopb_shipping_route_address_xref')
				->group('address_id');
			$db->setQuery($query);

			// We have found at least one holiday
			$shippingRoutes = $db->loadObjectList('address_id');

			if ($shippingRoutes)
			{
				foreach ($items as $key => $value)
				{
					$items[$key]->shipping_route_ids = array();

					if (!empty($shippingRoutes[$value->id]))
					{
						$items[$key]->shipping_route_ids = explode(',', $shippingRoutes[$value->id]->shipping_route_ids);
					}
				}
			}
		}
	}

	/**
	 * onBeforeStoreRedshopb event
	 *
	 * @param   RTable  $table        Table Class
	 * @param   bool    $updateNulls  True to update null values as well.
	 *
	 * @return  boolean
	 */
	public function onAfterStoreRedshopb($table, $updateNulls = false)
	{
		// We store shipping route ids reference
		if ($table->get('_tbl') == '#__redshopb_address')
		{
			if (property_exists($table, '_shipping_route_ids'))
			{
				// We only store Shipping Route reference if we have addresses saved properly
				if (!empty($table->id) && is_array($table->get('_shipping_route_ids')))
				{
					// Turn back shipping route ids property for return system compatibility
					if ($table->get('_shipping_route_ids'))
					{
						$table->set('shipping_route_ids', $table->get('_shipping_route_ids'));
						unset($table->_shipping_route_ids);
					}

					// Delete all items
					$db    = Factory::getDbo();
					$query = $db->getQuery(true)
						->delete('#__redshopb_shipping_route_address_xref')
						->where($db->qn('address_id') . ' = ' . (int) $table->id);

					$db->setQuery($query);

					if (!$db->execute())
					{
						return false;
					}

					if (count($table->get('shipping_route_ids')) <= 0)
					{
						return true;
					}

					/** @var RedshopbTableShipping_Route_Address_Xref $xrefTable */
					$xrefTable = RedshopbTable::getAdminInstance('Shipping_Route_Address_Xref');

					// Store the new items
					foreach ($table->get('shipping_route_ids') as $shippingRouteId)
					{
						if (!(int) $shippingRouteId)
						{
							continue;
						}

						$keys = array('id' => 0, 'address_id' => $table->id, 'shipping_route_id' => $shippingRouteId);

						if (!$xrefTable->save($keys))
						{
							$table->setError($xrefTable->getError());

							return false;
						}
					}

					return true;
				}
			}
		}

		return true;
	}

	/**
	 * RedshopbOnCheckoutConfirm event
	 *
	 * @param   string  $prefix  Prefix
	 *
	 * @throws  Exception
	 *
	 * @return  void
	 */
	public function redshopbOnCheckoutConfirm($prefix)
	{
		$app            = Factory::getApplication();
		$shippingRateId = $app->getUserStateFromRequest('checkout.shipping_rate_id', 'shipping_rate_id', '', 'string');

		if (strpos($shippingRateId, '_shipping_route_day_') !== false)
		{
			$shippingRateId = explode('_shipping_route_day_', $shippingRateId);
			$selectedDay    = $shippingRateId[2];
			$shippingDate   = $this->shippingHelper->getShippingDateFromDay($selectedDay);
			$app->setUserState('checkout.shipping_date', $shippingDate->format('Y-m-d'));
		}
	}
}
