<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\MVC\View\HtmlView;

/**
 * A xml view working with a RModelList.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  View
 * @since       1.0
 */
abstract class RedshopbViewXml extends HtmlView
{
	/**
	 * This is locale for UTF8 support in XML files.
	 *
	 * @var string
	 */
	public $localeEncoding = 'en_GB.UTF-8';

	/**
	 * Root element in XML document
	 *
	 * @var string
	 */
	public $rootElementName = '';

	/**
	 * Root element in XML document
	 *
	 * @var string
	 */
	public $childElementName = '';

	/**
	 * Get the columns for the xml file.
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
					'Empty columns not allowed for the xml view %s',
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
		header("Content-type: text/xml; charset=UTF-8");
		header("Content-Disposition: attachment; filename=\"$fileName.xml\";");
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
		$model->setState('streamOutput', 'xml');

		// Prepare the items
		$items = $model->getItems();
		$xml   = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><' . $this->rootElementName . ' />');

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
				$row = $xml->addChild($this->childElementName);

				foreach ($columns as $name => $title)
				{
					if (property_exists($item, $name))
					{
						$row->addChild($title, $preprocessExists ? $this->preprocess($name, $item->$name) : $item->$name);
					}
				}
			}
		}

		echo $xml->asXML();

		Factory::getApplication()->close();
	}

	/**
	 * Get the xml file name.
	 *
	 * @return  string  The file name.
	 */
	protected function getFileName()
	{
		$date     = Date::getInstance()->format('Y-m-d-h-i-s', true);
		$fileName = $this->getName() . '_' . $date;

		return $fileName;
	}
}
