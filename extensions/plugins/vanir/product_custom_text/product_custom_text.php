<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  product_custom_text
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */


defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;

JLoader::import('redshopb.library');

/**
 * Aesir E-Commerce - Product Custom Text plugin
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  product_custom_text
 * @since       1.0.0
 */
class PlgVanirProduct_Custom_Text extends CMSPlugin
{
	/**
	 * Auto load language
	 *
	 * @var    boolean
	 *
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Name of populate field
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	protected $fieldName = 'customText';

	/**
	 * Trigger happens before item is added to the cart
	 *
	 * @param   array     $items         Cart items
	 * @param   array     $item          CurrentCart Item.
	 * @param   integer   $quantity      Number of items.
	 * @param   integer   $customerId    Customer id.
	 * @param   string    $customerType  Customer type.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function onBeforeRedshopbAddToCart(&$items, &$item, &$quantity, $customerId, $customerType)
	{
		$input      = Factory::getApplication()->input;
		$customText = $input->getString($this->fieldName, '');
		$productId  = $input->getInt('productId', 0);

		if (null === $customText || ($productId > 0 && $productId != $item['productId']))
		{
			return;
		}

		// Store custom text data in item cart session.
		$item[$this->fieldName] = $customText;
	}

	/**
	 * On Before Store Redshopb
	 *
	 * @param   object   $table        Store values
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  true on success
	 *
	 * @throws  RuntimeException
	 *
	 * @since   1.0.0
	 */
	public function onBeforeStoreRedshopb($table, $updateNulls = false)
	{
		if ($table->getTableName() != '#__redshopb_order_item')
		{
			return true;
		}

		$data = $table->get('dataBeforeBind');

		if (!array_key_exists($this->fieldName, $data))
		{
			return true;
		}

		$params = new Registry($table->get('params'));
		$params->set($this->fieldName, $data[$this->fieldName]);

		$table->set('params', $params->toString());

		return true;
	}

	/**
	 * Method for prepare checkout fields
	 *
	 * @param   array  $checkoutFields  Checkout fields
	 * @param   array  $items           Cart items data.
	 * @param   RForm  $form            Cart items form.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function onVanirPrepareCheckoutFields(&$checkoutFields, &$items, $form)
	{
		// Add checkout fields - moved to configuration in case we'll need it againe
		$showAsColumn = $this->params->get('showAsColumn', false);

		if ($showAsColumn)
		{
			$checkoutFields[] = $this->fieldName;
			$label            = (string) $this->params->get('textLabel', Text::_('PLG_VANIR_PRODUCT_CUSTOM_TEXT_COLUMN_NAME'));

			// Add this field to form for validate and populate data.
			$xml = new SimpleXMLElement(
				'<field name="' . $this->fieldName . '" label="' . $label . '" type="text" />'
			);
			$form->setField($xml);
		}

		// Prepare necessary data.
		foreach ($items as &$item)
		{
			// In case custom text store in cart session.
			if (property_exists($item, $this->fieldName))
			{
				continue;
			}

			// In order edit or pdf generate
			if ($item->params instanceof Registry)
			{
				$params = $item->params;
			}
			else
			{
				$params = new Registry($item->params);
			}

			$item->{$this->fieldName} = $params->get($this->fieldName, '');
		}
	}

	/**
	 * Method for prepare checkout fields
	 *
	 * @param   integer  $orderId        ID of order
	 * @param   object   $customerOrder  Order data.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function onVanirPrepareOrderBeforePrintPDF($orderId, $customerOrder)
	{
		if (!$orderId || empty($customerOrder->regular->items))
		{
			return;
		}

		$key = $this->params->get('textLabel', Text::_('PLG_VANIR_PRODUCT_CUSTOM_TEXT_COLUMN_NAME'));

		// Put custom data into attributes
		foreach ($customerOrder->regular->items as $item)
		{
			if ($item->params instanceof Registry)
			{
				$param = $item->params;
			}
			else
			{
				$param = new Registry($item->params);
			}

			$value = new stdClass;

			if ($param->get($this->fieldName) != '')
			{
				$value->value           = $param->get($this->fieldName);
				$item->attributes[$key] = $value;
			}
		}
	}

	/**
	 * Makes sure that the custom text is kept when logging in
	 *
	 * @param   array   $item   The item being added to the cart
	 *
	 * @return  void
	 */
	public function setVanirUserDefaultValuesBeforeAddToCart(&$item)
	{
		$this->updateCustomTextInRequest($item);
	}

