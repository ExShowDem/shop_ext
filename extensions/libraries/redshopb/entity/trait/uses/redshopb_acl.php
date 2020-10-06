<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity.Trait
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
/**
 * Trait for entities that use redshopb ACL
 *
 * @since  2.0
 */
trait RedshopbEntityTraitUsesRedshopb_Acl
{
	/**
	 * Relationships between actions and accesses
	 *
	 * @var  array
	 */
	protected static $actionsAccesses;

	/**
	 * Check if current user can create an item
	 *
	 * @return  boolean
	 */
	public function canCreate()
	{
		if ($this->canDo($this->getAclPrefix() . '.manage'))
		{
			return true;
		}

		if ($this->canDo($this->getAclPrefix() . '.manage.own'))
		{
			return true;
		}

		return false;
	}

	/**
	 * Check if current user has permission to perform an action
	 *
	 * @param   string  $action  The action. Example: core.create
	 *
	 * @return  boolean
	 */
	public function canDo($action)
	{
		// Inherit core permissions check
		if (parent::canDo($action))
		{
			return true;
		}

		$rUser = RedshopbApp::getUser();

		if (!$rUser->isLoaded())
		{
			return false;
		}

		$roleId = $rUser->getRole()->id;

		if (!$roleId)
		{
			return false;
		}

		$access = $this->getActionAccess($action);

		if (!$access->isLoaded())
		{
			return false;
		}

		$assetId = $this->get('asset_id', RedshopbApp::getRootAsset()->id);

		return $access->allow($assetId);
	}

	/**
	 * Check if current user can edit this item
	 *
	 * @return  boolean
	 */
	public function canEdit()
	{
		return $this->canCreate();
	}

	/**
	 * Check if user can view this item.
	 *
	 * @return  boolean
	 */
	public function canView()
	{
		if (!parent::canView())
		{
			return false;
		}

		return $this->canDo($this->getAclPrefix() . '.view');
	}


	/**
	 * Get the applicable access for an action
	 *
	 * @param   string  $action  Action. Example. redshopb.company.manage
	 *
	 * @return  integer
	 */
	protected function getActionAccess($action)
	{
		$actionsAcceses = $this->getActionsAccesses();

		if (isset($actionsAcceses[$action]))
		{
			return $actionsAcceses[$action];
		}

		return RedshopbEntityAcl_Access::getInstance();
	}

	/**
	 * Get the list of accesses for actions
	 *
	 * @return  array
	 */
	protected function getActionsAccesses()
	{
		if (null === static::$actionsAccesses)
		{
			$this->loadActionsAccesses();
		}

		return static::$actionsAccesses;
	}

	/**
	 * Load the actions - accesses relationships
	 *
	 * @return  self
	 *
	 * @since   2.0
	 */
	protected function loadActionsAccesses()
	{
		static::$actionsAccesses = array();

		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select($db->qn('aa') . '.*')
			->select($db->qn('as.name', 'section_name'))
			->from($db->qn('#__redshopb_acl_access', 'aa'))
			->innerJoin($db->qn('#__redshopb_acl_section', 'as') . ' ON ' . $db->qn('as.id') . ' = ' . $db->qn('aa.section_id'));

		$db->setQuery($query);

		$items = $db->loadObjectList('name');

		if (!$items)
		{
			return $this;
		}

		foreach ($items as $action => $item)
		{
			$access = RedshopbEntityAcl_Access::getInstance($item->id)->bind($item);

			// We will populate section data because we already have the info
			$sectionData = (object) array(
				'id'   => $item->section_id,
				'name' => $item->section_name
			);

			$section = RedshopbEntityAcl_Section::getInstance($sectionData->id)->bind($sectionData);

			$access->setSection($section);

			static::$actionsAccesses[$action] = $access;
		}

		return $this;
	}
}
