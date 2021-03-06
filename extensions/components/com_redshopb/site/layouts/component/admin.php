<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */
defined('JPATH_REDCORE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

$data = $displayData;

$app   = Factory::getApplication();
$input = $app->input;

/**
 * Handle raw format
 */
$format = $input->getString('format');

if ('raw' === $format)
{
	/** @var RView $view */
	$view = $data['view'];

	if (!$view instanceof RViewBase)
	{
		throw new InvalidArgumentException(
			sprintf(
				'Invalid view %s specified for the component layout',
				get_class($view)
			)
		);
	}

	$toolbar = $view->getToolbar();

	// Get the view template.
	$tpl = $data['tpl'];

	// Get the view render.
	return $view->loadTemplate($tpl);
}

$templateComponent = 'component' === $input->get('tmpl');
$input->set('tmpl', 'component');
$input->set('redcore', true);

// Load bootstrap + fontawesome
HTMLHelper::_('vnrbootstrap.framework');

RHelperAsset::load('component.min.js', 'redcore');
RHelperAsset::load('component.js', 'com_redshopb');
RHelperAsset::load('component.min.css', 'redcore');

$user = Factory::getUser();

$comOption = $input->get('option', null);

// Load a custom CSS option for this component if exists
if ($comOption)
{
	RHelperAsset::load($comOption . '.css', $comOption);
}

// Do we have to display the sidebar ?
$displaySidebar = false;

if (isset($data['sidebar_display']))
{
	$displaySidebar = (bool) $data['sidebar_display'];
}

$sidebarLayout = '';

// The sidebar layout name.
if ($displaySidebar)
{
	if (!isset($data['sidebar_layout']))
	{
		throw new InvalidArgumentException('No sidebar layout specified in the component layout.');
	}

	$sidebarLayout = $data['sidebar_layout'];
}

$sidebarData = array();

if (isset($data['sidebar_data']))
{
	$sidebarData = $data['sidebar_data'];
}

// Do we have to display the topbar ?
$displayTopbar = false;

if (isset($data['topbar_display']))
{
	$displayTopbar = (bool) $data['topbar_display'];
}

$topbarLayout = '';

// The topbar layout name.
if ($displayTopbar)
{
	if (!isset($data['topbar_layout']))
	{
		throw new InvalidArgumentException('No topbar layout specified in the component layout.');
	}

	$topbarLayout = $data['topbar_layout'];
}

$topbarData = array();

if (isset($displayTopbar))
{
	$topbarData = $data;
}

// The view to render.
if (!isset($data['view']))
{
	throw new InvalidArgumentException('No view specified in the component layout.');
}

/** @var RView $view */
$view = $data['view'];

if (!$view instanceof RViewBase)
{
	throw new InvalidArgumentException(
		sprintf(
			'Invalid view %s specified for the component layout',
			get_class($view)
		)
	);
}

$toolbar = $view->getToolbar();

// Get the view template.
$tpl = $data['tpl'];

// Get the view render.
$result = $view->loadTemplate($tpl);

if ($result instanceof Exception)
{
	return $result;
}
?>
	<script type="text/javascript">
		jQuery(document).ready(function () {
			jQuery('.message-sys').append(jQuery('#system-message-container'));

			<?php if ($input->getBool('disable_topbar') || $input->getBool('hidemainmenu')) : ?>
			jQuery('.topbar').addClass('opacity-70');
			jQuery('.topbar button').prop('disabled', true);
			jQuery('.topbar a').attr('disabled', true).attr('href', '#').addClass('disabled');
			<?php endif; ?>

			<?php if ($input->getBool('disable_sidebar') || $input->getBool('hidemainmenu')) : ?>
			jQuery('.sidebar').addClass('opacity-70');
			jQuery('.sidebar button').prop('disabled', true);
			jQuery('.sidebar a').attr('disabled', true).attr('href', '#').addClass('disabled');
			<?php endif; ?>
		});
	</script>
<?php if ($view->getLayout() === 'modal') : ?>
	<div class="row redcore">
		<section id="component">
			<div class="row message-sys"></div>
			<div class="row">
				<?php echo $result ?>
			</div>
		</section>
	</div>
<?php elseif ($templateComponent) : ?>
	<div class="container-fluid redcore rsb-container">
		<div class="col-md-12 content rsb-content">
			<section id="component">
				<div class="row">
					<h1><?php echo $view->getTitle() ?></h1>
				</div>
				<div class="row message-sys"></div>
				<hr/>
				<div class="row">
					<?php echo $result ?>
				</div>
			</section>
		</div>
	</div>
	<?php
else : ?>
	<div class="redcore">
	<div class="container-fluid rsb-container">
		<?php if ($displayTopbar) : ?>
			<?php echo RedshopbLayoutHelper::render($topbarLayout, $topbarData) ?>
		<?php endif; ?>
		<div class="row">
			<?php if ($displaySidebar) : ?>
			<div class="col-md-2 sidebar rsb-sidebar">
				<?php echo RedshopbLayoutHelper::render($sidebarLayout, $sidebarData) ?>
			</div>
			<div class="col-md-10 content rsb-content">
			<?php else : ?>
				<div class="col-md-12 content rsb-content">
			<?php endif; ?>
					<section id="component">
						<div class="row">
							<h1><?php echo $view->getTitle() ?></h1>
						</div>
						<?php if ($toolbar instanceof RToolbar) : ?>
							<div class="row">
								<?php echo $toolbar->render() ?>
							</div>
						<?php endif; ?>
						<div class="row message-sys"></div>
						<div class="row">
							<?php echo $result ?>
						</div>
					</section>
				</div>
			</div>
		</div>
		<footer class="footer pagination-centered navbar navbar-fixed-bottom hidden-phone rsb-footer">
			Copyright 2018 Aesir. All rights reserved.
		</footer>
	</div>
<?php endif;
