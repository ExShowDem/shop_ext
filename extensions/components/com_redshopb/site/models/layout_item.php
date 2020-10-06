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
use Joomla\CMS\Table\Table;
use Joomla\CMS\Log\Log;
/**
 * Layout item Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.13.0
 */
class RedshopbModelLayout_Item extends RedshopbModelAdmin
{
	/**
	 * Stock method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @since   1.13.0
	 */
	protected function populateState()
	{
		parent::populateState();
		$input      = Factory::getApplication()->input;
		$id         = $input->getString('id');
		$explodedId = explode('|', base64_decode($id));
		$this->setState('layoutId', $explodedId[0]);

		if (isset($explodedId[1]))
		{
			$this->setState('layoutFolder', $explodedId[1]);
		}
		else
		{
			$this->setState('layoutFolder', 'templates.' . Factory::getApplication()->getTemplate() . '.html.layouts.com_redshopb');
		}
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   1.13.0
	 */
	public function getItem($pk = null)
	{
		$item            = new stdClass;
		$item->content   = '';
		$item->editable  = false;
		$item->isNew     = false;
		$layoutFolder    = $this->getState('layoutFolder');
		$filePath        = JPATH_ROOT . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $layoutFolder)
			. DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $this->getState('layoutId')) . '.php';
		$defaultTemplate = Factory::getApplication()->getTemplate();

		if ($layoutFolder && JFile::exists($filePath))
		{
			$item->content = file_get_contents($filePath);

			if (stripos(
				str_replace(
					array('\\', '/'),
					DIRECTORY_SEPARATOR,
					$filePath
				),
				JPATH_THEMES . DIRECTORY_SEPARATOR . $defaultTemplate . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'layouts'
			) !== false)
			{
				$item->editable = true;
			}
		}
		else
		{
			$filePath = RedshopbLayoutFile::getInstance($this->getState('layoutId'))
				->getPath();

			if (JFile::exists($filePath))
			{
				$item->content  = file_get_contents($filePath);
				$item->editable = true;
				$item->isNew    = true;
			}
		}

		return $item;
	}

	/**
	 * Get the associated Table
	 *
	 * @param   string  $name    Table name
	 * @param   string  $prefix  Table prefix
	 * @param   array   $config  Configuration array
	 *
	 * @return  Table
	 *
	 * @since  1.13.0
	 */
	public function getTable($name = 'Template', $prefix = 'RedshopbTable', $config = array())
	{
		return parent::getTable($name, $prefix, $config);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   1.13.0
	 */
	public function save($data)
	{
		// Fixes bug in joomla with updating and not setting item id in the model state
		$this->getState();

		if ($this->canSave($data) && $this->getState('layoutFolder') && $this->getState('layoutId'))
		{
			$filePath = JPATH_ROOT . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $this->getState('layoutFolder'))
				. DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $this->getState('layoutId')) . '.php';
			$isNew    = true;

			if (JFile::exists($filePath))
			{
				$isNew = false;
			}

			if (!JFile::write($filePath, $data['content']))
			{
				$this->setError(Text::sprintf('Could not save the file %', $filePath));

				return false;
			}

			$this->setState($this->getName() . '.id', base64_encode($this->getState('layoutId') . '|' . $this->getState('layoutFolder')));
			$this->setState($this->getName() . '.new', $isNew);

			return true;
		}

		$msg = 'JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED';

		if (!empty($data['id']))
		{
			$msg = 'JLIB_APPLICATION_ERROR_EDIT_ITEM_NOT_PERMITTED';
		}

		$this->setError(Text::_($msg));

		return false;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  $pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   1.13.0
	 */
	public function delete(&$pks)
	{
		$pks = array($pks);

		foreach ($pks as $i => $pk)
		{
			if (!$pk)
			{
				continue;
			}

			$record     = new stdClass;
			$record->id = $pk;

			if ($this->canDelete($record))
			{
				$explodedId = explode('|', base64_decode($pk));

				$filePath = JPATH_ROOT . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $explodedId[1])
					. DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $explodedId[0]) . '.php';

				if (JFile::exists($filePath))
				{
					if (!JFile::delete($filePath))
					{
						$this->setError(Text::sprintf('Could not delete the file %', $filePath));

						return false;
					}
				}
			}
			else
			{
				// Prune items that you can't change.
				unset($pks[$i]);
				$error = $this->getError();

				if ($error)
				{
					Log::add($error, Log::WARNING, 'jerror');

					return false;
				}
				else
				{
					Log::add(Text::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), Log::WARNING, 'jerror');

					return false;
				}
			}
		}

		return true;
	}
}
