<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Browser
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Multilanguage;
/**
 * Custom Breadcrumbs class.
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Browser
 * @since       1.0
 */
class RedshopbBrowserBreadcrumbs
{
	/**
	 * Impersonation Breadcrumbs
	 *
	 * @var array
	 *
	 * @since 1.13.0
	 */
	protected static $impersonationBreadcrumbs = array();

	/**
	 * Get Impersonation Breadcrumbs
	 *
	 * @return array
	 *
	 * @since 1.13.0
	 */
	public static function getImpersonationBreadcrumbs()
	{
		return self::$impersonationBreadcrumbs;
	}

	/**
	 * Render Impersonation Breadcrumbs
	 *
	 * @return string
	 *
	 * @since   1.13.0
	 */
	public static function renderImpersonationBreadcrumbs()
	{
		$params      = RedshopbApp::getConfig();
		$cloneParams = clone $params;
		$cloneParams->set('breadcrumbs_here', 'COM_REDSHOPB_CONFIG_IMPERSONATION_BREADCRUMBS_HERE');
		$cloneParams->set('showLast', 1);
		$separator = self::setSeparator($cloneParams->get('separator'));
		$html      = '';

		if (!empty(self::$impersonationBreadcrumbs))
		{
			$html = RedshopbLayoutHelper::render('browser.impersonationbreadcrumb', array(
					'breadcrumbs' => self::$impersonationBreadcrumbs,
					'params' => $cloneParams,
					'class_sfx' => '',
					'separator' => $separator)
			);
		}

		return $html;
	}

	/**
	 * Create and add a new pathway object.
	 *
	 * @param   string  $name                               Name of the item
	 * @param   string  $link                               Link to the item
	 * @param   bool    $addInImpersonationBreadcrumbArray  Add In Impersonation Breadcrumb Array or in the main breadcrumb
	 *
	 * @return  void
	 *
	 * @since   1.13.0
	 */
	protected static function makeItem($name, $link, $addInImpersonationBreadcrumbArray = false)
	{
		if (RedshopbHelperACL::getPermissionInto('impersonate', 'order'))
		{
			if ($addInImpersonationBreadcrumbArray)
			{
				$item       = new stdClass;
				$item->name = html_entity_decode($name, ENT_COMPAT, 'UTF-8');
				$item->link = $link;

				self::$impersonationBreadcrumbs[] = $item;
			}
			else
			{
				$pathway = Factory::getApplication()->getPathway();
				$pathway->addItem($name, $link);
			}
		}
	}

	/**
	 * Render breadcrumbs
	 *
	 * @return  string
	 *
	 * @since 1.0
	 */
	public static function renderBreadcrumbs()
	{
		$params          = RedshopbApp::getConfig();
		$showBreadcrumbs = $params->get('show_breadcrumbs', 1);
		$html            = '';

		if ($showBreadcrumbs)
		{
			// Set the default separator
			$separator   = self::setSeparator($params->get('separator'));
			$breadcrumbs = self::getBreadcrumbs($params);

			$html = RedshopbLayoutHelper::render('browser.breadcrumb', array(
					'breadcrumbs' => $breadcrumbs,
					'params' => $params,
					'class_sfx' => '',
					'separator' => $separator)
			);

			if ($params->get('impersonation_breadcrumbs', 'show_in_main') == 'show_separate')
			{
				$html .= self::renderImpersonationBreadcrumbs();
			}
		}

		return $html;
	}

