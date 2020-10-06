<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
/**
 * @var array $displayData
 * @var object $address
 */

extract($displayData);

if (property_exists($address, 'company'))
{
	echo RedshopbLayoutHelper::render(
		'myprofile.address.one_field',
		array(
			'title' => Text::_('COM_REDSHOPB_MYPROFILE_COMPANY_NAME'),
			'value' => $address->company
		)
	);
}

if (property_exists($address, 'userNumber'))
{
	echo RedshopbLayoutHelper::render(
		'myprofile.address.one_field',
		array(
			'title' => Text::_('COM_REDSHOPB_MYPROFILE_USER_NUMBER'),
			'value' => $address->userNumber
		)
	);
}

if (property_exists($address, 'vatNumber') && !empty($address->vatNumber))
{
	echo RedshopbLayoutHelper::render(
		'myprofile.address.one_field',
		array(
			'title' => Text::_('COM_REDSHOPB_MYPROFILE_COMPANY_NUMBER'),
			'value' => $address->vatNumber
		)
	);
}

if (!empty($address->name) || !empty($address->name2))
{
	$name = array($address->name, $address->name2);
	echo RedshopbLayoutHelper::render(
		'myprofile.address.one_field',
		array(
			'title' => Text::_('COM_REDSHOPB_MYPROFILE_NAME'),
			'value' => implode(' ', $name)
		)
	);
}

if (property_exists($address, 'userName'))
{
	echo RedshopbLayoutHelper::render(
		'myprofile.address.one_field',
		array(
			'title' => Text::_('COM_REDSHOPB_MYPROFILE_USERNAME'),
			'value' => $address->userName
		)
	);
}

if (!empty($address->address) || !empty($address->address2))
{
	$addressArray = array($address->address, $address->address2);
	echo RedshopbLayoutHelper::render(
		'myprofile.address.one_field',
		array(
			'title' => Text::_('COM_REDSHOPB_MYPROFILE_ADDRESS'),
			'value' => implode('<br />', $addressArray)
		)
	);
}

if (!empty($address->zip))
{
	echo RedshopbLayoutHelper::render(
		'myprofile.address.one_field',
		array(
			'title' => Text::_('COM_REDSHOPB_MYPROFILE_POSTNUMBER'),
			'value' => $address->zip
		)
	);
}

if (!empty($address->city))
{
	echo RedshopbLayoutHelper::render(
		'myprofile.address.one_field',
		array(
			'title' => Text::_('COM_REDSHOPB_MYPROFILE_BY'),
			'value' => $address->city
		)
	);
}

if (!empty($address->country))
{
	echo RedshopbLayoutHelper::render(
		'myprofile.address.one_field',
		array(
			'title' => Text::_('COM_REDSHOPB_MYPROFILE_LAND'),
			'value' => Text::_($address->country)
		)
	);
}

if (!empty($address->phone))
{
	echo RedshopbLayoutHelper::render(
		'myprofile.address.one_field',
		array(
			'title' => Text::_('COM_REDSHOPB_MYPROFILE_ADDRESS_PHONE'),
			'value' => Text::_($address->phone)
		)
	);
}
elseif (property_exists($address, 'userPhone'))
{
	echo RedshopbLayoutHelper::render(
		'myprofile.address.one_field',
		array(
			'title' => Text::_('COM_REDSHOPB_MYPROFILE_USER_PHONE'),
			'value' => $address->userPhone
		)
	);
}

if (property_exists($address, 'userEmail'))
{
	echo RedshopbLayoutHelper::render(
		'myprofile.address.one_field',
		array(
			'title' => Text::_('COM_REDSHOPB_MYPROFILE_EMAIL'),
			'value' => '<a href="mailto:' . $address->userEmail . '">' . $address->userEmail . '</a>'
		)
	);
}
