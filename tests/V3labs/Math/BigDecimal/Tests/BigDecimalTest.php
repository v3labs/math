<?php
/*
 * Copyright (c)
 * Kirill chEbba Chebunin <iam@chebba.org>
 * Vladislav Veselinov <vladislav@v3labs.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 */

namespace V3labs\Math\BigDecimal\Tests;

use V3labs\Math\BigDecimal;

/**
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 * @author Vladislav Veselinov <vladislav@v3labs.com>
 */
class BigDecimalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test constructWithCorrectFormat
     * @dataProvider correctFormat
     *
     * @param string $string
     * @param int    $scale
     * @param string $value
     * @param string $case
     */
    public function constructWithCorrectFormat($string, $scale, $value, $case)
    {
        $decimal = new BigDecimal($string, $scale);
        $this->assertSame($value, $decimal->value(), $case);
    }

    /**
     * @test constructWithWrongFormat
     * @dataProvider wrongFormat
     *
     * @param string $string
     * @param string $case
     */
    public function constructWithWrongFormat($string, $case)
    {
        try {
            new BigDecimal($string);
        } catch (\InvalidArgumentException $e) {
            // Correct
            return;
        }

        $this->fail($case);
    }

    /**
     * @test constructNonScalar
     * @dataProvider nonScalar
     *
     * @param mixed $value
     * @param string $case
     */
    public function constructNonScalar($value, $case)
    {
        try {
            new BigDecimal($value);
        } catch (\InvalidArgumentException $e) {
            // Correct
            return;
        }

        $this->fail($case);
    }

    /**
     * @test constructNullScale
     * @dataProvider scaleDetection
     *
     * @param string $string
     * @param int    $expectedScale
     * @param string $case
     */
    public function constructDetectScale($string, $expectedScale, $case)
    {
        $decimal = new BigDecimal($string);
        $this->assertSame($expectedScale, $decimal->scale(), $case);
    }

    /**
     * @test precisionCalculation
     * @dataProvider decimalPrecisions
     *
     * @param string $value
     * @param string $precision
     */
    public function precisionCalculation($value, $precision)
    {
        $decimal = new BigDecimal($value);

        $this->assertSame($precision, $decimal->precision());
    }

    /**
     * @test add should correctly sum decimals
     */
    public function addGeneric()
    {
        $value1 = new BigDecimal('192341864273423843765928364.12345', 5);
        $value2 = new BigDecimal('1476127319823712827462.6789', 4);
        $sum = $value1->add($value2);

        $this->assertSame('192343340400743667478755826.80235', $sum->value());
    }

    /**
     * @test sub should correctly subtract
     */
    public function subGeneric()
    {
        $value1 = new BigDecimal('192341864273423843765928364.12345', 5);
        $value2 = new BigDecimal('1476127319823712827462.6789', 4);
        $result = $value1->subtract($value2);

        $this->assertSame('192340388146104020053100901.44455', $result->value());
    }

    /**
     * @test mul should correctly multiply
     */
    public function mulGeneric()
    {
        $value1 = new BigDecimal('192341864273423843765928364.12345', 5);
        $value2 = new BigDecimal('1476127319823712827462.6789', 4);
        $result = $value1->multiply($value2);

        $this->assertSame('283921080599825482308979477183220255889553969484.587310205', $result->value());
    }

    /**
     * @test div should correctly divide
     */
    public function divGeneric()
    {
        $value1 = new BigDecimal('192341864273423843765928364.12345', 5);
        $value2 = new BigDecimal('1476127319823712827462.6789', 4);
        $result = $value1->divide($value2);

        $this->assertSame('130301.676346180', $result->value());
    }

    /**
     * @test divide with 0 throws exception
     * @expectedException InvalidArgumentException
     */
    public function divZero()
    {
        $value1 = new BigDecimal('123.45', 2);
        $value1->divide(new BigDecimal('0.0000'));
    }

    /**
     * @test powGeneric
     */
    public function powGeneric()
    {
        $value1 = new BigDecimal('192341.12345', 5);
        $result = $value1->pow(7);

        $this->assertSame('9738790844484549401155595521762619025.20542465353794878176298358858515625', $result->value());
    }

    /**
     * @test powZero
     */
    public function powZero()
    {
        $value1 = new BigDecimal('192341.12345', 5);
        $result = $value1->pow(0);

        $this->assertSame('1', $result->value());
    }

    /**
     * @test powZero
     */
    public function powNegative()
    {
        try {
            $value1 = new BigDecimal('192341.12345', 5);
            $result = $value1->pow(-3);
        } catch (\InvalidArgumentException $e) {
            // pass
            return;
        }

        $this->fail('Exception was not raised for negative power');
    }

    /**
     * @test signumCorrectSign
     * @dataProvider decimalSigns
     *
     * @param string $string
     * @param int    $sign
     */
    public function signumCorrectSign($string, $sign)
    {
        $decimal = new BigDecimal($string);

        $this->assertSame($sign, $decimal->signum());
    }

    /**
     * @test negateCorrectConversion
     * @dataProvider negateDecimals
     *
     * @param string $value
     * @param int    $negative
     */
    public function negateCorrectConversion($value, $negative)
    {
        $decimal = new BigDecimal($value);

        $this->assertSame($negative, $decimal->negate()->value());
    }

    /**
     * @test absCorrectConversion
     * @dataProvider absDecimals
     *
     * @param string $value
     * @param string $abs
     */
    public function absCorrectConversion($value, $abs)
    {
        $decimal = new BigDecimal($value);

        $this->assertSame($abs, $decimal->abs()->value());
    }

    /**
     * @test roundModes
     * @dataProvider roundValues
     *
     * @param int    $mode
     * @param string $value
     * @param int    $scale
     * @param string $result
     */
    public function roundModes($mode, $value, $scale, $result)
    {
        $decimal = new BigDecimal($value);
        $rounded = $decimal->round($scale, $mode);

        $this->assertSame($result, $rounded->value(), sprintf('Round "%s" with mode "%d" and scale "%d"', $value, $mode, $scale));
    }

    /**
     * @test scaleChange
     * @dataProvider scaleChange
     */
    public function scaleChangeTest($initial, $newScale, $expected)
    {
        $this->assertEquals((new BigDecimal($initial))->setScale($newScale)->value(), $expected);
    }

    public function scaleChange()
    {
        return [
            ['100.00', 0, '100'],
            ['100.12', 3, '100.120'],
            ['100', 2, '100.00'],
            ['50.123', 2, '50.12'],
            ['-50.541', 1, '-50.5']
        ];
    }

    public function correctFormat()
    {
        return [
            ['123.45', 2, '123.45', 'Default positive conversion'],
            ['123.456', 2, '123.45', 'Fraction scale trim'],
            ['00123.45', 2, '123.45', 'Zero trim'],
            ['-123.45', 2, '-123.45', 'Default negative conversion'],
            ['-00123.45', 2, '-123.45', 'Negative zero trim'],
            ['123.45', 4, '123.4500', 'Fraction padding'],
            ['123', 4, '123.0000', 'Empty fraction padding'],
            ['+123.45', 2, '123.45', 'Plus sign'],
            ['123.10', 0, '123', 'Null fraction'],
            ['0.00', 2, '0.00', 'Zero'],
            ['0', 2, '0.00', 'Zero padding'],
            ['-0.00', 2, '0.00', 'Negative zero'],
            ['1E10', null, '10000000000', 'Scientific notation without sign in exponent'],
            ['1E+9', null, '1000000000', 'Scientific notation with sign in exponent'],
            ['1E-10', null, '0.0000000001', 'Scientific notation with negative exponent'],
            ['1.1E2', null, '110', 'Scientific notation'],
            ['0.012E+9', 2, '12000000.00', 'Scientific notation'],
            ['0.0123E+9', null, '12300000', 'Scientific notation'],
            ['10.0530E+1', null, '100.530', 'Scientific notation'],
            ['10.1530E+1', 1, '101.5', 'Scientific notation']
        ];
    }

    public function wrongFormat()
    {
        return [
            ['--123.45', 'Double sign'],
            ['*123.45', 'Wrong sign'],
            ['1a3.45', 'Wrong char in integer'],
            ['123.45a', 'Wrong char in fraction'],
            ['123.', 'Empty fraction'],
            ['.45', 'Empty integer'],
        ];
    }

    public function nonScalar()
    {
        return [
            [new \DateTime(), 'Object'],
            [array(), 'Array'],
            [null, 'Null'],
        ];
    }

    public function decimalPrecisions()
    {
        return [
            ['123.45', 3],
            ['-123.45', 3],
            ['0.00', 1],
            ['123.4500', 3],
        ];
    }

    public function scaleDetection()
    {
        return [
            ['123.45', 2, 'Simple value'],
            ['123.4500', 4, 'Trailing zeros'],
            ['123.00', 2, 'Zero fraction'],
            ['123', 0, 'Integer'],
        ];
    }

    public function decimalSigns()
    {
        return [
            ['123.4500', 1],
            ['-123.4500', -1],
            ['0.0000', 0],
            ['0.91', 1],
            ['+0.92', 1],
            ['-0.91', -1]
        ];
    }

    public function negateDecimals()
    {
        return [
            ['-123.45', '123.45'],
            ['123.45', '-123.45'],
            ['0.00', '0.00'],
            ['0.91', '-0.91'],
            ['-0.91', '0.91']
        ];
    }

    public function absDecimals()
    {
        return [
            ['123.45', '123.45'],
            ['-123.45', '123.45'],
            ['0.00', '0.00'],
            ['0.91', '0.91'],
            ['-0.91', '0.91'],
        ];
    }

    public function roundValues()
    {
        return [
            [BigDecimal::ROUND_UP, '5.5', 0, '6'],
            [BigDecimal::ROUND_UP, '2.5', 0, '3'],
            [BigDecimal::ROUND_UP, '1.6', 0, '2'],
            [BigDecimal::ROUND_UP, '1.1', 0, '2'],
            [BigDecimal::ROUND_UP, '1.0', 0, '1'],
            [BigDecimal::ROUND_UP, '-1.0', 0, '-1'],
            [BigDecimal::ROUND_UP, '-1.1', 0, '-2'],
            [BigDecimal::ROUND_UP, '-1.6', 0, '-2'],
            [BigDecimal::ROUND_UP, '-2.5', 0, '-3'],
            [BigDecimal::ROUND_UP, '-5.5', 0, '-6'],

            [BigDecimal::ROUND_DOWN, '5.5', 0, '5'],
            [BigDecimal::ROUND_DOWN, '2.5', 0, '2'],
            [BigDecimal::ROUND_DOWN, '1.6', 0, '1'],
            [BigDecimal::ROUND_DOWN, '1.1', 0, '1'],
            [BigDecimal::ROUND_DOWN, '1.0', 0, '1'],
            [BigDecimal::ROUND_DOWN, '-1.0', 0, '-1'],
            [BigDecimal::ROUND_DOWN, '-1.1', 0, '-1'],
            [BigDecimal::ROUND_DOWN, '-1.6', 0, '-1'],
            [BigDecimal::ROUND_DOWN, '-2.5', 0, '-2'],
            [BigDecimal::ROUND_DOWN, '-5.5', 0, '-5'],

            [BigDecimal::ROUND_CEILING, '5.5', 0, '6'],
            [BigDecimal::ROUND_CEILING, '2.5', 0, '3'],
            [BigDecimal::ROUND_CEILING, '1.6', 0, '2'],
            [BigDecimal::ROUND_CEILING, '1.1', 0, '2'],
            [BigDecimal::ROUND_CEILING, '1.0', 0, '1'],
            [BigDecimal::ROUND_CEILING, '-1.0', 0, '-1'],
            [BigDecimal::ROUND_CEILING, '-1.1', 0, '-1'],
            [BigDecimal::ROUND_CEILING, '-1.6', 0, '-1'],
            [BigDecimal::ROUND_CEILING, '-2.5', 0, '-2'],
            [BigDecimal::ROUND_CEILING, '-5.5', 0, '-5'],

            [BigDecimal::ROUND_FLOOR, '5.5', 0, '5'],
            [BigDecimal::ROUND_FLOOR, '2.5', 0, '2'],
            [BigDecimal::ROUND_FLOOR, '1.6', 0, '1'],
            [BigDecimal::ROUND_FLOOR, '1.1', 0, '1'],
            [BigDecimal::ROUND_FLOOR, '1.0', 0, '1'],
            [BigDecimal::ROUND_FLOOR, '-1.0', 0, '-1'],
            [BigDecimal::ROUND_FLOOR, '-1.1', 0, '-2'],
            [BigDecimal::ROUND_FLOOR, '-1.6', 0, '-2'],
            [BigDecimal::ROUND_FLOOR, '-2.5', 0, '-3'],
            [BigDecimal::ROUND_FLOOR, '-5.5', 0, '-6'],

            [BigDecimal::ROUND_HALF_UP, '5.5', 0, '6'],
            [BigDecimal::ROUND_HALF_UP, '2.5', 0, '3'],
            [BigDecimal::ROUND_HALF_UP, '1.6', 0, '2'],
            [BigDecimal::ROUND_HALF_UP, '1.1', 0, '1'],
            [BigDecimal::ROUND_HALF_UP, '1.0', 0, '1'],
            [BigDecimal::ROUND_HALF_UP, '-1.0', 0, '-1'],
            [BigDecimal::ROUND_HALF_UP, '-1.1', 0, '-1'],
            [BigDecimal::ROUND_HALF_UP, '-1.6', 0, '-2'],
            [BigDecimal::ROUND_HALF_UP, '-2.5', 0, '-3'],
            [BigDecimal::ROUND_HALF_UP, '-5.5', 0, '-6'],

            // Bug with zero at first truncated position.
            // It was been rounded to 6, because zero was ignored and the next digit was used
            [BigDecimal::ROUND_HALF_UP, '5.06', 0, '5'],

            [BigDecimal::ROUND_HALF_DOWN, '5.5', 0, '5'],
            [BigDecimal::ROUND_HALF_DOWN, '2.5', 0, '2'],
            [BigDecimal::ROUND_HALF_DOWN, '1.6', 0, '2'],
            [BigDecimal::ROUND_HALF_DOWN, '1.1', 0, '1'],
            [BigDecimal::ROUND_HALF_DOWN, '1.0', 0, '1'],
            [BigDecimal::ROUND_HALF_DOWN, '-1.0', 0, '-1'],
            [BigDecimal::ROUND_HALF_DOWN, '-1.1', 0, '-1'],
            [BigDecimal::ROUND_HALF_DOWN, '-1.6', 0, '-2'],
            [BigDecimal::ROUND_HALF_DOWN, '-2.5', 0, '-2'],
            [BigDecimal::ROUND_HALF_DOWN, '-5.5', 0, '-5'],

            [BigDecimal::ROUND_HALF_EVEN, '5.5', 0, '6'],
            [BigDecimal::ROUND_HALF_EVEN, '2.5', 0, '2'],
            [BigDecimal::ROUND_HALF_EVEN, '1.6', 0, '2'],
            [BigDecimal::ROUND_HALF_EVEN, '1.1', 0, '1'],
            [BigDecimal::ROUND_HALF_EVEN, '1.0', 0, '1'],
            [BigDecimal::ROUND_HALF_EVEN, '-1.0', 0, '-1'],
            [BigDecimal::ROUND_HALF_EVEN, '-1.1', 0, '-1'],
            [BigDecimal::ROUND_HALF_EVEN, '-1.6', 0, '-2'],
            [BigDecimal::ROUND_HALF_EVEN, '-2.5', 0, '-2'],
            [BigDecimal::ROUND_HALF_EVEN, '-5.5', 0, '-6'],

            [BigDecimal::ROUND_HALF_ODD, '5.5', 0, '5'],
            [BigDecimal::ROUND_HALF_ODD, '2.5', 0, '3'],
            [BigDecimal::ROUND_HALF_ODD, '1.6', 0, '2'],
            [BigDecimal::ROUND_HALF_ODD, '1.1', 0, '1'],
            [BigDecimal::ROUND_HALF_ODD, '1.0', 0, '1'],
            [BigDecimal::ROUND_HALF_ODD, '-1.0', 0, '-1'],
            [BigDecimal::ROUND_HALF_ODD, '-1.1', 0, '-1'],
            [BigDecimal::ROUND_HALF_ODD, '-1.6', 0, '-2'],
            [BigDecimal::ROUND_HALF_ODD, '-2.5', 0, '-3'],
            [BigDecimal::ROUND_HALF_ODD, '-5.5', 0, '-5'],
        ];
    }
}
