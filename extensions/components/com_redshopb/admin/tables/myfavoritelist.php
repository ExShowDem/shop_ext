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

jimport('joomla.database.usergroup');

/**
 * Tag table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableMyfavoritelist extends RedshopbTable
{
	/**
	 * The table name without the prefix
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_favoritelist';

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
	 * @var  integer
	 */
	public $state = 1;

	/**
	 * @var  integer
	 */
	public $company_id = null;

	/**
	 * @var  integer
	 */
	public $department_id = null;

	/**
	 * @var  integer
	 */
	public $user_id = null;

	/**
	 * @var  integer
	 */
	public $visible_others = 0;

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
	 * Method to bind an associative array or object to the Table instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the Table instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws  InvalidArgumentException
	 */
	public function bind($src, $ignore = array())
	{
		if (!empty($src['user_id']))
		{
			// Get user entity
			$user = RedshopbEntityUser::getInstance($src['user_id']);

			if (!$user)
			{
				$this->setError(Text::_('COM_REDSHOPB_MYFAVORITELIST_INVALID_USER_ID'));

				return false;
			}

			$department = $user->getDepartment();
			$company    = $user->getSelectedCompany();

			// Set department and company
			if ($department)
			{
				$src['department_id'] = $department->getId();
			}
			else
			{
				$src['department_id'] = 0;
			}

			if ($company)
			{
				$src['company_id'] = $company->getId();
			}
			else
			{
				$src['company_id'] = 0;
			}
		}

		return parent::bind($src, $ignore);
	}
}
