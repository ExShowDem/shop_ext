<?php
	/**
	 * @package     Aesir.E-Commerce
	 * @subpackage  Page Class
	 * @copyright   Copyright (C) 2012 - 2018 Aesir. E-Commerce. All rights reserved.
	 * @license     GNU General Public License version 2 or later; see LICENSE.txt
	 */
	namespace Page\Acceptance\AdminJoomla;
	use Page\Frontend\Redshopb2bPage;
	class InstallPage extends Redshopb2bPage
	{
		/**
		 * Include url of current page
		 *
		 * @var   string
		 * @since 2.1.1
		 */
		public static $URL = '/administrator/index.php?option=com_installer';
		
		/**
		 * Title of this page.
		 * @var   string
		 * @since 2.1.1
		 */
		public static $installTitle = "Extensions: Install";
		
		/**
		 * Link install Folder
		 * @var array
		 * @since 2.1.1
		 */
		public static $installFolder = ['link' => 'Install from Folder'];
		
		/**
		 * Link install Url
		 * @var array
		 * @since 2.1.1
		 */
		public static $installUrl = ['link' => 'Install from URL'];
		
		/**
		 * Name extension folder
		 * @var string
		 * @since 2.1.1
		 */
		public static $nameExtensionFolder = 'extension folder';
		
		/**
		 * Field install
		 * @var array
		 * @since 2.1.1
		 */
		public static $fieldInstall = '#install_directory';
		
		/**
		 * Button install
		 * @var array
		 * @since 2.1.1
		 */
		public static $buttonInstall = '#installbutton_directory';
		
		/**
		 * Button install url
		 * @var array
		 * @since 2.1.1
		 */
		public static $urlField = '#install_url';
		
		/**
		 * Button install url
		 * @var array
		 * @since 2.1.1
		 */
		public static $buttonUrl = '#installbutton_url';
		
		/**
		 * Message install success
		 * @var string
		 * @since 2.1.1
		 */
		public static $messageInstallSuccess = "installed successfully";
		
		/**
		 * Message install Component success
		 * @var string
		 * @since 2.1.1
		 */
		public static $messageInstallComponentSuccess = "Installation of the component was successful.";
		
		/**
		 * Button demo content
		 * @var array
		 * @since 2.1.1
		 */
		public static $buttonDemo = '#btn-demo-content';
		
		/**
		 * Button demo content
		 * @var array
		 * @since 2.1.1
		 */
		public static $message = '#system-message-container';
		
		/**
		 * Message demo content
		 * @var array
		 * @since 2.1.1
		 */
		public static $H1 = ['css' => 'H1'];

		/**
		 * Button install demo
		 * @var string
		 * @since 2.4.0
		 */
		public static $installDemoButton = "//button[@id='installdemo']";

		/**
		 * Message install data success
		 * @var string
		 * @since 2.4.0
		 */
		public static $messageInstallDataSuccess = "data installed successful";

		/**
		 * Frontend components/modules options
		 * @var string
		 * @since 2.4.0
		 */
		public static $frontendComponentsModulesOptions = "Frontend components/modules options";

		/**
		 * @var string
		 * @since 2.4.0
		 */
		public static $includeRedcoreCssAndJsId = "#jform_frontend_css";

		/**
		 * @var string
		 * @since 2.4.0
		 */
		public static $includeRedcoreCssAndJsLbl = "Include redCORE CSS and JS";

		/**
		 * @var string
		 * @since 2.4.0
		 */
		public static $translationOptions = "Translation options";

		/**
		 * @var string
		 * @since 2.4.0
		 */
		public static $enableTranslationsId = "#REDCORE_TRANSLATIONS_OPTIONS";

		/**
		 * @var string
		 * @since 2.4.0
		 */
		public static $enableTranslationsLbl = "Enable translations";

		/**
		 * @var string
		 * @since 2.4.0
		 */
		public static $webserviceOptions = "Webservice options";

		/**
		 * @var string
		 * @since 2.4.0
		 */
		public static $enableWebserviceId = "#REDCORE_WEBSERVICES_OPTIONS";

		/**
		 * @var string
		 * @since 2.4.0
		 */
		public static $enableWebserviceScript = "javascript:document.getElementById(\"REDCORE_WEBSERVICES_OPTIONS\").scrollIntoView();";

		/**
		 * @var string
		 * @since 2.4.0
		 */
		public static $JavaScript = "\"javascript:window.scrollBy(0,200);\"";

		/**
		 * @var string
		 * @since 2.4.0
		 */
		public static $enableWebserviceLbl = "Enable webservices";

		/**
		 * @var string
		 * @since 2.4.0
		 */
		public static $checkUserPermissionAgainstLbl = "Check user permission against";

		/**
		 * @var string
		 * @since 2.4.0
		 */
		public static $checkUserPermissionAgainstOption = "Joomla - Use already defined authorization checks in Joomla";

		/**
		 * @var string
		 * @since  2.4.0
		 */
		public static $enableSoapServerLbl = "Enable SOAP Server";

		/**
		 * @var string
		 * @since 2.4.0
		 */
		public static $oauth2ServerOptions = "OAuth2 Server options";

		/**
		 * @var string
		 * @since 2.4.0
		 */
		public static $enableOauth2Server = "Enable Oauth2 Server";

		/**
		 * @var string
		 * @since 2.4.0
		 */
		public static $enableOauth2ServerScript = "window.scrollTo(0,0)";

		/**
		 * @var string
		 * @since 2.4.0
		 */
		public static $saveSuccessMessage = "Save success";

		/**
		 * @var string
		 * @since 2.4.0
		 */
		public static $webserviceManager = "Webservice Manager";

		/**
		 * @var string
		 * @since 2.4.0
		 */
		public static $installWebservice = ".lc-install_all_webservices";

		/**
		 * @var string
		 * @since 2.4.0
		 */
		public static $notInstallWebservice = ".lc-not_installed_webservices";

		/**
		 * @var string
		 * @since 2.4.0
		 */
		public static $oauthClients = "#oauthClientsList";

		/**
		 * @var string
		 * @since 2.4.0
		 */
		public static $searchWebservice = "#filter_search_webservices";
	}