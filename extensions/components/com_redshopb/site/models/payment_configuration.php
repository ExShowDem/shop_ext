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
use Joomla\CMS\Table\Table;

RLoader::import('models.payment_configuration', JPATH_ADMINISTRATOR . '/components/com_redcore');
RLoader::import('tables.payment_configuration', JPATH_ADMINISTRATOR . '/components/com_redcore');

/**
 * Payment Configuration Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelPayment_Configuration extends RedcoreModelPayment_Configuration
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  Configuration array
	 *
	 * @throws  RuntimeException
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->context = strtolower('com_redcore.edit.' . $this->getName());
	}

	/**
	 * Get the associated Table
	 *
	 * @param   string  $name    Table name
	 * @param   string  $prefix  Table prefix
	 * @param   array   $config  Configuration array
	 *
	 * @return  Table
	 */
	public function getTable($name = null, $prefix = '', $config = array())
	{
		if (empty($name) && empty($prefix))
		{
			$prefix = 'RedcoreTable';
		}

		return parent::getTable($name, $prefix, $config);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @throws  RuntimeException
	 *
	 * @since   1.2
	 */
	public function save($data)
	{
		$pluginParams = Factory::getApplication()->input->get('plugin', array(), 'array');
		$data         = array_merge($data, $pluginParams);

		if (parent::save($data))
		{
			return true;
		}

		return false;
	}
}
