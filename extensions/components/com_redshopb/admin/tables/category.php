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
/**
 * Category table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableCategory extends RedshopbTableNested
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_category';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  string
	 */
	public $name;

	/**
	 * @var  integer
	 */
	public $state = 1;

	/**
	 * @var  integer
	 */
	public $hide = 0;

	/**
	 * @var  string
	 */
	public $path = '';

	/**
	 * @var  string
	 */
	public $image = '';

	/**
	 * @var  string
	 */
	public $alias = '';

	/**
	 * @var  string
	 */
	public $description = '';

	/**
	 * @var integer
	 */
	public $filter_fieldset_id = null;

	/**
	 * @var  string
	 */
	public $created_date = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	public $created_by = null;

	/**
	 * @var  string
	 */
	public $modified_date = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	public $modified_by = null;

	/**
	 * @var  integer
	 */
	public $checked_out = null;

	/**
	 * @var  string
	 */
	public $checked_out_time = '0000-00-00 00:00:00';

	/**
	 * @var integer
	 */
	public $company_id = null;

	/**
	 * @var integer
	 */
	public $parent_id = null;

	/**
	 * @var null
	 */
	public $template_id = null;

	/**
	 * @var null
	 */
	public $product_grid_template_id = null;

	/**
	 * @var null
	 */
	public $product_list_template_id = null;

	/**
	 * Layout params in JSON format.
	 *
	 * @var  integer
	 */
	public $params;

	/**
	 * Sync cascade children deletion
	 *
	 * @var  array
	 */
	protected $syncCascadeChildren = array(
		'Category' => 'parent_id'
	);

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'pim' => array(
			'erp.pim.category'
		),
		'erp' => array(
			'ws.category'
		),
		'b2b' => array(
			'erp.webservice.categories'
		),
		'fengel' => array(
			'fengel.itemgroup'
		)
	);

	/**
	 * WS sync mapping for other fields of the model table with other model pks
	 *
	 * @var  array
	 */
	protected $wsSyncMapFields = array(
		'company_id' => array(
			'model' => 'Companies'
		),
		'parent_id' => array(
			'model' => 'Categories'
		),
		'filter_fieldset_id' => array(
			'model' => 'Filter_Fieldsets'
		)
	);

	/**
	 * WS sync mapping for code fields with other model related data (alias, etc)
	 *
	 * @var  array
	 */
	protected $wsSyncMapCodeFields = array(
		'template_code' => 'Templates',
		'product_list_template_code' => 'Templates',
		'product_grid_template_code' => 'Templates'
	);

	/**
	 * WS sync mapping for other fields of the model table result with other model pks - using array of related ids
	 *
	 * @var  array
	 */
	protected $wsSyncMapFieldsMultiple = array(
		'local_fields' => 'Fields'
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

		if (empty($this->template_id))
		{
			$this->template_id = null;
		}

		if (empty($this->product_list_template_id))
		{
			$this->product_list_template_id = null;
		}

		if (empty($this->product_grid_template_id))
		{
			$this->product_grid_template_id = null;
		}

		if (empty($this->company_id))
		{
			$this->company_id = null;

			return true;
		}

		$userId         = Factory::getUser()->id;
		$childCompanies = explode(',', RedshopbHelperACL::listAvailableChildCompanies($userId, $this->company_id));

		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('company_id')
			->from($db->qn('#__redshopb_category'))
			->where($db->qn('id') . ' = ' . $this->parent_id);
		$db->setQuery($query);
		$parentCompany = $db->loadResult();

		if (in_array($parentCompany, $childCompanies))
		{
			$this->setError(Text::_('COM_REDSHOPB_CANNOT_CREATE_PARENT_CATEGORIES_UNDER_CHILD_COMPANIES'));

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
		if (!parent::bind($src, $ignore))
		{
			return false;
		}

		if ($this->id && empty($src['company_id']))
		{
			$this->company_id = null;
			$this->setOption('storeNulls', true);
		}

		if (empty($src['filter_fieldset_id']))
		{
			$this->filter_fieldset_id = null;
			$this->setOption('storeNulls', true);
		}

		return true;
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

		if (!parent::store($updateNulls))
		{
			return false;
		}

		return true;
	}

	/**
	 * Delete Categories
	 *
	 * @param   string/array  $pk  Array of company ids or ids comma separated
	 *
	 * @return boolean
	 */
	public function deleteCategories($pk)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		$pk = (is_null($pk)) ? array($this->{$k}) : $pk;

		// Received an array of ids?
		if (!is_array($pk))
		{
			$pk = array($pk);
		}

		// Sanitize input.
		$pk = ArrayHelper::toInteger($pk);
		$pk = RHelperArray::quote($pk);
		$pk = implode(',', $pk);

		// If no primary key is given, return false.
		if ($pk === null)
		{
			return false;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(array('id'))
			->from($db->qn('#__redshopb_category'))
			->where('company_id IN (' . $pk . ')');
		$db->setQuery($query);

		$categories = $db->loadColumn();

		if (!$categories)
		{
			return true;
		}

		foreach ($categories as $categoryId)
		{
			if ($this->load($categoryId, true) && !$this->delete($categoryId))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to delete a node and, optionally, its child nodes from the table.
	 *
	 * @param   integer  $pk        The primary key of the node to delete.
	 * @param   boolean  $children  True to delete child nodes, false to move them up a level or cause error when using deleted field
	 *
	 * @return  boolean  True on success.
	 */
	public function delete($pk = null, $children = true)
	{
		if ($children)
		{
			if (is_array($pk))
			{
				// Sanitize input.
				$localIds = implode(
					',',
					RHelperArray::quote(
						ArrayHelper::toInteger($pk)
					)
				);
			}
			else
			{
				$localIds = $this->_db->q($pk);
			}

			if (!empty($localIds))
			{
				$db    = Factory::getDbo();
				$query = $db->getQuery(true)
					->select('c.image')
					->from($db->qn($this->_tbl, 'c'))
					->leftJoin($db->qn($this->_tbl, 'cp') . ' ON c.lft BETWEEN cp.lft AND cp.rgt')
					->where($db->qn('cp.id') . ' IN (' . $localIds . ')');

				$childrenImages = $db->setQuery($query)->loadColumn();
			}
		}

		$result = parent::delete($pk, $children);

		if ($result)
		{
			// We need to delete image too
			if ($this->image)
			{
				RedshopbHelperThumbnail::deleteImage($this->image, 1, 'categories');
			}

			if (!empty($childrenImages))
			{
				foreach ($childrenImages as $childrenImage)
				{
					RedshopbHelperThumbnail::deleteImage($childrenImage, 1, 'categories');
				}
			}
		}

		return $result;
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

		$pk = ArrayHelper::toInteger($pks);

		foreach ($pks as $categoryId)
		{
			$children = RedshopbEntityCategory::load($categoryId)->getChildrenIds();
			$pks      = array_merge($pks, $children);
		}

		$pks = array_unique($pks);

		return parent::publish($pks, $state, $userId);
	}
}
