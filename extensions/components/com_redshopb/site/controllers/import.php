<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Import Controller
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerImport extends RedshopbControllerForm
{
	/**
	 * Import data
	 *
	 * @return void
	 * @throws InvalidArgumentException
	 */
	public function importCSV()
	{
		$app            = Factory::getApplication();
		$modelName      = $app->input->get('model', null, 'string');
		$returnUrl      = $this->input->get('return', null, 'string');
		$modelNameUpper = strtoupper($modelName);

		try
		{
			$app->setHeader('Content-Encoding', 'UTF-8', true);

			$model = BaseDatabaseModel::getInstance(ucwords($modelName), 'RedshopbModel');

			$files = $app->input->files->get('rform');
			$csv   = $files['csv'];

			if (!$this->isCSVExtension($csv))
			{
				throw new InvalidArgumentException(Text::_('COM_REDSHOPB_IMPORT_ERROR_INVALID_FILE_EXTENSION'));
			}

			$header = null;
			$data   = array();
			$handle = fopen($csv['tmp_name'], 'r');
			$isCart = $modelName === 'carts' ? true : false;

			if ($handle !== false)
			{
				setlocale(LC_ALL, 'en_GB.UTF-8');
				$firstRow = true;

				while (($row = fgetcsv($handle, 10000, ';')) !== false) // @codingStandardsIgnoreLine
				{
					if ($firstRow)
					{
						$firstRow = false;

						// Remove BOM from first row
						if (substr($row[0], 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf))
						{
							$row[0] = substr($row[0], 3);
						}
					}

					if (!$header)
					{
						$header = array_map('mb_strtolower', $row);

						if ($header[0] !== 'crud' && !$isCart)
						{
							throw new InvalidArgumentException(Text::_('COM_REDSHOPB_IMPORT_ERROR_INVALID_CSV_HEADER'));
						}

						// If importing cart, then no need for CRUD field
						if ($isCart)
						{
							// If importing cart, then need to adjust for the absent CRUD title
							array_unshift($header, "crud");
						}
					}
					else
					{
						if (is_array($row) && (count($row) > 1))
						{
							// If importing cart, then need to adjust for the absent CRUD fields
							if ($isCart)
							{
								array_unshift($row, "CREATE");
							}

							$data[] = array_combine($header, array_map('trim', $row));
						}
					}
				}

				fclose($handle);
			}

			if (is_callable(array($model, 'import')) && isset($data))
			{
				$result = $model->import($data);

				if (!empty($result))
				{
					if (!empty($result['success']['CREATE']))
					{
						$app->enqueueMessage(
							Text::sprintf('COM_REDSHOPB_' . $modelNameUpper . '_SUCCESSFULLY_CREATED', count($result['success']['CREATE'])),
							'success'
						);
					}

					if (!empty($result['success']['UPDATE']))
					{
						$app->enqueueMessage(
							Text::sprintf('COM_REDSHOPB_' . $modelNameUpper . '_SUCCESSFULLY_UPDATED', count($result['success']['UPDATE'])),
							'success'
						);
					}

					if (!empty($result['success']['DELETE']))
					{
						$app->enqueueMessage(
							Text::sprintf('COM_REDSHOPB_' . $modelNameUpper . '_SUCCESSFULLY_DELETED', count($result['success']['DELETE'])),
							'success'
						);
					}

					if (!empty($result['error']))
					{
						$errors = implode('<br /><br />', $result['error']);
						$app->enqueueMessage($errors, 'error');
					}
				}
			}
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		if ($returnUrl)
		{
			$returnUrl = base64_decode($returnUrl);
		}
		else
		{
			$returnUrl = 'index.php?option=com_redshopb&view=' . $modelName;
		}

		$app->redirect(RedshopbRoute::_($returnUrl, false));
	}

	/**
	 * Method to check if the file extension is CSV
	 *
	 * @param   array  $file  of file information from the input
	 *
	 * @return boolean
	 */
	private function isCSVExtension($file)
	{
		// First check the extension to make sure it is .csv
		$parts     = explode('.', $file['name']);
		$extension = strtolower(array_pop($parts));

		return ($extension == 'csv');
	}
}
