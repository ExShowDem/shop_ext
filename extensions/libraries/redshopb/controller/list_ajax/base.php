<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;

/**
 * Ajax list base controller.
 *
 * @since  2.0
 */
class RedshopbControllerList_AjaxBase extends RControllerAdmin
{
	/**
	 * @const  integer
	 * @since  2.0
	 */
	const XML_TYPE_FORM = 1;

	/**
	 * @const  integer
	 * @since  2.0
	 */
	const XML_TYPE_VIEW = 2;

	/**
	 * Field XML
	 *
	 * @var  SimpleXMLElement
	 */
	protected $field;

	/**
	 * Name of the field being processed
	 *
	 * @var  string
	 */
	protected $fieldName;

	/**
	 * Allowed field type
	 *
	 * @var  string
	 */
	protected $fieldType = 'redshopb.list_ajax';

	/**
	 * Search query
	 *
	 * @var  string
	 */
	protected $query;

	/**
	 * XML name received from the request
	 *
	 * @var  string
	 */
	protected $receivedXmlFileName;

	/**
	 * Path to the XML file
	 *
	 * @var  string
	 */
	protected $xmlPath;

	/**
	 * Type of XML loaded
	 *
	 * @var  integer
	 */
	protected $xmlType;

	/**
	 * Form or view XML
	 *
	 * @var  SimpleXMLElement
	 */
	protected $xml;

	/**
	 * Name of the XML file to load
	 *
	 * @var  string
	 */
	protected $xmlFileName;

	/**
	 * Gets the json list.
	 *
	 * @return  void
	 */
	public function json()
	{
		RedshopbHelperAjax::validateAjaxRequest('get');

		$fieldOptions = $this->getFieldOptions();

		$modelState = array(
			'list.limit'                           => $fieldOptions->get('limit'),
			'list.ordering'                        => $fieldOptions->get('ordering'),
			'list.direction'                       => $fieldOptions->get('direction'),
			$fieldOptions->get('completionFilter') => $this->getQuery()
		);

		// Merge extra filters
		$modelState = array_merge($modelState, (array) $fieldOptions->get('filter'));

		$model = RedshopbModel::getInstanceFromString($fieldOptions->get('modelName'));

		// $items = $modelHelper->search($filters, $options);
		$items = $model->search($modelState);

		$json = array();

		foreach ($items as $item)
		{
			$value      = $item->{$fieldOptions->get('identifier')};
			$properties = explode(',', $fieldOptions->get('property'));
			$text       = array();

			foreach ($properties as $property)
			{
				$text[] = $item->{$property};
			}

			$json[] = array(
				'text'  => implode(' ', $text),
				'value' => $value
			);
		}

		echo json_encode($json);

		Factory::getApplication()->close();
	}

	/**
	 * Gets the json list with pagination limit.
	 *
	 * @return  void
	 */
	public function jsonPagination()
	{
		RedshopbHelperAjax::validateAjaxRequest();

		$fieldOptions = $this->getFieldOptions();

		$app        = Factory::getApplication();
		$page       = $app->input->getInt('page', 1);
		$limitStart = ($page - 1) * $fieldOptions->get('limit');

		$modelState = array(
			'list.limit'                           => $fieldOptions->get('limit'),
			'list.ordering'                        => $fieldOptions->get('ordering'),
			'list.direction'                       => $fieldOptions->get('direction'),
			$fieldOptions->get('completionFilter') => $this->getQuery(),
			'list.start'                           => $limitStart
		);

		// Merge extra filters
		$modelState = array_merge($modelState, (array) $fieldOptions->get('filter'));

		$model = RedshopbModel::getInstanceFromString($fieldOptions->get('modelName'));
		$items = $model->search($modelState);

		$json         = new stdClass;
		$json->result = array();
		$json->total  = $model->getTotal();

		foreach ($items as $item)
		{
			$value      = $item->{$fieldOptions->get('identifier')};
			$properties = explode(',', $fieldOptions->get('property'));
			$text       = array();

			foreach ($properties as $property)
			{
				$text[] = $item->{$property};
			}

			$json->result[] = array(
				'text'  => implode(' ', $text),
				'id' => $value
			);
		}

		echo json_encode($json);

		$app->close();
	}

	/**
	 * Get the field XML
	 *
	 * @return  SimpleXMLElement
	 */
	protected function getField()
	{
		if (null === $this->field)
		{
			$this->loadField();
		}

		return $this->field;
	}

