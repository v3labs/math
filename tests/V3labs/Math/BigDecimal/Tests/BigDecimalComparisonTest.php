<?php
/*
 * Copyright (c)
 * Vladislav Veselinov <vladislav@v3labs.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 */

namespace V3labs\Math\BigDecimal\Tests;

use V3labs\Math\BigDecimal;

/**
 * @author Vladislav Veselinov <vladislav@v3labs.com>
 */
class BigDecimalComparisonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test comparisonFunctions
     * @dataProvider comparisonData
     */
    public function comparisonFunctionTest($val1, $val2, $expectedResult)
    {
        $num1 = new BigDecimal($val1);
        $num2 = new BigDecimal($val2);

        $this->assertEquals($expectedResult, $num1->compareTo($num2));

        if ($expectedResult == -1) {
            $this->assertEquals(true,  $num1->isLessThan($num2));
            $this->assertEquals(true,  $num1->isLessThanOrEqualTo($num2));
            $this->assertEquals(false, $num1->isEqualTo($num2));
            $this->assertEquals(false, $num1->isGreaterThan($num2));
            $this->assertEquals(false, $num1->isGreaterThanOrEqualTo($num2));
        }

        if ($expectedResult == 0) {
            $this->assertEquals(false, $num1->isLessThan($num2));
            $this->assertEquals(true,  $num1->isLessThanOrEqualTo($num2));
            $this->assertEquals(true,  $num1->isEqualTo($num2));
            $this->assertEquals(false, $num1->isGreaterThan($num2));
            $this->assertEquals(true,  $num1->isGreaterThanOrEqualTo($num2));
        }

        if ($expectedResult == 1) {
            $this->assertEquals(false, $num1->isLessThan($num2));
            $this->assertEquals(false, $num1->isLessThanOrEqualTo($num2));
            $this->assertEquals(false, $num1->isEqualTo($num2));
            $this->assertEquals(true,  $num1->isGreaterThan($num2));
            $this->assertEquals(true,  $num1->isGreaterThanOrEqualTo($num2));
        }
    }


    public function comparisonData()
    {
        return [
            ['1.09',     '1.10',         -1],
            ['5.41',     '1.5300',       1],
            ['2.00',     '-3.00',        1],
            ['-5.41',    '1.5126',       -1],
            ['-10.00',   '-20',          1],
            ['2.00',     '2.00',         0],
            ['-15.5401', '-15.54011',    1],
            ['-15.5401', '-15.54010000', 0],
            ['0.01'    , '0.002'       , 1],
            ['0.01'    , '0.02'        , -1],
            ['-0.01'   , '-0.002'      , -1],
            ['-0.01'   , '-0.02'       , 1]
        ];
    }
} 