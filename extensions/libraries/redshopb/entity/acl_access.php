<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
/**
 * ACL Access Entity.
 *
 * @since  2.0
 */
class RedshopbEntityAcl_Access extends RedshopbEntity
{
	/**
	 * Rules applicable to accesses. Key is access identifier.
	 *
	 * @var  array
	 */
	protected static $rules = array();

	/**
	 * Section of this access.
	 *
	 * @var  RedshopbEntityAcl_Section
	 */
	protected $section;

	/**
	 * Allow access to an asset id
	 *
	 * @param   integer  $assetId  Asset identifier
	 *
	 * @return  boolean
	 */
	public function allow($assetId)
	{
		$user = Factory::getUser();

		if ($user->authorise('core.admin'))
		{
			return true;
		}

		$roleId = RedshopbApp::getUser()->getRole()->id;

		if (!$roleId)
		{
			return false;
		}

		$assetId = (int) $assetId;

		if (!$assetId)
		{
			return false;
		}

		$rules = $this->getRules($assetId);

		if (!$rules)
		{
			return false;
		}

		foreach ($rules as $rule)
		{
			if ($rule->allow($assetId))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the rules that apply to this access.
	 *
	 * @param   integer  $assetId  Asset identifier
	 *
	 * @return  array
	 */
	public function getRules($assetId)
	{
		if (!$this->hasId())
		{
			return array();
		}

		if (!isset(static::$rules[$this->id]))
		{
			$this->loadRules();
		}

		if (!isset(static::$rules[$this->id][$assetId]))
		{
			return array();
		}

		return static::$rules[$this->id][$assetId];
	}

	/**
	 * Get the section of this access.
	 *
	 * @return  RedshopbEntityAcl_Section
	 */
	public function getSection()
	{
		if (null === $this->section)
		{
			$this->loadSection();
		}

		return $this->section;
	}

	/**
	 * Load applicable rules for this access
	 *
	 * @return  self
	 */
	protected function loadRules()
	{
		static::$rules[$this->id] = array();

		if (!$this->hasId())
		{
			return $this;
		}

		$db = $this->getDbo();

		$query  = $db->getQuery(true);
		$query2 = $db->getQuery(true);

		$query2->select(Array('max(' . $db->qn('ap2.level') . ')'))
			->from($db->qn('#__assets', 'ap2'))
			->innerJoin($db->qn('#__redshopb_acl_rule', 'r2') . ' ON ' . $db->qn('ap2.id') . ' = ' . $db->qn('r2.joomla_asset_id'))
			->where($db->qn('ap2.level') . ' <= ' . $db->qn('a.level'))
			->where($db->qn('ap2.lft') . ' <= ' . $db->qn('a.lft'))
			->where($db->qn('ap2.rgt') . ' >= ' . $db->qn('a.rgt'))
			->where($db->qn('r2.role_id') . ' = ' . $db->qn('r.role_id'))
			->where($db->qn('r2.access_id') . ' = ' . $db->qn('r.access_id'));

		$query->select($db->qn('r') . '.*')
			->select($db->qn('a.id', 'asset_id'))
			->from($db->qn('#__assets', 'a'))
			->innerJoin(
				$db->qn('#__assets', 'ap') . ' ON ' .
				$db->qn('ap.level') . ' <= ' . $db->qn('a.level') .
				' AND ' . $db->qn('ap.lft') . ' <= ' . $db->qn('a.lft') .
				' AND ' . $db->qn('ap.rgt') . ' >= ' . $db->qn('a.rgt')
			)
			->innerJoin($db->qn('#__redshopb_acl_rule', 'r') . ' ON ' . $db->qn('ap.id') . ' = ' . $db->qn('r.joomla_asset_id'))
			->where($db->qn('r.access_id') . ' = ' . (int) $this->id)
			->where($db->qn('r.role_id') . ' = ' . (int) RedshopbApp::getUser()->getRole()->id)
			->where($db->qn('ap.level') . ' = (' . $query2->__toString() . ')');

		$db->setQuery($query);

		$items = $db->loadObjectList();

		if (!$items)
		{
			return $this;
		}

		foreach ($items as $item)
		{
			if (!isset(static::$rules[$this->id][$item->asset_id]))
			{
				static::$rules[$this->id][$item->asset_id] = array();
			}

			$entity = RedshopbEntityAcl_Rule::getInstance($item->id)->bind($item);

			static::$rules[$this->id][$item->asset_id][] = $entity;
		}

		return $this;
	}

	/**
	 * Load section information
	 *
	 * @return  self
	 */
	protected function loadSection()
	{
		$this->section = RedshopbEntityAcl_Section::getInstance();

		$item = $this->getItem();

		if (!$item || $item->section_id)
		{
			return $this;
		}

		$this->section = RedshopbEntityAcl_Section::load($item->section_id);

		return $this;
	}

	/**
	 * Set the section of this access
	 *
	 * @param   RedshopbEntityAcl_Section  $section  Section data
	 *
	 * @return  self
	 */
	public function setSection(RedshopbEntityAcl_Section $section)
	{
		$this->section = $section;

		return $this;
	}
}
