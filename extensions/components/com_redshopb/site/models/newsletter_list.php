<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Newsletter list Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.6.17
 */
class RedshopbModelNewsletter_List extends RedshopbModelAdmin
{
	/**
	 * Favorite list object
	 *
	 * @var  null
	 */
	public $item = null;

	/**
	 * @var mixed
	 */
	public $subscribers;

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 */
	public function save($data)
	{
		$primaryKey = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');

		// Update reference user list for this newsletter list.
		if ($primaryKey > 0 && !empty($data['segmentation_query']))
		{
			// Clean up old reference
			$this->clearSubscribers($primaryKey);

			// Get subscribers list base on new segmentation query
			$subscribers = $this->getSubscribersBaseSegmentationQuery($data['segmentation_query']);

			// Store new reference with "fixed" is true
			$this->storeSubscribers($primaryKey, $subscribers);
		}

		return parent::save($data);
	}

	/**
	 * Method to get the row form.
	 *
	 * @param   int  $pk  Primary key
	 *
	 * @return	boolean|object
	 */
	public function getItem($pk = null)
	{
		$newsletterList = parent::getItem($pk);

		if ($newsletterList->id)
		{
			$this->subscribers = RedshopbHelperNewsletter_List::getSubscribers($newsletterList->id);
		}

		return $newsletterList;
	}

