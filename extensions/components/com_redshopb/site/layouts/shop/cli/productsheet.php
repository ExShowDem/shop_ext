<?php
/**
 * @package     Vanir.Cli.Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * $displayData contains:
 *
 * ->product               - RedshopbEntityProduct (Please before using this see if you can't find the data below)
 * ->attributes            - Null|Array with RedshopbEntityAttribute
 * ->description           - Null|Array with RedshopbEntityDescription
 * ->manufacture           - Null|RedshopbEntityManufacture
 * ->category              - Array with RedshopbEntityCategory
 * ->images                - Array with RedshopbEntityMedia
 * ->customFields['full']  - Array with customFields.
 * ->customFields['value'] - Array with customField values where the key is the field name.
 * ->company               - stdClass The company
 *
 * For easy use of the customfields do:
 *
 * foreach ($displayData->customFields['value'] AS $name => $value)
 * {
 *   // $name Will contain the name of the custom field.
 *   // $value Will contain the value of the custom field.
 * }
 *
 * in case you need more data you can use:
 *
 * foreach ($displayData->customFields['full'] AS $fullCustomField)
 * {
 *  // $fullCustomField will contain all data for the custom field
 *
 * }
 *
 */
use Joomla\CMS\Language\Text;

// Set all data from DisplayData.
$product     = $displayData->product;
$manufacture = $displayData->manufacture;

$attributes    = $displayData->attributes;
$attributeKeys = array_keys($attributes);

$descriptions    = $displayData->descriptions;
$descriptionKeys = array_keys($descriptions);

$categories   = $displayData->category;
$categoryKeys = array_keys($categories);

$images    = $displayData->images;
$imageKeys = array_keys($images);

$customFields    = $displayData->customFields;
$customFieldKeys = array_keys($customFields);

// Set CustomFields as Advanced / simple.
$customFieldsAdvanced = $customFields['full'];
$customFieldsSimple   = $customFields['simple'];

$company = $displayData->company;

// Set all sections to be empty
$pageHeader          = '';
$imageSection        = '';
$subSeection         = '';
$descSection         = '';
$attributeSection    = '';
$customFieldsSection = '';
$pages               = array();
$styles              = array();

// Page one:

// Sets the logo based on the Company Image.
$img  = RedshopbHelperThumbnail::originalToResize($company->image, 150, 50, 100, 0, 'companies', false, '', true);
$logo = '<span class="psNoLogo">' . $company->name . '</span>';

if (!empty($img))
{
	$logo = '<img src="' . $img . '" alt="logo" width="30mm" height="15mm">';
}

// The header of The product sheet
$pageHeader
	= '
	<table class="psPageHeader psBGRed">
		<tr>
			<td class="psHeaderLeft">
				<h6 class="psFont psFCWhite">' . $product->get('sku') . '</h6>
				<h3 ckass="psFont psFCdeep">' . $product->get('name') . '</h3>
			</td>
			<td class="psHeaderRight">
				' . $logo . '
			</td>
		</tr>
	</table>
	';

// The Image section of the product sheet
if (!empty($images))
{
	$mainImg = RedshopbHelperThumbnail::originalToResize($images[$imageKeys[0]]->get('name'), '1000', '700', 100, 0,
		'products', false, $images[$imageKeys[0]]->get('remote_path'), true
	);

	unset($imageKeys[0]);

	$count         = (count($imageKeys) > 4) ? 4 : count($imageKeys);
	$subHTMLImages = array();

	for ($i = 1; $i <= $count; $i++)
	{
		$image    = $images[$imageKeys[$i]];
		$imageUrl = RedshopbHelperThumbnail::originalToResize($image->get('name'), '150', '100', 100, 0,
			'products', false, $image->get('remote_path'), true
		);

		$subHTMLImages[] = '<td><img src="' . $imageUrl . '" alt="' . $image->get('title') . '" width="40mm"></td>';
	}

	$imageSection
		= '
			<table class="psImages psMT-10">
				<tr>
					<td colspan="' . $count . ' " class="psMainImg">
						<img src="' . $mainImg . '" alt="Main product image"  width="150mm" height="75mm">
					</td>
				</tr>
				<tr>
					' . implode(' ', $subHTMLImages) . '
				</tr>
			</table>
		';
}

// Description
if (!empty($descriptions))
{
	$desc = $descriptions[$descriptionKeys[0]]->description;

	$descSection .=
		'
		<div class="psSection">
			<h3>' . Text::_('COM_REDSHOPB_DESCRIPTION') . '</h3>
			' . $desc . '
		</div>
		';
}

// Page 1 building
$pageWrapper  = $pageHeader;
$pageWrapper .= '<div class="psPageWrapper">';
$pageWrapper .= $imageSection;
$pageWrapper .= '<div class="psSpacer"></div>';
$pageWrapper .= $descSection;
$pageWrapper .= '<div class="psSpacer"></div>';
$pageWrapper .= '</div>';
$pages[]      = $pageWrapper;

// End of page One.
// Page two:

// Attributes
if (!empty($attributes))
{
	$attributeSection .=
		'
		<div class="psSection">
			<h3>' . Text::_('COM_REDSHOPB_PRODUCT_LABEL') . ' ' . Text::_('COM_REDSHOPB_ATTRIBUTE_VALUES_LBL') . '</h3>
		';

	foreach ($attributes as  $attribute)
	{
		$values   = $attribute->get('values');
		$countVal = count($values);

		$attributeSection .=
			'
			<table class="psTable">
				<tr>
					<th colspan="' . $countVal . '">
						' . $attribute->get('name') . '
					</th>
				</tr>
			';

		$td = '';

		for ($i = 0; $i < $countVal; $i++)
		{
			$td .= '<td>' . $values[$i]->value . '</td>';

			if ((($i + $i) + 2) % 8 === 0 || $i == ($countVal - 1))
			{
				$attributeSection .= '<tr>' . $td . '</tr>';

				$td = '';
			}
		}

		// <table class="psTable"> ending
		$attributeSection .= '</table>';
	}

	// <div class="psSection"> Ending
	$attributeSection .= '</div>';
}

// Custom Fields
if (!empty($customFieldsSimple))
{
	$customFieldsSection .=
		'
		<div class="psSection">
			<h3>' . Text::_('COM_REDSHOPB_FIELDS_LIST_TITLE') . '</h3>

			<table class="psTable">
		';

	foreach ($customFieldsSimple as $key => $value)
	{
		$customFieldsSection .=
			'
			<tr>
				<td>' . $key . '</td>
				<td>' . $value . '</td>
			</tr>
			';
	}

	// <table class="psTable"> Ends
	// <div class="psSection"> Ends
	$customFieldsSection .=
		'
			</table>
		</div>
		';
}

$attrCustSection = '
	<table class="psw-100 psMT-10 psValignTop">
		<tr class="psw-100">
			<td class="psw-50"> ' . $attributeSection . '</td>
			<td class="psw-50"> ' . $customFieldsSection . '</td>
		</tr>
	</table>
	';

// Page 2 building
$pageWrapper  = $pageHeader;
$pageWrapper .= '<div class="psPageWrapper">';
$pageWrapper .= $attrCustSection;
$pageWrapper .= '<div class="psSpacer"></div>';
$pageWrapper .= '</div>';
$pages[]      = $pageWrapper;


// Settings
$styles[]            = JPATH_BASE . '/media/com_redshopb/css/productsheet.css';
$settings            = new stdClass;
$settings->marginAll = 0;

$layoutData = (object) array('styles' => $styles, 'pages' => $pages, 'settings' => $settings);

echo json_encode($layoutData);
