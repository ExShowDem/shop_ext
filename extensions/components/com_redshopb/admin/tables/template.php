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
 * Type table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableTemplate extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_template';

	/**
	 * Columns used to generate alias from
	 *
	 * @var  string
	 */
	protected $_aliasColumns = array('scope', 'name');

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
	 * @var  string (enum)
	 */
	public $scope;

	/**
	 * @var string
	 */
	public $template_group = '';

	/**
	 * @var  string
	 */
	public $content;

	/**
	 * @var  integer
	 */
	public $state = 1;

	/**
	 * @var integer
	 */
	public $default = 0;

	/**
	 * @var integer
	 */
	public $editable = 1;

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
	 * @var [type]
	 */
	public $params;

	/**
	 * Method to store a node in the database table.
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = false)
	{
		$db           = Factory::getDbo();
		$templateName = '';

		// Check exists the same name file for current scope
		$query  = $db->getQuery(true)
			->select('id')
			->from($db->qn('#__redshopb_template'))
			->where('alias = ' . $db->q($this->alias))
			->where('scope = ' . $db->q($this->scope))
			->where('template_group = ' . $db->q($this->template_group))
			->where('id <> ' . $db->q($this->id));
		$result = $db->setQuery($query)->loadResult();

		if ($result)
		{
			$this->setError(Text::sprintf('COM_REDSHOPB_TEMPLATE_ERROR_FILE_WITH_THE_SAME_NAME_EXISTS', $this->alias, $this->scope));

			return false;
		}

		if ($this->default == 1)
		{
			$this->state = 1;

			// Remove old default
			$query = $db->getQuery(true)
				->update($db->qn('#__redshopb_template'))
				->set($db->qn('default') . ' = 0')
				->where($db->qn('scope') . ' = ' . $db->quote($this->scope))
				->where('template_group = ' . $db->q($this->template_group));

			try
			{
				$db->setQuery($query)->execute();
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());

				return false;
			}
		}

		if ($this->id)
		{
			// Get file name before store
			$query        = $db->getQuery(true)
				->select('alias')
				->from($db->qn('#__redshopb_template'))
				->where('id = ' . $db->q($this->id));
			$templateName = $db->setQuery($query, 0, 1)
				->loadResult();
		}

		if (!parent::store($updateNulls))
		{
			return false;
		}

		RedshopbHelperTemplate::createDefaultTemplate($this, true);

		// If file in the component folder rename, then need rename all relate customizations
		if ($templateName != '' && $templateName != $this->alias)
		{
			$oldValues         = clone $this;
			$oldValues->alias  = $templateName;
			$oldCustomizations = RedshopbHelperTemplate::getListCustomizations($oldValues);

			// Delete old template in the component folder
			$oldPath = RedshopbHelperTemplate::getFilePath($oldValues);

			if (JFile::exists($oldPath))
			{
				JFile::delete($oldPath);
			}

			foreach ($oldCustomizations as $oldCustomization)
			{
				$newPath = RedshopbHelperTemplate::getFilePath($this, $oldCustomization->template);

				JFile::move($oldCustomization->fullPath, $newPath);
			}
		}

		return true;
	}

	/**
	 * Method to load a row from the database by primary key and bind the fields
	 * to the Table instance properties.
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
	 *                           set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found.
	 */
	public function load($keys = null, $reset = true)
	{
		if (!parent::load($keys, $reset))
		{
			return false;
		}

		$filePath = RedshopbHelperTemplate::getFilePath($this);

		if (JFile::exists($filePath))
		{
			$this->content = file_get_contents($filePath);
		}

		return true;
	}

	/**
	 * Deletes this row in database (or if provided, the row of key $pk)
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 */
	public function delete($pk = null)
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

		foreach ((array) $pk as $item)
		{
			if ($this->load($item))
			{
				if ($this->default == 1)
				{
					$this->setError(Text::sprintf('COM_REDSHOPB_TEMPLATE_DELETE_DEFAULT_NOT_ALLOW', $this->name));

					return false;
				}

				$file           = RedshopbHelperTemplate::getFilePath($this);
				$customizations = RedshopbHelperTemplate::getListCustomizations($this);

				if (!parent::delete($item))
				{
					return false;
				}

				foreach ($customizations as $customization)
				{
					JFile::delete($customization->fullPath);
				}

				if (JFile::exists($file))
				{
					JFile::delete($file);
				}
			}
		}

		return true;
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table. The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.
	 * If not set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return boolean True on success; false if $pks is empty.
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		// Initialise variables.
		$k       = $this->_tbl_key;
		$copyPks = $pks;

		// Sanitize input.
		$copyPks = ArrayHelper::toInteger($copyPks);
		$userId  = (int) $userId;
		$state   = (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($copyPks))
		{
			if ($this->$k)
			{
				$copyPks = array($this->$k);
			}

			// Nothing to set publishing state on, return false.
			else
			{
				$this->setError(Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));

				return false;
			}
		}

		if ($state == 0)
		{
			$db     = Factory::getDbo();
			$query  = $db->getQuery(true)
				->select('name')
				->from($db->qn('#__redshopb_template'))
				->where($db->qn('default') . ' = 1')
				->where($db->qn($k) . '=' . implode(' OR ' . $db->qn($k) . '=', $copyPks));
			$result = $db->setQuery($query, 0, 1)
				->loadResult();

			if ($result)
			{
				$this->setError(Text::sprintf('COM_REDSHOPB_TEMPLATE_DEFAULT_CAN_NOT_UNPUBLISH', $result));

				return false;
			}
		}

		return parent::publish($pks, $state, $userId);
	}
}
