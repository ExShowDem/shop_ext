<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

RLoader::import('models.payment_configurations', JPATH_ADMINISTRATOR . '/components/com_redcore');

/**
 * Payment Configuration Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelPayment_Configurations extends RedcoreModelPayment_Configurations
{
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
				'extension_name', 'pc.extension_name',
				'owner_name', 'pc.owner_name',
				'payment_name', 'pc.payment_name',
				'state',
				'id'
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
	public function populateState($ordering = null, $direction = null)
	{
		$ordering  = is_null($ordering) ? 'element' : $ordering;
		$direction = is_null($direction) ? 'ASC' : $direction;

		parent::populateState($ordering, $direction);
	}
}
