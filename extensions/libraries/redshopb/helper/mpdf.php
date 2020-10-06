<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Helpers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Mpdf\Mpdf;
use Mpdf\Config\FontVariables;
use Mpdf\Config\ConfigVariables;

/**
 * mPDF helper.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Helpers
 * @since       1.0
 */
class RedshopbHelperMpdf
{
	/**
	 * Returns a mPDF object, with inits done
	 *
	 * @param   string  $customHeader  Use a custom header added to the default one
	 * @param   string  $customFooter  Use a custom footer added to the default one
	 * @param   array   $options       Use to set other specific PDF options
	 *
	 * @return Mpdf
	 * @since 1.0
	 */
	public static function getInstance($customHeader = '', $customFooter = '', $options = array())
	{
		$config      = RedshopbApp::getConfig();
		$imageHeader = $config->getImageHeader();
		$imageFooter = $config->getImageFooter();

		$defaultOptions = [
			'topMargin' => 15,
			'bottomMargin' => 15,
			'headerMargin' => 0,
			'footerMargin' => 0,
			'leftMargin' => 15,
			'rightMargin' => 15,
			'format' => $config->getString('default_pdf_orientation', 'A4'),
			'orientation' => 'P',
			'defaultFontSize' => 10,
			'defaultFont' => '',
			'showFooterImage' => true,
			'showHeaderImage' => true,
			'mode' => 'utf-8'
		];

		$text = $config->getString('pdf_options');

		if (!empty($text))
		{
			// Remove comments
			$text = preg_replace("/\/\*[\s\S]*?\*\/|([^\\:]|^)\/\/.*$/m", "$1", $text);
			$text = str_replace(["\n", "\r", " ", "\t", "\",}"], ["", "", "", "", "\"}"], $text);

			$pdfConfigOptions = json_decode($text, true);

			if (!empty($pdfConfigOptions))
			{
				if (array_key_exists('fontDir', $pdfConfigOptions))
				{
					$defaultConfig = (new ConfigVariables)->getDefaults();
					$fontDirs      = $defaultConfig['fontDir'];

					foreach ((array) $pdfConfigOptions['fontDir'] as $onePath)
					{
						$fontDirs[] = JPATH_ROOT . '/' . trim($onePath, ' /\\') . '/';
					}

					$defaultOptions['fontDir'] = $fontDirs;
					unset($pdfConfigOptions['fontDir']);
				}

				if (array_key_exists('fontdata', $pdfConfigOptions))
				{
					$defaultFontConfig = (new FontVariables)->getDefaults();
					$fontData          = $defaultFontConfig['fontdata'];
					$fontData          = array_replace($fontData, (array) $pdfConfigOptions['fontdata']);

					$defaultOptions['fontdata'] = $fontData;
					unset($pdfConfigOptions['fontdata']);
				}

				// Everything else can be just merged as is
				if (!empty($pdfConfigOptions))
				{
					$defaultOptions = array_replace($defaultOptions, $pdfConfigOptions);
				}
			}
		}

		$defaultOptions = array_replace($defaultOptions, array_filter($options));

		$explodedFormat = explode('|', $defaultOptions['format']);

		// If found width and height then use values inside an array
		if (count($explodedFormat) == 2)
		{
			$defaultOptions['format'] = $explodedFormat;
		}

		if ($defaultOptions['showHeaderImage'] && $imageHeader)
		{
			list($width, $height, $type, $attr) = getimagesize(JPATH_SITE . '/' . $imageHeader);
			$defaultOptions['headerMargin']     = 10;
			$defaultOptions['topMargin']        = 10 + $height;
		}

		if ($defaultOptions['showFooterImage'] && $imageFooter)
		{
			list($width, $height, $type, $attr) = getimagesize(JPATH_SITE . '/' . $imageFooter);
			$defaultOptions['footerMargin']     = 10;
			$defaultOptions['bottomMargin']     = 10 + $height;
		}

		foreach ($defaultOptions as $key => $value)
		{
			if (empty($value))
			{
				continue;
			}

			switch ($key)
			{
				case 'defaultFontSize':
					$defaultOptions['default_font_size'] = $value;
					break;
				case 'defaultFont':
					$defaultOptions['default_font'] = $value;
					break;
				case 'leftMargin':
					$defaultOptions['margin_left'] = $value;
					break;
				case 'rightMargin':
					$defaultOptions['margin_right'] = $value;
					break;
				case 'topMargin':
					$defaultOptions['margin_top'] = $value / Mpdf::SCALE;
					break;
				case 'bottomMargin':
					$defaultOptions['margin_bottom'] = $value / Mpdf::SCALE;
					break;
				case 'headerMargin':
					$defaultOptions['margin_header'] = $value / Mpdf::SCALE;
					break;
				case 'footerMargin':
					$defaultOptions['margin_footer'] = $value / Mpdf::SCALE;
					break;
			}
		}

		// Start pdf code
		$pdfObj = new Mpdf($defaultOptions);

		$pdfObj->charset_in = 'utf-8';
		$pdfObj->SetCreator(Text::_('COM_REDSHOPB_PDF_CREATOR'));
		$pdfObj->SetAuthor(Text::_('COM_REDSHOPB_PDF_CREATOR'));
		$pdfObj->keep_table_proportions = true;

		if ($defaultOptions['showHeaderImage'] && $imageHeader != '')
		{
			$pdfObj->SetHTMLHeader('<div class="pdfheader"><img src="' . Uri::root() . $imageHeader . '" /></div>' . $customHeader);
		}

		if ($defaultOptions['showFooterImage'] && $imageFooter != '')
		{
			$pdfObj->SetHTMLFooter('<div class="pdffooter"><img src="' . Uri::root() . $imageFooter . '" /></div>' . $customFooter);
		}

		return $pdfObj;
	}
}
