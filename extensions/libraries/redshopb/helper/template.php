<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Helpers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

/**
 * Template helper.
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Helpers
 * @since       1.6.33
 */
class RedshopbHelperTemplate
{
	const FOLDER_SHOP      = 'templates';
	const FOLDER_EMAIL     = 'emails';
	const FOLDER_EMAIL_TAG = 'emails_tags';
	const FOLDER_SHOP_TAG  = 'tags';

	/**
	 * Use for store static flags from and for layouts, because static variables doesn't work inside layouts
	 *
	 * @var array
	 */
	public static $layoutsInitValues = array();

	/**
	 * Get Customization Path
	 *
	 * @param   object  $b2bTemplateItem     B2B Template item
	 * @param   string  $joomlaTemplateName  Joomla template name
	 *
	 * @return string
	 */
	public static function getFilePath($b2bTemplateItem, $joomlaTemplateName = '')
	{
		if ($joomlaTemplateName == '')
		{
			$path = JPATH_SITE . '/components/com_redshopb/layouts/';
		}
		elseif (strpos($joomlaTemplateName, '.') !== false)
		{
			$path = JPATH_ROOT . '/' . str_replace('.', '/', $joomlaTemplateName) . '/';
		}
		else
		{
			$path = JPATH_THEMES . '/' . $joomlaTemplateName . '/html/layouts/com_redshopb/';
		}

		return $path . static::getGroupFolderName($b2bTemplateItem->template_group) . '/'
			. $b2bTemplateItem->scope . '/' . $b2bTemplateItem->alias . '.php';
	}

	/**
	 * Get list customizations for current template
	 *
	 * @param   object  $template  Template data
	 *
	 * @return array
	 */
	public static function getListCustomizations($template)
	{
		$customizations = array();

		if ($template->scope && $template->template_group)
		{
			$folders = static::getJoomlaTemplateList();

			foreach ($folders as $folder)
			{
				$filePath = static::getFilePath($template, $folder);

				if (JFile::exists($filePath))
				{
					$customization           = new stdClass;
					$customization->template = $folder;
					$customization->fullPath = $filePath;
					$customizations[$folder] = $customization;
				}
			}
		}

		return $customizations;
	}

	/**
	 * Get List Extra Customizations which user can not change
	 *
	 * @param   object  $template          Template data
	 * @param   bool    $isTemplateLayout  Flag is template layout
	 *
	 * @return  array
	 *
	 * @since  1.13.0
	 */
	public static function getListExtraCustomizations($template, $isTemplateLayout = true)
	{
		$customizations = array();
		$layout         = RedshopbLayoutFile::getInstance($template->alias);

		foreach ($layout->getDefaultIncludePaths() as $onePath)
		{
			if (0 === strpos($onePath, JPATH_THEMES))
			{
				continue;
			}

			if ($isTemplateLayout
				&& 0 === strpos(
					str_replace('/', DIRECTORY_SEPARATOR, $onePath),
					JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_redshopb'
				)
			)
			{
				// Not have a sense to show layouts with smaller priority, because it won't reach frontend
				break;
			}

			$filePath = $onePath . '/' . self::getGroupFolderName($template->template_group) . '/'
						. $template->scope . '/' . $template->alias . '.php';

			if (JFile::exists($filePath))
			{
				$customization               = new stdClass;
				$customization->fullPath     = $filePath;
				$onePath                     = str_replace('/', DIRECTORY_SEPARATOR, $onePath);
				$filePath                    = str_replace('/', DIRECTORY_SEPARATOR, $filePath);
				$customization->relativePath = str_replace(JPATH_ROOT . DIRECTORY_SEPARATOR, '', $filePath);
				$customization->folder       = str_replace(
					array(JPATH_ROOT . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR),
					array('', '.'),
					$onePath
				);
				array_unshift($customizations, $customization);
			}
		}

		return $customizations;
	}

