<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Ajax
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
/**
 * Class RedshopbAjaxResponse
 *
 * Standardized container used when returning ajax responses
 *
 * @since  1.12.35
 *
 * @property integer $quantity
 * @property array   $totals
 * @property array   $formatted_totals
 * @property array   $taxes
 * @property array   $formatted_taxes
 * @property array   $taxSummary
 * @property array   $items
 * @property array   $shippingDateResult
 * @property integer $productCount
 * @property integer $categoryCount
 * @property integer $finished
 * @property integer $nextStep
 * @property boolean $productFinished
 * @property boolean $categoryFinished
 */
final class RedshopbAjaxResponse
{
	/**
	 * @var string
	 */
	public $message;

	/**
	 * @var string
	 */
	public $messageType;

	/**
	 * @var string
	 */
	public $body;

	/**
	 * @var string
	 */
	public $modal;

	/**
	 * RedshopbAjaxResponse constructor.
	 *
	 * @param   string  $message      response message
	 * @param   string  $messageType  response message type
	 */
	public function __construct($message = '', $messageType = 'alert-success')
	{
		$this->message     = $message;
		$this->messageType = $messageType;
	}

	/**
	 * Method to set the message
	 *
	 * @param   string  $message    the message to be set
	 * @param   bool    $translate  Should we translate the message?
	 *
	 * @return $this
	 */
	public function setMessage($message, $translate = false)
	{
		if ($translate)
		{
			$message = Text::_($message);
		}

		$this->message = $message;

		return $this;
	}

	/**
	 * Method to set the message type
	 *
	 * @param   string  $messageType  I.E. alert-success, alert-info, alert-error
	 *
	 * @return $this
	 */
	public function setMessageType($messageType)
	{
		$this->messageType = $messageType;

		return $this;
	}

	/**
	 * Method to set the response body
	 *
	 * @param   string  $content  content of the response body
	 *
	 * @return $this
	 */
	public function setBody($content)
	{
		$this->body = $content;

		return $this;
	}

	/**
	 * Method to set the response modal (should only be set if content type is modal)
	 *
	 * @param   string $content content of the response modal
	 *
	 * @return $this
	 */
	public function setModal($content)
	{
		if ($this->messageType == "modal")
		{
			$this->modal = $content;
		}
		else
		{
			$this->modal = false;
		}

		return $this;

	}

		/**
		 * Cast response to json object/array.
		 *
		 * @param   bool  $forceObject  Force json as object.
		 *
		 * @return  string  Json string.
		 *
		 * @since   1.12.50
		 */
	public function toJson($forceObject = false)
	{
		if ($forceObject)
		{
			return json_encode($this, JSON_FORCE_OBJECT);
		}

		return json_encode($this);
	}

	/**
	 * Set object or an array data for response manipulation.
	 *
	 * @param   array|object  $data  Data to parse and set to response class.
	 *
	 * @return  void
	 *
	 * @since   1.12.50
	 */
	public function setData($data)
	{
		if (empty($data))
		{
			return;
		}

		if (is_array($data))
		{
			// Check if array is assoc
			if (array_keys($data) !== range(0, count($data) - 1))
			{
				foreach ($data as $key => $value)
				{
					$this->$key = $value;
				}
			}
			else
			{
				foreach ($data as $value)
				{
					$this->$value = $value;
				}
			}
		}
		elseif (is_object($data))
		{
			$this->setData(get_object_vars($data));
		}
	}
}
