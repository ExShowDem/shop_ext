<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir . E-commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Page\Integration\CheckoutWithProductVariants;
use Page\Frontend\Redshopb2bPage as Redshopb2bPage;

/**
 * Class CheckoutWithProductVariantsPage
 *
 * @package Page\Integration\CheckoutWithProductVariants
 * @since 2.8.0
 */
class CheckoutWithProductVariantsPage extends Redshopb2bPage
{
	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $btnInfoAddToCart = "//button[@class='btn btn-info add-to-cart add-to-cart-product']";

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $orderItemRow = "//tr[@class='orderItemRow footable-even']";

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $addToCartVariants = "//button[@class='btn btn-success add-to-cart']";
}