	/**
	 * Gets all links from Itemid
	 *
	 * @param   \Joomla\Registry\Registry  $params  Breadcrumb parameters
	 *
	 * @return  array
	 *
	 * @since 1.0
	 */
	public static function getBreadcrumbs($params)
	{
		$app     = Factory::getApplication();
		$pathway = $app->getPathway();
		$items   = $pathway->getPathway();
		$lang    = Factory::getLanguage();
		$menu    = $app->getMenu();

		// Look for the home menu
		if (Multilanguage::isEnabled())
		{
			$home = $menu->getDefault($lang->getTag());
		}
		else
		{
			$home = $menu->getDefault();
		}

		$count = count($items);

		// Don't use $items here as it references Pathway properties directly
		$crumbs	= array();

		for ($i = 0; $i < $count; $i ++)
		{
			$crumbs[$i]       = new stdClass;
			$crumbs[$i]->name = stripslashes(htmlspecialchars($items[$i]->name, ENT_COMPAT, 'UTF-8'));
			$crumbs[$i]->link = RedshopbRoute::_($items[$i]->link);

			if ((boolean) $app->input->getString('mycollections') && strcmp($app->input->get('view'), 'shop') === 0)
			{
				if (strrpos($crumbs[$i]->link, '?') > 0)
				{
					$crumbs[$i]->link .= '&mycollections=1';
				}
				else
				{
					$crumbs[$i]->link .= '?mycollections=1';
				}
			}
		}

		if ($params->get('showHome', 1))
		{
			$item       = new stdClass;
			$item->name = htmlspecialchars($params->get('homeText', Text::_('COM_REDSHOPB_CONFIG_BREADCRUMBS_HOME')));
			$item->link = RedshopbRoute::_('index.php?Itemid=' . $home->id);
			array_unshift($crumbs, $item);
		}

		return $crumbs;
	}

