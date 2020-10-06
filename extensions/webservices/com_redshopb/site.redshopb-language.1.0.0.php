<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Webservices
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Table\Table;

JLoader::import('languages', JPATH_ADMINISTRATOR . '/components/com_languages/models');

/**
 * Api Helper class for overriding default methods
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Webservices
 * @since       1.6
 */
class RApiHalHelperSiteRedshopbLanguage extends LanguagesModelLanguages
{
	/**
	 * @var    string
	 */
	public $option = 'com_redshopb';

	/**
	 * Method to get the model name
	 *
	 * @return  string  The name of the model
	 */
	public function getName()
	{
		return 'language';
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	public function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select(
			array(
					'a.*',
					$db->qn('a.lang_code', 'code'),
					$db->qn('a.title', 'name')
				)
		)
			->from($db->qn('#__languages', 'a'));

		// Filter search
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');

			$searchFields = array(
				'a.title LIKE ' . $search,
				'a.lang_code LIKE ' . $search,
			);

			$query->where('(' . implode(' OR ', $searchFields) . ')');
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');
		$order         = !empty($orderList) ? $orderList : 'code';
		$direction     = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}

	/**
	 * Method to get a single record using the language code as a reference
	 *
	 * @param   string  $code  The lang code to be retrieved
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function getItem($code)
	{
		$table = Table::getInstance('Language');

		if ($table->load(array('lang_code' => $code)))
		{
			$model = RModel::getAdminInstance('Language', array(), 'com_languages');

			$item = $model->getItem($table->lang_id);

			if ($item)
			{
				$item->code = $item->lang_code;
				$item->name = $item->title;

				return $item;
			}
		}

		return false;
	}
}
