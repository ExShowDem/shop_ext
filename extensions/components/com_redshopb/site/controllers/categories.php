<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Categories Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerCategories extends RedshopbControllerAdmin
{
	/**
	 * Rebuild the nested set tree.
	 *
	 * @return  boolean  False on failure or error, true on success.
	 *
	 * @since   1.9.14
	 */
	public function rebuild()
	{
		$this->setRedirect($this->getRedirectToListRoute());

		if (!RedshopbHelperUser::isRoot())
		{
			return false;
		}

		$model = $this->getModel();

		if ($model->rebuild())
		{
			// Reorder succeeded.
			$this->setMessage(Text::_('COM_REDSHOPB_REBUILD_SUCCESS'));

			return true;
		}
		else
		{
			// Rebuild failed.
			$this->setMessage(Text::sprintf('COM_REDSHOPB_REBUILD_FAILED'), 'error');

			return false;
		}
	}

	/**
	 * Generates data for creating CSV files
	 *
	 * @param   JApplication     $app       The Joomla application
	 * @param   string           $type      Name of the currect view, used to get the associating model
	 * @param   RedshopbViewCsv  $view      CSV View
	 * @param   array            $viewData  Protected view data
	 *
	 * @return  array
	 */
	protected function getCsvData($app, $type, $view, $viewData)
	{
		$csvLines = array();

		// Get the columns
		$columns = $viewData->get('columns');

		/** @var RModelList $model */
		$model = $this->getModel($type);

		// For additional filtering and formating if needed
		$model->setState('streamOutput', 'csv');

		$data = json_decode($app->input->post->getString('result', '[]'));

		// Prepare the items
		$items       = !empty($data)
			? $model->getItemsCsv(substr($type, 0, 1), $data)
			: $model->getItems();
		$csvLines[0] = $columns;
		$wsRefs      = array();
		$inc         = 1;

		$this->csvMapWS($csvLines, $wsRefs);

		// Check if the preprocessing method exists
		$preprocessExists = method_exists($view, 'preprocess');
		$syncHelper       = new RedshopbHelperSync;

		foreach ($items as $item)
		{
			$csvLines[$inc] = array();

			foreach ($columns as $name => $title)
			{
				if (property_exists($item, $name))
				{
					$csvLines[$inc][$name] = $preprocessExists ? $view->preprocess($name, $item->$name) : $item->$name;
				}
				else
				{
					$csvLines[$inc][$name] = '';
				}
			}

			foreach ($wsRefs as $wsRef)
			{
				$id                     = $syncHelper->findSyncedLocalId($wsRef, $item->id);
				$csvLines[$inc][$wsRef] = !is_null($id) ? $id : '';

				// Parent ID
				$id                                                                  = $syncHelper->findSyncedLocalId($wsRef, $item->parent_id);
				$csvLines[$inc][Text::_('COM_REDSHOPB_PARENT_LABEL') . ' ' . $wsRef] = !is_null($id) ? $id : '';
			}

			$inc++;
		}

		return $csvLines;
	}

	/**
	 * Map webservice data
	 *
	 * @param   array  $csvLines  CSV columns
	 * @param   array  $wsRefs    Webservice references
	 *
	 * @return  void
	 */
	private function csvMapWS(&$csvLines, &$wsRefs)
	{
		$table = RedshopbTable::getInstance('Category', 'RedshopbTable');
		$wsMap = $table->get('wsSyncMapPK');

		// Merge all reference keys
		if (count($wsMap))
		{
			foreach ($wsMap as $key => $wsSyncMapFieldsRefs)
			{
				if (in_array($key, array('pim', 'erp', 'b2b')))
				{
					$wsRefs = array_merge($wsRefs, $wsSyncMapFieldsRefs);
				}
			}
		}

		// Add reference columns
		foreach ($wsRefs as $wsRef)
		{
			$csvLines[0][$wsRef]                                              = $wsRef;
			$csvLines[0][Text::_('COM_REDSHOPB_PARENT_LABEL') . ' ' . $wsRef] = Text::_('COM_REDSHOPB_PARENT_LABEL') . ' ' . $wsRef;
		}
	}
}
