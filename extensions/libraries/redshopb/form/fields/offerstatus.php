<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('rlist');

/**
 * Type Offer Status
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldOfferStatus extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'OfferStatus';

	/**
	 * @var array
	 */
	protected $hideStatuses = array();

	/**
	 * Method to attach a Joomla\CMS\Form\Form object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		if (!parent::setup($element, $value, $group))
		{
			return false;
		}

		if (isset($this->element['hidestatus']))
		{
			$this->hideStatuses = explode(',', (string) $this->element['hidestatus']);
		}

		return true;
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options = array();

		/*
		 * Created: admin, the one who is creating it, can access it / the other cannot.
		 * Checkout is not possible in this state. Possible next steps: requested (employee) / sent (admin).
		 *
		 * Requested: employee created and requested the offer.
		 * No modifying is possible for employee, just for admin. No checkout. Possible next steps: sent.
		 *
		 * Sent: admin sent the final offer. No modifying is possible for admin nor employee. Possible next steps: accepted / rejected / ordered.
		 *
		 * Accepted: employee decided to accept the offer. No modifying. Checkout is possible. Possible next steps: ordered / rejected
		 *
		 * Rejected: employee rejects the offer. No modifying. Checkout is not possible. No next steps.
		 *
		 * Ordered: employee actually created an order using the offer. No modifying. No checkout. No next steps.
		 */
		$values = array(
			'created' => Text::_('COM_REDSHOPB_MYOFFERS_STATUS_CREATED'),
			'requested' => Text::_('COM_REDSHOPB_MYOFFERS_STATUS_REQUESTED'),
			'sent' => Text::_('COM_REDSHOPB_MYOFFERS_STATUS_SENT'),
			'accepted' => Text::_('COM_REDSHOPB_MYOFFERS_STATUS_ACCEPTED'),
			'rejected' => Text::_('COM_REDSHOPB_MYOFFERS_STATUS_REJECTED'),
			'ordered' => Text::_('COM_REDSHOPB_MYOFFERS_STATUS_ORDERED'),
		);

		foreach ($values as $value => $text)
		{
			$result = $this->setOption($value, $text);

			if ($result)
			{
				$options[] = $result;
			}
		}

		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Set one option
	 *
	 * @param   string  $value  Value
	 * @param   string  $text   Text
	 *
	 * @return  mixed|false
	 */
	protected function setOption($value, $text)
	{
		if (!in_array($value, $this->hideStatuses))
		{
			return HTMLHelper::_('select.option', $value, $text);
		}
		else
		{
			return false;
		}
	}
}