	/**
	 * Get list Joomla templates
	 *
	 * @return array
	 */
	public static function getJoomlaTemplateList()
	{
		static $folders = null;

		if (is_null($folders))
		{
			$folders = JFolder::folders(JPATH_THEMES . '/', '.', false, false, array('.svn', 'CVS', '.DS_Store', '__MACOSX', 'system'));
		}

		return $folders;
	}

	/**
	 * Create Default Template
	 *
	 * @param   object  $template  Template data
	 * @param   bool    $reset     Flag for reset template if exists
	 *
	 * @return  void
	 */
	public static function createDefaultTemplate($template, $reset = false)
	{
		if ($template->scope && $template->template_group)
		{
			$filePath = static::getFilePath($template);

			if (!JFile::exists($filePath) || $reset)
			{
				JFile::write($filePath, $template->content);
			}
		}
	}

	/**
	 * Render B2B template
	 *
	 * @param   string          $scope        Scope template
	 * @param   string          $group        Template group
	 * @param   int|null        $templateId   Template id
	 * @param   array|null      $displayData  Object which properties are used inside the layout file to build displayed output
	 * @param   string          $basePath     Base path to use when loading layout files
	 * @param   Registry|array  $options      Optional custom options to load.
	 * @param   bool            $allInfo      Return all template information
	 *
	 * @return string|object|null
	 *
	 * @since 1.6.0
	 */
	public static function renderTemplate(
		$scope, $group = 'shop', $templateId = null, $displayData = null, $basePath = '', $options = null, $allInfo = false
	)
	{
		$dispatcher = RFactory::getDispatcher();
		PluginHelper::importPlugin('vanir');

		$entityData = null;

		if (static::isSerialized($options, $result))
		{
			$options = $result;
		}

		// If no specific template is set, it tries to select a pre-defined template using the base entity
		if (is_null($templateId) && isset($displayData['mainTemplateEntity']) && !is_null($displayData['mainTemplateEntity']))
		{
			$templateId = $displayData['mainTemplateEntity']->getTemplateId($group, $scope);

			if (!$templateId)
			{
				$templateId = null;
			}
		}

		$templateData = static::findTemplate($scope, $group, $templateId, $basePath, $options, $allInfo);

		if ($allInfo)
		{
			$templateDesc = $templateData->content;
		}
		else
		{
			$templateDesc = $templateData;
		}

		// Decides the entity data to send to the pre/post render plugin event
		switch ($group)
		{
			case 'shop':
				switch ($scope)
				{
					case 'product':
						$entityData = $displayData['product'];
						break;
				}
				break;
		}

		/**
		 * Trigger any vanir-related plugin modifying the template content before it's rendered.
		 * @toDo: compatibility with other entities (products only)
		*/
		$dispatcher->trigger('onRedshopbBeforeRenderTemplate', array(&$templateDesc, $group, $scope, $templateId, $entityData, &$displayData));

		static::findAndRenderTags($templateDesc, $basePath, $options, $group);

		$tmpPath = JPATH_SITE . '/cache/com_redshopb_templates/'
			. $group . '-' . $scope . '-' . (int) $templateId . '-' . static::getHash() . '-' . md5($templateDesc) . '.php';

		$templateDesc = static::executeFileContent($tmpPath, $templateDesc, $displayData);

		/**
		 * Trigger any vanir-related plugin modifying the template content after it's rendered.
		 * @TODO: compatibility with other entities (products only)
		 */
		$dispatcher->trigger('onRedshopbAfterRenderTemplate', array(&$templateDesc, $group, $scope, $templateId, $entityData));

		if ($allInfo)
		{
			$templateData->content = $templateDesc;

			return $templateData;
		}

		return $templateDesc;
	}

