<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Helpers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;


use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

/**
 * Newsletter list helper.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Helpers
 * @since       1.6.17
 */
final class RedshopbHelperNewsletter_List
{
	/**
	 * Get Newsletter Stats Count
	 *
	 * @param   int        $newsletterId  Id newsletter
	 * @param   bool|null  $isSent        Get sent or not sent
	 *
	 * @return mixed
	 */
	public static function getNewsletterStatsCount($newsletterId, $isSent = null)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(user_id)')
			->from($db->qn('#__redshopb_newsletter_user_stats'))
			->where('newsletter_id = ' . (int) $newsletterId);

		if ($isSent === true)
		{
			$query->where('sent > 0');
		}
		elseif ($isSent === false)
		{
			$query->where('sent = 0');
		}

		return $db->setQuery($query)->loadResult();
	}

	/**
	 * Method for get list of user whom subscribe to Newsletter List.
	 *
	 * @param   int  $newsletterListId  Newsletter list id
	 * @param   int  $fixed             Null => get all, "1" => Only get fixed, "0" => Only get not fixed
	 *
	 * @return  array|false           List of user Id if success. False otherwise.
	 */
	public static function getSubscribers($newsletterListId, $fixed = null)
	{
		$newsletterListId = (int) $newsletterListId;

		if (!$newsletterListId)
		{
			return false;
		}

		$db = RFactory::getDbo();

		$query = $db->getQuery(true)
			->select($db->qn('nlux.user_id'))
			->from($db->qn('#__redshopb_newsletter_user_xref', 'nlux'))
			->leftJoin($db->qn('#__redshopb_user', 'u') . ' ON u.id = nlux.user_id')
			->where('u.send_email = 1')
			->where('u.use_company_email = 0')
			->where($db->qn('nlux.newsletter_list_id') . ' = ' . $newsletterListId);

		// If "fixed" has been set.
		if (!is_null($fixed))
		{
			$query->where($db->qn('nlux.fixed') . ' = ' . (int) $fixed);
		}

		$db->setQuery($query);

		return $db->loadColumn();
	}

	/**
	 * Method for get fixed status of an user on specific Newsletter List
	 *
	 * @param   int  $newsletterListId  ID of Newsletter List
	 * @param   int  $userId            ID of user
	 *
	 * @return  integer|boolean
	 */
	public static function getSubscriberFixedStatus($newsletterListId, $userId)
	{
		$newsletterListId = (int) $newsletterListId;
		$userId           = (int) $userId;

		if (!$newsletterListId || !$userId)
		{
			return false;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->qn('fixed'))
			->from($db->qn('#__redshopb_newsletter_user_xref'))
			->where($db->qn('newsletter_list_id') . ' = ' . $newsletterListId)
			->where($db->qn('user_id') . ' = ' . $userId);
		$db->setQuery($query);
		$result = $db->loadObject();

		if (!$result)
		{
			return false;
		}

		return (int) $result->fixed;
	}

	/**
	 * Mail preview script frame init
	 *
	 * @param   string  $idArea   Id frame
	 * @param   int     $tempid   Number template
	 * @param   string  $subject  Title newsletter subject
	 *
	 * @return  void
	 */
	public static function mailPreviewScriptInit($idArea, $tempid = null, $subject = '')
	{
		HTMLHelper::_('behavior.framework');

		if (isset($_SERVER["REQUEST_URI"]))
		{
			$requestUri = $_SERVER["REQUEST_URI"];
		}
		else
		{
			$requestUri = $_SERVER['PHP_SELF'];

			if (!empty($_SERVER['QUERY_STRING']))
			{
				$requestUri = rtrim($requestUri, '/') . '?' . $_SERVER['QUERY_STRING'];
			}
		}

		if (((!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == "on") || $_SERVER['SERVER_PORT'] == 443))
		{
			$currentURL = 'https://';
		}
		else
		{
			$currentURL = 'http://';
		}

		$currentURL .= $_SERVER["HTTP_HOST"] . $requestUri;

		$js = "var iframecreated = false;
				function redshopbdisplayPreview(){
					var d = document, area = d.getElementById('$idArea');
					if(!area) return;
					if(iframecreated) return;
					iframecreated = true;
					var content = area.innerHTML;
					var myiframe = d.createElement(\"iframe\");
					myiframe.id = 'iframepreview';
					myiframe.style.width = '100%';
					myiframe.style.borderWidth = '0px';
					myiframe.allowtransparency = \"true\";
					myiframe.frameBorder = '0';
					area.innerHTML = '';
					area.appendChild(myiframe);
					myiframe.onload = function(){
						var iframeloaded = false;
						try{
							if(myiframe.contentDocument != null && initIframePreview(myiframe,content) && replaceAnchors(myiframe)){
								iframeloaded = true;
							}
						}catch(err){
							iframeloaded = false;
						}

						if(!iframeloaded){
							area.innerHTML = content;
						}
					}
					myiframe.src = '';
				}
				function resetIframeSize(myiframe){
					var innerDoc = (myiframe.contentDocument) ? myiframe.contentDocument : myiframe.contentWindow.document;
					var objToResize = (myiframe.style) ? myiframe.style : myiframe;
					if(objToResize.width != '100%') return;
					var newHeight = innerDoc.body.scrollHeight;
					if(!objToResize.height || parseInt(objToResize.height,10)+10 < newHeight || parseInt(objToResize.height,10)-10 > newHeight)
						objToResize.height = newHeight+'px';
					setTimeout(function(){resetIframeSize(myiframe);},1000);
				}
				function replaceAnchors(myiframe){
					var myiframedoc = myiframe.contentWindow.document;
					var myiframebody = myiframedoc.body;
					var el = myiframe;
					var myiframeOffset = el.offsetTop;
					while ( ( el = el.offsetParent ) != null )
					{
						myiframeOffset += el.offsetTop;
					}

					var elements = myiframebody.getElementsByTagName(\"a\");
					for( var i = elements.length - 1; i >= 0; i--){
						var aref = elements[i].getAttribute('href');
						if(!aref) continue;
						if(aref.indexOf(\"#\") != 0 && aref.indexOf(\"" . addslashes($currentURL) . "#\") != 0) continue;

						if(elements[i].onclick && elements[i].onclick != \"\") continue;

						var adest = aref.substring(aref.indexOf(\"#\")+1);
						if( adest.length < 1 ) continue;

						elements[i].dest = adest;
						elements[i].onclick = function(){
							elem = myiframedoc.getElementById(this.dest);
							if(!elem){
								elems = myiframedoc.getElementsByName(this.dest);
								if(!elems || !elems[0]) return false;
								elem = elems[0];
							}
							if( !elem ) return false;

							var el = elem;
							var elemOffset = el.offsetTop;
							while ( ( el = el.offsetParent ) != null )
							{
								elemOffset += el.offsetTop;
							}
							window.scrollTo(0,elemOffset+myiframeOffset-15);
							return false;
						};
					}
					return true;
				}
				function initIframePreview(myiframe,content){
					var d = document;

					var heads = myiframe.contentWindow.document.getElementsByTagName(\"head\");
					if(heads.length == 0){
						return false;
					}

					var head = heads[0];

					var myiframebodys = myiframe.contentWindow.document.getElementsByTagName('body');
					if(myiframebodys.length == 0){
						var myiframebody = d.createElement(\"body\");
						myiframe.appendChild(myiframebody);
					}else{
						var myiframebody = myiframebodys[0];
					}
					if(!myiframebody) return false;
					myiframebody.style.margin = '0px';
					myiframebody.style.padding = '0px';
					myiframebody.innerHTML = content;

					var title1 = d.createElement(\"title\");
					title1.innerHTML = '" . addslashes($subject) . "';

					var base1 = d.createElement(\"base\");
					base1.target = \"_blank\";

					head.appendChild(base1);

					var existingTitle = head.getElementsByTagName(\"title\");
					if(existingTitle.length == 0){
						head.appendChild(title1);
					}
				";

		if (!empty($tempid))
		{
			$js .= "var link1 = d.createElement(\"link\");
					link1.type = \"text/css\";
					link1.rel = \"stylesheet\";
					link1.href =  '" . URI::root() . "media/com_redshopb/templates/css/template_"
				. $tempid . ".css?v=" . @filemtime(JPATH_SITE . '/templates/css/template_' . $tempid . '.css') . "';
					head.appendChild(link1);
				";
		}

		$js .= "var style1 = d.createElement(\"style\");
				style1.type = \"text/css\";
				style1.id = \"overflowstyle\";
				try{style1.innerHTML = 'html,body,iframe{overflow-y:hidden} ';}
				catch(err){style1.styleSheet.cssText = 'html,body,iframe{overflow-y:hidden} ';}
				";

		$js .= "
				head.appendChild(style1);
				resetIframeSize(myiframe);
				return true;
			}
			window.addEvent('domready', function(){redshopbdisplayPreview();} );";

		$doc = Factory::getDocument();
		$doc->addScriptDeclaration($js);
	}
}
