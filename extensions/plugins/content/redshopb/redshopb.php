<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  redshopb_newsletter
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Plugin\CMSPlugin;

Table::addIncludePath(JPATH_SITE . '/components/com_redshopb/tables');
JLoader::import('redshopb.library');

/**
 * PlgContent_Redshopb class.
 *
 * @package  Redshopb.Plugin
 * @since    1.6.8
 */
class PlgContentRedshopb extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 */
	protected $autoloadLanguage = true;

	/**
	 * @var array
	 */
	protected static $products = array();

	/**
	 * Method is called right after the content is saved.
	 *
	 * @param   string  $context  The context of the content passed to the plugin.
	 * @param   object  $table    A Content object.
	 * @param   bool    $isNew    If the content is just about to be created.
	 *
	 * @return  void|boolean
	 */
	public function onContentAfterSave($context, $table, $isNew)
	{
		$dispatcher = RFactory::getDispatcher();
		PluginHelper::importPlugin('redshopb_newsletter');

		try
		{
			// Trigger the onRedshopbAfterDelete event.
			$dispatcher->trigger('onRedshopbAfterSave', array($context, $table, $isNew));
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return true;
	}

	/**
	 * Content is passed by reference, but after the deletion.
	 *
	 * @param   string  $context  The context of the content passed to the plugin.
	 * @param   object  $table    A Content object.
	 *
	 * @return  void|boolean
	 */
	public function onContentAfterDelete($context, $table)
	{
		$dispatcher = RFactory::getDispatcher();
		PluginHelper::importPlugin('redshopb_newsletter');

		try
		{
			// Trigger the onRedshopbAfterDelete event.
			$dispatcher->trigger('onRedshopbAfterDelete', array($context, $table));
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return true;
	}

	/**
	 * Plugin that retrieves contact information for contact
	 *
	 * @param   string   $context   The context of the content being passed to the plugin.
	 * @param   mixed    $article   An object with a "text" property
	 * @param   mixed    $params    Additional parameters. See {@see PlgContentContent()}.
	 * @param   integer  $page      Optional page number. Unused. Defaults to zero.
	 *
	 * @return  boolean	True on success.
	 */
	public function onContentPrepare($context, &$article, $params, $page = 0)
	{
		// Simple performance check to determine whether bot should process further
		if (strpos($article->text, '{redshopb.') === false)
		{
			return true;
		}

		$regex = '/{redshopb\.(.+?):(.+?)}/i';

		// Find all instances of plugin and put in $matches for redshopb
		preg_match_all($regex, $article->text, $matches, PREG_SET_ORDER);

		// No matches, skip this
		if ($matches)
		{
			$loadCart = false;

			foreach ($matches as $match)
			{
				// $match[0] is full pattern match, $match[1] is the section, $match[2] is the ID
				switch ($match[1])
				{
					case 'product':
					default:
						$output   = $this->renderProduct((int) $match[2]);
						$loadCart = true;
					break;
				}

				// We should replace only first occurrence in order to allow positions with the same name to regenerate their content:
				$article->text = preg_replace("|$match[0]|", addcslashes($output, '\\$'), $article->text, 1);
			}

			if ($loadCart)
			{
				RHelperAsset::load('redshopb.shop.js', 'com_redshopb');
				RedshopbHelperCommon::initCartScript();
			}
		}

		return true;
	}

	/**
	 * Render Product Data
	 *
	 * @param   int  $productId  Product id
	 *
	 * @return mixed
	 */
	protected function renderProduct($productId)
	{
		if (array_key_exists($productId, self::$products))
		{
			return self::$products[$productId];
		}

		$values = new stdClass;
		RedshopbHelperShop::setUserStates($values);
		$collections        = RedshopbHelperCollection::getCustomerCollectionsForShop($values->customerId, $values->customerType);
		$collectionProducts = array();
		$hasProduct         = false;
		$basePath           = JPATH_SITE . $this->params->get('baseLayoutPath', '/components/com_redshopb/layouts/');

		// Get the actual products per collection
		if ($collections)
		{
			foreach ($collections as $collectionId)
			{
				$products = $this->getProduct(
					$values->customerId, $values->customerType, $collectionId, $productId
				);

				if (!empty($products))
				{
					$hasProduct                        = true;
					$collectionProducts[$collectionId] = $products;
				}
			}
		}
		elseif ($collections === false)
		{
			$products = $this->getProduct(
				$values->customerId, $values->customerType, 0, $productId
			);

			if (!empty($products))
			{
				$hasProduct            = true;
				$collectionProducts[0] = $products;
			}
		}

		if (!$hasProduct)
		{
			self::$products[$productId] = '';
		}
		else
		{
			ob_start();

			echo '<div class="redshopbProductInContent">'
				. RedshopbHelperTemplate::renderTemplate(
					'product-list-collection', 'shop', $this->params->get('renderTemplateName', 'product-list-collection'
					),
					array(
						"collectionProducts" => $collectionProducts,
						"showPagination" => false,
						"showAs" => 'list',
						"collectionId" => 0,
						'basePath' => $basePath,
						'cartPrefix' => 'productContent'
					),
					$basePath
				) . '</div>';

			self::$products[$productId] = ob_get_clean();
		}

		return self::$products[$productId];
	}

	/**
	 * Get Product Data
	 *
	 * @param   int     $customerId    Customer id
	 * @param   string  $customerType  Customer type
	 * @param   int     $collectionId  Collection id
	 * @param   int     $productId     Product id
	 *
	 * @return array
	 */
	protected function getProduct($customerId, $customerType, $collectionId, $productId)
	{
		$products = array();
		$product  = RedshopbHelperProduct::loadProduct($productId);

		if ($product)
		{
			$products[$productId] = $product;
		}

		if (!empty($products))
		{
			RModelAdmin::addIncludePath(JPATH_SITE . '/components/com_redshopb/models');
			$shopModel                  = RModelAdmin::getInstance('Shop', 'RedshopbModel', array('ignore_request' => true));
			$preparedItems              = $shopModel->prepareItemsForShopView($products, $customerId, $customerType, $collectionId, true);
			$preparedItems->productData = $products;

			return $preparedItems;
		}

		return array();
	}
}
