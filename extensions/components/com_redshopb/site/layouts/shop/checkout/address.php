<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

extract($displayData);

$html = array();

if ($address->name)
{
	$html[] = '<p>' . $address->name . '</p>';
}

if ($address->address)
{
	$html[] = '<p>' . $address->address . '</p>';
}

if ($address->address2)
{
	$html[] = '<p>' . $address->address2 . '</p>';
}

$location = '';

if ($address->zip)
{
	$location .= $address->zip;
}

if ($address->city)
{
	$location .= ', ' . $address->city;
}

if ($location)
{
	$html[] = '<p>' . $location . '</p>';
}

if ($address->country)
{
	$html[] = '<p>' . Text::_($address->country) . '</p>';
}

if (!count($html))
{
	$html[] = '-';
}

echo implode('', $html);
