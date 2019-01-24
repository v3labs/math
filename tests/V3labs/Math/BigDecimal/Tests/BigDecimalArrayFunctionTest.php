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
class BigDecimalArrayFunctionTest extends TestCase
{
    /**
     * @test comparisonFunctions
     */
    public function arrayFunctionsTest()
    {
        $values = BigDecimal::ofValues(['1.5', '2.6', '5.15']);
        $expectedValues = [BigDecimal::of('1.5'), BigDecimal::of('2.6'), BigDecimal::of('5.15')];

        $this->assertEquals(count($values), count($expectedValues));

        foreach ($values as $idx => $value) {
            $this->assertEquals($value->value(), $expectedValues[$idx]->value());
        }

        $sum = BigDecimal::sum($values);
        $this->assertEquals($sum->value(), '9.25');

        $avg = BigDecimal::avg($values, 3);
        $this->assertEquals($avg->value(), '3.083');

        $min = BigDecimal::min($values);
        $this->assertEquals($min->value(), '1.5');

        $max = BigDecimal::max($values);
        $this->assertEquals($max->value(), '5.15');
    }
} 