<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;

/**
 * A JSON view working with a RModelList.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  View
 * @since       1.0
 */
abstract class RedshopbViewJson extends HtmlView
{
	/**
	 * This is locale for UTF8 support in JSON files.
	 *
	 * @var string
	 */
	public $localeEncoding = 'en_GB.UTF-8';

	/**
	 * Root element in JSON document
	 *
	 * @var string
	 */
	public $rootElementName = '';

	/**
	 * Root element in JSON document
	 *
	 * @var string
	 */
	public $childElementName = '';

	/**
	 * Get the columns for the JSON file.
	 *
	 * @return  array  An associative array of column names as key and the title as value.
	 */
	abstract protected function getColumns();

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse.
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException
	 */
	public function display($tpl = null)
	{
		// Get the columns
		$columns = $this->getColumns();

		if (empty($columns))
		{
			throw new RuntimeException(
				sprintf(
					'Empty columns not allowed for the JSON view %s',
					get_class($this)
				)
			);
		}

		// Get the file name
		$fileName = $this->getFileName();
		setlocale(LC_ALL, $this->localeEncoding);

		// Send the headers
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private", false);
		header("Content-type: application/json; charset=UTF-8");
		header("Content-Disposition: attachment; filename=\"$fileName.json\";");
		header("Content-Transfer-Encoding: binary");

		/** @var RModelList $model */
		$model = $this->getModel();

		if (empty($this->rootElementName))
		{
			$this->rootElementName = RInflector::pluralize($model->getName());
			$this->rootElementName = RInflector::classify(str_replace('_', ' ', $this->rootElementName));
		}

		if (empty($this->childElementName))
		{
			$this->childElementName = RInflector::singularize($model->getName());
			$this->childElementName = RInflector::classify(str_replace('_', ' ', $this->childElementName));
		}

		// For additional filtering and formating if needed
		$model->setState('streamOutput', 'json');

		// Prepare the items
		$items = $model->getItems();

		$jsonLines = array();
		$i = 0;

		// Check if the preprocessing method exists
		$preprocessExists = method_exists($this, 'preprocess');

		// Classify titles
		foreach ($columns as $name => $title)
		{
			$columns[$name] = RInflector::classify($title);
		}

		if ($items !== false)
		{
			foreach ($items as $item)
			{
				$jsonLines[$i] = array();

				foreach ($columns as $name => $title)
				{
					if (property_exists($item, $name))
					{
						$jsonLines[$i][$title] = $preprocessExists ? $this->preprocess($name, $item->$name) : $item->$name;
					}
				}

				$i++;
			}
		}

		echo json_encode($jsonLines);

		Factory::getApplication()->close();
	}

	/**
	 * Get the JSON file name.
	 *
	 * @return  string  The file name.
	 */
	protected function getFileName()
	{
		$date = Date::getInstance()->format('Y-m-d-h-i-s', true);
		$fileName = $this->getName() . '_' . $date;

		return $fileName;
	}
}
