<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
/**
 * Redshop Config Model
 *
 * @package     Redshop.Component
 * @subpackage  Models.Config
 * @since       1.13.0
 *
 */
class RedshopbModelConfig extends RedshopbModelAdmin
{
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array  The default data is an empty array.
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState(
			$this->context . '.data',
			array()
		);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function getItem($pk = null)
	{
		$data = RedshopbApp::getConfig()->toArray();
		$item = new stdClass;

		foreach ($data as $key => $value)
		{
			$item->{$key} = $value;
		}

		return $item;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   12.2
	 */
	public function save($data)
	{
		// Fixes bug in joomla with updating and not setting item id in the model state
		$this->getState();

		if (!$this->canSave($data))
		{
			return false;
		}

		$table = $this->getTable();

		if ($data['date_new_product'] < 0)
		{
			$this->setError(Text::_('COM_REDSHOPB_CONFIG_DATE_NEW_PRODUCT_SAVE_ERROR'));

			return false;
		}

		if ($data['stored_max_width'] < 0)
		{
			$this->setError(Text::_('COM_REDSHOPB_CONFIG_STORED_MAX_WIDTH_SAVE_ERROR'));

			return false;
		}

		if ($data['stored_max_height'] < 0)
		{
			$this->setError(Text::_('COM_REDSHOPB_CONFIG_STORED_MAX_HEIGHT_SAVE_ERROR'));

			return false;
		}

		// Store multiple data.
		foreach ($data as $key => $value)
		{
			$table->reset();

			if (!$table->load(array('name' => $key)))
			{
				$table->id = null;
				$table->set('name', $key);
			}

			$table->set('value', json_encode($value));

			if (!$table->store())
			{
				$this->setError($table->getError());

				return false;
			}
		}

		return true;
	}
}