	/**
	 * Render from string
	 *
	 * @param   string  $content      Content by string
	 * @param   string  $sectionName  Content section name
	 * @param   array   $displayData  Variables
	 * @param   string  $group        Tags folder group
	 *
	 * @return  string
	 *
	 * @since  1.13.0
	 */
	public static function renderFromString($content, $sectionName = 'string', $displayData = array(), $group = 'shop')
	{
		self::findAndRenderTags($content, '', null, $group);
		$tmpPath = JPATH_SITE . '/cache/com_redshopb_templates/'
			. $sectionName . '-' . $group . '-' . self::getHash() . '-' . md5($content) . '.php';

		return self::executeFileContent($tmpPath, $content, $displayData);
	}

	/**
	 * Find template
	 *
	 * @param   string               $scope       Scope template
	 * @param   string               $group       Template group
	 * @param   integer|string|null  $templateId  Template id
	 * @param   string               $basePath    Base path to use when loading layout files
	 * @param   Registry|array       $options     Optional custom options to load.
	 * @param   boolean              $allInfo     Return all template information
	 *
	 * @return  string|object|null
	 * @throws  Exception
	 */
	public static function findTemplate($scope, $group = 'shop', $templateId = null, $basePath = '', $options = null, $allInfo = false)
	{
		static $templates = array();
		$key              = $scope . '_' . $group . '_' . $templateId;

		if (!array_key_exists($key, $templates))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('t.*')
				->from($db->qn('#__redshopb_template', 't'))
				->where($db->qn('t.scope') . ' = ' . $db->q($scope))
				->where($db->qn('t.template_group') . ' = ' . $db->q($group))
				->order($db->qn('t.id') . ' ASC');

			if (is_numeric($templateId))
			{
				$query->where('t.id = ' . (int) $templateId)
					->where('t.state = 1');
			}
			elseif (is_string($templateId))
			{
				$query->where('t.alias = ' . $db->q($templateId))
					->where('t.state = 1');
			}
			else
			{
				$query->where('t.default = 1');
			}

			$template = $db->setQuery($query, 0, 1)
				->loadObject();

			if ($template)
			{
				$result = static::renderLayout(
					static::getGroupFolderName($template->template_group) . '.' . $template->scope . '.' . $template->alias, $basePath, $options
				);

				if (!empty($result))
				{
					$template->content      = (isset($result['content'])) ? $result['content'] : $template->content;
					$template->exists       = $result['exists'];
					$template->path         = $result['path'];
					$template->templateName = $result['templateName'];

					if (!empty($template->params))
					{
						$registry = new Registry;
						$registry->loadString($template->params);
						$params = $registry->toArray();

						if (array_key_exists(0, $params))
						{
							if ($template->templateName != 0 && array_key_exists($template->templateName, $params))
							{
								$params = array_replace($params[0], $params[$template->templateName]);
							}
							else
							{
								$params = $params[0];
							}

							$registry         = new Registry;
							$template->params = $registry->loadArray($params);
						}
						else
						{
							$template->params = $registry;
						}
					}
				}
			}
			elseif ($templateId)
			{
				// If template not exists in DB, then find default template
				$template = static::findTemplate($scope, $group, null, $basePath, $options, true);
			}
			else
			{
				$template = (object) array('content' => '');
			}

			$templates[$key] = $template;
		}

		if ($allInfo)
		{
			return $templates[$key];
		}

