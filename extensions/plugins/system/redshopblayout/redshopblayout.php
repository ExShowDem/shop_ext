<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Plugin
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\Menu\MenuItem;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

JLoader::import('redshopb.library');

/**
 * Redshopb System Logging Plugin
 *
 * @package     Aesir.E-Commerce
 * @subpackage  System
 * @since       1.0
 */
class PlgSystemRedshopbLayout extends CMSPlugin
{
	/**
	 * @var integer
	 */
	public $customLayoutId = 0;

	/**
	 * @var boolean
	 */
	public $autoloadLanguage = true;

	/**
	 * @var boolean
	 * @since 1.13.0
	 */
	protected $loginModalInitialized = false;

	/**
	 * Contains the URL for the correct login page
	 *
	 * When logging out we need to store the URL for the login page based on the layout
	 *
	 * @var string|null
	 *
	 * @since 2.1.0
	 */
	protected $logoutUrl;

	/**
	 * After routing, checks login/logout status, current company or template set, and redirects to set a new layout if needed
	 *
	 * @throws Exception If Factory::getApplication() fails
	 * @throws Exception {@see self::buildLayoutUrl()}
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function onAfterRoute()
	{
		$app   = Factory::getApplication();
		$input = $app->input;

		if ($app->isClient('site') && ComponentHelper::isInstalled('com_sh404sef'))
		{
			$alias = ltrim(Uri::getInstance(Uri::current())->getPath(), '/');

			$url = $this->buildLayoutUrl($alias);

			if (!empty($url) && !$this->aliasExists($alias))
			{
				$this->createSefAlias($url, $alias);

				$app->redirect(Uri::current());

				return;
			}
		}

		if ($app->isClient('site'))
		{
			// Allows a custom layout to be loaded by using layoutid parameter
			$customLayoutId = $app->input->getInt('layoutid', 0);

			if ($customLayoutId)
			{
				$customLayout = RedshopbHelperLayout::getLayout($customLayoutId);

				if ($customLayout)
				{
					$this->customLayoutId = $customLayoutId;
					RedshopbHelperLayout::setCurrentLayout($customLayoutId, $customLayout->style);
				}
			}

			if (!$this->customLayoutId)
			{
				$this->selectLayout();
			}
		}
		else
		{
			$task   = $input->getCmd('task');
			$option = $input->getCmd('option');

			// Here we try to find tables with foreign keys in field checked_out
			if ($option == 'com_checkin' && $task == 'checkin')
			{
				// Check for request forgeries
				Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

				$lang = Factory::getLanguage();

				// Load common and local language files.
				$lang->load($option, JPATH_BASE, null, false, true)
				|| $lang->load($option, JPATH_BASE . '/components/' . $option, null, false, true);

				$ids = $input->get('cid', array(), 'array');

				if (empty($ids))
				{
					$app->enqueueMessage(Text::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'), 'error');
					$app->redirect('index.php?option=com_checkin', 500);
				}

				$db       = Factory::getDbo();
				$nullDate = $db->getNullDate();
				$conf     = Factory::getConfig();

				// This int will hold the checked item count.
				$results        = 0;
				$approvedTables = array();

				foreach ($ids as $tn)
				{
					// Make sure we get the right tables based on prefix.
					if (stripos($tn, $app->get('dbprefix')) !== 0)
					{
						continue;
					}

					$fields = $db->getTableColumns($tn);

					if (!(isset($fields['checked_out']) && isset($fields['checked_out_time'])))
					{
						continue;
					}

					$approvedTables[] = $tn;
				}

				$refQuery = $db->getQuery(true)
					->select('k.table_name')
					->from($db->qn('information_schema.KEY_COLUMN_USAGE', 'k'))
					->where('k.REFERENCED_TABLE_SCHEMA = ' . $db->q($conf->get('db')))
					->where('k.REFERENCED_TABLE_NAME is not null')
					->where('k.column_name = ' . $db->q('checked_out'))
					->where('k.TABLE_NAME IN (' . implode(',', RHelperArray::quote($approvedTables)) . ')');

				$foreigners = $db->setQuery($refQuery)
					->loadColumn();

				foreach ($approvedTables as $approvedTable)
				{
					$query = $db->getQuery(true)
						->update($db->quoteName($approvedTable))
						->set('checked_out_time = ' . $db->quote($nullDate))
						->where('checked_out > 0');

					// Found foreign key for checked_out field
					if (in_array($approvedTable, $foreigners))
					{
						$query->set('checked_out = NULL');
					}
					else
					{
						$query->set('checked_out = 0');
					}

					if ($db->setQuery($query)->execute())
					{
						$results += $db->getAffectedRows();
					}
				}

				// Checked in the items.
				$app->enqueueMessage(Text::plural('COM_CHECKIN_N_ITEMS_CHECKED_IN', $results));

				$app->redirect('index.php?option=com_checkin');
			}
		}
	}

	/**
	 * Selection of layout based on logged in company, selected menu or default layout
	 *
	 * @throws Exception If Factory::getApplication() fails
	 *
	 * @return  void
	 */
	protected function selectLayout()
	{
		$app           = Factory::getApplication();
		$user          = RedshopbHelperCommon::getUser();
		$currentLayout = RedshopbHelperLayout::getCurrentLayout();

		/**
		 * Checks if user is logged in to see if it needs to check request
		 * or just set its layout (or default layout if user doesn't have one assigned)
		 */
		if ($user->id)
		{
			// User is logged in, checks if it has a layout
			$userLayout = RedshopbHelperLayout::getUserLayout($user->id);

			if ($userLayout)
			{
				// If a user layout is set, ensures it's set in the user params
				if ($userLayout->id != $currentLayout)
				{
					RedshopbHelperLayout::setCurrentLayout($userLayout->id, $userLayout->style);
				}
			}
			else
			{
				// If no user layout is set, set default
				$defaultLayout = RedshopbHelperLayout::getDefaultLayout();

				if ($defaultLayout)
				{
					if ($defaultLayout->id != $currentLayout)
					{
						RedshopbHelperLayout::setCurrentLayout($defaultLayout->id, $defaultLayout->style);
					}
				}
			}
		}
		elseif ($user->b2cMode)
		{
			$companyLayout = RedshopbHelperLayout::getCompanyLayout($user->b2cCompany);
			$defaultLayout = RedshopbHelperLayout::getDefaultLayout();

			// User in B2C mode and try to get layout of current b2c company.
			if ($companyLayout)
			{
				/*
				 * Sets session variables to preserve it even in login screen (if it's not, this doesn't
				 * get after logout when being redirected by app to login screen)
				 */
				$session = Factory::getSession();
				$session->set('companyLayoutId', $companyLayout->id, 'com_redshopb');
				$session->set('companyLayoutStyle', $companyLayout->style, 'com_redshopb');

				RedshopbHelperLayout::setCurrentLayout($companyLayout->id, $companyLayout->style);
			}
			elseif ($defaultLayout)
			{
				if ($defaultLayout->id != $currentLayout)
				{
					RedshopbHelperLayout::setCurrentLayout($defaultLayout->id, $defaultLayout->style);
				}
			}
		}
		else
		{
			$setDefaultLayout = true;

			// User is not logged in.  Checks if the layout is set in the current menu item (if it's a b2buserregister view)
			if ($app->input->getString('option', '') == 'com_redshopb'
				&& $app->input->getString('view', '') == 'b2buserregister')
			{
				$activeMenu = $app->getMenu()->getActive();

				if ($activeMenu)
				{
					$companyId     = $activeMenu->getParams()->get('company_id') ?? $activeMenu->getParams()->get('companyId', 0);
					$companyLayout = RedshopbHelperLayout::getCompanyLayout($companyId);

					if ($companyLayout)
					{
						$setDefaultLayout = false;

						if ($companyLayout->id != $currentLayout)
						{
							/*
							 * Sets session variables to preserve it even in login screen (if it's not, this doesn't
							 * get after logout when being redirected by app to login screen)
							 */
							$session = Factory::getSession();
							$session->set('companyLayoutId', $companyLayout->id, 'com_redshopb');
							$session->set('companyLayoutStyle', $companyLayout->style, 'com_redshopb');

							RedshopbHelperLayout::setCurrentLayout($companyLayout->id, $companyLayout->style);

							return;
						}
					}
				}
			}
			elseif ($app->input->getString('option', '') == 'com_users'
				&& $app->input->getString('view', '') == 'login'
			)
			{
				// Captures after-logout session variables to set the company selected layout
				$session         = Factory::getSession();
				$companyLayoutId = $session->get('companyLayoutId', 0, 'com_redshopb');

				if ($companyLayoutId)
				{
					$companyLayoutStyle = $session->get('companyLayoutStyle', '', 'com_redshopb');
					RedshopbHelperLayout::setCurrentLayout($companyLayoutId, $companyLayoutStyle);

					// Reinitializes session variables to avoid problems when changing layout again
					$session->set('companyLayoutId', 0, 'com_redshopb');
					$session->set('companyLayoutStyle', '', 'com_redshopb');
				}
			}

			/*
			 * If no layout has been selected, and no current layout is set, sets the default layout if it exists.
			 * This will apply to every new visitor when logged out, just the first time.
			 * Also doing this if the dashboard view is set and the selected company has no layout, or is the default
			 * company
			 */
			if (!$currentLayout || $setDefaultLayout)
			{
				$defaultLayout = RedshopbHelperLayout::getDefaultLayout();

				if ($defaultLayout)
				{
					if ($defaultLayout->id != $currentLayout)
					{
						RedshopbHelperLayout::setCurrentLayout($defaultLayout->id, $defaultLayout->style);
					}
				}
			}
		}
	}

