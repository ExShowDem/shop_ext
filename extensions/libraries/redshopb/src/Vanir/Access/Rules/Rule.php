<?php
/**
 * @package     Vanir.Library
 * @subpackage  Access
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

namespace Vanir\Access\Rules;

defined('_JEXEC') or die;

use Vanir\Access\Specification;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Input\Input;
use Joomla\CMS\Uri\Uri;
/**
 * Class Rule
 *
 * @package  Vanir\Access\Rules
 * @since    2.0
 */
abstract class Rule implements RulesInterface
{
	/**
	 * The top level specification container
	 *
	 * @var Specification
	 */
	protected $specificationContainer = null;

	/**
	 * @var \SimpleXMLElement
	 */
	protected $xmlDefinition;

	/**
	 * Sub rule to be checked after this one
	 *
	 * @var null|RulesInterface
	 */
	protected $subRule = null;

	/**
	 * The result returned from check
	 *
	 * @var boolean
	 */
	protected $grantResult = false;

	/**
	 * RedshopbAccessRulesRule constructor.
	 *
	 * @param   Specification      $specification  the specification container
	 * @param   \SimpleXMLElement  $xmlDefinition  xml definition of the rule
	 */
	public function __construct($specification, $xmlDefinition)
	{
		$this->specificationContainer = $specification;
		$this->xmlDefinition          = $xmlDefinition;
	}

	/**
	 * Method to grant or deny permission to the user
	 *
	 * @param   \RedshopbEntityUser  $user   the user asking for permission
	 * @param   Input                $input  input variables to be used by the rules
	 *
	 * @return boolean
	 */
	public function grant($user, $input)
	{
		$this->grantResult = $this->check($user, $input);

		if (!$this->grantResult)
		{
			return false;
		}

		return $this->grantChain($user, $input);
	}

	/**
	 * Method to check user against subrule chain
	 *
	 * @param   \RedshopbEntityUser  $user   the user asking for permission
	 * @param   Input                $input  input variables to be used by the rules
	 *
	 * @return boolean
	 */
	protected function grantChain($user, $input)
	{
		if (empty($this->subRule))
		{
			return true;
		}

		return $this->subRule->grant($user, $input);
	}

	/**
	 * Method to build a chain of rules
	 *
	 * @param   array  $rules  FIFO array of xml rule definitions to apply
	 *
	 * @throws \ErrorException
	 * @return void
	 */
	public function buildChain($rules = array())
	{
		if (empty($rules))
		{
			// This is the last rule in the chain
			return;
		}

		/** @var \SimpleXMLElement $subRule */
		$subRule = array_shift($rules);

		$ruleAttributes = $this->getXmlAttributes($subRule);

		$classPrefix = (array_key_exists('classPrefix', $ruleAttributes)) ? $ruleAttributes['classPrefix'] : 'Vanir\Access\Rules\\';
		$className   = $classPrefix . ucfirst((string) $ruleAttributes['name']);

		if (!class_exists($className))
		{
			throw new \ErrorException(Text::_('LIB_REDSHOPB_ERROR_UNKNOWN_SPECIFICATION_RULE_CLASS'));
		}

		/** @var RulesInterface $subRuleInstance */
		$subRuleInstance = new $className($this->specificationContainer, $subRule);
		$subRuleInstance->buildChain($rules);

		$this->subRule = $subRuleInstance;
	}

	/**
	 * Convenient method to get attributes of an XML element
	 *
	 * @param   \SimpleXMLElement  $xml  the xml definition
	 *
	 * @return array
	 */
	protected function getXmlAttributes($xml)
	{
		$attr = (array) $xml->attributes();

		if (is_null($attr))
		{
			return array();
		}

		return array_shift($attr);
	}

	/**
	 * Method to get a specific attribute
	 *
	 * @param   string             $name  name of the attribute
	 * @param   \SimpleXMLElement  $xml   element to get the attribute from
	 *
	 * @return mixed|null
	 */
	protected function getXmlAttribute($name, $xml)
	{
		$attr = $this->getXmlAttributes($xml);

		if (empty($attr[$name]))
		{
			return null;
		}

		return $attr[$name];
	}

	/**
	 * Proxy for \RedshopbHelperACL
	 *
	 * Adding this so that we can isolate direct calls to the helper
	 * In the future, if we replace the helper class then we won't have to change the individual rules
	 *
	 * @param   string   $permission       Permission to check
	 * @param   string   $objectType       optional   Object to check permission from
	 * @param   array    $corePermissions  optional   Core permissions to check with the first rule (create, delete, etc), additional to it (AND).
	 *                                                Only one core permission has to match at least
	 * @param   boolean  $checkOwn         optional   For creating purposes, managing your own object may be enough (own)
	 * @param   int      $assetId          optional   Asset ID to check on (if ommited it will check on the core component)
	 * @param   string   $component        optional   Component to check out from
	 * @param   int      $userId           optional   Joomla User id used for getting permission
	 *
	 * @return  boolean
	 */
	protected function getPermission(
		$permission, $objectType = '', $corePermissions = array(),
		$checkOwn = true, $assetId = 0, $component = 'redshopb', $userId = 0
	)
	{
		return \RedshopbHelperACL::getPermission($permission, $objectType, $corePermissions, $checkOwn, $assetId, $component, $userId);
	}

	/**
	 * Method to get a return value using Uri
	 *
	 * @return string
	 */
	protected function getReturn()
	{
		$returnUri = Uri::getInstance();
		$returnUri->delVar('return');

		return '&return=' . base64_encode($returnUri->toString());
	}

	/**
	 * Method to preform the rule specific checks on the user
	 *
	 * @param   \RedshopbEntityUser  $user   the user asking for permission
	 * @param   Input                $input  input variables to be used by the rules
	 *
	 * @return boolean
	 */
	abstract protected function check($user, $input);

	/**
	 * Method to get the layout scope from the rule input
	 *
	 * @return array
	 */
	protected function getLayoutScope()
	{
		$attributes = $this->getXmlAttributes($this->xmlDefinition);

		if (empty($attributes['layoutScope']))
		{
			return array();
		}

		return explode(',', $attributes['layoutScope']);
	}
}
