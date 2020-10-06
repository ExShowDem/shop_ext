<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  FTP Client
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Client\FtpClient;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;
/**
 * FTP client class
 *
 * @package     Joomla.Platform
 * @subpackage  Client
 * @since       12.1
 */
class RedshopbClientFtp extends FtpClient
{
	/**
	 * Method to list the contents of a directory on the FTP server
	 *
	 * @param   string  $path  Path to the local file to be stored on the FTP server
	 *
	 * @return  mixed  If $type is raw: string Directory listing, otherwise array of string with file-names
	 *
	 * @since   12.1
	 */
	public function listDetailsMlsd($path = null)
	{
		$dirList          = new stdClass;
		$dirList->files   = array();
		$dirList->folders = array();
		$data             = null;

		if (FTP_NATIVE)
		{
			// Turn passive mode on
			$connect = @ftp_raw($this->_conn, "PASV");

			// Parse the response and build the IP and port from the values
			if (!empty($connect) && preg_match("/.*\((\d+),(\d+),(\d+),(\d+),(\d+),(\d+)\)/", $connect[0], $m))
			{
				$address = "{$m[1]}.{$m[2]}.{$m[3]}.{$m[4]}";
				$port    = $m[5] * 256 + $m[6];

				@ftp_raw($this->_conn, "MLSD");

				$sock = socket_create(AF_INET, SOCK_STREAM, 0);

				if ($sock)
				{
					socket_connect($sock, $address, $port);
					socket_recv($sock, $data, 4096, MSG_WAITALL);
					socket_close($sock);
				}
			}

			$this->reinit();
		}
		else
		{
			// Start passive mode
			if (!$this->_passive())
			{
				Log::add(Text::_('JLIB_CLIENT_ERROR_JFTP_LISTDETAILS_PASSIVE'), Log::WARNING, 'jerror');

				return false;
			}

			// If a path exists, prepend a space
			if ($path != null)
			{
				$path = ' ' . $path;
			}

			// Request the file listing
			if (!$this->_putCmd('MLSD ' . $path, array(150, 125)))
			{
				Log::add(Text::sprintf('JLIB_CLIENT_ERROR_JFTP_LISTDETAILS_BAD_RESPONSE_LIST', $this->_response, $path), Log::WARNING, 'jerror');
				@fclose($this->_dataconn);

				return false;
			}

			// Read in the file listing.
			while (!feof($this->_dataconn))
			{
				$data .= fread($this->_dataconn, 4096);
			}

			fclose($this->_dataconn);

			// Everything go okay?
			if (!$this->_verifyResponse(226))
			{
				Log::add(
					Text::sprintf('JLIB_CLIENT_ERROR_JFTP_LISTDETAILS_BAD_RESPONSE_TRANSFER', $this->_response, $path),
					Log::WARNING, 'jerror'
				);

				return false;
			}
		}

		// Make sure the new line characters correct.
		$data = str_replace(array("\r\n", "\r", "\n"), CRLF, $data);

		// Convert string list of files to array.
		$data = explode(CRLF, $data);

		// Remove empty value of content array.
		$data = array_values($data);

		// If we received the listing of an empty directory, we are done as well
		if (empty($data[0]))
		{
			return $dirList;
		}

		foreach ($data as $key => $content)
		{
			if (!empty($content))
			{
				$itemContents = explode(';', $content);
				$item         = array();

				foreach ($itemContents as $keyContent => $itemContent)
				{
					$itemContentsPairs = explode('=', $itemContent);

					if (count($itemContentsPairs) == 1)
					{
						$item['name'] = trim($itemContentsPairs[0]);
					}
					else
					{
						$item[$itemContentsPairs[0]] = trim($itemContentsPairs[1]);
					}

					unset($itemContents[$keyContent]);
				}

				if (!empty($item['type']) && in_array($item['type'], array('dir', 'file')))
				{
					if ($item['type'] == 'file')
					{
						$dirList->files[] = $item;
					}
					else
					{
						$dirList->folders[] = $item;
					}
				}
			}

			unset($data[$key]);
		}

		return $dirList;
	}
}
