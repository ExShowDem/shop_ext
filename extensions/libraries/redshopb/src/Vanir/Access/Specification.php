<?php
/**
 * @package     Vanir.Library
 * @subpackage  Access
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

namespace Vanir\Access;

use RedshopbRoute, Vanir\Access\Rules\RulesInterface, RedshopbEntityUser, ErrorException;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Input\Input;

/**
 * Class Specification
 *
 * @package  Vanir\Access
 * @since    2.0
 */
class Specification
{
	/**
	 * Access specification
	 *
	 * @var \SimpleXMLElement
	 */
	protected $xmlSpecifications = null;

	/**
	 * Array of built specifications
	 *
	 * @var RulesInterface[]
	 */
	protected $specifications = array();

	/**
	 * Redirect URL optionally set by rules
	 *
	 * @var string
	 */
	protected $redirect = '';

	/**
	 * Messages to send to the application set by the rules
	 *
	 * @var array
	 */
	protected $messages = array();

	/**
	 * Specification constructor.
	 *
	 * @param   \SimpleXMLElement  $xmlSpecification  xml element to use when granting access
	 *                                                 default is located in component access.xml
	 */
	public function __construct($xmlSpecification)
	{
		$this->xmlSpecifications = $xmlSpecification;
	}

	/**
	 * Method to check the user and input against a specification
	 *
	 * @param   string              $specificationName  Name of the specification
	 *                                                   If an xml specification element with that name isn't found
	 *                                                   We use the default specification.
	 *
	 * @param   RedshopbEntityUser  $user               the user asking for permission
	 * @param   Input               $input              input variables to be used by the rules
	 * @param   boolean             $rebuild            should we rebuild the specification?
	 *
	 * @throws ErrorException
	 * @return boolean
	 */
	public function grant($specificationName, $user, $input, $rebuild = false)
	{
		if (!empty($this->specifications[$specificationName]) && !$rebuild)
		{
			return $this->specifications[$specificationName]->grant($user, $input);
		}

		$spec = $this->xmlSpecifications->xpath('//specification[@name="' . $specificationName . '"]');

		if (empty($spec))
		{
			$spec = $this->xmlSpecifications->xpath('//specification[@name="default"]');
		}

		$rules = (array) $spec[0]->xpath('./rule');

		// If there are no rules set for this specification then we return true.
		if (empty($rules))
		{
			return true;
		}

		$firstRule  = array_shift($rules);
		$attributes = (array) $firstRule->attributes();

		if (empty($attributes['@attributes']['name']))
		{
			throw new ErrorException(Text::_('LIB_REDSHOPB_ERROR_SPECIFICATION_RULES_MUST_HAVE_A_NAME'));
		}

		$attributes  = array_shift($attributes);
		$classPrefix = (array_key_exists('classPrefix', $attributes)) ? $attributes['classPrefix'] : 'Vanir\Access\Rules\\';
		$className   = $classPrefix . ucfirst((string) $attributes['name']);

		if (!class_exists($className))
		{
			throw new ErrorException(Text::sprintf('LIB_REDSHOPB_ERROR_UNKNOWN_SPECIFICATION_RULE_CLASS', $className));
		}

		/** @var RulesInterface $firstRuleInstance */
		$firstRuleInstance = new $className($this, $firstRule);
		$firstRuleInstance->buildChain($rules);

		$this->specifications[$specificationName] = $firstRuleInstance;

		return $this->specifications[$specificationName]->grant($user, $input);
	}

	/**
	 * Method to set the deferred redirect
	 *
	 * Note if you leave the redirect URL null, it will be replaced with the site menu default
	 *
	 * @param   string   $url    Absolute or Relative URI to Joomla resource.
	 * @param   boolean  $xhtml  Replace & by &amp; for XML compilance.
	 * @param   integer  $ssl    Secure state for the resolved URI.
	 *                             1: Make URI secure using global secure site URI.
	 *                             2: Make URI unsecure using the global unsecure site URI.
	 *
	 * @return Specification
	 */
	public function setRedirect($url = null, $xhtml = true, $ssl = null)
	{
		if (is_null($url))
		{
			// Redirect into home page
			$default        = Factory::getApplication()->getMenu()->getDefault();
			$this->redirect = Route::_('index.php?Itemid=' . $default->id, false);

			return $this;
		}

		$this->redirect = RedshopbRoute::_($url, $xhtml, $ssl);

		return $this;
	}

	/**
	 * Method to add deferred messages to the message que
	 *
	 * @param   string  $msg   The message to enqueue.
	 * @param   string  $type  The message type. Default is error.
	 *
	 * @return Specification
	 */
	public function addMessage($msg, $type='error')
	{
		if (empty($msg))
		{
			return $this;
		}

		if (empty($this->messages[$type])
			|| !is_array($this->messages[$type]))
		{
			$this->messages[$type] = array();
		}

		$this->messages[$type][] = $msg;

		return $this;
	}

	/**
	 * Set any message to the application and redirect to another URL.
	 *
	 * @return void
	 */
	public function redirect()
	{
		$app = Factory::getApplication();

		foreach ($this->messages AS $type => $messages)
		{
			foreach ($messages AS $message)
			{
				$app->enqueueMessage($message, $type);
			}
		}

		$app->redirect($this->redirect);
	}
}
