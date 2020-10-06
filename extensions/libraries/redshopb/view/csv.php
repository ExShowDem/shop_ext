<?php
/**
 * @package     Vanir\Library\View\Csv
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;

/**
 * CSV view
 *
 * @since 1.13.0
 */
abstract class RedshopbViewCsv extends RViewCsv
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse.
	 *
	 * @return  null
	 *
	 * @throws  RuntimeException	@see parent::display()
	 */
	public function display($tpl = null)
	{
		$app  = Factory::getApplication();
		$data = $app->input->get->getString('data');

		if (empty($data))
		{
			return parent::display($tpl);
		}

		$filename = unserialize(base64_decode($data));
		$file     = JPATH_ROOT . "/tmp/{$filename}";

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private", false);
		header("Content-type: text/csv; charset=UTF-8");
		header("Content-Disposition: attachment; filename=\"{$filename}.csv\";");
		header("Content-Transfer-Encoding: binary");

		echo file_get_contents($file);

		unlink($file);

		$app->close();
	}

	/**
	 * Wrapper for accessing protected data outside the class
	 *
	 * @see getFilename()
	 * @see getColumns()
	 *
	 * @return   Registry
	 */
	public function getViewData()
	{
		$registry = new Registry;

		$registry->set('filename', $this->getFileName());
		$registry->set('columns', $this->getColumns());

		return $registry;
	}
}
