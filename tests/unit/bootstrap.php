<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

define('_JEXEC', 1);

// Fix magic quotes.
@ini_set('magic_quotes_runtime', 0);

// Maximise error reporting.
error_reporting(E_ALL & ~E_STRICT);
ini_set('display_errors', 1);

$rootPath = realpath(__DIR__ . '/../..');

echo $rootPath;

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', $rootPath);
	require_once JPATH_BASE . '/includes/defines.php';
}

if (!defined('JPATH_TESTS'))
{
	define('JPATH_TESTS', realpath(__DIR__));
}

if (!defined('JPATH_TEST_DATABASE'))
{
	define('JPATH_TEST_DATABASE', JPATH_TESTS . '/stubs/database');
}
if (!defined('JPATH_TEST_STUBS'))
{
	define('JPATH_TEST_STUBS', JPATH_TESTS . '/stubs');
}

// Import the platform in legacy mode.
if (file_exists(JPATH_LIBRARIES . '/import.legacy.php'))
{
// Joomla! 2.5
	require_once JPATH_LIBRARIES . '/import.legacy.php';
}
else
{
// Joomla! 3.x
	require_once JPATH_LIBRARIES . '/import.php';
}

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

//include redcore bootstrap file
require_once JPATH_LIBRARIES . '/redcore/bootstrap.php';

//include the Database file
require_once JPATH_TESTS . '/core/case/database.php';

//include the Testcase file
require_once JPATH_TESTS . '/core/case/case.php';

JLoader::registerPrefix('Redshopb', JPATH_LIBRARIES . '/redshopb');

// Register the core Joomla test classes.
JLoader::registerPrefix('Test', __DIR__ . '/core');