	/**
	 * Get the field name
	 *
	 * @return  string
	 */
	protected function getFieldName()
	{
		if (null === $this->fieldName)
		{
			$this->loadFieldName();
		}

		return $this->fieldName;
	}

	/**
	 * Gets the field options.
	 *
	 * @return  Registry
	 */
	private function getFieldOptions()
	{
		$field = $this->getField();

		$filter = array();

		if (isset($field['filter']))
		{
			$filter = json_decode(str_replace('\'', '"', $field['filter']), true);
		}

		if (isset($field['dynamicFilters']))
		{
			$dynamicFilters = json_decode(str_replace('\'', '"', $field['dynamicFilters']), true);
			$input          = Factory::getApplication()->input;

			foreach ($dynamicFilters as $filterName => $filterInput)
			{
				// Apparently Input converts filter.myfilter into filter_myfilter
				$inputVar = str_replace('.', '_', $filterName);

				$filter[$filterName] = $input->get($inputVar);
			}
		}

		return new Registry(
			array(
				'modelName'        => (string) $field['model'],
				'completionFilter' => (string) $field['completionFilter'],
				'filter'           => $filter,
				'property'         => (string) $field['property'],
				'identifier'       => isset($field['identifier']) ? (string) $field['identifier'] : 'id',
				'limit'            => isset($field['limit']) ? (string) $field['limit'] : 5,
				'ordering'         => (string) $field['ordering'],
				'direction'        => isset($field['direction']) ? (string) $field['direction'] : 'ASC',
			)
		);
	}

	/**
	 * Get the search query
	 *
	 * @return  string
	 */
	protected function getQuery()
	{
		if (null === $this->query)
		{
			$this->loadQuery();
		}

		return $this->query;
	}

	/**
	 * Get the XML content.
	 *
	 * @return  SimpleXMLElement
	 */
	protected function getXml()
	{
		if (null === $this->xml)
		{
			$this->loadXml();
		}

		return $this->xml;
	}

	/**
	 * Get the XML file name
	 *
	 * @return  string
	 */
	protected function getXmlFileName()
	{
		if (null === $this->xmlFileName)
		{
			$this->loadXmlFileName();
		}

		return $this->xmlFileName;
	}

	/**
	 * Are we processing a form field?
	 *
	 * @return  boolean
	 */
	protected function isForm()
	{
		return ((int) $this->xmlType === static::XML_TYPE_FORM);
	}

	/**
	 * Are we processing a view field?
	 *
	 * @return  boolean
	 */
	protected function isView()
	{
		return ((int) $this->xmlType === static::XML_TYPE_VIEW);
	}

	/**
	 * Load the field XML
	 *
	 * @throws  InvalidArgumentException
	 *
	 * @return  self
	 */
	protected function loadField()
	{
		$xml = $this->getXml();

		$fields = $xml->xpath('//field[@name="' . $this->getFieldName() . '"]');

		if (empty($fields))
		{
			throw new InvalidArgumentException(sprintf(__FUNCTION__ . ': The field "%s" does not exist: %s', $this->fieldName));
		}

		$field = $fields[0];

		// Security: only allow redshopb.list_ajax
		if (!isset($field['type']) || (string) $field['type'] !== $this->fieldType)
		{
			throw new InvalidArgumentException(sprintf(__FUNCTION__ . ': Invalid field type. Only "%s" are allowed', $this->fieldType));
		}

		// For security reasons compare the received XML file name with the received
		if (!isset($field['formName']) || (string) $field['formName'] !== $this->receivedXmlFileName)
		{
			throw new InvalidArgumentException(sprintf(__FUNCTION__ . ': Invalid XML file name: %s', $this->receivedXmlFileName));
		}

		// Also check that we are processing the expected field
		if (!isset($field['name']) || (string) $field['name'] !== $this->getFieldName())
		{
			throw new InvalidArgumentException(sprintf(__FUNCTION__ . ': Invalid XML file name: %s', $this->receivedXmlFileName));
		}

		$this->field = $field;

		return $this;
	}

	/**
	 * Receive the field name from request
	 *
	 * @throws  InvalidArgumentException
	 *
	 * @return  self
	 */
	protected function loadFieldName()
	{
		$fieldName = Factory::getApplication()->input->get('field_name');

		// This field is always required
		if (null === $fieldName)
		{
			throw new InvalidArgumentException(sprintf(__FUNCTION__ . ': Invalid field name: %s', $this->fieldName));
		}

		$this->fieldName = $fieldName;

		return $this;
	}

