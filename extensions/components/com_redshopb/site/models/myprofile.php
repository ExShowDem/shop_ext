<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
/**
 * Shop My Profile
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelMyProfile extends RModelList
{
	/**
	 * Get the zone form
	 *
	 * @param   array    $data      data
	 * @param   boolean  $loadData  load current data
	 *
	 * @return  Form/false  the Form object or false
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			$this->context,
			'myprofile',
			array('control' => 'jform', 'load_data' => false)
		);

		return $form;
	}

	/**
	 * Get User Billing Address Id
	 * Regular address = billing address => the one stored in the address_id of the user ($typeId = 2)
	 * Default delivery address = shipping address -> $typeId = 3
	 * Delivery address -> $typeId = 1
	 *
	 * @param   int  $rsbUserId  User id
	 * @param   int  $typeId     address type Id
	 *
	 * @return  integer  $addressId  User billing address id
	 */
	public function getAddressId($rsbUserId, $typeId)
	{
		$db = Factory::getDbo();

		if ($typeId == 2)
		{
			$query = $db->getQuery(true)
				->select('address_id')
				->from($db->qn('#__redshopb_user'))
				->where('id = ' . (int) $rsbUserId);

			$addressId = $db->setQuery($query, 0, 1)
				->loadResult();
		}
		else
		{
			$query = $db->getQuery(true)
				->select('id')
				->from($db->qn('#__redshopb_address'))
				->where('customer_id = ' . (int) $rsbUserId)
				->where('type = ' . (int) $typeId)
				->where('customer_type = ' . $db->q('employee'));

			$addressId = $db->setQuery($query, 0, 1)
				->loadResult();
		}

		return $addressId;
	}

	/**
	 * Change user password
	 *
	 * @param   array  $data  List of necessary data.
	 *
	 * @return  boolean       True on success. False otherwise.
	 */
	public function changeUserPassword($data)
	{
		$userId = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('user.id');

		$table = $this->getTable('User', 'redshopbTable');

		if ($table->load(array('joomla_user_id' => $userId)))
		{
			if (isset($data['password2']))
			{
				$table->set('password2', $data['password2']);
			}

			if (isset($data['password1']))
			{
				$table->set('password', $data['password1']);
			}

			if (!$table->store())
			{
				$this->setError($table->getError());

				return false;
			}
		}
		else
		{
			$this->setError(Text::_('COM_UREDSHOPB_MYPROFILE_USER_NOT_FOUND'));

			return false;
		}

		return true;
	}
}