	/**
	 * Access the textLabel field outside the plugin
	 *
	 * @param   object    $item      Product item we are rendering
	 * @param   mixed     $field     Gets filled with the custom text HTML field
	 * @param   boolean   $isOffer   Is for offer item or not
	 *
	 * @return  void
	 */
	public function onVanirProductCustomTextGetField($item, &$field, $isOffer = false)
	{
		$label           = $this->params->get('textLabel', Text::_('PLG_VANIR_PRODUCT_CUSTOM_TEXT_COLUMN_NAME'));
		$showLabel       = $this->params->get('showLabel', true);
		$customTextLabel = '';

		if (!is_object($item))
		{
			$item = (object) $item;
		}

		if ($showLabel)
		{
			$customTextLabel = "<span class=\"custom_text_label\">{$label}: </span>";
		}

		if ($isOffer)
		{
			if (!isset($item->params) && isset($item->offer_id) && isset($item->product_id))
			{
				$db    = Factory::getDbo();
				$query = $db->getQuery(true);
				$query->select(array('*'));
				$query->from($db->qn("#__redshopb_offer_item_xref"));
				$query->where($db->qn('offer_id') . ' = ' . $db->q($item->offer_id));
				$query->where($db->qn('product_id') . ' = ' . $db->q($item->product_id));

				if (isset($item->product_item_id) && !is_null($item->product_item_id))
				{
					$query->where($db->qn('product_item_id') . ' = ' . $db->q($item->product_item_id));
				}

				$item = $db->setQuery($query)->loadObject();
			}

			if (isset($item) && isset($item->params))
			{
				$params = new Registry;
				$params->loadString($item->params);

				if ($params->get('customText'))
				{
					$field = "<div>" . $customTextLabel . "<span class=\"custom_text_value\">{$params->get('customText')}</span></div>";
				}
			}
		}
		else
		{
			if (isset($item->{$this->fieldName}))
			{
				$field = "<div>" . $customTextLabel . "<span class=\"custom_text_value\">{$item->{$this->fieldName}}</span></div>";
			}
		}
	}

	/**
	 * Access the custom text input outside the plugin
	 *
	 * @param   object    $item          Product item we are rendering
	 * @param   mixed     $inputHtml     Gets filled with the custom text HTML input
	 * @param   boolean   $isOffer       Is for offer item or not
	 *
	 * @return  void
	 */
	public function onVanirProductCustomTextGetInput($item, &$inputHtml, $isOffer = false)
	{
		$data      = array('productId' => $item->product_id);
		$inputHtml = RedshopbLayoutHelper::render('tags.product.custom_text', $data);
	}

	/**
	 * Adds the custom text to the request data
	 *
	 * Because we re-add the whole item to the cart when updating the quantity
	 * we need to set the customText in the request data so it gets added again
	 * when running the {@see onBeforeRedshopbAddToCart} trigger
	 *
	 * @param   array   $item   Item from the cart
	 *
	 * @return   void
	 */
	public function onAECSetItemQuantityByHashBeforeAddToCart(&$item)
	{
		$this->updateCustomTextInRequest($item);
	}

	/**
	 * Updates the customText parameter in the request data
	 *
	 * @param   array   $item   Single item from the cart
	 *
	 * @return   void
	 */
	private function updateCustomTextInRequest($item)
	{
		if (array_key_exists($this->fieldName, $item))
		{
			Factory::getApplication()->input->set($this->fieldName, $item[$this->fieldName]);
		}
	}

	/**
	 * Add custom text to offer item before offer item is stored
	 *
	 * @param   array   $offerItem   Single offer item
	 * @param   string  $customText  Custom text
	 *
	 * @return   void
	 */
	public function onRedshopbOfferItemStore(&$offerItem, $customText)
	{
		$params   = array('customText' => $customText);
		$registry = new Registry;
		$registry->loadArray($params);
		$stringParams = (string) $registry;

		$offerItem['params'] = $stringParams;
	}
}
