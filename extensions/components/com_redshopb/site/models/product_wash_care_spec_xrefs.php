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
 * Wash and care specs Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelProduct_Wash_Care_Spec_Xrefs extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_product_wash_care_spec_xrefs';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'product_wash_care_spec_xrefs_limit';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitstartField = 'auto';

	/**
	 * Delete items
	 *
	 * @param   mixed  $pks  id or array of ids of items to be deleted
	 *
	 * @return  boolean
	 */
	public function delete($pks = null)
	{
		if (!empty($pks))
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$query->delete($db->qn('#__redshopb_product_wash_care_spec_xref'));
			$where = array();

			foreach ($pks as $pk)
			{
				$ids       = explode('_', $pk);
				$wncId     = $ids[0];
				$productId = $ids[1];

				$where[] = '(' . $db->qn('product_id') . ' = ' . (int) $productId . ' AND '
							. $db->qn('wash_care_spec_id') . ' = ' . (int) $wncId . ')';
			}

			$query->where('(' . implode(' OR ', $where) . ')');

			$db->setQuery($query);

			return $db->execute();
		}

		return true;
	}
}
