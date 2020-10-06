<?php
/**
 * @package     Plugin.Vanir_Search
 * @subpackage  SOLR
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Layout\LayoutHelper;

/**
 * [JFormFieldVanirSearchSolrSyncButton description]
 *
 * @since __VERSION__
 */
class JFormFieldVanirSearchSolrSyncButton extends FormField
{
	/**
	 * @var   string
	 */
	public $type = 'VanirSearchSolrSyncButton';

	/**
	 * [getInput description]
	 *
	 * @return   [type]
	 */
	protected function getInput()
	{
		HTMLHelper::script(JPATH_SITE . '/media/com_redshopb/js/redshopb.js');
		RHelperAsset::load('solr.js', 'plg_vanir_search_solr');

		$data = array();

		// Convert data to array
		foreach ($this as $key => $value)
		{
			$data[$key] = $value;
		}

		return LayoutHelper::render('vanirsearchsyncbutton', $data, JPATH_SITE . '/plugins/vanir_search/solr/layouts');
	}
}
