<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
/**
 * Product attribute values Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelProduct_Attribute_Values extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_product_attribute_values';

	/**
	 * Limit field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'product_attribute_value_limit';

	/**
	 * Limit start field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitstartField = 'auto';

	/**
	 * Main table query prefix
	 *
	 * @var  array
	 */
	protected $mainTablePrefix = 'pav';

	/**
	 * Constructor
	 *
	 * @param   array  $config  Configuration array
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'pav.id',
				'product_id', 'pav.product_id',
				'product_attribute_id', 'pav.product_attribute_id',
				'ordering', 'pav.ordering'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState('pav.id', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select(
			array(
					'pav.*'
					)
		)
			->select($db->quoteName('pav.string_value', 'value'))
			->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
			->join('inner', $db->qn('#__redshopb_product_attribute', 'pa') . ' ON ' . $db->qn('pa.id') . ' = ' . $db->qn('pav.product_attribute_id'))
			->join('inner', $db->qn('#__redshopb_product', 'p') . ' ON ' . $db->qn('p.id') . ' = ' . $db->qn('pa.product_id'));

		$filterId = $this->getState('filter.id');

		if (is_numeric($filterId) && $filterId > 0)
		{
			$query->where($db->qn('pav.id') . ' = ' . (int) $filterId);
		}

		$filterProductAttributeId = $this->getState('filter.product_attribute_id');

		if (is_numeric($filterProductAttributeId) && $filterProductAttributeId > 0)
		{
			$query->where($db->qn('pa.id') . ' = ' . (int) $filterProductAttributeId);
		}

		$filterProductId = $this->getState('filter.product_id');

		if (is_numeric($filterProductId) && $filterProductId > 0)
		{
			$query->where($db->qn('p.id') . ' = ' . (int) $filterProductId);
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');
		$order         = !empty($orderList) ? $orderList : 'pav.id';
		$direction     = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		// Adds related web service data when requested
		$this->getListQueryWS($query);

		return $query;
	}

	/**
	 * Overridden to add support for images
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   11.1
	 */
	public function getItems()
	{
		$items = parent::getItems();

		if (empty($items) || ($this->getState('filter.include_images', $this->getState('list.include_images', 'false')) != 'true'))
		{
			return $items;
		}

		foreach ($items as $item)
		{
			if (empty($item->image))
			{
				continue;
			}

			$increment      = RedshopbHelperMedia::getIncrementFromFilename($item->image);
			$folderName     = RedshopbHelperMedia::getFolderName($increment);
			$item->imageurl = Uri::root() . 'media/com_redshopb/images/originals/categories/' . $folderName . '/' . $item->image;
		}

		return $items;
	}
}
