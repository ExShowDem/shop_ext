<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Plugin
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Plugin\CMSPlugin;

FormHelper::addFieldPath(__DIR__ . '/library/form/fields');

JLoader::registerPrefix('Redshopb', __DIR__ . '/library', false, true);

Table::addIncludePath(__DIR__ . '/library/table');

/**
 * Redshopb System Customer Plugin
 *
 * @package     Aesir.E-Commerce
 * @subpackage  System
 * @since       1.0
 */
class PlgSystemRedshopb_Stockroom_Groups extends CMSPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Container for unit prices
	 *
	 * @var    array
	 */
	public static $unitPrices = array();

	/**
	 * onAfterRoute function.
	 *
	 * @return void
	 */
	public function onAfterRoute()
	{
		$input = Factory::getApplication()->input;

		if ($input->getCmd('option') == 'com_redshopb')
		{
			$stockroomGroupId = $input->getString('stockroomGroupId');
			$explodeStockroom = explode('_', $stockroomGroupId);

			if ($stockroomGroupId && count($explodeStockroom) == 2)
			{
				if ($explodeStockroom[0])
				{
					$input->set('stockroom', $explodeStockroom[0]);
				}

				if ($explodeStockroom[1])
				{
					$input->set('stockroomGroupId', $explodeStockroom[1]);
				}
				else
				{
					$input->set('stockroomGroupId', null);
				}
			}
		}
	}

	/**
	 * Event for add fields in cart items match check
	 *
	 * @param   array  $cartItem  Cart item
	 * @param   array  $item      Current cart item
	 *
	 * @return  void
	 */
	public function onRedshopbBeforeCartItemMatch(&$cartItem, &$item)
	{
		if (!array_key_exists('stockroomGroupId', $cartItem))
		{
			$cartItem['stockroomGroupId'] = 0;
		}

		if (empty($cartItem['stockroomGroupId']))
		{
			$item['stockroomGroupId'] = Factory::getApplication()->input->getInt('stockroomGroupId', 0);
		}
	}

	/**
	 * Set cart fields for check
	 *
	 * @param   array  $cartItemsForCheck   Current cart fields for check
	 * @param   bool   $getDBNames          Return DB values instead cart session
	 *
	 * @return void
	 */
	public function onRedshopbAfterCartFieldsForCheck(&$cartItemsForCheck, $getDBNames = false)
	{
		if ($getDBNames)
		{
			$cartItemsForCheck[] = 'stockroom_group_id';
		}
		else
		{
			$cartItemsForCheck[] = 'stockroomGroupId';
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
		$views['stockroom_groups'] = 'company';
	}

	/**
	 * Add additional menu items inside the menu
	 *
	 * @param   RView  $view    Menu items
	 * @param   bool   $isShop  Menu items
	 *
	 * @return  void
	 */
	public function onAfterRedshopbViewDashboardDisplayAccessButtons(&$view, $isShop)
	{
		$stockroomGroup = array(
			array('view' => 'stockroom_groups', 'icon' => 'icon-archive', 'text' => Text::_('COM_REDSHOPB_STOCKROOM_GROUPS_TITLE'))
		);

		foreach ($view->accessButtons as $key => $menuItem)
		{
			// We will insert it before stockrooms
			if ($menuItem['view'] == 'stockrooms')
			{
				array_splice($view->accessButtons, $key, 0, $stockroomGroup);

				break;
			}
		}
	}

	/**
	 * Prepare form.
	 *
	 * @param   Form   $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since	2.5
	 */
	public function onContentPrepareForm($form, $data)
	{
		if ($form->getName() == 'com_redshopb.edit.stockroom.stockroom')
		{
			if (is_array($data))
			{
				$data = (object) $data;
			}

			$itemId = isset($data->id) ? $data->id : 0;

			$ids                    = $this->loadStockroomGroupsForStockroom($itemId);
			$data->stockroom_groups = implode(', ', $ids);

			$fieldXml = '<form><fieldset><field
	                name="stockroom_groups"
	                type="stockroomgroups"
	                default="' . (isset($data->stockroom_groups) ? $data->stockroom_groups : '') . '"
	                multiple="true"
	                label="COM_REDSHOPB_STOCKROOM_GROUP_STOCKROOM_GROUP_LABEL"
	                description="COM_REDSHOPB_STOCKROOM_GROUP_STOCKROOM_GROUP_DESC"
	                >
	            </field></fieldset></form>';
			$form->load($fieldXml);

			$stockroomGroups = $form->getField('stockroom_groups');
			$fieldHtml       = '<div class="form-group">' . $stockroomGroups->renderField() . '</div>';
			$fieldHtml       = str_replace(array("\n", "\r"), "", $fieldHtml);

			// Adding javascript on change trigger
			$script   = array();
			$script[] = '	(function ($) {';
			$script[] = '		$(document).ready(function () {';
			$script[] = '		    $(\'#jform_color\').closest(\'.control-group\').after(\'' . $fieldHtml . '\');';
			$script[] = '		});';
			$script[] = '	})(jQuery);';

			// Add the script to the document head.
			Factory::getDocument()->addScriptDeclaration(implode("\n", $script));
		}

		return true;
	}

	/**
	 * Load all stockroom groups for this stockroom
	 *
	 * @param   int  $id  Id
	 *
	 * @return  mixed
	 */
	private function loadStockroomGroupsForStockroom($id)
	{
		if (!empty($id))
		{
			// Load the profile data from the database.
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('stockroom_group_id')
				->from('#__redshopb_stockroom_group_stockroom_xref')
				->where($db->qn('stockroom_id') . ' = ' . (int) $id);

			$db->setQuery($query);

			$ids = $db->loadColumn();

			if (!is_array($ids))
			{
				$ids = array();
			}

			return $ids;
		}

		return array();
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
		if ($apiHal->webserviceName == 'redshopb-stockroom')
		{
			// We add our additional resources here
			$this->addResourceToTheConfiguration($apiHal->configuration->operations->read->list->resources, true);
			$this->addResourceToTheConfiguration($apiHal->configuration->operations->read->item->resources);

			// We add our additional fields here
			$this->addFieldToTheConfiguration($apiHal->configuration->operations->create->fields);
			$this->addFieldToTheConfiguration($apiHal->configuration->operations->update->fields);
		}
		elseif ($apiHal->webserviceName == 'redshopb-order_item')
		{
			// We add our additional resources here
			$this->addResourceToTheConfiguration($apiHal->configuration->operations->read->list->resources, true, 'stockroom_group_id');
			$this->addResourceToTheConfiguration($apiHal->configuration->operations->read->item->resources, false, 'stockroom_group_id');
			$this->addResourceToTheConfiguration($apiHal->configuration->operations->read->list->resources, true, 'stockroom_group_name');
			$this->addResourceToTheConfiguration($apiHal->configuration->operations->read->item->resources, false, 'stockroom_group_name');
		}
		elseif ($apiHal->webserviceName == 'redshopb-order')
		{
			// We add our additional fields here
			$this->addFieldToTheConfiguration($apiHal->configuration->complexArrays->orderitems->fields, false, 'stockroom_group_id');
			$this->addFieldToTheConfiguration($apiHal->configuration->complexArrays->orderitems->fields, false, 'stockroom_group_id');
			$this->addFieldToTheConfiguration($apiHal->configuration->complexArrays->orderitems->fields, false, 'stockroom_group_name');
			$this->addFieldToTheConfiguration($apiHal->configuration->complexArrays->orderitems->fields, false, 'stockroom_group_name');
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
	private function addResourceToTheConfiguration($xml, $isList = false, $fieldName = 'stockroom_groups')
	{
		switch ($fieldName)
		{
			case 'stockroom_groups':
				$xmlResource = $xml->addChild('resource');
				$xmlResource->addAttribute('displayName', 'stockroom_groups');
				$xmlResource->addAttribute('transform', 'array');
				$xmlResource->addAttribute('fieldFormat', '{stockroom_groups}');
				$xmlResource->addAttribute('displayGroup', '');
				$xmlResource->addAttribute('resourceSpecific', $isList ? 'listItem' : 'rcwsGlobal');
				$description = $xmlResource->addChild('description', Text::_('COM_REDSHOPB_STOCKROOM_GROUP_STOCKROOM_GROUP_LABEL'));

				break;

			case 'stockroom_group_id':
				$xmlResource = $xml->addChild('resource');
				$xmlResource->addAttribute('displayName', 'stockroom_group_id');
				$xmlResource->addAttribute('transform', 'int');
				$xmlResource->addAttribute('fieldFormat', '{stockroom_group_id}');
				$xmlResource->addAttribute('displayGroup', '');
				$xmlResource->addAttribute('resourceSpecific', $isList ? 'listItem' : 'rcwsGlobal');
				$description = $xmlResource->addChild('description', Text::_('COM_REDSHOPB_STOCKROOM_GROUP_STOCKROOM_GROUP_ID_LABEL'));

				break;

			case 'stockroom_group_name':
				$xmlResource = $xml->addChild('resource');
				$xmlResource->addAttribute('displayName', 'stockroom_group_name');
				$xmlResource->addAttribute('transform', 'string');
				$xmlResource->addAttribute('fieldFormat', '{stockroom_group_name}');
				$xmlResource->addAttribute('displayGroup', '');
				$xmlResource->addAttribute('resourceSpecific', $isList ? 'listItem' : 'rcwsGlobal');
				$description = $xmlResource->addChild('description', Text::_('COM_REDSHOPB_STOCKROOM_GROUP_NAME_DESC'));

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
	private function addFieldToTheConfiguration($xml, $isList = false, $fieldName = 'stockroom_groups')
	{
		switch ($fieldName)
		{
			case 'stockroom_groups':
				$xmlResource = $xml->addChild('field');
				$xmlResource->addAttribute('name', 'stockroom_groups');
				$xmlResource->addAttribute('transform', 'array');
				$xmlResource->addAttribute('defaultValue', '');
				$xmlResource->addAttribute('isRequiredField', 'false');
				$xmlResource->addAttribute('isFilterField', 'false');
				$xmlResource->addAttribute('isSearchableField', 'false');
				$xmlResource->addAttribute('isPrimaryField', 'false');
				$description = $xmlResource->addChild('description', Text::_('COM_REDSHOPB_STOCKROOM_GROUP_STOCKROOM_GROUP_LABEL'));

				break;

			case 'stockroom_group_id':
				$xmlResource = $xml->addChild('field');
				$xmlResource->addAttribute('name', 'stockroom_group_id');
				$xmlResource->addAttribute('transform', 'int');
				$xmlResource->addAttribute('defaultValue', '');
				$xmlResource->addAttribute('isRequiredField', 'false');
				$xmlResource->addAttribute('isFilterField', 'false');
				$xmlResource->addAttribute('isSearchableField', 'false');
				$xmlResource->addAttribute('isPrimaryField', 'false');
				$description = $xmlResource->addChild('description', Text::_('COM_REDSHOPB_STOCKROOM_GROUP_STOCKROOM_GROUP_ID_LABEL'));

				break;

			case 'stockroom_group_name':
				$xmlResource = $xml->addChild('field');
				$xmlResource->addAttribute('name', 'stockroom_group_name');
				$xmlResource->addAttribute('transform', 'string');
				$xmlResource->addAttribute('defaultValue', '');
				$xmlResource->addAttribute('isRequiredField', 'false');
				$xmlResource->addAttribute('isFilterField', 'false');
				$xmlResource->addAttribute('isSearchableField', 'false');
				$xmlResource->addAttribute('isPrimaryField', 'false');
				$description = $xmlResource->addChild('description', Text::_('COM_REDSHOPB_STOCKROOM_GROUP_NAME_DESC'));

				break;
		}
	}

	/**
	 * Load stockroom groups into the list
	 *
	 * @param   RModel  $model   Model
	 * @param   array   $items   Loaded items
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onRedshopbListAfterGetItems($model, &$items)
	{
		// We add our filtering logic to the stockroom list
		if ($model->get('context') == 'com_redshopb.stockroom.stockrooms')
		{
			if (!empty($items))
			{
				foreach ($items as $key => $item)
				{
					$items[$key]->stockroom_groups = $this->loadStockroomGroupsForStockroom($item->id);
				}
			}
		}
	}

	/**
	 * Load stockroom groups into the item
	 *
	 * @param   RModel  $model  Model
	 * @param   array   $item   Loaded item
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onRedshopbModelAdminAfterGetItem($model, &$item)
	{
		// We add our filtering logic to the stockroom item
		if ($model->get('context') == 'com_redshopb.edit.stockroom')
		{
			if (!empty($item) && $item->id > 0)
			{
				$item->stockroom_groups = $this->loadStockroomGroupsForStockroom($item->id);
			}
		}
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
		if ($table->get('_tbl') == '#__redshopb_stockroom')
		{
			// We initialise stockroom group property so we can bind it before store
			$table->stockroom_groups = null;
		}
		elseif ($table->get('_tbl') == '#__redshopb_order_item')
		{
			$table->stockroomGroupId = null;
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
		// We store Stockroom group reference
		if ($table->get('_tbl') == '#__redshopb_stockroom')
		{
			// Turn back stockroom_groups property for return system compatibility
			if ($table->get('_stockroom_groups'))
			{
				$table->set('stockroom_groups', $table->get('_stockroom_groups'));
				unset($table->_stockroom_groups);
			}

			// We only Stockroom group reference if we have stockroom saved properly
			if (!empty($table->id))
			{
				// Delete all items
				$db    = Factory::getDbo();
				$query = $db->getQuery(true)
					->delete('#__redshopb_stockroom_group_stockroom_xref')
					->where($db->qn('stockroom_id') . ' = ' . (int) $table->id);

				$db->setQuery($query);

				if (!$db->execute())
				{
					return false;
				}

				if (!is_array($table->get('stockroom_groups')) || count($table->get('stockroom_groups')) <= 0)
				{
					return true;
				}

				/** @var RedshopbTableStockroom_Group_Stockroom_Xref $xrefTable */
				$xrefTable = RedshopbTable::getAdminInstance('Stockroom_Group_Stockroom_Xref');

				// Store the new items
				foreach ($table->get('stockroom_groups') as $stockroomGroupId)
				{
					if (!(int) $stockroomGroupId)
					{
						continue;
					}

					$keys = array('id' => 0, 'stockroom_id' => $table->id, 'stockroom_group_id' => $stockroomGroupId);

					if (!$xrefTable->save($keys))
					{
						$table->setError($xrefTable->getError());

						return false;
					}
				}

				return true;
			}
		}

		return true;
	}

	/**
	 * Method for get stockroom of list product item
	 *
	 * @param   array|int  $productIds      List ID of product
	 * @param   array|int  $stockrooms      List ID of stockroom
	 * @param   int        $stockroomGroup  Specific stockroom group
	 *
	 * @return  array               Data of stockroom
	 */
	public static function getProductsStockroomData($productIds = array(), $stockrooms = array(), $stockroomGroup = 0)
	{
		$productIds = (array) $productIds;
		$stockrooms = (array) $stockrooms;

		if (empty($productIds))
		{
			return false;
		}

		$productIds = ArrayHelper::toInteger($productIds);

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('ref.*')
			->select($db->qn('sg.id', 'stockroom_group_id'))
			->select($db->qn('s.min_delivery_time'))
			->select('COALESCE(' . $db->qn('sg.name') . ', ' . $db->qn('s.name') . ') AS name')
			->select('COALESCE(' . $db->qn('sg.color') . ', ' . $db->qn('s.color') . ') AS color')
			->select('COALESCE(' . $db->qn('sg.description') . ', ' . $db->qn('s.description') . ') AS description')
			->select(
				'CONCAT(' . $db->qn('ref.product_id') . ',' . $db->quote('_') . ',' . $db->qn('ref.stockroom_id') . ','
				. $db->quote('_') . ',' . $db->qn('sg.id') . ') AS ' . $db->qn('key')
			)
			->from($db->qn('#__redshopb_stockroom_product_xref', 'ref'))
			->leftJoin($db->qn('#__redshopb_stockroom', 's') . ' ON ' . $db->qn('s.id') . ' = ' . $db->qn('ref.stockroom_id'))
			->leftJoin(
				$db->qn('#__redshopb_stockroom_group_stockroom_xref', 'sgsx') . ' ON ' . $db->qn('sgsx.stockroom_id') . ' = ' . $db->qn('s.id')
			)
			->leftJoin(
				$db->qn('#__redshopb_stockroom_group', 'sg') . ' ON ' . $db->qn('sg.id') . ' = ' . $db->qn('sgsx.stockroom_group_id')
				. ' AND  ' . $db->qn('sg.state') . ' > 0'
			)
			->where($db->qn('ref.product_id') . ' IN (' . implode(',', $productIds) . ')')
			->order($db->qn('s.min_delivery_time') . ' ASC');

		if (!empty($stockrooms))
		{
			$stockrooms = ArrayHelper::toInteger($stockrooms);

			$query->where($db->qn('ref.stockroom_id') . ' IN (' . implode(',', $stockrooms) . ')');
		}

		if (!empty($stockroomGroup))
		{
			$query->where($db->qn('sg.id') . ' = ' . (int) $stockroomGroup);
		}

		$items               = $db->setQuery($query)->loadObjectList('key');
		$stockroomGroupItems = array();
		$stockroomItems      = array();

		foreach ($items as $key => $item)
		{
			if ($item->stockroom_group_id)
			{
				$duplicateGroup = false;

				// Search for duplicate stockroom groups
				foreach ($stockroomGroupItems as $groupKey => $stockroomGroupItem)
				{
					// If there is already stockroom group in the list we add amounts
					if ($stockroomGroupItem->stockroom_group_id == $item->stockroom_group_id)
					{
						$duplicateGroup                          = true;
						$stockroomGroupItems[$groupKey]->amount += $item->amount;

						if ($item->unlimited == 1)
						{
							$stockroomGroupItems[$groupKey]->unlimited = $item->unlimited;
						}

						// If delivery time is better than previous one, then select that stockroom reference
						if ($item->min_delivery_time < $stockroomGroupItem->min_delivery_time)
						{
							$item->amount = $stockroomGroupItems[$groupKey]->amount;
							unset($stockroomGroupItems[$groupKey]);
							$stockroomGroupItems[$key] = $item;
						}

						break;
					}
				}

				if (!$duplicateGroup)
				{
					$stockroomGroupItems[$key] = $item;
				}
			}
			else
			{
				$stockroomItems[$key] = $item;
			}
		}

		return array_merge($stockroomGroupItems, $stockroomItems);
	}

	/**
	 * Trigger happens before item is added to the cart
	 *
	 * @param   array   $items         Cart Items.
	 * @param   array   $item          CurrentCart Item.
	 * @param   int     $quantity      Number of items.
	 * @param   int     $customerId    Customer id.
	 * @param   string  $customerType  Customer type.
	 *
	 * @return  void
	 */
	public function onBeforeRedshopbAddToCart(&$items, &$item, &$quantity, $customerId, $customerType)
	{
		$stockroomGroupId = Factory::getApplication()->input->getInt('stockroomGroupId', 0);

		if ($stockroomGroupId)
		{
			$item['stockroomGroupId'] = (int) $stockroomGroupId;
		}
	}

	/**
	 * Trigger happens after redshop have finished collecting data for the view
	 *
	 * @param   object  $view    View
	 * @param   RModel  $model   Model
	 * @param   string  $layout  Layout
	 *
	 * @return  void
	 */
	public function onAfterRedshopbViewShopDisplay(&$view, &$model, &$layout)
	{
		switch ($layout)
		{
			case 'delivery':
			case 'shipping':
			case 'payment':
			case 'confirm':
				$this->checkForExpiredStockroomGroupDeadlines();
				break;
		}
	}

	/**
	 * onRedshopbOrderVariablesReview
	 *
	 * @param   array  $variables  Order list variables
	 *
	 * @return array
	 */
	public function onRedshopbOrderVariablesReview($variables)
	{
		/**
		 * Extracted variables
		 *
		 * @var array  $items
		 */
		 extract($variables);

		foreach ($items as $item)
		{
			if (isset($item->stockroomGroupId) && $item->stockroomGroupId && RedshopbEntityStockroom_Group::load($item->stockroomGroupId)->isLoaded())
			{
				$showDelivery = true;
			}
		}

		return compact(array_keys(get_defined_vars()));
	}

	/**
	 * Trigger happens before redshop will list cart item in the checkout or in order item view
	 *
	 * @param   array   $items      List of items
	 * @param   object  $item       Item object
	 * @param   object  $field      Field object
	 * @param   array   $variables  Order list variables
	 *
	 * @return  boolean
	 */
	public function onBeforeRedshopbOrderProductListItemColumn(&$items, &$item, &$field, $variables)
	{
		if (!empty($item->stockroom_group_id))
		{
			/**
			 * Extracted variables
			 *
			 * @var string $view
			 * @var string $customerType
			 * @var string $customerId
			 * @var array  $data
			 * @var array  $stockPresented
			 * @var string $delivery
			 * @var bool   $showDelivery
			 */
			 extract($variables);

			switch ($field->fieldname)
			{
				case 'stock':
					if ($data['showStockAs'] == 'hide')
					{
						return false;
					}

					if ($view != 'order')
					{
						$group = 'customer' . $customerType . '_customerId' . $customerId;

						foreach (RedshopbHelperCart::cartFieldsForCheck() as $itemForCheck)
						{
							$group .= '_' . $itemForCheck;

							if (property_exists($item, $itemForCheck))
							{
								$group .= $item->{$itemForCheck};
							}
						}

						$group .= '_currencyId' . $item->currency_id;

						$field->group = $group;
						$field->name  = $field->fieldname;
						$field->id    = $field->fieldname;
					}
					elseif ($view == 'order' || $view == 'orders')
					{
						$fieldId   = $field->fieldname . '_' . $item->id . '_' . $item->product_id .
									'_' . $item->product_item_id . '_' . $item->collectionId;
						$fieldId  .= (isset($item->keyAccessories)) ? '_' . (string) $item->keyAccessories : '';
						$field->id = $fieldId;
					}

					$stockroomGroupStockrooms = self::getProductsStockroomData(array($item->product_id), array(), $item->stockroom_group_id);
					$item->stock              = 0;

					foreach ($stockroomGroupStockrooms as $stockroomGroupStockroom)
					{
						// Set unlimited if exists
						if ($stockroomGroupStockroom->unlimited == 1)
						{
							$item->stock = -1;
							break;
						}
						else
						{
							$item->stock += $stockroomGroupStockroom->amount;
						}
					}

					?><td class="field_<?php echo $field->fieldname ?>"><?php

if ($data['showStockAs'] == 'color_codes')
					{
	$class = '';

	if (isset($item->stock))
						{
		$amount = $item->stock;

		if ($amount == -1)
							{
			$class = 'inStock';
		}
		else
							{
			$amount = $amount < 0 ? 0 : $amount;
			$class  = ($amount > 0) ? 'inStock' : $class;
		}
	}

	switch ($stockPresented)
						{
		case 'semaphore':
			// Workaround for getColorAmount hardcoded fieldname
			$item->amount = $item->stock;
			$colorAmount  = RedshopbHelperProduct::getColorAmount($item);
			$iconStock    = 'ok';

			switch ($colorAmount)
								{
				case ' amountLessZero':
					$iconStock = 'remove';
					break;
				case ' amountMoreZeroLessLower':
					$iconStock = 'warning-sign';
					break;
			}

			$class .= $colorAmount;
			?>

			<i class="icon-<?php echo $iconStock ?> <?php echo $class ?>"></i>
								<br/>
								<?php
								break;
		case 'specific_color':
			$stockroomEntity = RedshopbEntityStockroom_Group::load($item->stockroom_group_id);
			$style           = '';

			if ($stockroomEntity->get('color'))
								{
				$style .= 'color: ' . $stockroomEntity->get('color') . ';';
			}

			$class .= ' stockroomSpecificColor';
			?><i class="icon-circle <?php echo $class ?>"
				 style="<?php echo $style; ?>"></i><?php
								break;
	}
}
else
					{
	if (isset($item->stock))
						{
		echo $item->stock == -1 ? Text::_('COM_REDSHOPB_STOCKROOM_UNLIMITED') : $item->stock;
	}
	else
						{
		echo $field->value == -1 ? Text::_('COM_REDSHOPB_STOCKROOM_UNLIMITED') : $field->value;
	}
}
					?></td><?php

					return false;
				case 'stockroom_id':
					if (!$showDelivery)
					{
						return true;
					}

					?><td class="field_<?php echo $field->fieldname ?>"><?php
					?></td><?php

					return false;
			}
		}

		return true;
	}

	/**
	 * onRedshopbAddToCartValidation
	 *
	 * @param   array  $variables  add to cart variables
	 * @param   null   $return     Flag for return status if needed
	 *
	 * @return  array|boolean
	 */
	public function onRedshopbAddToCartValidation($variables, &$return = null)
	{
		$stockroomGroupId = Factory::getApplication()->input->getInt('stockroomGroupId', 0);

		if (!$stockroomGroupId)
		{
			return true;
		}

		/**
		 * Extracted variables
		 *
		 * @var object $userCompany
		 * @var int    $productId
		 * @var int    $quantity
		 * @var int    $productItem
		 */
		 extract($variables);

		if (!empty($userCompany->stockroom_verification))
		{
			// Check stockroom amount if this adaptable for quantity item
			// @TODO: add possibility use stockroom groups for product items *bump*
			if (!$productItem)
			{
				$stockroom = self::getProductsStockroomData(array($productId), array(), $stockroomGroupId);

				if (!empty($stockroom))
				{
					$stockroom = current($stockroom);

					// Disable stockroom validation when use stock room groups
					$userCompany->stockroom_verification = 0;

					// Disable find stockroom id after event onRedshopbAddToCartValidation
					$findStockroomId = false;

					if (!$stockroom->unlimited && $stockroom->amount < $quantity)
					{
						$return = array(
							'items' => array(),
							'msg' => Text::_('COM_REDSHOPB_ADD_TO_CART_ERROR_STOCK_AMOUNT'),
							'msgStatus' => 'alert-error'
						);
					}
				}
			}
		}

		return compact(array_keys(get_defined_vars()));
	}

	/**
	 * onRedshopbExpandCheckMinimumDeliveryPeriodForOrder
	 *
	 * @param   array    $cartItem  Cart item
	 * @param   integer  $return    Number of deliver days
	 *
	 * @return  void
	 *
	 * @since 1.12.30
	 */
	public function onRedshopbExpandCheckMinimumDeliveryPeriodForOrder($cartItem, &$return)
	{
		if (empty($cartItem['stockroomGroupId']))
		{
			return;
		}

		$results = self::getProductsStockroomData(array($cartItem['productId']), array(), $cartItem['stockroomGroupId']);

		if (empty($results))
		{
			$return = -1;

			return;
		}

		$stockroom = reset($results);

		if (!$stockroom->unlimited && $stockroom->amount < $cartItem['quantity'])
		{
			$return = -1;

			return;
		}

		$joomlaConfig = Factory::getConfig();
		$user         = Factory::getUser();
		$currentTime  = Date::getInstance();
		$currentTime->setTimezone(new DateTimeZone($user->getParam('timezone', $joomlaConfig->get('offset'))));
		$weekDay = $currentTime->format('w', true);

		if ($weekDay > 0 && $weekDay < 6)
		{
			$stockroomTime = RedshopbEntityStockroom_Group::getInstance(
				$cartItem['stockroomGroupId']
			)->get('deadline_weekday_' . $weekDay);

			if ($stockroomTime != '00:00:00' || $currentTime->format('H:i:00', true) > $stockroomTime)
			{
				if ($return < 1)
				{
					// Deadline passed, add one day for delivery
					$return = 1;
				}
			}
			else
			{
				if ($return < 0)
				{
					$return = 0;
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
	public function onBeforeStoreRedshopb($table, $updateNulls = false)
	{
		// We store stockroom group in order items for history purposes
		if ($table->get('_tbl') == '#__redshopb_order_item')
		{
			// We only store stockroom group if possible
			if (property_exists($table, 'stockroomGroupId'))
			{
				if ($table->stockroomGroupId)
				{
					$table->stockroom_group_id   = $table->stockroomGroupId;
					$table->stockroom_group_name = RedshopbEntityStockroom_Group::getInstance($table->stockroomGroupId)->get('name');
				}

				unset($table->stockroomGroupId);
			}
		}
		elseif ($table->get('_tbl') == '#__redshopb_stockroom')
		{
			// Move stockroom_groups to non store property for avoid error
			$table->set('_stockroom_groups', $table->get('stockroom_groups'));
			unset($table->stockroom_groups);
		}

		return true;
	}

	/**
	 * Checks for Expired time on deadline and displays a message
	 *
	 * @return  void
	 */
	private function checkForExpiredStockroomGroupDeadlines()
	{
		$app          = Factory::getApplication();
		$customers    = RedshopbHelperCart::getCartCustomers();
		$joomlaConfig = Factory::getConfig();
		$user         = Factory::getUser();
		$currentTime  = Date::getInstance();
		$currentTime->setTimezone(new DateTimeZone($user->getParam('timezone', $joomlaConfig->get('offset'))));
		$weekDay             = $currentTime->format('w', true);
		$stockroomGroupNames = array();

		// If the days is from monday to friday
		if ($weekDay > 0 && $weekDay < 6)
		{
			foreach ($customers as $customer)
			{
				$cstring      = explode('.', $customer);
				$customerId   = $cstring[1];
				$customerType = $cstring[0];
				$cart         = RedshopbHelperCart::getCart($customerId, $customerType);
				$items        = $cart->get('items', array());

				foreach ($items as $cartItem)
				{
					// If item actually have stockroom group assigned
					if (!empty($cartItem['stockroomGroupId']))
					{
						$stockroomTime = RedshopbEntityStockroom_Group::getInstance(
							$cartItem['stockroomGroupId']
						)->get('deadline_weekday_' . $weekDay);

						if ($stockroomTime != '00:00:00' && $currentTime->format('H:i:00', true) > $stockroomTime)
						{
							$stockroomGroupNames[RedshopbEntityStockroom_Group::getInstance($cartItem['stockroomGroupId'])->get('name')] = 1;
						}
					}
				}
			}

			if (!empty($stockroomGroupNames))
			{
				$app->enqueueMessage(Text::_('COM_REDSHOPB_STOCKROOM_GROUP_DEADLINE_NOTIFICATION_LABEL'), 'warning');
			}
		}
	}
}
