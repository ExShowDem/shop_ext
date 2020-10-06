<?php
/**
 * @package    Redshopb.sh404SEF
 *
 * @copyright  Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

// @codingStandardsIgnoreFile

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

// standard plugin initialize function - don't change
global $sh_LANG, $sefConfig;

$app        = Factory::getApplication();
$document   = Factory::getDocument();
$shPageInfo = Sh404sefFactory::getPageInfo();
$sefConfig  = Sh404sefFactory::getConfig();

if (isset($limitstart))
{
	shRemoveFromGETVarsList('limitstart');
}

$view   = $app->input->getCmd('view', null);
$layout = $app->input->getCmd('layout', null);
$id     = $app->input->getInt('id', null);
$task   = $app->input->getString('task', null);
//-------------------------------------------------------------

global $shCustomTitleTag, $shCustomDescriptionTag, $shCustomKeywordsTag, $shCustomLangTag, $shCustomRobotsTag, $shCanonicalTag;

// special case for 404
if (!empty($shPageInfo->httpStatus) && $shPageInfo->httpStatus == 404)
{
	$shCustomRobotsTag = 'noindex, follow';
	return;
}

if ($view == 'shop')
{
	switch ($layout)
	{
		case 'category':
		case 'product':
		case 'manufacturer':
			if ($id)
			{
				$config                 = RedshopbHelperSeo::getMetaSettings($layout, $id);
				$shCustomTitleTag       = RedshopbHelperSeo::replaceTags($config['titles'], $layout, $id);
				$shCustomDescriptionTag = RedshopbHelperSeo::replaceTags($config['description'], $layout, $id);
				$shCustomKeywordsTag    = RedshopbHelperSeo::replaceTags($config['keywords'], $layout, $id);
			}

			break;
		default:
			break;
	}
}
?>
