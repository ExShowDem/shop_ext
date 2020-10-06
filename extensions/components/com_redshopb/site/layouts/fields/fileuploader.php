<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormField;

$data = (object) $displayData;
/** @var FormField $field */
$field = $data->field;

RHelperAsset::load('lib/jquery-fileupload/jquery.fileupload.css', 'com_redshopb');

// The jQuery UI widget factory, can be omitted if jQuery UI is already included
RHelperAsset::load('lib/jquery-fileupload/vendor/jquery.ui.widget.js', 'com_redshopb');

// The Iframe Transport is required for browsers without support for XHR file uploads
RHelperAsset::load('lib/jquery-fileupload/jquery.iframe-transport.js', 'com_redshopb');

// The basic File Upload plugin
RHelperAsset::load('lib/jquery-fileupload/jquery.fileupload.js', 'com_redshopb');

if (isset($field->element['loadFileProcessingLib']) && $field->element['loadFileProcessingLib'] == true)
{
	// The File Upload processing plugin
	RHelperAsset::load('lib/jquery-fileupload/jquery.fileupload-process.js', 'com_redshopb');
}

if (isset($field->element['loadPreviewResizeLib']) && $field->element['loadPreviewResizeLib'] == true)
{
	// The File Upload image preview & resize plugin
	RHelperAsset::load('lib/jquery-fileupload/jquery.fileupload-image.js', 'com_redshopb');
}

if (isset($field->element['loadAudioLib']) && $field->element['loadAudioLib'] == true)
{
	// The File Upload audio preview plugin
	RHelperAsset::load('lib/jquery-fileupload/jquery.fileupload-audio.js', 'com_redshopb');
}

if (isset($field->element['loadVideoLib']) && $field->element['loadVideoLib'] == true)
{
	// The File Upload video preview plugin
	RHelperAsset::load('lib/jquery-fileupload/jquery.fileupload-video.js', 'com_redshopb');
}

if (isset($field->element['loadValidationLib']) && $field->element['loadValidationLib'] == true)
{
	// The File Upload validation plugin
	RHelperAsset::load('lib/jquery-fileupload/jquery.fileupload-validate.js', 'com_redshopb');
}


?>
<span class="btn btn-success fileinput-button">
	<i class="glyphicon glyphicon-plus"></i>
	<span><?php echo Text::_('COM_REDSHOPB_MEDIA_IMAGE_SELECT_FILE'); ?></span>
	<input id="<?php echo $field->id; ?>" type="file" name="<?php echo $field->name; ?>[]">
</span>
<br />
<br />
<div class="progress progress-striped image-progress">
	<div class="bar bar-success" style="width: 0%"></div>
</div>

<div id="files" class="files"></div>
