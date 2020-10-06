<?php
/**
 * @package     Aesir-e-commerce
 * @subpackage  Cest
 * @copyright   Copyright (C) 2014 - 2018 Aesir-e-commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Page\bootstrap3;

use Page\Acceptance\AdministratorPage;

class SetupFrontendFrameworkPage extends AdministratorPage
{
	/**
	 * @var string
	 * @since 2.4.0
	 */
	public static $templateUrl = '/administrator/index.php?option=com_templates&view=styles';

	/**
	 * @var string
	 * @since 2.4.0
	 */
	public static $moduleSiteUrl = '/administrator/index.php?option=com_modules';

	/**
	 * @var string
	 * @since 2.4.0
	 */
	public static $indexURL = '/index.php';

	/**
	 * @var string
	 * @since 2.4.0
	 */
	public static $template = 'Templates: Styles (Site)';

	/**
	 * @var string
	 * @since 2.4.0
	 */
	public static $templateAdministrator = 'Templates: Styles (Administrator)';

	/**
	 * @var string
	 * @since 2.4.0
	 */
	public static $templateEditStyle = 'Templates: Edit Style';

	/**
	 * @var string
	 * @since 2.4.0
	 */
	public static $advanced = 'Advanced';

	/**
	 * @var string
	 * @since 2.4.0
	 */
	public static $module = 'Modules (Site)';

	/**
	 * @var string
	 * @since 2.4.0
	 */
	public static $administratorId = "#client_id";

	/**
	 * @var string
	 * @since 2.4.0
	 */
	public static $isisDefault = 'isis - Default';

	/**
	 * @var array
	 * @since 2.4.0
	 */
	public static $linkWrightB2BDefault = ['link'=>'wrightB2B - Default'];

	/**
	 * @var array
	 * @since 2.4.0
	 */
	public static $setDefault = "//a[@data-original-title='Set default']";

	/**
	 * @var string
	 * @since 2.4.0
	 */
	public static $messageDefault = 'set';

	/**
	 * @var string
	 * @since 2.4.0
	 */
	public static $messagePublish = '1 module published.';

	/**
	 * @var array
	 * @since 2.4.0
	 */
	public static $moduleNotice = "//span[@class='icon-cube module']";

	/**
	 * @var array
	 * @since 2.4.0
	 */
	public static $moduleId = "#general";

	/**
	 * @var array
	 * @since 2.4.0
	 */
	public static $menuAssignmentLbl = "#jform_menus-lbl";

	/**
	 * @var array
	 * @since 2.4.0
	 */
	public static $menuAssignmentId = "#jform_assignment_chzn";

	/**
	 * @var array
	 * @since 2.4.0
	 */
	public static $onAllPage = '//li[@data-option-array-index=\'0\']';

	/**
	 * @var array
	 * @since 2.4.0
	 */
	public static $moduleApply = "//div[@id='toolbar-apply']/button";

    /**
     * @var string
     * @since 2.4.0
     */
	public static $moduleSave = "//button[@class='btn btn-small button-save']";

	/**
	 * @var string
	 * @since 2.4.0
	 */
	public static $moduleSaveSuccessMessage = "Module saved";

	/**
	 * @var string
	 * @since 2.4.0
	 */
	public static $statusModulePosition = "label[data-original-title='Status Module Position']";

	/**
	 * @var string
	 * @since 2.4.0
	 */
	public static $statusModulePositionLbl = 'Status Module Position';

	/**
	 * @var string
	 * @since 2.4.0
	 */
	public static $pinnedToolbar = 'Pinned Toolbar';

	/**
	 * @var string
	 * @since 2.4.0
	 */
	public static $saveSuccessMessage = 'Style saved.';

	/**
	 * @var string
	 * @since 2.4.0
	 */
	public static $executeJS = "window.scrollTo(0, document.body.scrollHeight);";

	/**
	 * @param $value
	 * @return string
	 * @since 2.4.0
	 */
	public static function xPathATag($value)
	{
		$xpath = "//div[@class='moduletable']/h3[contains(text(), '" . $value . "')]";
		return $xpath;
	}
}