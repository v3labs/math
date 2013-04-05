<?php
/*
 * Copyright (c)
 * Kirill chEbba Chebunin <iam@chebba.org>
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 */

namespace Che\Math\Decimal\Tests;

use Che\Math\Decimal\BigDecimal;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Description of BigDecimalTest
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class BigDecimalTest extends TestCase
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
     * @test constructNonNumeric
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
        $result = $value1->sub($value2);

        $this->assertSame('192340388146104020053100901.44455', $result->value());
    }

    /**
     * @test mul should correctly multiply
     */
    public function mulGeneric()
    {
        $value1 = new BigDecimal('192341864273423843765928364.12345', 5);
        $value2 = new BigDecimal('1476127319823712827462.6789', 4);
        $result = $value1->mul($value2);

        $this->assertSame('283921080599825482308979477183220255889553969484.587310205', $result->value());
    }

    /**
     * @test div should correctly divide
     */
    public function divGeneric()
    {
        $value1 = new BigDecimal('192341864273423843765928364.12345', 5);
        $value2 = new BigDecimal('1476127319823712827462.6789', 4);
        $result = $value1->div($value2);

        $this->assertSame('130301.676346180', $result->value());
    }

    /**
     * @test divide with 0 throws exception
     * @expectedException InvalidArgumentException
     */
    public function divZero()
    {
        $value1 = new BigDecimal('123.45', 2);
        $value1->div(new BigDecimal('0.0000'));
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

    public function correctFormat()
    {
        return array(
            array('123.45', 2, '123.45', 'Default positive conversion'),
            array('123.456', 2, '123.45', 'Fraction scale trim'),
            array('00123.45', 2, '123.45', 'Zero trim'),
            array('-123.45', 2, '-123.45', 'Default negative conversion'),
            array('-00123.45', 2, '-123.45', 'Negative zero trim'),
            array('123.45', 4, '123.4500', 'Fraction padding'),
            array('123', 4, '123.0000', 'Empty fraction padding'),
            array('+123.45', 2, '123.45', 'Plus sign'),
            array('123.10', 0, '123', 'Null fraction'),
            array('0.00', 2, '0.00', 'Zero'),
            array('0', 2, '0.00', 'Zero padding'),
            array('-0.00', 2, '0.00', 'Negative zero')
        );
    }

    public function wrongFormat()
    {
        return array(
            array('--123.45', 'Double sign'),
            array('*123.45', 'Wrong sign'),
            array('1a3.45', 'Wrong char in integer'),
            array('123.45a', 'Wrong char in fraction'),
            array('123.', 'Empty fraction'),
            array('.45', 'Empty integer'),
        );
    }

    public function nonScalar()
    {
        return array(
            array(new \DateTime(), 'Object'),
            array(array(), 'Array'),
            array(null, 'Null')
        );
    }

    public function decimalPrecisions()
    {
        return array(
            array('123.45', 3),
            array('-123.45', 3),
            array('0.00', 1),
            array('123.4500', 3),
        );
    }

    public function scaleDetection()
    {
        return array(
            array('123.45', 2, 'Simple value'),
            array('123.4500', 4, 'Trailing zeros'),
            array('123.00', 2, 'Zero fraction'),
            array('123', 0, 'Integer')
        );
    }

    public function decimalSigns()
    {
        return array(
            array('123.4500', 1),
            array('-123.4500', -1),
            array('0.0000', 0),
        );
    }

    public function negateDecimals()
    {
        return array(
            array('-123.45', '123.45'),
            array('123.45', '-123.45'),
            array('0.00', '0.00')
        );
    }

    public function absDecimals()
    {
        return array(
            array('123.45', '123.45'),
            array('-123.45', '123.45'),
            array('0.00', '0.00'),
        );
    }
}
