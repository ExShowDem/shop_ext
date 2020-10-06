<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

jimport('joomla.database.usergroup');

/**
 * Tag table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableTag extends RedshopbTableNested
{
	/**
	 * The table name without the prefix
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_tag';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  string
	 */
	public $name;

	/**
	 * @var  string
	 */
	public $alias;

	/**
	 * @var  string
	 */
	public $type;

	/**
	 * @var  integer
	 */
	public $company_id = null;

	/**
	 * @var  integer
	 */
	public $parent_id = null;

	/**
	 * @var  integer
	 */
	public $lft;

	/**
	 * @var  integer
	 */
	public $rgt;

	/**
	 * @var  integer
	 */
	public $level = null;

	/**
	 * @var  string
	 */
	public $path = '';

	/**
	 * @var  string
	 */
	public $image = '';

	/**
	 * @var  integer
	 */
	public $state = 1;

	/**
	 * @var  integer
	 */
	public $checked_out = null;

	/**
	 * @var  string
	 */
	public $checked_out_time = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	public $created_by = null;

	/**
	 * @var  string
	 */
	public $created_date = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	public $modified_by = null;

	/**
	 * @var  string
	 */
	public $modified_date = '0000-00-00 00:00:00';

	/**
	 * Columns used to generate alias from
	 *
	 * @var  string
	 */
	protected $_aliasColumns = array('type', 'name');

	/**
	 * Keys to consider exclusive from the alias (appart from the alias field itself)
	 *
	 * @var  string
	 */
	protected $_aliasKeys = array('parent_id');

	/**
	 * Sync cascade children deletion
	 *
	 * @var  array
	 */
	protected $syncCascadeChildren = array(
		'Tag' => 'parent_id'
	);

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'pim' => array(
			'erp.pim.departmentCode',
			'erp.pim.productType'
		),
		'erp' => array(
			'ws.tag'
		),
		'b2b' => array(
			'erp.webservice.tags'
		),
		'fengel' => array(
			'fengel.category'
		)
	);

	/**
	 * WS sync mapping for other fields of the model table with other model pks
	 *
	 * @var  array
	 */
	protected $wsSyncMapFields = array(
		'parent_id' => array(
			'model' => 'Tags'
		),
		'company_id' => array(
			'model' => 'Companies'
		)
	);

	/**
	 * WS sync map of fields from string to boolean or viceversa
	 *
	 * @var  array
	 */
	protected $wsSyncMapBoolean = array('state');

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
		$this->name = trim($this->name);

		if (empty($this->name))
		{
			$this->setError(Text::_('COM_REDSHOPB_TITLE_CANNOT_BE_EMPTY'));

			return false;
		}

		if (empty($this->company_id))
		{
			$this->company_id = null;
		}

		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select(
			array(
				$db->qn('id'),
				$db->qn('company_id')
			)
		)
			->from($db->qn('#__redshopb_tag'))
			->where($db->qn('name') . ' = ' . $db->q($this->name))
			->where($db->qn('type') . ' = ' . $db->q($this->type));
		$db->setQuery($query);
		$tag = $db->loadObject();

		if (!is_null($tag) && $tag->id != $this->id && $tag->company_id == $this->company_id)
		{
			$this->setError(Text::sprintf('COM_REDSHOPB_TAG_ERROR_NOT_UNIQUE_TITLE', $this->name));

			return false;
		}

		return true;
	}

	/**
	 * Method to bind an associative array or object to the Table instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the Table instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 * @throws  InvalidArgumentException
	 */
	public function bind($src, $ignore = array())
	{
		if (parent::bind($src, $ignore))
		{
			if ($this->id)
			{
				if (isset($src['company_id']) && $src['company_id'] == '')
				{
					$this->company_id = null;
					$this->setOption('storeNulls', true);
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Method to store a node in the database table.
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = false)
	{
		if ($this->getOption('storeNulls', false))
		{
			$updateNulls = true;
		}

		return parent::store($updateNulls);
	}

	/**
	 * Delete Tags
	 *
	 * @param   string/array  $pk  Array of company ids or ids comma separated
	 *
	 * @return boolean
	 */
	public function deleteTags($pk)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Received an array of ids?
		if (is_array($pk))
		{
			// Sanitize input.
			$pk = ArrayHelper::toInteger($pk);
			$pk = RHelperArray::quote($pk);
			$pk = implode(',', $pk);
		}

		$pk = (is_null($pk)) ? $this->$k : $pk;

		// If no primary key is given, return false.
		if ($pk === null)
		{
			return false;
		}

		if (!is_array($pk))
		{
			$pk = array($pk);
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(array('id'))
			->from($db->qn('#__redshopb_tag'))
			->where('company_id IN (' . implode(',', $pk) . ')');
		$db->setQuery($query);

		$tags = $db->loadColumn();

		if ($tags)
		{
			foreach ($tags as $tagId)
			{
				if ($this->load($tagId, true))
				{
					if (!$this->delete($tagId))
					{
						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table.  The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.
	 *                            If not set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return  boolean  True on success; false if $pks is empty.
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		// Update children categories state follow this state
		if (!is_array($pks))
		{
			$pks = array($pks);
		}

		$pks = ArrayHelper::toInteger($pks);

		foreach ($pks as $tagId)
		{
			$childrens = RedshopbEntityTag::load($tagId)->getChildrenIds();
			$pks       = array_merge($pks, $childrens);
		}

		$pks = array_unique($pks);

		return parent::publish($pks, $state, $userId);
	}
}
