<?php
/**
 * @package     Vanir.Plugin
 * @subpackage  Logman.Vanir
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

/**
 * Vanir LOGman plugin
 *
 * @package     Vanir.Plugin
 * @subpackage  Logman.Vanir
 * @since       1.12.65
 */
class PlgLogmanVanir extends ComLogmanPluginJoomla
{
	/**
	 * @var boolean
	 */
	protected $doExclude;

	/**
	 * Array of excluded entity names and substrings
	 *
	 * @var array
	 */
	protected $excludedEntities = array(
		'acl',
		'rule',
		'role',
		'cart',
		'order',
		'xref',
		'user_multi_company',
		'product_price'
	);

	/**
	 * Check if to exclude this entity
	 *
	 * @param   string  $entityName Entity's name
	 *
	 * @return  void
	 */
	protected function checkExclude($entityName)
	{
		$this->doExclude = false;

		foreach ($this->excludedEntities as $excludedEntity)
		{
			if (strpos($entityName, $excludedEntity) > 0)
			{
				$this->doExclude = true;

				break;
			}
		}
	}

	/**
	 * After b2b data save event handler.
	 *
	 * @param   Table    $data         B2B data.
	 * @param   boolean  $updateNulls  Update null values.
	 *
	 * @return  void
	 */
	public function onAfterStoreRedshopb($data, $updateNulls = false)
	{
		$verb = $data->get('_isNew') ? 'add' : 'edit';

		$this->doLog($data, $verb, __FUNCTION__);
	}

	/**
	 * After data publish or unpublish event handler.
	 *
	 * @param   Table    $data   B2B data.
	 *
	 * @return  void
	 */
	public function onAfterPublishRedshopb($data)
	{
		$this->doLog($data, 'edit', __FUNCTION__);
	}

	/**
	 * Before b2b data save event handler.
	 *
	 * @param   Table    $data         B2B data.
	 * @param   boolean  $updateNulls  Update null values.
	 *
	 * @return  void
	 */
	public function onBeforeStoreRedshopb($data, $updateNulls = false)
	{
		$data->set('_isNew', !$data->hasPrimaryKey());
	}

	/**
	 * Get log object
	 *
	 * @param   Table  $data  Data
	 *
	 * @return array
	 */
	protected function getLogObject($data)
	{
		$id = $data->get('id');

		$object = array(
			'package' => 'redshopb',
			'type'    => 'Vanir ' . $data->get('context'),
			'id'      => $id
		);

		if ($data->get('name'))
		{
			$object['name'] = '"' . $data->get('name') . '"';
		}
		else
		{
			$object['name'] = '-';
		}

		$object['name'] .= ' (ID: ' . $id . ')';

		return $object;
	}

	/**
	 * After b2b data delete event handler.
	 *
	 * @param   Table   $data  B2B data.
	 * @param   int     $id    Deleted id.
	 *
	 * @return  void
	 */
	public function onAfterDeleteRedshopb($data, $id)
	{
		$this->doLog($data, 'delete', __FUNCTION__);
	}

	/**
	 * Calling Logman to do the log.
	 *
	 * @param   JTable  $data    B2B data.
	 * @param   string  $verb    CRUD action made.
	 * @param   string  $event   Caller function name.
	 *
	 * @return  void
	 */
	protected function doLog($data, $verb, $event)
	{
		$this->checkExclude($data->get('_tableName'));

		if (!$this->doExclude)
		{
			$object = $this->getLogObject($data);

			$this->log(
				array(
					'object' => $object,
					'verb'   => $verb,
					'event'  => $event,
					'data'   => $data
				)
			);
		}
	}
}
