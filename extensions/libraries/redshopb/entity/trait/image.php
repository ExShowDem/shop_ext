<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity.Trait
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Trait for entities with a image
 *
 * @since  2.0
 */
trait RedshopbEntityTraitImage
{
	/**
	 * Image path
	 *
	 * @var  string
	 */
	protected $image;

	/**
	 * Image URL (full)
	 *
	 * @var  string
	 */
	protected $imageURL;

	/**
	 * Image thumbs (full URLs)
	 *
	 * @var  array
	 */
	protected $imageThumbs = array();

	/**
	 * Get the image path
	 *
	 * @return  string
	 */
	public function getImage()
	{
		if (null === $this->image)
		{
			$this->loadImage();
		}

		return $this->image;
	}

	/**
	 * Get the image URL
	 *
	 * @return  string
	 */
	public function getImageURL()
	{
		if (null === $this->imageURL)
		{
			$this->loadImageURL();
		}

		return $this->imageURL;
	}

	/**
	 * Get the image thumb
	 *
	 * @param   int  $w  Image width
	 * @param   int  $h  Image size
	 *
	 * @return  string
	 */
	public function getImageThumb($w, $h)
	{
		if (!isset($this->imageThumbs[$w][$h]))
		{
			$this->loadImageThumb($w, $h);
		}

		return $this->imageThumbs[$w][$h];
	}

	/**
	 * Load image from DB
	 *
	 * @return  self
	 */
	protected function loadImage()
	{
		$this->image = $this->get('image');

		return $this;
	}

	/**
	 * Load image URL
	 *
	 * @return  self
	 */
	protected function loadImageURL()
	{
		$this->imageURL = '';

		if (empty($this->getImage()))
		{
			return $this;
		}

		if (!preg_match('/RedshopbEntity(.*)?/', get_class($this), $entityMatches))
		{
			return $this;
		}

		$this->imageURL = RedshopbHelperThumbnail::getFullImagePath($this->getImage(), strtolower(RInflector::pluralize($entityMatches[1])));

		return $this;
	}

	/**
	 * Load image Thumb
	 *
	 * @param   int  $w  Image width
	 * @param   int  $h  Image size
	 *
	 * @return  self
	 */
	protected function loadImageThumb($w, $h)
	{
		if (!isset($this->imageThumbs[$w]))
		{
			$this->imageThumbs[$w] = array();
		}

		$this->imageThumbs[$w][$h] = '';

		if (empty($this->getImage()))
		{
			return $this;
		}

		if (!preg_match('/RedshopbEntity(.*)?/', get_class($this), $entityMatches))
		{
			return $this;
		}

		$this->imageThumbs[$w][$h] = RedshopbHelperThumbnail::originalToResize(
			$this->getImage(), $w, $w, 100, 0, strtolower(RInflector::pluralize($entityMatches[1])), true
		);

		return $this;
	}
}
