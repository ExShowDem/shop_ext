<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

/**
 * Words Controller
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerWords extends RedshopbControllerAdmin
{
	/**
	 * The method => state map.
	 *
	 * @var  array
	 */
	protected $shares = array(
		'share' => 1,
		'unshare' => 0
	);

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerTask('unshare', 'share');
	}

	/**
	 * Method to share a list of items
	 *
	 * @return  void
	 */
	public function share()
	{
		// Check for request forgeries
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

		// Get items to share from the request.
		$cid   = Factory::getApplication()->input->get('cid', array(), 'array');
		$value = ArrayHelper::getValue($this->shares, $this->getTask(), 0, 'int');

		if (empty($cid))
		{
			Log::add(Text::_($this->text_prefix . '_NO_ITEM_SELECTED'), Log::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			$cid = ArrayHelper::toInteger($cid);

			// Share the items.
			try
			{
				if ($model->share($cid, $value))
				{
					switch ($this->getTask())
					{
						case 'share':
							$ntext = $this->text_prefix . '_N_ITEMS_SHARED';
							break;

						case 'unshare':
							$ntext = $this->text_prefix . '_N_ITEMS_UNSHARED';
							break;
					}

					$this->setMessage(Text::plural($ntext, count($cid)));
				}
				else
				{
					$this->setMessage($model->getError(), 'error');
				}
			}
			catch (Exception $e)
			{
				$this->setMessage(Text::_('JLIB_DATABASE_ERROR_ANCESTOR_NODES_LOWER_STATE'), 'error');
			}
		}

		// Set redirect
		$this->setRedirect($this->getRedirectToListRoute());
	}
}
