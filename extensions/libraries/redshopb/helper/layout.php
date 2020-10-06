<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helpers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Layout helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
class RedshopbHelperLayout
{
	/**
	 * Get user count using specific layout.
	 *
	 * @param   int  $id  Layout id
	 *
	 * @return integer User count
	 */
	public static function getUserCount($id = 0)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id')
			->from($db->qn('#__redshopb_company'))
			->where($db->qn('deleted') . ' = 0')
			->where('layout_id = ' . (int) $id);
		$db->setQuery($query);

		$companies = $db->loadColumn();

		return RedshopbHelperCompany::getUsersCount($companies);
	}

	/**
	 * Get default layout.
	 *
	 * @return object Layout object. Null on failure.
	 */
	public static function getDefaultLayout()
	{
		$db    = Factory::getDbo();
		$query = self::getDefaultLayoutQuery();
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Get default layout query.
	 *
	 * @return JDatabaseQuery Query object;
	 */
	public static function getDefaultLayoutQuery()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*')
			->from($db->qn('#__redshopb_layout'))
			->where('home = 1');

		return $query;
	}

	/**
	 * Check if we can add new redirects.
	 *
	 * @return boolean
	 */
	public static function canRedirect()
	{
		if (function_exists('apache_get_modules'))
		{
			$apacheModules = apache_get_modules();
			$modRewrite    = false;

			foreach ($apacheModules as $module)
			{
				if ($module == 'mod_rewrite')
				{
					$modRewrite = true;
				}
			}

			// Check if mod_rewrite is enabled
			if (!$modRewrite)
			{
				Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_LAYOUT_REDIRECT_REWRITE_FAIL'), 'warning');

				return false;
			}
		}

		// Check if com_redirect exists and if it's enabled
		if (!ComponentHelper::isEnabled('com_redirect'))
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_LAYOUT_REDIRECT_COMPONENT_NOT_ENABLED'), 'warning');

			return false;
		}

		// Check if plg_redirect exists and if it's enabled
		if (!PluginHelper::isEnabled('system', 'redirect'))
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_LAYOUT_REDIRECT_PLUGIN_NOT_ENABLED'), 'warning');

			return false;
		}

		return true;
	}

	/**
	 * Gets the current layout set
	 *
	 * @return integer Layout Id
	 */
	public static function getCurrentLayout()
	{
		$user = Factory::getUser();

		return $user->getParam('redshopb.Layout', '');
	}

	/**
	 * Sets the current layout and template (Wright template) theme
	 *
	 * @param   int     $layoutId       Layout Id to be set in user params
	 * @param   string  $templateTheme  Wright template theme to be set in "theme" user param
	 *
	 * @return void
	 */
	public static function setCurrentLayout($layoutId, $templateTheme)
	{
		$user = Factory::getUser();

		$user->setParam('redshopb.Layout', $layoutId);
		$user->setParam('theme', $templateTheme);
	}

	/**
	 * Gets the layout for the given User Id
	 *
	 * @param   int  $userId  User Id to search (Joomla user Id)
	 *
	 * @return object  Layout DB object (null if not set for this user)
	 */
	public static function getUserLayout($userId)
	{
		// @toDo: optimize by setting info in the session

		$db                = Factory::getDbo();
		$query             = $db->getQuery(true);
		$selectedCompanyId = RedshopbEntityUser::getCompanyIdForCurrentUser();

		$query->select('l.*')
			->from($db->qn('#__users', 'u'))
			->join('inner', $db->qn('#__redshopb_user', 'ru') . ' ON u.id = ru.joomla_user_id')
			->leftJoin('#__redshopb_user_multi_company AS umc ON umc.user_id = ru.id AND umc.company_id = ' . $selectedCompanyId)
			->join('inner', $db->qn('#__redshopb_company', 'c') . ' ON umc.company_id = c.id AND ' . $db->qn('c.deleted') . ' = 0')
			->join('inner', $db->qn('#__redshopb_layout', 'l') . ' ON c.layout_id = l.id')
			->where('u.id = ' . (int) $userId);
		$oldTranslate  = $db->translate;
		$db->translate = false;
		$db->setQuery($query);
		$return        = $db->loadObject();
		$db->translate = $oldTranslate;

		return $return;
	}

	/**
	 * Gets the layout for the given Company Id
	 *
	 * @param   int  $companyId  Company Id to search
	 *
	 * @return object  Layout DB object (null if not set for this company)
	 */
	public static function getCompanyLayout($companyId)
	{
		// @toDo: optimize by setting info in the session

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('l.*')
			->from($db->qn('#__redshopb_layout', 'l'))
			->join('inner', $db->qn('#__redshopb_company', 'c') . ' ON c.layout_id = l.id AND ' . $db->qn('c.deleted') . ' = 0')
			->where('c.id = ' . (int) $companyId);

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Gets the requested Layout
	 *
	 * @param   int  $layoutId  Layout Id to search
	 *
	 * @return object  Layout DB object (null if it does not exisg)
	 */
	public static function getLayout($layoutId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('l.*')
			->from($db->qn('#__redshopb_layout', 'l'))
			->where('l.id = ' . (int) $layoutId);

		$db->setQuery($query);

		return $db->loadObject();
	}
}