	/**
	 * Set the breadcrumbs separator for the breadcrumbs display.
	 *
	 * @param   Uri  $uri              Uri of the new breadcrumb
	 * @param   bool $changePosition   Move found item in the last position
	 *
	 * @return  boolean
	 *
	 * @since 1.0
	 */
	public static function checkIfBreadcrumbExist($uri, $changePosition = false)
	{
		$id      = (int) $uri->getVar('id', 0);
		$layout  = $uri->getVar('layout', 'default');
		$view    = $uri->getVar('view', '');
		$pathway = Factory::getApplication()->getPathway();
		$crumbs  = $pathway->getPathway();
		$browser = RedshopbBrowser::getInstance(RedshopbBrowser::REDSHOPB_HISTORY);

		foreach ($crumbs as $key => $crumb)
		{
			$linkUri    = $browser->getUri($crumb->link);
			$linkView   = $linkUri->getVar('view', '');
			$linkId     = (int) $linkUri->getVar('id', 0);
			$layoutView = $linkUri->getVar('layout', 'default');

			if ($linkView == $view && $linkId == $id && $layoutView == $layout)
			{
				if ($changePosition === true)
				{
					$crumbs[] = $crumb;
				}

				if ($changePosition === true || $changePosition === -1)
				{
					unset($crumbs[$key]);
					$pathway->setPathway($crumbs);
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * Set the breadcrumbs separator for the breadcrumbs display.
	 *
	 * @param   string  $custom  Custom xhtml complient string to separate the
	 * items of the breadcrumbs
	 *
	 * @return  string	Separator string
	 *
	 * @since   1.5
	 */
	public static function setSeparator($custom = null)
	{
		$lang = Factory::getLanguage();

		// If a custom separator has not been provided we try to load a template
		// specific one first, and if that is not present we load the default separator
		if ($custom == null)
		{
			if ($lang->isRtl())
			{
				$separator = HTMLHelper::_('image', 'system/arrow_rtl.png', null, null, true);
			}
			else
			{
				$separator = HTMLHelper::_('image', 'system/arrow.png', null, null, true);
			}
		}
		else
		{
			$separator = htmlspecialchars($custom);
		}

		return $separator;
	}

	/**
	 * Generate breadcrumbs
	 *
	 * @param   string  $url  Current URL
	 *
	 * @throws Exception
	 *
	 * @return  void
	 *
	 * @since 1.0
	 */
	public static function generateBreadcrumbs($url = 'SERVER')
	{
		$browser                        = RedshopbBrowser::getInstance(RedshopbBrowser::REDSHOPB_HISTORY);
		$uri                            = $browser->getUri($url);
		$currentId                      = $uri->getVar('id');
		$currentLayout                  = $uri->getVar('layout');
		$app                            = Factory::getApplication();
		$pathway                        = $app->getPathway();
		$menus                          = $app->getMenu('site');
		$createLayouts                  = array('edit', 'create');
		$view                           = $uri->getVar('view');
		$history                        = array();
		$getHistory                     = true;
		self::$impersonationBreadcrumbs = array();
		$componentConfig                = RedshopbApp::getConfig();
		$impersonationBreadcrumbsConfig = $componentConfig->get('impersonation_breadcrumbs', 'show_in_main');

		if (in_array(
			$view,
			array(
				'contact_list',
				'reports',
				'quick_order',
				'return_order',
				'mypage',
				'mywallet',
				'shop',
				'dashboard',
				'layout_list',
				'layout_item',
				'layout'
			)
		))
		{
			$history[]  = $url;
			$getHistory = false;
		}

		elseif (in_array($view, array(RInflector::pluralize($view), 'myprofile')))
		{
			$browser->clearHistory();
			$history[] = $url;
		}

		if ($getHistory)
		{
			if (($currentLayout == 'edit' && $currentId) || !in_array($currentLayout, $createLayouts))
			{
				$browser->browse();
				$browser->clearHistoryUntil('index.php?' . $uri->getQuery());
			}

			$history = $browser->getHistory();
		}

		if (count($history))
		{
			foreach ($history as $link)
			{
				$linkUri    = $browser->getUri($link);
				$linkView   = $linkUri->getVar('view');
				$linkId     = (int) $linkUri->getVar('id');
				$layout     = $linkUri->getVar('layout', 'default');
				$text       = '';
				$viewPlural = RInflector::pluralize($linkView);
				$addCrumb   = true;

				if ($linkView != 'shop' && $linkView != 'manufacturerlist')
				{
					if (self::checkIfBreadcrumbExist($linkUri))
					{
						continue;
					}
				}

				switch ($linkView)
				{
					case 'collection':
						$text = Text::_('COM_REDSHOPB_BREADCRUMB_' . strtoupper($linkView))
							. ': ' . RedshopbHelperBreadcrumb::getItemName($linkId, '#__redshopb_' . $linkView);
						break;

					case 'word':
						$text = self::getText('COM_REDSHOPB_BREADCRUMB_' . strtoupper($linkView), $linkView)
							. ': ' . RedshopbHelperBreadcrumb::getItemName($linkId, '#__redshopb_word', 'word');
						break;

					case 'address':
						$text    = Text::_('COM_REDSHOPB_BREADCRUMB_' . strtoupper($linkView)) . ': ';
						$address = RedshopbEntityAddress::getInstance($linkId)->getExtendedData();

						if (empty($address->name))
						{
							$text .= $address->address;
						}
						else
						{
							$text .= $address->name;
						}

						break;

					case 'company':
					case 'department':
					case 'product':
					case 'layout':
					case 'category':
					case 'tag':
					case 'newsletter':
					case 'newsletter_list':
					case 'offer':
					case 'stockroom':
					case 'product_discount_group':
					case 'manufacturer':
					case 'cart':
					case 'unit_measure':
					case 'field':
					case 'field_group':
					case 'field_value':
					case 'filter_fieldset':
					case 'country':
						$text = self::getText('COM_REDSHOPB_BREADCRUMB_' . strtoupper($linkView), $linkView)
							. ': ' . Text::_(RedshopbHelperBreadcrumb::getItemName($linkId, '#__redshopb_' . $linkView));
						break;
					case 'currency':
					case 'state':
					case 'tax_group':
					case 'holiday':
					case 'tax':
						$text = self::getText('COM_REDSHOPB_BREADCRUMB_' . strtoupper($linkView), $linkView)
							. ': ' . RedshopbHelperBreadcrumb::getItemName($linkId, '#__redshopb_' . $linkView);
						break;
					case 'myfavoritelist':
						$text = self::getText('COM_REDSHOPB_BREADCRUMB_MYFAVORITELIST', $linkView)
							. ': ' . RedshopbHelperBreadcrumb::getItemName($linkId, '#__redshopb_favoritelist');
						break;
					case 'shipping_rate':
						$text = self::getText('COM_REDSHOPB_BREADCRUMB_SHIPPING_RATE', $linkView)
							. ': ' . RedshopbHelperBreadcrumb::getItemName($linkId, '#__redshopb_shipping_rates');
						break;
					case 'myoffer':
						$text = Text::_('COM_REDSHOPB_BREADCRUMB_OFFER');

						if ($currentLayout != 'requestoffer')
						{
							$text .= ': ' . RedshopbHelperBreadcrumb::getItemName($linkId, '#__redshopb_offer');
						}

						break;
					case 'template':
						$templateName = $linkUri->getVar('templateName');

						if (strpos($templateName, '.') !== false)
						{
							$templateEntity = RedshopbEntityTemplate::load($linkId);
							$text           = Text::_('COM_REDSHOPB_BREADCRUMB_' . strtoupper($linkView))
								. ': ' . str_replace('.', '/', $templateName)
								. '/' . RedshopbHelperTemplate::getGroupFolderName($templateEntity->get('template_group'))
								. '/' . $templateEntity->get('scope') . '/' . $templateEntity->get('alias') . '.php';
						}
						else
						{
							$text = Text::_('COM_REDSHOPB_BREADCRUMB_' . strtoupper($linkView))
								. ': ' . RedshopbHelperBreadcrumb::getItemName($linkId, '#__redshopb_' . $linkView);

							if ($templateName != 'none' && $templateName != '')
							{
								$text .= Text::sprintf('COM_REDSHOPB_BREADCRUMB_CUSTOMIZATION_IN', $templateName);
							}
							elseif ($templateName == 'none')
							{
								$text .= Text::_('COM_REDSHOPB_BREADCRUMB_NEW_CUSTOMIZATION');
							}
						}

						break;
					case 'layout_list':
						$text = self::getText('COM_REDSHOPB_BREADCRUMB_' . strtoupper($linkView), $linkView);
						break;
					case 'layout_item':
						$layoutItemId = explode('|', base64_decode($linkUri->getVar('id')));
						$text         = Text::_('COM_REDSHOPB_BREADCRUMB_' . strtoupper($linkView)) . ': ';

						if (isset($layoutItemId[1]))
						{
							$text .= str_replace('.', '/', $layoutItemId[1])
								. '/' . str_replace('.', '/', $layoutItemId[0]) . '.php';
						}
						else
						{
							$text .= $layoutItemId[0] . ' ' . Text::sprintf('COM_REDSHOPB_BREADCRUMB_NEW_CUSTOMIZATION');
						}
						break;
					case 'user':
						$redshopbUser = RedshopbHelperUser::getUser($linkId);

						if (isset($redshopbUser->name))
						{
							$text = Text::_('COM_REDSHOPB_BREADCRUMB_USER') . ': ' . $redshopbUser->name;
						}

						break;
					case 'order':
					case 'product_item':
					case 'description':
						$text = Text::_('COM_REDSHOPB_BREADCRUMB_' . strtoupper($linkView)) . ': ' . $linkId;
						break;
					case 'all_price':
						$text = Text::_('COM_REDSHOPB_BREADCRUMB_PRICE') . ': ' . $linkId;
						break;
					case 'price_debtor_group':
						$text = Text::_('COM_REDSHOPB_BREADCRUMB_' . strtoupper($linkView))
							. ': ' . RedshopbHelperBreadcrumb::getItemName($linkId, '#__redshopb_customer_price_group');
						break;
					case 'all_discount':
						$text = Text::_('COM_REDSHOPB_BREADCRUMB_DISCOUNT') . ': ' . $linkId;
						break;
					case 'discount_debtor_group':
						$text = Text::_('COM_REDSHOPB_BREADCRUMB_' . strtoupper($linkView))
							. ': ' . RedshopbHelperBreadcrumb::getItemName($linkId, '#__redshopb_customer_discount_group');
						break;
					case 'contact_list':
					case 'mypage':
					case 'mywallet':
					case 'myprofile':
					case 'quick_order':
					case 'return_order':
					case 'report_sales_shipping':
					case 'report_sales_orders':
					case 'report_customers_new':
					case 'report_customers_most_orders':
					case 'report_products_top_sellers':
					case 'report_products_top_views':
					case 'report_products_low_stock':
					case 'report_products_in_carts':
					case 'report_general_newsletter':
					case 'recent_purchased_products':
					case 'collections':
						$text = Text::_('COM_REDSHOPB_BREADCRUMB_' . strtoupper($linkView));
						break;

					case 'shop':
					case 'manufacturerlist':
						$text             = Text::_('COM_REDSHOPB_BREADCRUMB_' . strtoupper($linkView));
						$customerId       = $app->getUserState('shop.customer_id', 0);
						$customerType     = $app->getUserState('shop.customer_type', '');
						$isCollectionMode = (isset($app->input->getArray()['collection_id']) || $layout == 'collection' ? '1' : '0');

						$values = new stdClass;
						RedshopbHelperShop::setUserStates($values);
						$user = RedshopbHelperCommon::getUser();

						// Create a company node children of home. Not for B2C mode.
						if ($values->companyId && !$user->b2cMode && !RedshopbEntityCompany::load($values->companyId)->get('hide_company'))
						{
							$model              = RModelAdmin::getInstance('Shop', 'RedshopbModel');
							$filterShopName     = $model->getState('filter.shopname');
							$departmentsPerPage = (int) $componentConfig->get('shop_departments_per_page', 12);
							$departments        = array();
							$companiesAll       = array();

							if ($currentLayout == 'default' || $currentLayout == 'categories')
							{
								// Check if customer is selected so we can get products for showing
								if ($values->customerId != 0 && $values->customerType != '')
								{
									$departments = RedshopbHelperACL::listAvailableDepartments(
										$user->id,
										'objectList',
										$filterShopName ? 0 : $values->companyId,
										false,
										0,
										'',
										'redshopb.order.impersonate',
										$filterShopName ? $filterShopName : ''
									);
								}

								// Customer is not selected, show all employees, companies and departments
								else
								{
									$departments  = RedshopbHelperACL::listAvailableDepartments(
										$user->id,
										'objectList',
										$filterShopName ? 0 : $values->companyId,
										false,
										$filterShopName ? 0 : $values->departmentId,
										'',
										'redshopb.order.impersonate',
										$filterShopName ? $filterShopName : '',
										0,
										$departmentsPerPage
									);
									$companiesAll = RedshopbHelperACL::listAvailableCompanies(
										$user->id,
										'objectList'
									);
								}
							}

							$parents          = $model->getParents('#__redshopb_company', $values->companyId, $companiesAll);
							$shoppingOnBehalf = false;

							// Create "Shopping on behalf of" node in menu
							if ($values->customerType != '' && $values->customerId != 0
								&& (!RedshopbHelperUser::getUser() || RedshopbHelperUser::getUser()->id != $values->customerId))
							{
								$shoppingOnBehalf = true;
							}

							if (RedshopbHelperACL::getPermissionInto('impersonate', 'order'))
							{
								self::makeItem(
									Text::_('COM_REDSHOPB_BREADCRUMB_IMPERSONATION'),
									RedshopbHelperRoute::getRoute(
										'index.php?option=com_redshopb&task=shop.savepath&company_id=0&department_id=0&rsbuser_id=0'
									),
									$impersonationBreadcrumbsConfig != 'show_in_main'
								);
							}

							if ($parents)
							{
								foreach ($parents as $parent)
								{
									if (!$shoppingOnBehalf
										|| ($shoppingOnBehalf && !($values->customerType == 'company' && $parent->id == $values->customerId))
									)
									{
										self::makeItem(
											$parent->customer_number . ' ' . $parent->name,
											RedshopbHelperRoute::getRoute('index.php?option=com_redshopb&task=shop.savepath&company_id=' . $parent->id
												. '&department_id=0&rsbuser_id=0'
											),
											$impersonationBreadcrumbsConfig != 'show_in_main'
										);
									}
								}
							}
							else
							{
								if (!$filterShopName)
								{
									if (!$shoppingOnBehalf || ($shoppingOnBehalf && $values->customerType != 'company'))
									{
										self::makeItem(
											RedshopbEntityCompany::getInstance($values->companyId)->get('name'),
											RedshopbHelperRoute::getRoute(
												'index.php?option=com_redshopb&task=shop.savepath&company_id=' . $values->companyId
												. '&department_id=0&rsbuser_id=0'
											),
											$impersonationBreadcrumbsConfig != 'show_in_main'
										);
									}
								}
							}

							if ($values->departmentId)
							{
								// Create a department node children of company.
								$departmentParents = $model->getParents('#__redshopb_department', $values->departmentId, $departments);

								if ($departmentParents)
								{
									foreach ($departmentParents as $parent)
									{
										if (!$shoppingOnBehalf
											|| ($shoppingOnBehalf && !($values->customerType == 'department' && $parent->id == $values->customerId))
										)
										{
											self::makeItem(
												$parent->name,
												RedshopbHelperRoute::getRoute('index.php?option=com_redshopb&task=shop.savepath&company_id='
													. $values->companyId . '&department_id=' . $parent->id . '&rsbuser_id=0'
												),
												$impersonationBreadcrumbsConfig != 'show_in_main'
											);
										}
									}
								}
								else
								{
									if (!$filterShopName)
									{
										if (!$shoppingOnBehalf || ($shoppingOnBehalf && $values->customerType != 'department'))
										{
											self::makeItem(
												RedshopbHelperDepartment::getName($values->departmentId),
												RedshopbHelperRoute::getRoute('index.php?option=com_redshopb&task=shop.savepath&company_id='
													. $values->companyId . '&department_id=' . $values->departmentId . '&rsbuser_id=0'
												),
												$impersonationBreadcrumbsConfig != 'show_in_main'
											);
										}
									}
								}
							}

							if ($filterShopName)
							{
								self::makeItem(
									Text::_('COM_REDSHOPB_SHOP_SEARCH_RESULTS'),
									RedshopbHelperRoute::getRoute('index.php?option=com_redshopb&task=shop.savepath&company_id='
										. $values->companyId . '&department_id=' . $values->departmentId . '&rsbuser_id=0'
									),
									$impersonationBreadcrumbsConfig != 'show_in_main'
								);
							}

							// Create "Shopping on behalf of" node in menu
							if ($shoppingOnBehalf)
							{
								$nCompanyId    = 0;
								$nDepartmentId = 0;
								$nRsbUserId    = 0;
								$nName         = '';

								switch ($values->customerType)
								{
									case 'employee' :
										$nCompanyId    = RedshopbHelperUser::getUserCompanyId($values->customerId);
										$nDepartmentId = RedshopbHelperUser::getUserDepartmentId($values->customerId);
										$nRsbUserId    = $values->customerId;
										$nName         = RedshopbHelperUser::getUser($values->customerId)->name;
										break;
									case 'department' :
										$nCompanyId    = RedshopbHelperDepartment::getCompanyId($values->customerId);
										$nDepartmentId = $values->customerId;
										$nName         = RedshopbHelperDepartment::getName($values->customerId);
										break;
									case 'company' :
										$nCompanyId = $values->customerId;
										$nName      = RedshopbEntityCompany::getInstance($values->customerId)->get('name');
										break;
								}

								self::makeItem(
									Text::_('COM_REDSHOPB_SHOP_SHOPPING_ON_BEHALF') . ' ' . $nName,
									RedshopbHelperRoute::getRoute('index.php?option=com_redshopb&task=shop.savepath&company_id=' . $nCompanyId
										. '&department_id=' . $nDepartmentId . '&rsbuser_id=' . $nRsbUserId
									),
									$impersonationBreadcrumbsConfig != 'show_in_main'
								);
							}
						}

						if ($layout == 'default')
						{
							$addCrumb = false;
						}

						if ($linkView == 'manufacturerlist')
						{
							$layout = 'manufacturerlist';
						}

						$usingCollections = $app->input->getBool('mycollections', false)
							|| RedshopbHelperShop::inCollectionMode(RedshopbEntityCompany::getInstance($values->companyId));

						if (in_array($layout, array('category', 'product', 'categories'))
							&& !$isCollectionMode
							&& RedshopbApp::getConfig()->get('breadcrumbsShowCategories'))
						{
							$link          = RedshopbHelperRoute::getRoute('index.php?option=com_redshopb&view=shop&layout=categories');
							$collectionUri = $browser->getUri($link);

							if (!self::checkIfBreadcrumbExist($collectionUri, true))
							{
								$itemId = RedshopbHelperRoute::findRedshopbMenuItem('index.php?option=com_redshopb&view=shop&layout=categories');
								$text   = Text::_('COM_REDSHOPB_BREADCRUMB_CATEGORIES');

								if ($itemId)
								{
									$item = $menus->getItem((int) $itemId);

									if ($item)
									{
										$text = $item->title;
									}
								}

								$pathway->addItem($text, $link);
							}
						}

						switch ($layout)
						{
							case 'category':
								if (!RedshopbHelperShop::inCollectionMode(RedshopbEntityCompany::getInstance($values->companyId))
									&& (boolean) Factory::getApplication()->input->get('mycollections', false))
								{
									$link          = RedshopbHelperRoute::getRoute('index.php?option=com_redshopb&view=shop&layout=collections');
									$collectionUri = $browser->getUri($link);

									if (!self::checkIfBreadcrumbExist($collectionUri, true))
									{
										$itemId = RedshopbHelperRoute::findRedshopbMenuItem(
											'index.php?option=com_redshopb&view=shop&layout=collections'
										);
										$text   = Text::_('COM_REDSHOPB_BREADCRUMB_COLLECTIONS_TITLE');

										if ($itemId)
										{
											$item = $menus->getItem((int) $itemId);

											if ($item)
											{
												$text = $item->title;
											}
										}

										$pathway->addItem($text, $link);
									}
								}

								// Remove an empty category link
								$link             = RedshopbHelperRoute::getRoute('index.php?option=com_redshopb&view=shop&layout=category');
								$prodWithoutIdUri = $browser->getUri($link);
								self::checkIfBreadcrumbExist($prodWithoutIdUri, -1);
								$parentCategories = RedshopbHelperCategory::getParentCategories($customerId, $customerType, $linkId);

								// We need to get full path for category
								if ($parentCategories)
								{
									foreach ($parentCategories as $parentCategory)
									{
										// Skip create breadcrumb if category marked "hide"
										if ($parentCategory->hide)
										{
											continue;
										}

										$text          = $parentCategory->name;
										$link          = RedshopbHelperRoute::getRoute(
											'index.php?option=com_redshopb&view=shop&layout=' . $layout . '&id=' . $parentCategory->id .
											($usingCollections ? '&mycollections=1' : '')
										);
										$collectionUri = $browser->getUri($link);

										if (self::checkIfBreadcrumbExist($collectionUri, true))
										{
											continue;
										}

										$pathway->addItem($text, $link);
									}

									$addCrumb = false;
								}
								else
								{
									if (self::checkIfBreadcrumbExist($linkUri))
									{
										$addCrumb = false;
									}
									else
									{
										$text = RedshopbHelperBreadcrumb::getItemName($linkId, '#__redshopb_' . $layout);
									}
								}

								break;

							case 'categories':
								if (self::checkIfBreadcrumbExist($linkUri))
								{
									$addCrumb = false;
								}
								else
								{
									$text = Text::_('COM_REDSHOPB_BREADCRUMB_CATEGORIES');
								}

								break;

							case 'collection':
								if (!RedshopbHelperShop::inCollectionMode(RedshopbEntityCompany::getInstance($values->companyId))
									&& Factory::getApplication()->input->get('mycollections'))
								{
									$link          = RedshopbHelperRoute::getRoute('index.php?option=com_redshopb&view=shop&layout=collections');
									$collectionUri = $browser->getUri($link);

									if (!self::checkIfBreadcrumbExist($collectionUri, true))
									{
										$itemId = RedshopbHelperRoute::findRedshopbMenuItem(
											'index.php?option=com_redshopb&view=shop&layout=collections'
										);
										$text   = Text::_('COM_REDSHOPB_BREADCRUMB_COLLECTIONS_TITLE');

										if ($itemId)
										{
											$item = $menus->getItem((int) $itemId);

											if ($item)
											{
												$text = $item->title;
											}
										}

										$pathway->addItem($text, $link);
									}
								}

								// Remove an empty collection link
								$link             = RedshopbHelperRoute::getRoute('index.php?option=com_redshopb&view=shop&layout=collection');
								$prodWithoutIdUri = $browser->getUri($link);
								self::checkIfBreadcrumbExist($prodWithoutIdUri, -1);

								// We need to get full path for category
								if (self::checkIfBreadcrumbExist($linkUri))
								{
									$addCrumb = false;
								}
								else
								{
									$text = RedshopbHelperBreadcrumb::getItemName($linkId, '#__redshopb_' . $layout);
								}

								break;

							case 'collections':
								if (self::checkIfBreadcrumbExist($linkUri))
								{
									$addCrumb = false;
								}
								else
								{
									$text = Text::_('COM_REDSHOPB_BREADCRUMB_COLLECTIONS_TITLE');
								}
								break;

							case 'product':
								// Remove an empty product link
								$link             = RedshopbHelperRoute::getRoute('index.php?option=com_redshopb&view=shop&layout=product');
								$prodWithoutIdUri = $browser->getUri($link);
								self::checkIfBreadcrumbExist($prodWithoutIdUri, -1);
								$category = $linkUri->getVar('category_id');

								if (!$category)
								{
									// Build category tree out of product main category
									$product = RedshopbHelperProduct::loadProduct($linkId);

									if ($product)
									{
										$category = $product->categories[0];
									}
								}

								// If we have set category for this product we display full path
								if ($category)
								{
									$parentCategories = RedshopbHelperCategory::getParentCategories($customerId, $customerType, $category);

									// We need to get full path for category
									if ($parentCategories)
									{
										foreach ($parentCategories as $parentCategory)
										{
											// Skip create breadcrumb if category marked "hide"
											if ($parentCategory->hide)
											{
												continue;
											}

											$catLink       = RedshopbHelperRoute::getRoute(
												'index.php?option=com_redshopb&view=shop&layout=category&id=' . $parentCategory->id
											);
											$collectionUri = $browser->getUri($catLink);

											if (self::checkIfBreadcrumbExist($collectionUri, true))
											{
												continue;
											}

											$pathway->addItem($parentCategory->name, $catLink);
										}
									}
								}

								$text = RedshopbHelperBreadcrumb::getItemName($linkId, '#__redshopb_' . $layout);
								break;

							case 'productlist':

								if (self::checkIfBreadcrumbExist($linkUri, true))
								{
									$addCrumb = false;
								}
								else
								{
									$text = Text::_('COM_REDSHOPB_BREADCRUMB_PRODUCTLIST');
								}

								break;
							case 'productrecent':

								if (self::checkIfBreadcrumbExist($linkUri, true))
								{
									$addCrumb = false;
								}
								else
								{
									$text = Text::_('COM_REDSHOPB_BREADCRUMB_SHOP_PRODUCTRECENT');
								}

								break;
							case 'productfeatured':

								if (self::checkIfBreadcrumbExist($linkUri, true))
								{
									$addCrumb = false;
								}
								else
								{
									$text = Text::_('COM_REDSHOPB_BREADCRUMB_SHOP_PRODUCTFEATURED');
								}

								break;
							case 'manufacturerlist':
								$link    = RedshopbHelperRoute::getRoute('index.php?option=com_redshopb&view=manufacturerlist');
								$linkUri = $browser->getUri($link);

								if (!self::checkIfBreadcrumbExist($linkUri, true))
								{
									$text = Text::_('COM_REDSHOPB_BREADCRUMB_MANUFACTURERS');
									$pathway->addItem($text, RedshopbRoute::_($link));
								}

								break;
							case 'manufacturer':
								$link    = RedshopbHelperRoute::getRoute('index.php?option=com_redshopb&view=manufacturerlist');
								$linkUri = $browser->getUri($link);

								if (!self::checkIfBreadcrumbExist($linkUri, true))
								{
									$text = Text::_('COM_REDSHOPB_BREADCRUMB_MANUFACTURERS');
									$pathway->addItem($text, RedshopbRoute::_($link));
								}

								// If we have set category for this product we display full path
								if ($linkId)
								{
									$manufacturer = RedshopbEntityManufacturer::load($linkId);

									if ($manufacturer->isLoaded())
									{
										$text = $manufacturer->get('name');
									}
								}

								break;
							default:
								$text = self::getText('COM_REDSHOPB_BREADCRUMB_SHOP_' . $layout, $layout);
								break;
						}

						break;

					case $viewPlural:
						$text = self::getText('COM_REDSHOPB_BREADCRUMB_' . $viewPlural, $viewPlural);
						break;
					default:
						$text = $linkId;
						$app->triggerEvent('onRedshopbBreadcrumbEntity', array(&$text, $linkUri));
						break;
				}

				if ($addCrumb && $text)
				{
					$pathway->addItem($text, RedshopbHelperRoute::getRoute('index.php?' . $linkUri->getQuery()));
				}
			}
		}

		if (in_array($currentLayout, $createLayouts) && !$currentId)
		{
			$pathway->addItem(Text::_('JNEW'));
		}
	}

	/**
	 * Get text for display in breadcrumb
	 *
	 * @param   string  $textKey      Text key
	 * @param   string  $alternative  Alternative text if Text not exists
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public static function getText($textKey, $alternative)
	{
		$lang = Factory::getLanguage();

		if ($lang->hasKey($textKey))
		{
			$text = Text::_(strtoupper($textKey));
		}
		else
		{
			$text = ucfirst($alternative);
		}

		return $text;
	}
}
