<?php
/*
 * Copyright (c)
 * Vladislav Veselinov <vladislav@v3labs.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 */

namespace V3labs\Math\BigDecimal\Tests;

use PHPUnit\Framework\TestCase;
use V3labs\Math\BigDecimal;

/**
 * @author Vladislav Veselinov <vladislav@v3labs.com>
 */
class BigDecimalComparisonTest extends TestCase
{
    /**
     * @test comparisonFunctions
     * @dataProvider comparisonData
     */
    public function comparisonFunction($val1, $val2, $expectedResult, $isLessThan, $isLessThanOrEqualTo, $isEqualTo, $isGreaterThan, $isGreaterThanOrEqualTo)
    {
        $num1 = new BigDecimal($val1);
        $num2 = new BigDecimal($val2);

        $this->assertEquals($expectedResult, $num1->compareTo($num2));

        $this->assertEquals($isLessThan,  $num1->isLessThan($num2));
        $this->assertEquals($isLessThanOrEqualTo,  $num1->isLessThanOrEqualTo($num2));
        $this->assertEquals($isEqualTo, $num1->isEqualTo($num2));
        $this->assertEquals($isGreaterThan, $num1->isGreaterThan($num2));
        $this->assertEquals($isGreaterThanOrEqualTo, $num1->isGreaterThanOrEqualTo($num2));
    }

    public function comparisonData()
    {
        return [
            ['1.09',     '1.10',         -1, true, true, false, false, false],
            ['5.41',     '1.5300',       1, false, false, false, true, true],
            ['2.00',     '-3.00',        1, false, false, false, true, true],
            ['-5.41',    '1.5126',       -1, true, true, false, false, false],
            ['-10.00',   '-20',          1, false, false, false, true, true],
            ['2.00',     '2.00',         0, false, true, true, false, true],
            ['-15.5401', '-15.54011',    1, false, false, false, true, true],
            ['-15.5401', '-15.54010000', 0, false, true, true, false, true],
            ['0.01'    , '0.002'       , 1, false, false, false, true, true],
            ['0.01'    , '0.02'        , -1, true, true, false, false, false],
            ['-0.01'   , '-0.002'      , -1, true, true, false, false, false],
            ['-0.01'   , '-0.02'       , 1, false, false, false, true, true],
        ];
    }
}
