<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
/**
 * Contact List View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewContact_List extends RedshopbView
{
	/**
	 * @var  object
	 */
	public $customer;

	/**
	 * @var  object
	 */
	public $vendor;

	/**
	 * @var  string
	 */
	public $vendorImageLink;

	/**
	 * @var  integer
	 */
	public $vendorImageWidth;

	/**
	 * @var  integer
	 */
	public $vendorImageHeight;

	/**
	 * @var  string
	 */
	public $content;

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->customer = RedshopbHelperCompany::getCustomerCompanyByCustomer(RedshopbHelperUser::getUserRSid(), 'employee');

		PluginHelper::importPlugin('kvasir_sync');
		$result = RFactory::getDispatcher()->trigger('onRedshopbContactListDisplay', array(&$this->content, $this->customer));

		if ($result && $result[0] === true && !empty($this->content) && !is_null($this->content))
		{
			parent::display('kvasir');
		}

		$customerEntity          = RedshopbEntityCompany::load($this->customer->id);
		$this->vendor            = ($customerEntity->getVendor()->getId() > 1 ? $customerEntity->getVendor() : $customerEntity);
		$this->vendorImageWidth  = (int) RedshopbApp::getConfig()->get('thumbnail_width', 144);
		$this->vendorImageHeight = (int) RedshopbApp::getConfig()->get('thumbnail_height', 144);

		if (!empty($this->vendor->get('image')))
		{
			$this->vendorImageLink = RedshopbHelperThumbnail::originalToResize(
				$this->vendor->get('image'),
				$this->vendorImageWidth,
				$this->vendorImageHeight,
				100,
				0,
				'companies',
				true
			);
		}

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return Text::_('COM_REDSHOPB_CONTACT_LIST_TITLE');
	}
}
