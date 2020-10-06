<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * Order Entity.
 *
 * @since  2.0
 *
 * @property Registry|string $params
 */
class RedshopbEntityOrder extends RedshopbEntity
{
	use RedshopbEntityTraitCurrency, RedshopbEntityTraitFields;

	/**
	 * @const  integer
	 * @since  2.0
	 */
	const STATE_PENDING = 0;

	/**
	 * @const  integer
	 * @since  2.0
	 */
	const STATE_CONFIRMED = 1;

	/**
	 * @const  integer
	 * @since  2.0
	 */
	const STATE_CANCELLED = 2;

	/**
	 * @const  integer
	 * @since  2.0
	 */
	const STATE_REFUNDED = 3;

	/**
	 * @const  integer
	 * @since  2.0
	 */
	const STATE_SHIPPED = 4;

	/**
	 * @const  integer
	 * @since  2.0
	 */
	const STATE_READY_FOR_DELIVERY = 5;

	/**
	 * @const  integer
	 * @since  2.0
	 */
	const STATE_EXPEDITED = 6;

	/**
	 * @const  integer
	 * @since  2.0
	 */
	const STATE_COLLECTED = 7;

	/**
	 * Children orders if it has.
	 *
	 * @var    RedshopbEntitiesCollection
	 * @since  2.0
	 */
	protected $children;

	/**
	 * Get an array of available statuses
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public static function getAllowedStatuses()
	{
		$statuses = self::getAllowedStatusCodes();

		if ($statuses)
		{
			foreach ($statuses as $id => $status)
			{
				$statuses[$id] = Text::_('COM_REDSHOPB_ORDER_STATUS_' . strtoupper($status));
			}
		}

		return $statuses;
	}

	/**
	 * Get an array of available status codes
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public static function getAllowedStatusCodes()
	{
		return array(
			static::STATE_PENDING             => 'pending',
			static::STATE_CONFIRMED           => 'confirmed',
			static::STATE_READY_FOR_DELIVERY  => 'ready_for_delivery',
			static::STATE_SHIPPED             => 'shipped',
			static::STATE_COLLECTED           => 'collected',
			static::STATE_EXPEDITED           => 'expedited',
			static::STATE_CANCELLED           => 'cancelled',
			static::STATE_REFUNDED            => 'refunded'
		);
	}

	/**
	 * Get the child orders.
	 *
	 * @return  RedshopbEntitiesCollection
	 *
	 * @since   1.7
	 */
	public function getChildren()
	{
		if (null === $this->children)
		{
			$this->children = $this->searchChildren();
		}

		return $this->children;
	}

	/**
	 * Get the name of an status
	 *
	 * @param   integer  $statusId  Status identifier
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function getStatusName($statusId = null)
	{
		if (null === $statusId)
		{
			$item = $this->getItem();

			if (!$item || !property_exists($item, 'status'))
			{
				return null;
			}

			$statusId = (int) $item->status;
		}

		if (!static::isAllowedStatus($statusId))
		{
			return null;
		}

		$allowedStatuses = static::getAllowedStatuses();

		return $allowedStatuses[$statusId];
	}

	/**
	 * Check if this order status is allowed
	 *
	 * @return  boolean
	 *
	 * @since   2.0
	 */
	public function hasAllowedStatus()
	{
		$item = $this->getItem();

		if (!$item || !property_exists($item, 'status'))
		{
			return false;
		}

		return static::isAllowedStatus($item->status);
	}

	/**
	 * Check if a status id is valid
	 *
	 * @param   integer  $statusId  Status identifier
	 *
	 * @return  boolean
	 *
	 * @since   2.0
	 */
	public static function isAllowedStatus($statusId)
	{
		$statusId = (int) $statusId;

		$allowedStatuses = array_keys(static::getAllowedStatuses());

		return in_array($statusId, $allowedStatuses);
	}

	/**
	 * Render a status label/badge.
	 *
	 * @param   integer  $statusId  Status identifier
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function renderStatusLabel($statusId = null)
	{
		if (null === $statusId)
		{
			$item = $this->getItem();

			if (!$item || !property_exists($item, 'status'))
			{
				return null;
			}

			$statusId = (int) $item->status;
		}

		$statusName = $this->getStatusName($statusId);

		if (!$statusName)
		{
			return null;
		}

		return RedshopbLayoutHelper::render('redshopb.order.status', compact('statusId', 'statusName'), null, array('debug' => false));
	}

	/**
	 * Search in child orders.
	 *
	 * @param   array  $modelState  State for the Orders model
	 *
	 * @return  RedshopbEntitiesCollection
	 *
	 * @since   2.0
	 */
	public function searchChildren($modelState = array())
	{
		$collection = new RedshopbEntitiesCollection;

		if (!$this->hasId())
		{
			return $collection;
		}

		// Default state
		$state = array(
			'list.ordering'  => 'orders.created_date',
			'list.direction' => 'DESC',
			'list.limit'     => 0,
			'list.start'     => 0
		);

		// Override any received state
		foreach ($modelState as $key => $value)
		{
			$state[$key] = $value;
		}

		// Force search in this company
		$state['filter.parent_id'] = $this->id;

		$children = RedshopbModel::getFrontInstance('orders')->search($state);

		// In an ideal world the model already returned a collection
		if ($children instanceof RedshopbEntitiesCollection)
		{
			return $children;
		}

		foreach ($children as $child)
		{
			$entity = static::getInstance($child->id)->bind($child);

			$collection->add($entity);
		}

		return $collection;
	}

	/**
	 * Verifies that the supplied token matches the order token
	 *
	 * @param   string   $token   Security token to verify
	 *
	 * @return   boolean
	 */
	public function verifyToken($token)
	{
		$item = $this->getItem();

		if (null === $item)
		{
			return false;
		}

		return $item->token === $token;
	}
}
