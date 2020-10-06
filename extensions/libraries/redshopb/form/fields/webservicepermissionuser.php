<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Factory;

FormHelper::loadFieldClass('rlist');
FormHelper::loadFieldClass('checkboxes');

/**
 * Webservice permission user Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldWebservicepermissionuser extends JFormFieldCheckboxes
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Webservicepermissionuser';

	/**
	 * A static cache.
	 *
	 * @var  array
	 */
	protected $cache = array();

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		if (empty($this->cache))
		{
			$db = Factory::getDbo();

			$query = $db->getQuery(true)
				->select('wp.id as value')
				->select('wp.name as text')
				->select($db->q('') . ' as checked')
				->from($db->qn('#__redshopb_webservice_permission', 'wp'))

				->order('wp.name')
				->group('wp.id');

			if (isset($this->element['scope']))
			{
				$query->where('wp.scope = ' . $db->q((string) $this->element['scope']));
			}

			$db->setQuery($query);

			$result = $db->loadObjectList();

			if (is_array($result))
			{
				$this->cache = $result;
			}
		}

		return $this->cache;
	}
}
