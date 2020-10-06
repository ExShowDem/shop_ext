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
 * Product Image Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelMedias extends RedshopbModelList
{
	/**
	 * Name to check in ACL
	 *
	 * @var  string
	 */
	protected $aclCheckName = 'product';

	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_medias';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'media_limit';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitstartField = 'auto';

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
				'id',
				'view',
				'state',
				'product_id', 'product_id_array', 'product_ids',
				'product_attribute_value_id',
				'attribute_value_id',
				'include_all_images'
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
		parent::populateState('m.id', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select(
				array(
					'm.*',
					$db->qn('pav.string_value', 'main_attribute_name'),
					$db->qn('m.attribute_value_id', 'product_attribute_value_id')
					)
			)
			->from($db->qn('#__redshopb_media', 'm'))
			->leftJoin($db->qn('#__redshopb_product_attribute_value', 'pav') . ' ON pav.id = m.attribute_value_id');

		$id = $this->getState('filter.id');

		if (is_numeric($id) && $id > 0)
		{
			$query->where('id = ' . (int) $id);
		}

		$productId = $this->getState('filter.product_id');

		if (is_numeric($productId))
		{
			$query->where('product_id = ' . (int) $productId);
		}

		// Product id search by array
		$productIdArray = $this->getState('filter.product_id_array');

		if (!empty($productIdArray))
		{
			$productIdArray = json_decode($productIdArray, true);

			if (!empty($productIdArray))
			{
				$query->where($db->qn('m.product_id') . ' IN (' . implode(',', RHelperArray::quote($productIdArray)) . ')');
			}
		}

		// Filter by multiple product ids
		$productIds = $this->getState('filter.product_ids', null);

		if (!is_null($productIds))
		{
			$query->where($db->qn('m.product_id') . ' IN (' . implode(',', $db->q($productIds)) . ')');
		}

		$productAttributeValueId = $this->getState('filter.product_attribute_value_id', null);

		if (is_numeric($productAttributeValueId))
		{
			$query->where('m.attribute_value_id = ' . (int) $productAttributeValueId);
		}

		$includeAllImages = $this->getState('filter.include_all_images', true);

		if (!$includeAllImages)
		{
			if (!is_numeric($productAttributeValueId))
			{
				$query->where('m.attribute_value_id IS NULL ');
			}
		}

		// Filter search
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('(name LIKE ' . $search . ') OR  (alt LIKE ' . $search . ')');
		}

		// Filter above some media id
		$previousId = $this->getState('filter.previous_id', null);

		if (!is_null($previousId))
		{
			$query->where($db->qn('id') . ' > ' . (int) $previousId);
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 'm.id';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		// Adds related web service data when requested
		$this->getListQueryWS($query);

		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   11.1
	 */
	public function getItems()
	{
		$productImages = parent::getItems();

		if (!empty($productImages) && ($this->getState('list.ws', 'false') == 'true'))
		{
			foreach ($productImages as $productImage)
			{
				if (empty($productImage->name))
				{
					continue;
				}

				$productImage->image = Uri::root()
					. RedshopbHelperMedia::getFullMediaPath($productImage->name, 'products', 'images', $productImage->remote_path);
			}
		}

		return $productImages;
	}
}
