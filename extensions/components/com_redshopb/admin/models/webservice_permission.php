<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Webservice Permission Model
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelWebservice_Permission extends RedshopbModelAdmin
{
	/**
	 * Get Webservice Permission Scope Items
	 *
	 * @param   string  $scope                   Scope
	 * @param   int     $webservicePermissionId  Webservice Permission Id
	 *
	 * @return  mixed    Get list of scope items
	 */
	public function getPermissionScopeItems($scope = 'product', $webservicePermissionId = 0)
	{
		$permission                  = new stdClass;
		$permission->selected_values = '';

		if (empty($scope))
		{
			$permission->data = array();

			return $permission;
		}

		if (!empty($webservicePermissionId))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('wp.*')
				->from($db->qn('#__redshopb_webservice_permission', 'wp'))
				->where('wp.id = ' . (int) $webservicePermissionId)
				->order('wp.name')
				->select('GROUP_CONCAT(wpi.item_id) AS selected_values')
				->leftJoin($db->qn('#__redshopb_webservice_permission_item_xref', 'wpi') . ' ON wpi.webservice_permission_id = wp.id')
				->group('wp.id');

			$db->setQuery($query);

			$permission = $db->loadObject();
		}

		$permission->scope = $scope;

		if (!empty($webservicePermissionId) && $permission->manual == 0)
		{
			RFactory::getDispatcher()->trigger('onRedshopbGetPermissionScopeItems', array(&$permission));
		}

		if (!isset($permission->data))
		{
			$permission->data = array();

			switch ($scope)
			{
				case 'field':
					$permission->data = $this->loadFieldOptions();
					break;
				case 'product':
					$permission->data = $this->loadProductOptions();
					break;
				case 'category':
					$permission->data = $this->loadCategoryOptions();
					break;
			}
		}

		return $permission;
	}

	/**
	 * Get field options
	 *
	 * @return  mixed    Get list of field options
	 *
	 * @since   12.2
	 */
	public function loadFieldOptions()
	{
		$db      = Factory::getDbo();
		$options = array();

		$query = $db->getQuery(true)
			->select(
				array(
					$db->qn('id', 'value'),
					$db->qn('name', 'text')
				)
			)
			->from('#__redshopb_field')
			->order('name');

		$db->setQuery($query);
		$items = $db->loadObjectList();

		foreach ($items as $item)
		{
			$options[] = HTMLHelper::_('select.option', $item->value, $item->text);
		}

		return $options;
	}

	/**
	 * Get product options
	 *
	 * @return  mixed    Get list of product options
	 *
	 * @since   12.2
	 */
	public function loadProductOptions()
	{
		$db      = Factory::getDbo();
		$options = array();

		$query = $db->getQuery(true)
			->select(
				array(
					$db->qn('id', 'value'),
					$db->qn('name', 'text')
				)
			)
			->from('#__redshopb_product')
			->order('name');

		$db->setQuery($query);
		$items = $db->loadObjectList();

		foreach ($items as $item)
		{
			$options[] = HTMLHelper::_('select.option', $item->value, $item->text);
		}

		return $options;
	}

	/**
	 * Get category options
	 *
	 * @return  mixed    Get list of product options
	 *
	 * @since   12.2
	 */
	public function loadCategoryOptions()
	{
		$db      = Factory::getDbo();
		$options = array();

		$query = $db->getQuery(true)
			->select(
				array(
					$db->qn('id', 'value'),
					$db->qn('name', 'text')
				)
			)
			->from('#__redshopb_category')
			->order('name');

		$db->setQuery($query);
		$items = $db->loadObjectList();

		foreach ($items as $item)
		{
			$options[] = HTMLHelper::_('select.option', $item->value, $item->text);
		}

		return $options;
	}
}
