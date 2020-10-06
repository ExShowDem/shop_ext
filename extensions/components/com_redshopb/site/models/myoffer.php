<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Date\Date;
/**
 * My offer Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelMyoffer extends RedshopbModelAdmin
{
	/**
	 * Get the associated Table
	 *
	 * @param   string  $name    Table name
	 * @param   string  $prefix  Table prefix
	 * @param   array   $config  Configuration array
	 *
	 * @return  Table
	 */
	public function getTable($name = 'offer', $prefix = '', $config = array())
	{
		if ($name == '')
		{
			$name = 'offer';
		}

		return parent::getTable($name, $prefix, $config);
	}

	/**
	 * Method for get product list of an offer
	 *
	 * @param   int  $offerId  ID of offer
	 *
	 * @return  array/boolean     List of products if success. False otherwise.
	 */
	public function getProducts($offerId)
	{
		$offerId = (int) $offerId;

		if (!$offerId)
		{
			return false;
		}

		$db = Factory::getDbo();

		$subQuery = $db->getQuery(true)
			->select('GROUP_CONCAT(pav.sku ORDER BY pa.main_attribute desc, pa.ordering asc SEPARATOR ' . $db->q('-') . ')')
			->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
			->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'piavx') . ' ON piavx.product_attribute_value_id = pav.id')
			->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pa.id = pav.product_attribute_id')
			->where('pi.id = piavx.product_item_id')
			->order('pav.ordering');

		$query = $db->getQuery(true)
			->select('oix.*')
			->select('CONCAT_WS(' . $db->q('-') . ', p.sku, (' . $subQuery . ')) AS sku')
			->select($db->qn('p.name', 'product_name'))
			->from($db->qn('#__redshopb_offer_item_xref', 'oix'))
			->leftJoin($db->qn('#__redshopb_product', 'p') . ' ON p.id = oix.product_id')
			->leftJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON pi.id = oix.product_item_id')
			->where($db->qn('oix.offer_id') . ' = ' . (int) $offerId);

		return $db->setQuery($query)
			->loadObjectList();
	}

	/**
	 * Method for add comment to rejected offer
	 *
	 * @param   int     $offId     ID of an offer
	 * @param   string  $comments  comments
	 *
	 * @return  boolean           True on success. False otherwise.
	 */
	public function addComment($offId, $comments)
	{
		if (!$offId)
		{
			return false;
		}

		$table = RedshopbTable::getAdminInstance('Offer');

		if (!$table->load($offId))
		{
			return false;
		}

		if (in_array($table->get('status'), array('rejected', 'requested', 'created', 'ordered')))
		{
			return false;
		}

		$row['comments'] = $comments;
		$row['status']   = 'rejected';
		$row['id']       = $offId;

		return $table->save($row);
	}

	/**
	 * Method for load cart data from offers to cart session
	 *
	 * @param   int      $offerId       ID of offer
	 * @param   boolean  $ignoreStatus  Ignore availability-status of offer
	 *
	 * @return  boolean       True on success. False otherwise.
	 */
	public function loadCart($offerId, $ignoreStatus = false)
	{
		if (!$offerId)
		{
			return false;
		}

		$table = RedshopbTable::getAutoInstance('Offer');

		if (!$table->load($offerId))
		{
			return false;
		}

		if (in_array($table->get('status'), array('rejected', 'requested', 'created', 'requested')))
		{
			return false;
		}

		$row['status'] = 'accepted';
		$row['id']     = $offerId;

		$table->save($row);

		$offerItems = $this->loadOfferItem($offerId);

		if (empty($offerItems))
		{
			return false;
		}

		return RedshopbHelperCart::addToCartOffer($offerId, $ignoreStatus);
	}

	/**
	 * Method for load product items in cart
	 *
	 * @param   int  $offerId  ID of offer
	 *
	 * @return  array  List of product in offer.
	 */
	public function loadOfferItem($offerId)
	{
		$offerId = (int) $offerId;

		if (!$offerId)
		{
			return false;
		}

		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select('offitem.*')
			->from($db->qn('#__redshopb_offer_item_xref', 'offitem'))
			->where($db->qn('offitem.offer_id') . ' = ' . (int) $offerId);
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Method override to check-out a record.
	 * FOr current view checkout disabled, because not have a sense. The view just for review
	 *
	 * @param   integer  $pk  The ID of the primary key.
	 *
	 * @return  boolean  True.
	 */
	public function checkout($pk = null)
	{
		return true;
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 */
	protected function canDelete($record)
	{
		$user         = Factory::getUser();
		$redshopbUser = RedshopbHelperUser::getUser();

		if ($user->guest || !$redshopbUser)
		{
			return false;
		}

		if ($record->get('status') == 'ordered')
		{
			return false;
		}

		if (!RedshopbHelperOffers::canUse($record))
		{
			return false;
		}

		return parent::canDelete($record);
	}

	/**
	 * Method for accepting an offer
	 *
	 * @param   int  $pks  primary keys array
	 *
	 * @return  boolean           True on success. False otherwise.
	 */
	public function acceptOffer($pks)
	{
		$pks   = (array) $pks;
		$now   = Date::getInstance()->toSql();
		$table = $this->getTable();

		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				if (RedshopbHelperOffers::canUse($table))
				{
					$row = array(
						'status' => 'accepted',
						'execution_date' => $now
					);

					if (!$table->save($row))
					{
						$this->setError($table->getError());

						return false;
					}
				}
				else
				{
					$this->setError(Text::_('COM_REDSHOPB_OPERATION_NOT_PERMITTED'));

					return false;
				}
			}
		}

		$this->cleanCache();

		return true;
	}

	/**
	 * Method for reject an offer
	 *
	 * @param   int  $pks  primary keys array
	 *
	 * @return  boolean           True on success. False otherwise.
	 */
	public function rejectOffer($pks)
	{
		$table = $this->getTable();
		$pks   = (array) $pks;
		$now   = Date::getInstance()->toSql();

		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				if (RedshopbHelperOffers::canUse($table))
				{
					$row = array(
						'status' => 'rejected',
						'execution_date' => $now
					);

					if (!$table->save($row))
					{
						$this->setError($table->getError());

						return false;
					}
				}
				else
				{
					$this->setError(Text::_('COM_REDSHOPB_OPERATION_NOT_PERMITTED'));

					return false;
				}
			}
		}

		$this->cleanCache();

		return true;
	}
}