		return $templates[$key]->content;
	}

	/**
	 * Returns all field tags available for template
	 *
	 * @param   string  $template  Template with tags
	 *
	 * @return  array  array of template tags
	 */
	public static function getUsedTagsInTemplate($template)
	{
		$tags          = array();
		$matches       = array();
		$foundTags     = static::findTagsFromTemplate($template, $matches);
		$lastFoundTags = 0;

		// Need found all deep tags
		while ($foundTags && $lastFoundTags != $foundTags)
		{
			$lastFoundTags    = $foundTags;
			$foundIfCondition = false;

			foreach ($matches[0] as $key => $oneMatch)
			{
				// \{if (.*?)\}[\s\S]*\{endif \1\} ex: {if sometaghere} some content {sometaghere} some content {endif sometaghere}
				if ($matches[1][$key] != '')
				{
					$foundIfCondition = true;
					$match            = $matches[1][$key];
					$tagName          = str_replace(array('{', '}', ':', ' '), array('', '', '_', '_'), $match);
					$tags[$tagName]   = $tagName;
					$template         = str_replace(array('{' . $match . '}', '{if ' . $match . '}', '{endif ' . $match . '}'), '', $template);
				}

				// ({.+?}) ex: {sometagname}
				elseif ($matches[2][$key] != '')
				{
					$match   = $matches[2][$key];
					$tagName = str_replace(array('{', '}', ':', ' '), array('', '', '_', '_'), $match);

					$tags[$tagName] = $tagName;
					$template       = str_replace($match, '', $template);
				}
			}

			if ($foundIfCondition)
			{
				$matches   = array();
				$foundTags = static::findTagsFromTemplate($template, $matches);
			}
		}

		return $tags;
	}

	/**
	 * Method to find all tag matches from the given template string
	 *
	 * @param   string  $template  Template with tags
	 * @param   array   $matches   Match container for found tag keys
	 *
	 * @return  integer
	 */
	public static function findTagsFromTemplate($template, &$matches)
	{
		return preg_match_all('/\{if (.*?)\}[\s\S]*\{endif \1\}|({.+?(\|.+)?})/', $template, $matches);
	}

	/**
	 * Get template group folder name
	 *
	 * @param   string  $group  Group name
	 *
	 * @return  string
	 */
	public static function getGroupFolderName($group)
	{
		switch ($group)
		{
			case 'email':
				$return = static::FOLDER_EMAIL;
				break;
			case 'shop_tag':
				$return = static::FOLDER_SHOP_TAG;
				break;
			case 'email_tag':
				$return = static::FOLDER_EMAIL_TAG;
				break;
			case 'shop':
			default:
				$return = static::FOLDER_SHOP;
				break;
		}

		return $return;
	}

	/**
	 * Get tag folder nam for group
	 *
	 * @param   string  $group  Group name
	 *
	 * @return string  Folder name
	 */
	public static function getTagFolderForGroup($group)
	{
		switch ($group)
		{
			case 'email':
				$return = static::FOLDER_EMAIL_TAG;
				break;
			case 'shop':
			default:
				$return = static::FOLDER_SHOP_TAG;
				break;
		}

		return $return;
	}

	/**
	 * Method to render the redshopb tag layout.
	 *
	 * @param   string          $template   Template with tags
	 *                                      ex. send all user values $localVariables = compact(array_keys(get_defined_vars()));
	 * @param   string          $basePath   Base path to use when loading layout files
	 * @param   Registry|array  $options    Optional custom options to load.
	 * @param   string          $group      Template group
	 *
	 * @return  void
	 */
	public static function findAndRenderTags(&$template, $basePath = '', $options = null, $group = 'shop')
	{
		$matches       = array();
		$foundTags     = static::findTagsFromTemplate($template, $matches);
		$lastFoundTags = 0;

		// Need found all deep tags
		while ($foundTags && $lastFoundTags != $foundTags)
		{
			$lastFoundTags    = $foundTags;
			$foundIfCondition = false;
			$params           = '';

			foreach ($matches[0] as $key => $oneMatch)
			{
				// Replace \{if (.*?)\}[\s\S]*\{endif \1\} ex: {if sometaghere} some content {sometaghere} some content {endif sometaghere}
				if ($matches[1][$key] != '')
				{
					$foundIfCondition = true;
					$match            = $matches[1][$key];
					$tagName          = str_replace(array('{', '}', ':', ' '), array('', '', '_', '_'), $match);
					$return           = static::renderLayout(static::getTagFolderForGroup($group) . '.' . $tagName, $basePath, $options);

					if ($return['exists'] == true)
					{
						$tagValue     = str_replace(array('.', '-'), array('_', '_'), $tagName);
						$ifReplace    = '<?php $tags_' . $tagValue . ' = RedshopbHelperTemplate::renderTag(\''
							. $tagName . '\', compact(array_keys(get_defined_vars())), \'' . $basePath . '\', \''
							. serialize($options) . '\', \'' . $group . '\');
							if ($tags_' . $tagValue . ' != \'\'): ?>';
						$endifReplace = '<?php endif; ?>';
						$template     = str_replace(
							array('{' . $match . '}', '{if ' . $match . '}', '{else ' . $match . '}', '{endif ' . $match . '}'),
							array('<?php echo $tags_' . $tagValue . '; ?>', $ifReplace, '<?php else: ?>', $endifReplace),
							$template
						);
					}
					else
					{
						$template = str_replace($matches[0][$key], '', $template);
					}
				}

				// Replace ({.+?}) ex: {sometagname} or ({.+?\|{1}.+}) ex: {sometagname|param1,param2...paramN}
				elseif ($matches[2][$key] != '')
				{
					$match   = $matches[2][$key];
					$tagName = str_replace(array('{', '}', ':', ' '), array('', '', '_', '_'), $match);

					if ($matches[3][$key] != '')
					{
						$tmp     = explode('|', $match);
						$tagName = str_replace(array('{', '}', ':', ' '), array('', '', '_', '_'), $tmp[0]);
						$params  = str_replace('}', '', $tmp[1]);
					}

					$explodeTagName      = explode('.', $tagName);
					$countExplodeTagName = count($explodeTagName);

					if ($countExplodeTagName > 1 && $explodeTagName[0] == 'template')
					{
						$templateAliasReplace = null;

						if ($countExplodeTagName > 2)
						{
							$templateAliasReplace = $explodeTagName[2];
						}

						if (is_null($templateAliasReplace))
						{
							$templateAliasReplace = 'null';
						}
						elseif (preg_match('/\[(\$.+?)\]/', $templateAliasReplace, $outputArray))
						{
							$templateAliasReplace = $outputArray[1];
						}
						else
						{
							$templateAliasReplace = '\'' . $templateAliasReplace . '\'';
						}

						$displayData = "array_merge(compact(array_keys(get_defined_vars())), array('params' => '{$params}'))";

						$replace = "<?php echo RedshopbHelperTemplate::renderTemplate("
						. "'{$explodeTagName[1]}','{$group}',{$templateAliasReplace},{$displayData},'{$basePath}','" . serialize($options) . "'"
						. "); ?>";

						$template = str_replace($match, $replace, $template);
					}
					else
					{
						$return = static::renderLayout(static::getTagFolderForGroup($group) . '.' . $tagName, $basePath, $options);

						if ($return['exists'] == true)
						{
							$displayData = "array_merge(compact(array_keys(get_defined_vars())), array('params' => '{$params}'))";

							$replace = "<?php echo RedshopbHelperTemplate::renderTag("
							. "'{$tagName}',{$displayData},'{$basePath}', '" . serialize($options) . "' ,'{$group}'"
							. "); ?>";

							$template = str_replace($match, $replace, $template);
						}
					}
				}
			}

			if ($foundIfCondition)
			{
				$matches   = array();
				$foundTags = static::findTagsFromTemplate($template, $matches);
			}
		}
	}

	/**
	 * Method to render the redshopb tag layout.
	 *
	 * @param   string          $tagName      Name tag
	 * @param   array|null      $displayData  Object which properties are used inside the layout file to build displayed output
	 * @param   string          $basePath     Base path to use when loading layout files
	 * @param   Registry|array  $options      Optional custom options to load.
	 * @param   string          $group        Template group
	 *
	 * @return  string
	 */
	public static function renderTag($tagName, $displayData = null, $basePath = '', $options = null, $group = 'shop')
	{
		if ($displayData)
		{
			unset($displayData['displayData']);
		}

		$options = RedshopbHelperSync::mbUnserialize($options);
		$result  = $tagName;
		$return  = static::renderLayout(static::getTagFolderForGroup($group) . '.' . $tagName, $basePath, $options);

		if ($return['exists'] == true)
		{
			static::findAndRenderTags($return['content'], $basePath, $options, $group);
			$tmpPath = JPATH_SITE . '/cache/com_redshopb_templates/' .
				static::getHash() . '-cache-tags-' . $tagName . '-' . md5($return['content']) . '.php';
			$result  = static::executeFileContent($tmpPath, $return['content'], $displayData);
		}

		return $result;
	}

	/**
	 * Method to render the layout and return array with content and status layout exists.
	 *
	 * @param   string          $layoutFile  Dot separated path to the layout file, relative to base path
	 * @param   string          $basePath    Base path to use when loading layout files
	 * @param   Registry|array  $options     Optional custom options to load.
	 * @param   string          $group       Template group
	 *
	 * @return  array  Array with render content and flag content exists
	 */
	public static function renderLayout($layoutFile, $basePath = '', $options = null, $group = 'shop')
	{
		$basePath = empty($basePath) ? RLayoutHelper::$defaultBasePath : $basePath;

		// Make sure we send null to LayoutFile if no path set
		$basePath       = empty($basePath) ? null : $basePath;
		$layout         = RedshopbLayoutFile::getInstance($layoutFile, $basePath, $options);
		$path           = $layout->getPath();
		$renderedLayout = '';
		$templateName   = Factory::getApplication()->getTemplate();
		$templatePath   = JPATH_THEMES . '/' . $templateName;

		// When we do not have layout file for specific field, we use the default one from its type
		if (empty($path) && strpos($layoutFile, static::getTagFolderForGroup($group) . '.fields.') === 0)
		{
			$fieldAliasFromLayout = str_replace(static::getTagFolderForGroup($group) . '.fields.', '', $layoutFile);
			$isTitle              = false;

			if (strpos($layoutFile, static::getTagFolderForGroup($group) . '.fields.title.') === 0)
			{
				$fieldAliasFromLayout = str_replace(static::getTagFolderForGroup($group) . '.fields.title.', '', $layoutFile);
				$isTitle              = true;
			}

			$field = RedshopbHelperField::getFieldByAlias($fieldAliasFromLayout);

			if ($field)
			{
				$layoutId = static::getTagFolderForGroup($group) . '.types.' . $field->type_alias;

				if ($isTitle)
				{
					$layoutId = static::getTagFolderForGroup($group) . '.types.title';
				}

				$layout = RedshopbLayoutFile::getInstance($layoutId, $basePath, $options);
				$path   = $layout->getPath();

				$renderedLayout .= '<?php $currentFieldAlias = "' . $fieldAliasFromLayout . '"; ?>';
			}
		}

		if (!empty($path))
		{
			$renderedLayout .= file_get_contents($path);
			$isExists        = true;
		}
		else
		{
			$renderedLayout = '';
			$isExists       = false;
		}

		return array(
			'content' => $renderedLayout,
			'exists' => $isExists,
			'path' => $path,
			'templateName' => (substr($path, 0, strlen($templatePath)) == $templatePath) ? $templateName : 0
		);
	}

	/**
	 * Method to render the layout.
	 *
	 * @param   string          $layoutFile   Dot separated path to the layout file, relative to base path
	 * @param   object          $displayData  Object which properties are used inside the layout file to build displayed output
	 * @param   string          $basePath     Base path to use when loading layout files
	 * @param   Registry|array  $options      Optional custom options to load.
	 * @param   string          $group        Template group
	 *
	 * @return  string
	 */
	public static function render($layoutFile, $displayData = null, $basePath = '', $options = null, $group = 'shop')
	{
		$result = static::renderLayout($layoutFile, $basePath, $options, $group);

		if ($result['exists'] == true)
		{
			static::findAndRenderTags($result['content'], $basePath, $options, $group);
			$tmpPath           = JPATH_SITE . '/cache/com_redshopb_templates/'
				. static::getHash() . '-cache-layout-' . $layoutFile . '-' . md5($result['content']) . '.php';
			$result['content'] = static::executeFileContent($tmpPath, $result['content'], $displayData);
		}

		return $result['content'];
	}

	/**
	 * Execute file content
	 *
	 * @param   string  $filePath     File path
	 * @param   string  $content      Content
	 * @param   array   $displayData  Array which values are used inside the layout file to build displayed output
	 *
	 * @return  string
	 */
	public static function executeFileContent($filePath, $content, $displayData = array())
	{
		if (array_key_exists('displayData', $displayData))
		{
			unset($displayData['displayData']);
		}

		if (!JFile::exists($filePath))
		{
			$content = '<?php defined(\'_JEXEC\') or die; extract($displayData); ?>' . $content;
			JFile::write($filePath, $content);
		}

		ob_start();
		include $filePath;
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	/**
	 * Get secret hash
	 *
	 * @return string
	 */
	public static function getHash()
	{
		static $hash = null;

		if (!$hash)
		{
			$hash = md5(Factory::getConfig()->get('secret'));
		}

		return $hash;
	}

	/**
	 * Tests if an input is valid PHP serialized string.
	 *
	 * Checks if a string is serialized using quick string manipulation
	 * to throw out obviously incorrect strings. Unserialize is then run
	 * on the string to perform the final verification.
	 *
	 * Valid serialized forms are the following:
	 * <ul class="unstyled list-unstyled">
	 * <li>boolean: <code>b:1;</code></li>
	 * <li>integer: <code>i:1;</code></li>
	 * <li>double: <code>d:0.2;</code></li>
	 * <li>string: <code>s:4:"test";</code></li>
	 * <li>array: <code>a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}</code></li>
	 * <li>object: <code>O:8:"stdClass":0:{}</code></li>
	 * <li>null: <code>N;</code></li>
	 * </ul>
	 *
	 * @param   string  $value    Value to test for serialized form
	 * @param   mixed   $result   Result of unserialize() of the $value
	 *
	 * @return    boolean            True if $value is serialized data, otherwise FALSE
	 */
	public static function isSerialized($value, &$result = null)
	{
		// Bit of a give away this one
		if (!is_string($value))
		{
			return false;
		}

		/**
		 * Serialized FALSE, return TRUE. unserialize() returns FALSE on an
		 * invalid string or it could return FALSE if the string is serialized
		 * FALSE, eliminate that possibility.
		 */
		if ('b:0;' === $value)
		{
			$result = false;

			return true;
		}

		$length = strlen($value);
		$end    = '';

		if (isset($value[0]))
		{
			switch ($value[0])
			{
				case 's':
					if ('"' !== $value[$length - 2])
					{
						return false;
					}

				case 'b':
				case 'i':
				case 'd':
					// This looks odd but it is quicker than isset()ing
					$end .= ';';
				case 'a':
				case 'O':
					$end .= '}';

					if (':' !== $value[1])
					{
						return false;
					}

					switch ($value[2])
					{
						case 0:
						case 1:
						case 2:
						case 3:
						case 4:
						case 5:
						case 6:
						case 7:
						case 8:
						case 9:
							break;

						default:
							return false;
					}

				case 'N':
					$end .= ';';

					if ($value[$length - 1] !== $end[0])
					{
						return false;
					}
					break;

				default:
					return false;
			}
		}

		$result = @RedshopbHelperSync::mbUnserialize($value);

		if ($result === false)
		{
			$result = null;

			return false;
		}

		return true;
	}
}
