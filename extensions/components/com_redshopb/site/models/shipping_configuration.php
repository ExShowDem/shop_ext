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

/**
 * Shipping Configuration Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelShipping_Configuration extends RedshopbModelAdmin
{
	/**
	 * Name of the shipping plugin
	 *
	 * @var  string
	 */
	public $shippingName = '';

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

	/**
	 * Load item object
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   1.2
	 */
	public function getItem($pk = null)
	{
		$this->shippingName = $this->getState('shipping_name', '');

		if ($this->shippingName != '' || $this->getState('process_params', '0') == '1')
		{
			$item         = parent::getItem($pk);
			$item->folder = 'redshipping';

			if ($this->shippingName && (empty($item->shipping_name) || $item->shipping_name != $this->shippingName))
			{
				$db    = $this->getDbo();
				$query = $db->getQuery(true)
					->select('p.params as plugin_params, p.name as plugin_name, p.element, p.enabled, p.extension_id, p.folder')
					->select('CONCAT("plg_redshipping_", p.element) as plugin_path_name')
					->from($db->qn('#__extensions', 'p'))
					->where($db->qn('p.type') . '= ' . $db->q("plugin"))
					->where($db->qn('p.folder') . ' IN (' . $db->q("redshipping") . ', ' . $db->q("system") . ')')
					->where($db->qn('p.element') . '= ' . $db->q($this->shippingName));
				$db->setQuery($query);

				$defaultPlugin = $db->loadObject();

				if ($defaultPlugin)
				{
					$item->params        = $defaultPlugin->plugin_params;
					$item->shipping_name = $this->shippingName;
					$item->folder        = $defaultPlugin->folder;
				}
			}
			else
			{
				$this->shippingName = $item->shipping_name;

				if (isset($item->params['shipping_folder']))
				{
					$item->folder = $item->params['shipping_folder'];
				}

				$item->params = json_encode($item->params);
			}

			$item->element = $this->shippingName;

			return $item;
		}

		return parent::getItem($pk);
	}
}
