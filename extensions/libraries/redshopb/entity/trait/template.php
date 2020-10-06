<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity.Trait
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Trait for entities that use templates
 *
 * @since  1.13
 */
trait RedshopbEntityTraitTemplate
{
	/**
	 * Template ids for this entity.  Multi-dimensional array of template group/scope
	 *
	 * @var   array
	 *
	 * @since 1.13.0
	 */
	protected $templateId = array();

	/**
	 * Gets the template id for a certain template group/scope matching to this entity
	 *
	 * @param   string  $group  Template group
	 * @param   string  $scope  Template scope
	 *
	 * @return  integer | null
	 *
	 * @since   1.13.0
	 */
	public function getTemplateId($group, $scope)
	{
		if (isset($this->templateId[$group][$scope]))
		{
			return $this->templateId[$group][$scope];
		}

		if (!($this->hasId()))
		{
			return null;
		}

		$this->loadTemplateId($group, $scope);

		if (isset($this->templateId[$group][$scope]))
		{
			return $this->templateId[$group][$scope];
		}

		return null;
	}

	/**
	 * Loads the template id given a scope and group, using the templateMatches property
	 *
	 * @param   string  $group  Template group
	 * @param   string  $scope  Template scope
	 *
	 * @return  self
	 *
	 * @since   1.13.0
	 */
	public function loadTemplateId($group, $scope)
	{
		if (!$this->hasId() || !property_exists($this, 'templateMatches') || !isset($this->templateMatches[$group][$scope]))
		{
			return $this;
		}

		if (!isset($this->templateId[$group]))
		{
			$this->templateId[$group] = array();
		}

		$this->templateId[$group][$scope] = (int) $this->get($this->templateMatches[$group][$scope]);

		return $this;
	}
}
