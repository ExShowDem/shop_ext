<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Stockroom Group Stockroom Reference table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.6.0
 */
class RedshopbTableStockroom_Group_Stockroom_Xref extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_stockroom_group_stockroom_xref';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  integer
	 */
	public $stockroom_group_id;

	/**
	 * @var  integer
	 */
	public $stockroom_id;

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'erp' => array(
			'ws.stockroom_group_stockroom_xref'
		),
	);

	/**
	 * WS sync mapping for other fields of the model table with other model pks
	 *
	 * @var  array
	 */
	protected $wsSyncMapFields = array(
		'stockroom_id' => array(
			'model' => 'Stockrooms'
		),
		'stockroom_group_id' => array(
			'model' => 'Stockroom_Groups'
		)
	);

	/**
	 * Checks that the object is valid and able to be stored.
	 *
	 * This method checks that the parent_id is non-zero and exists in the database.
	 * Note that the root node (parent_id = 0) cannot be manipulated with this class.
	 *
	 * @return  boolean  True if all checks pass.
	 */
	public function check()
	{
		$this->stockroom_id       = (int) $this->stockroom_id;
		$this->stockroom_group_id = (int) $this->stockroom_group_id;

		if (!$this->stockroom_group_id)
		{
			$this->setError(Text::_('COM_REDSHOPB_STOCKROOM_GROUP_ERROR_MISSING_GROUP_ID'));

			return false;
		}

		if (!$this->stockroom_id)
		{
			$this->setError(Text::_('COM_REDSHOPB_STOCKROOM_GROUP_ERROR_MISSING_STOCKROOM_ID'));

			return false;
		}

		// We are assuming there can be only one record with the same stockroom/product_id combo
		$xrefId = $this->getXrefId($this->stockroom_id, $this->stockroom_group_id);

		if (empty($this->id) && $xrefId)
		{
			$this->id = $xrefId;
		}

		return true;
	}

	/**
	 * Method to return the xref ID from the stockroom_id and product_id
	 *
	 * @param   int  $stockroomId        primary key of the stockroom
	 * @param   int  $stockroomGroupId   primary key of the stockroom group
	 *
	 * @return mixed  id or null if one does not exist
	 */
	private function getXrefId($stockroomId, $stockroomGroupId)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('id')
			->from($db->qn($this->_tbl))
			->where($db->qn('stockroom_id') . ' = ' . (int) $stockroomId)
			->where($db->qn('stockroom_group_id') . ' = ' . (int) $stockroomGroupId);
		$result = $db->setQuery($query)->loadResult();

		return $result;
	}
}
