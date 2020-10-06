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
 * Trait for entities with custom fields
 *
 * @since  2.0
 */
trait RedshopbEntityTraitFields
{
	/**
	 * Fields scope
	 * @var string
	 */
	protected $scope;

	/**
	 * Field entity collection
	 *
	 * @var RedshopbEntitiesCollection
	 */
	protected $fields;

	/**
	 * Method to get the fields collection
	 *
	 * @return RedshopbEntitiesCollection
	 */
	public function getFields()
	{
		if (null == $this->fields)
		{
			$this->fields = $this->loadFields();
		}

		return $this->fields;
	}

	/**
	 * Method to load the fields collection
	 *
	 * @return RedshopbEntitiesCollection
	 */
	protected function loadFields()
	{
		$db    = Factory::getDbo();
		$scope = $this->getScope();

		// Get list executed in previous sync items
		$query = $db->getQuery(true)
			->select('f.id')
			->from($db->qn('#__redshopb_field', 'f'))
			->where('scope = ' . $db->q($scope))
			->order($db->qn('f.ordering') . ' ASC')
			->order($db->qn('f.title') . ' ASC');

		$db->setQuery($query);

		$fieldIds = $db->loadColumn();

		$fields = array();

		foreach ($fieldIds AS $id)
		{
			$fields[] = new RedshopbEntityField($id);
		}

		return new RedshopbEntitiesCollection($fields);
	}

	/**
	 * Method to get the fields scope
	 *
	 * @return string
	 */
	public function getScope()
	{
		if (!empty($this->scope))
		{
			return $this->scope;
		}

		// Try to guess scope from entity name
		$flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE;
		$parts = preg_split('/(?=[A-Z])/', get_class($this), null, $flags);

		$this->scope = strtolower(array_pop($parts));

		return $this->scope;
	}
}