	/**
	 * Before rendering, sets the layout options (special CSS, background image, etc)
	 *
	 * @throws Exception If Factory::getApplication() fails
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function onBeforeRender()
	{
		if (Factory::getApplication()->isClient('site'))
		{
			$doc = Factory::getDocument();

			// Checks current layout set
			$layout        = null;
			$layoutParams  = null;
			$currentLayout = RedshopbHelperLayout::getCurrentLayout();

			if ($currentLayout)
			{
				$layout = RedshopbHelperLayout::getLayout($currentLayout);

				if ($layout)
				{
					$layoutParams = json_decode($layout->params);
				}
			}

			if ($layoutParams)
			{
				$css = '';

				if ($layoutParams->backgroundImage != '')
				{
					$css .= 'body { background-image: url(' . Uri::root() . $layoutParams->backgroundImage . '); background-size: cover; } ';
				}

				if ($layoutParams->backgroundColor != '')
				{
					$css .= 'body { background-color: ' . $layoutParams->backgroundColor . '; background-image: none; } ';
				}

				if (isset($layoutParams->customCSS) && $layoutParams->customCSS != '')
				{
					$css .= $layoutParams->customCSS;
				}

				if ($css != '')
				{
					$doc->addStyleDeclaration($css);
				}
			}
		}
	}

	/**
	 * onAfterRender function
	 *
	 * @throws Exception If Factory::getApplication() fails
	 *
	 * @return  void
	 */
	public function onBeforeCompileHead()
	{
		JLoader::import('redshopb.library');
		RedshopbHelperScript::script('SITE_URL', Uri::root());
		RedshopbHelperScript::script('token', Session::getFormToken());
		RedshopbHelperScript::script('count_cart_items', RedshopbEntityConfig::getInstance()->get('count_cart_items', 'quantity'));
		RedshopbHelperScript::script('CART_ITEM_KEYS', RedshopbHelperCart::cartFieldsForCheck());
		RedshopbHelperScript::script('CART_LOADING_IMAGE', HTMLHelper::image('media/com_redshopb/images/loading.gif', ''));

		$cartRedirect = RedshopbEntityConfig::getInstance()->getInt('add_to_cart_redirect_cart', '0');

		if ($cartRedirect == 0)
		{
			RedshopbHelperScript::script('REDIRECT_AFTER_ADD_TO_CART', 0);
		}
		else
		{
			RedshopbHelperScript::script('REDIRECT_AFTER_ADD_TO_CART', 1);

			$uri = Uri::getInstance();
			$uri->setPath(RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=cart', false));

			RedshopbHelperScript::script('CART_PAGE', $uri->toString(array('scheme', 'user', 'pass', 'host', 'port', 'path', 'fragment')));
		}

		RedshopbHelperScript::scriptDeclaration();

		if (Factory::getApplication()->isClient('site')
			&& !$this->loginModalInitialized
			&& !Factory::getUser()->guest
			&& RedshopbEntityConfig::getInstance()->getInt('warning_logout_when_products_in_cart', 0))
		{
			$this->loginModalInitialized = true;
			RHtml::_('rjquery.framework');
			$doc = Factory::getDocument();
			$doc->addScriptDeclaration('(function($) {
				$(document).ready(function () {
					var linkUrl;
					function checkCartAmount(){
						var cartItems = jQuery(\'#redshopb-cart-link .redshopb-cart-items\');
						if (cartItems.length > 0 && cartItems.html() != 0){
							return true;
						}else{
							return false;
						}
					}
					var formHandler = function(){
						var $form = $(this);
						var $task = $form.find(\'input[name="task"]\');
						if (($task != undefined && $task.val() == \'user.logout\')
							|| $form.hasClass(\'logoutForm\')
							|| ($form.attr(\'action\') != undefined
								&& $form.attr(\'action\').indexOf("' . Route::_('index.php?option=com_users&task=user.logout', false) . '") >= 0)){
							if (checkCartAmount()){
								$(\'#logoutNoticeModalSubmit\').data(\'formSubmit\', this);
								$(\'#logoutNoticeModal\').modal(\'show\');
								return false;
							}
						}
						return this.formSubmit();
					};
					$(document).find(\'form\').each(function(i, a){
						a.formSubmit = a.submit;
						a.submit = formHandler;
					});
					$(document).on(\'submit\', \'form\', function(e) {
						if (this.submit() === false){
							e.preventDefault();
						}
					});
					$(document).on(\'click\', \'#logoutNoticeModalSubmit\', function(e){
						var form = $(this).data(\'formSubmit\');
						form.formSubmit();
					});
					$(document).on(\'click\', \'a.logoutLink\', function(e){
						if (checkCartAmount()){
							linkUrl = $(this).attr(\'href\');
							var form = {formSubmit : function(){
								window.location.href = linkUrl
							}};
							$(\'#logoutNoticeModalSubmit\').data(\'formSubmit\', form);
							$(\'#logoutNoticeModal\').modal(\'show\');
							e.preventDefault();
						}
					});
				});
			})(jQuery);'
			);
		}
	}

	/**
	 * OnAfterInitialise event
	 *
	 * @throws Exception If Factory::getApplication() fails
	 *
	 * @return boolean
	 */
	public function onAfterInitialise()
	{
		$app   = Factory::getApplication();
		$input = $app->input;

		if ($app->getClientId() === 0
			&& $app->get('session_handler') === 'database'
			&& $input->getString('api', '') === '')
		{
			$session = Factory::getSession();

			// This is hack for expired session with wrong token on pages with ajax requests
			if (!$session->getId() && !$session->isNew())
			{
				$app->redirect(Uri::getInstance()->toString());
			}
		}

		return true;
	}

	/**
	 * onAfterRender
	 *
	 * @throws Exception If Factory::getApplication() fails
	 *
	 * @since  1.13.0
	 *
	 * @return  void
	 */
	public function onAfterRender()
	{
		$app = Factory::getApplication();

		if (!$app->isClient('site') || !$this->loginModalInitialized)
		{
			return;
		}

		$buffer = $app->getBody();
		RedshopbLayoutFile::addIncludePathStatic(__DIR__ . '/layouts');
		$buffer .= RedshopbLayoutHelper::render('user.logout', compact(array_keys(get_defined_vars())));
		$app->setBody($buffer);
	}

	/**
	 * Builds a URL based on the layout and it's related menu item
	 *
	 * @param   string $alias Layout URL alias
	 *
	 * @throws Exception If AbstractMenu::getInstance() fails
	 *
	 * @return string
	 *
	 * @since 2.1.0
	 */
	private function buildLayoutUrl($alias)
	{
		$url = '';

		$menu = AbstractMenu::getInstance('site');

		foreach ($menu->getMenu() as $menuItem)
		{
			if ($alias === $menuItem->alias && $this->layoutExists($menuItem))
			{
				$url  = RedshopbRoute::_('index.php?' . http_build_query($menuItem->query));
				$url .= "?Itemid={$menuItem->id}";
			}
		}

		return $url;
	}

	/**
	 * Creates a new alias redirection in the sh404sef table
	 *
	 * @param   string $url   Page URL
	 * @param   string $alias Layout URL alias
	 *
	 * @return mixed
	 *
	 * @since 2.1.0
	 */
	private function createSefAlias($url, $alias)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->insert($db->qn('#__sh404sef_aliases'))
			->set("{$db->qn('newurl')} = {$db->q($url)}")
			->set("{$db->qn('alias')} = {$db->q($alias)}");

		return $db->setQuery($query)->execute();
	}

	/**
	 * Checks if an alias already exists in the sh404sef table
	 *
	 * @param   string $alias Layout URL alias
	 *
	 * @return boolean
	 *
	 * @since 2.1.0
	 */
	private function aliasExists($alias)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select($db->q(1))
			->from($db->qn('#__sh404sef_aliases'))
			->where("{$db->qn('alias')} = {$db->q($alias)}");

		return (bool) $db->setQuery($query)->loadResult();
	}

	/**
	 * Checks if a layout exists for a given menu item
	 *
	 * Checks if a layout is tied to the same company as a menu item
	 *
	 * @param   MenuItem $menuItem  Used to find the layout
	 *
	 * @return boolean
	 *
	 * @since 2.1.0
	 */
	private function layoutExists($menuItem)
	{
		$companyId = $menuItem->getParams()->get('company_id');

		$layout = RedshopbHelperLayout::getCompanyLayout($companyId);

		return is_object($layout);
	}

	/**
	 * Stores the URL for the correct login page when the user logs out
	 *
	 * When the user logs out we need to store the URL for the right login page based
	 * on the layout for the users company
	 *
	 * @param   array $credentials Used to get the users ID
	 * @param   array $options     Unused
	 *
	 * @throws Exception  If AbstractMenu::getInstance() fails
	 *
	 * @return void
	 *
	 * @since 2.1.0
	 */
	public function onUserLogout($credentials, $options)
	{
		$user   = RedshopbEntityUser::loadFromJoomlaUser($credentials['id']);
		$cid    = $user->getCompany()->getId();
		$did    = $user->getDepartment()->getId();
		$menues = AbstractMenu::getInstance('Site')->getMenu();
		$url    = null;

		foreach ($menues as $menuItem)
		{
			if ($menuItem->getParams()->get('company_id') === $cid)
			{
				if (isset($did) && $menuItem->getParams()->get('department_id') !== $did)
				{
					continue;
				}

				$url = RedshopbRoute::_($menuItem->link . '&Itemid=' . $menuItem->id);
			}
		}

		$this->logoutUrl = $url;
	}

	/**
	 * Redirects the user to the correct login page after logging out
	 *
	 * Checks if a URL was stored during {@see self::onUserLogout()}
	 *
	 * @param   array $options Unused
	 *
	 * @throws Exception If Factory::getApplication() fails
	 *
	 * @return void
	 *
	 * @since 2.1.0
	 */
	public function onUserAfterLogout($options)
	{
		if (isset($this->logoutUrl))
		{
			$url = $this->logoutUrl;

			unset($this->logoutUrl);

			Factory::getApplication()->redirect($url);
		}
	}
}