	/**
	 * Try to load XML file from file name
	 *
	 * @throws  UnexpectedValueException
	 *
	 * @return  self
	 */
	protected function loadXml()
	{
		$this->parseXmlFileName();

		if (!is_dir($this->xmlPath))
		{
			throw new UnexpectedValueException(sprintf(__FUNCTION__ . ': Folder does not exist: %s', $this->xmlPath));
		}

		$fullPath = $this->xmlPath . '/' . $this->getXmlFileName() . '.xml';

		if (!file_exists($fullPath))
		{
			throw new UnexpectedValueException(sprintf(__FUNCTION__ . ': Load XML file failed: %s', $fullPath));
		}

		$xml = simplexml_load_file($fullPath);

		if ($xml === false)
		{
			throw new UnexpectedValueException(sprintf(__FUNCTION__ . ': Load XML file failed: %s', $fullPath));
		}

		$this->xml = $xml;

		return $this;
	}

	/**
	 * Get the XML file name from request
	 *
	 * @throws  InvalidArgumentException
	 *
	 * @return  self
	 */
	protected function loadXmlFileName()
	{
		$xmlFileName = Factory::getApplication()->input->getString('form_name');

		// This field is always required
		if (null === $xmlFileName)
		{
			throw new InvalidArgumentException(sprintf(__FUNCTION__ . ': Invalid XML file name: %s', $xmlFileName));
		}

		// Ensure that only formats like: myform OR view/myview/mylayout are allowed
		if (false !== strpos($xmlFileName, '/'))
		{
			if (!preg_match("/^(\w+\/){1,3}\w+$/", $xmlFileName, $validPath))
			{
				throw new InvalidArgumentException(sprintf(__FUNCTION__ . ': Invalid XML file name: %s', $xmlFileName));
			}
			else
			{
				$xmlFileName = $validPath[0];
			}
		}

		$this->xmlFileName = $xmlFileName;

		// Store also the unprocessed file name to compare it later for security reasons
		$this->receivedXmlFileName = $xmlFileName;

		return $this;
	}

	/**
	 * Load search query from request
	 *
	 * @return  self
	 */
	protected function loadQuery()
	{
		$this->query = Factory::getApplication()->input->getString('query');

		return $this;
	}

	/**
	 * Parse the XML file name
	 *
	 * @throws  InvalidArgumentException
	 *
	 * @return  self
	 */
	protected function parseXmlFileName()
	{
		// Defaults
		$this->xmlType = static::XML_TYPE_FORM;
		$this->xmlPath = JPATH_COMPONENT . '/models/forms';
		$xmlFileName   = $this->getXmlFileName();
		$formPath	   = Factory::getApplication()->input->getString('form_path');

		if (!empty($formPath))
		{
			$this->xmlPath = JPATH_ROOT . '/' . $formPath;
		}

		// Sample form name received
		if (false === strpos($xmlFileName, '/'))
		{
			$this->xmlFileName = $xmlFileName;

			return $this;
		}

		$parts = explode('/', $xmlFileName);
		$parts = array_filter($parts);

		if (!$parts || $parts < 2)
		{
			$this->xmlFileName = trim($xmlFileName, '/');

			return $this;
		}

		// The rest is only applied for view XML files
		$viewPosition = array_search('view', $parts);

		if ($viewPosition === false)
		{
			throw new InvalidArgumentException(sprintf(__FUNCTION__ . ': Invalid XML file name: %s', $this->receivedXmlFileName));
		}

		unset($parts[$viewPosition]);

		$parts = array_values($parts);

		// At this point we can be sure that we are trying to load a view
		$this->xmlType = static::XML_TYPE_VIEW;

		// First part may be the base folder
		if (in_array($parts[0], array('site', 'admin')))
		{
			$this->xmlPath = ($parts[0] === 'site' ? JPATH_SITE : JPATH_ADMINISTRATOR) . '/components/com_redshopb/views';
			unset($parts[0]);
		}
		// Autodetect mode
		else
		{
			$this->xmlPath = JPATH_COMPONENT . '/views';
		}

		// We still need the view name and the xml file name
		if (count($parts) !== 2)
		{
			throw new InvalidArgumentException(sprintf(__FUNCTION__ . ': Invalid XML file name: %s', $this->receivedXmlFileName));
		}

		// Last part is the XML file name
		$this->xmlFileName = array_pop($parts);

		$this->xmlPath .= '/' . array_pop($parts) . '/tmpl';

		return $this;
	}
}