	/**
	 * Method for get subscribers list base on Segmentation Query
	 *
	 * @param   string  $criteria  Criteria for get subscribers.
	 *
	 * @return  array|false       List of subscribers on success. False otherwise.
	 */
	public function getSubscribersBaseSegmentationQuery($criteria)
	{
		if (empty($criteria))
		{
			return false;
		}

		$db                = $this->getDbo();
		$selectedCompanyId = RedshopbEntityUser::getCompanyIdForCurrentUser();

		$query = $db->getQuery(true)
			->select($db->qn('u.id'))
			->select($db->qn('c.id', 'company_id'))
			->select($db->qn('d.id', 'department_id'))
			->select($db->qn('rt.id', 'role_type_id'))
			->from($db->qn('#__redshopb_user', 'u'))
			->leftJoin($db->qn('#__redshopb_address', 'a') . ' ON ' . $db->qn('u.address_id') . ' = ' . $db->qn('a.id'))
			->leftJoin($db->qn('#__redshopb_department', 'd') . ' ON ' . $db->qn('u.department_id') . ' = ' . $db->qn('d.id'))
			->leftJoin(
				$db->qn('#__redshopb_user_multi_company', 'umc') . ' ON ' . $db->qn('umc.user_id')
				. ' = ' . $db->qn('u.id') . ' AND umc.company_id = ' . $selectedCompanyId
			)
			->leftJoin($db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('umc.company_id'))
			->leftJoin($db->qn('#__users', 'j') . ' ON ' . $db->qn('u.joomla_user_id') . ' = ' . $db->qn('j.id'))
			->leftJoin($db->qn('#__user_usergroup_map', 'map') . ' ON ' . $db->qn('u.joomla_user_id') . ' = ' . $db->qn('map.user_id'))
			->leftJoin(
				$db->qn('#__redshopb_role', 'r')
				. ' ON ' . $db->qn('r.joomla_group_id') . ' = ' . $db->qn('map.group_id')
				. ' AND ' . $db->qn('r.company_id') . ' = ' . $db->qn('c.id')
			)
			->leftJoin($db->qn('#__redshopb_role_type', 'rt') . ' ON ' . $db->qn('rt.id') . ' = ' . $db->qn('r.role_type_id'))
			->where('u.use_company_email = 0')
			->where('u.send_email = 1');

		// Build query for zip code
		$zipCodeSelect = '
			CASE WHEN ' . $db->qn('u.address_id') . ' IS NULL THEN
				CASE WHEN ' . $db->qn('d.address_id') . ' IS NULL THEN
					CASE WHEN ' . $db->qn('c.address_id') . ' IS NOT NULL THEN
						(SELECT ' . $db->qn('zip') . ' FROM ' . $db->qn('#__redshopb_address')
							. 'WHERE ' . $db->qn('id') . ' = ' . $db->qn('c.address_id') . ')
					END
				ELSE
					(SELECT ' . $db->qn('zip') . ' FROM ' . $db->qn('#__redshopb_address')
						. ' WHERE ' . $db->qn('id') . ' = ' . $db->qn('d.address_id') . ')
				END
			ELSE
				' . $db->qn('a.zip') . '
			END AS ' . $db->qn('finalzipcode');

		$query->select($zipCodeSelect);

		// Build query for city code
		$citySelect = '
			CASE WHEN ' . $db->qn('u.address_id') . ' IS NULL THEN
				CASE WHEN ' . $db->qn('d.address_id') . ' IS NULL THEN
					CASE WHEN ' . $db->qn('c.address_id') . ' IS NOT NULL THEN
						(SELECT ' . $db->qn('city') . ' FROM ' . $db->qn('#__redshopb_address')
							. 'WHERE ' . $db->qn('id') . ' = ' . $db->qn('c.address_id') . ')
					END
				ELSE
					(SELECT ' . $db->qn('city') . ' FROM ' . $db->qn('#__redshopb_address')
						. ' WHERE ' . $db->qn('id') . ' = ' . $db->qn('d.address_id') . ')
				END
			ELSE
				' . $db->qn('a.city') . '
			END AS ' . $db->qn('finalcity');

		$query->select($citySelect);

		// Build query for country code
		$countrySelect = '
			CASE WHEN ' . $db->qn('u.address_id') . ' IS NULL THEN
				CASE WHEN ' . $db->qn('d.address_id') . ' IS NULL THEN
					CASE WHEN ' . $db->qn('c.address_id') . ' IS NOT NULL THEN
						(SELECT ' . $db->qn('country_id') . ' FROM ' . $db->qn('#__redshopb_address')
							. 'WHERE ' . $db->qn('id') . ' = ' . $db->qn('c.address_id') . ')
					END
				ELSE
					(SELECT ' . $db->qn('country_id') . ' FROM ' . $db->qn('#__redshopb_address')
						. ' WHERE ' . $db->qn('id') . ' = ' . $db->qn('d.address_id') . ')
				END
			ELSE
				' . $db->qn('a.country_id') . '
			END AS ' . $db->qn('finalcountry');

		$query->select($countrySelect);

		// Replace value
		$finds    = array('company', 'department', 'role', 'zipcode', 'city', 'country');
		$replaces = array(
			$db->qn('company_id'),
			$db->qn('department_id'),
			$db->qn('role_type_id'),
			$db->qn('finalzipcode'),
			$db->qn('finalcity'),
			$db->qn('finalcountry')
		);

		$where = str_replace($finds, $replaces, $criteria);

		$query->having($where);
		$query->group($db->qn('u.id'));

		$db->setQuery($query);

		return $db->loadColumn(0);
	}

	/**
	 * Method for update segmentation query for newsletter list
	 *
	 * @param   int     $newsletterListId  ID of newsletter list
	 * @param   string  $criteria          Criteria for filter user
	 * @param   json    $criteriaJSON      Criteria for filter in JSON mode.
	 *
	 * @return  boolean|integer            True if success. False otherwise.
	 */
	public function updateSegmentationQuery($newsletterListId, $criteria, $criteriaJSON)
	{
		$newsletterListId = (int) $newsletterListId;

		if (!$newsletterListId)
		{
			return false;
		}

		$table = RedshopbTable::getAutoInstance('Newsletter_List');

		if (!$table->load($newsletterListId))
		{
			return false;
		}

		$table->segmentation_query = $criteria;
		$table->segmentation_json  = $criteriaJSON;

		// Clear subscribers list
		if (!$table->store() || !$this->clearSubscribers($newsletterListId))
		{
			return false;
		}

		// Load subscribers list.
		$subscribers = $this->getSubscribersBaseSegmentationQuery($criteria);

		// Store new subscribers list
		$this->storeSubscribers($newsletterListId, $subscribers);

		return !empty($subscribers) ? count($subscribers) : 0;
	}

	/**
	 * Method for store subscribers list belong to an newsletter list to reference table
	 *
	 * @param   int        $newsletterListId  ID of Newsletter List.
	 * @param   array/int  $subscribers       List of user Id
	 * @param   boolean    $fixed             Fixed value.
	 *
	 * @return  boolean                   True on success. False otherwise.
	 */
	public function storeSubscribers($newsletterListId, $subscribers, $fixed = false)
	{
		$newsletterListId = (int) $newsletterListId;
		$fixed            = (boolean) $fixed;

		if (!is_array($subscribers))
		{
			$subscribers = (int) $subscribers;
			$subscribers = array($subscribers);
		}

		// Check requirement variable
		if (!$newsletterListId || empty($subscribers))
		{
			return false;
		}

		$db = $this->getDbo();

		// Clean exist subscribers which is in fixed.
		$query = $db->getQuery(true)
			->delete($db->qn('#__redshopb_newsletter_user_xref'))
			->where($db->qn('newsletter_list_id') . ' = ' . $newsletterListId)
			->where($db->qn('user_id') . ' IN (' . implode(',', $subscribers) . ')');
		$db->setQuery($query);
		$db->execute();

		// Insert new reference
		$query->clear()
			->insert($db->qn('#__redshopb_newsletter_user_xref'))
			->columns($db->qn(array('newsletter_list_id', 'user_id', 'fixed')));

		foreach ($subscribers as $subscriber)
		{
			$query->values($newsletterListId . ',' . (int) $subscriber . ',' . (int) $fixed);
		}

		$db->setQuery($query);

		return $db->execute();
	}

	/**
	 * Method for clear subscriber list of an newsletter list.
	 *
	 * @param   int  $newsletterListId  ID of newsletter list.
	 *
	 * @return  boolean                 True on success. False otherwise.
	 */
	public function clearSubscribers($newsletterListId)
	{
		$newsletterListId = (int) $newsletterListId;

		if (!$newsletterListId)
		{
			return false;
		}

		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->delete($db->qn('#__redshopb_newsletter_user_xref'))
			->where($db->qn('newsletter_list_id') . ' = ' . $newsletterListId);

		$db->setQuery($query);

		return (boolean) $db->execute();
	}
}
