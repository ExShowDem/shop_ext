<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;


/**
 * A Product helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
class RedshopbHelperProductTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Data provider for testGenerateCombinations
	 *
	 * @return array
	 */
	public function generateCombinationsProvider()
	{
		return array(
			array(
				array(
					'color' => array('blue', 'red'),
				),
				array(
					array('color' => 'blue'),
					array('color' => 'red'),
				)
			),
			array(
				array(
					'color' => array('blue', 'red'),
					'size' => array(10, 20)
				),
				array(
					array('color' => 'blue', 'size' => 10),
					array('color' => 'blue', 'size' => 20),
					array('color' => 'red', 'size' => 10),
					array('color' => 'red', 'size' => 20)
				)
			),
			array(
				array(
					'color' => array('blue', 'red'),
					'size' => array(10, 20),
					'other' => array('a', 'b', 'c')
				),
				array(
					array('color' => 'blue', 'size' => 10, 'other' => 'a'),
					array('color' => 'blue', 'size' => 10, 'other' => 'b'),
					array('color' => 'blue', 'size' => 10, 'other' => 'c'),
					array('color' => 'blue', 'size' => 20, 'other' => 'a'),
					array('color' => 'blue', 'size' => 20, 'other' => 'b'),
					array('color' => 'blue', 'size' => 20, 'other' => 'c'),
					array('color' => 'red', 'size' => 10, 'other' => 'a'),
					array('color' => 'red', 'size' => 10, 'other' => 'b'),
					array('color' => 'red', 'size' => 10, 'other' => 'c'),
					array('color' => 'red', 'size' => 20, 'other' => 'a'),
					array('color' => 'red', 'size' => 20, 'other' => 'b'),
					array('color' => 'red', 'size' => 20, 'other' => 'c'),
				)
			),
		);
	}

	/**
	 * Test generateCombinations()
	 *
	 * @param   array  $input     The input.
	 * @param   array  $expected  The expected result.
	 *
	 * @return  void
	 *
	 * @dataProvider generateCombinationsProvider
	 */
	public function testGenerateCombinations(array $input, array $expected)
	{
		$this->assertEquals($expected, RedshopbHelperProduct::generateCombinations($input));
	}
}
