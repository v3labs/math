<?php
/*
 * Copyright (c)
 * Kirill chEbba Chebunin <iam@chebba.org>
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 */

namespace Che\Math\Decimal;

/**
 * Description of BigDecimal
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class BigDecimal
{
    const MAX_SCALE = 2147483647; // 2^32-1, actual is 100000000578158591, who knows what this number means

    const ROUND_UP          = 1;
    const ROUND_DOWN        = 2;
    const ROUND_CEILING     = 3;
    const ROUND_FLOOR       = 4;
    const ROUND_HALF_UP     = 5;
    const ROUND_HALF_DOWN   = 6;
    const ROUND_HALF_EVEN   = 7;
    const ROUND_HALF_ODD    = 8;
    const ROUND_UNNECESSARY = 9;

    const STRING_FORMAT_REGEX  = '/^([-+])?([0-9]+)(.([0-9]+))?$/';

    private $value;
    private $scale;

    public function __construct($value, $scale = null)
    {
        if ($scale !== null) {
            $scale = (int) $scale;
            if (abs($scale) > self::MAX_SCALE) {
                throw new \InvalidArgumentException(sprintf('Scale "%s" is grater than max "%s"', $scale, self::MAX_SCALE));
            }
        }

        if (!is_scalar($value)) {
            throw new \InvalidArgumentException(sprintf('Value of type "%s" is not as scalar', gettype($value)));
        }

        $value = (string) $value;
        if (!preg_match(self::STRING_FORMAT_REGEX, $value, $matches)) {
            throw new \InvalidArgumentException(sprintf('Wrong value "%s" format: expected "%s"', $value, self::STRING_FORMAT_REGEX));
        }

        $sign = $matches[1] === '-' ? '-' : '';
        $integer = ltrim($matches[2], '0') ?: '0'; // Remove leading zeros, empty treat as 0
        $fraction = isset($matches[4]) ? $matches[4] : '';
        // Check for zero
        if ($integer === '0' && trim($fraction, '0') === '') {
            $sign = '';
        }

        if ($scale === null) {
            $scale = strlen($fraction);
        } else {
            $scale = (int) $scale;
            if (strlen($fraction) > $scale) {
                $fraction = substr($fraction, 0, $scale);
            } else {
                $fraction = str_pad($fraction, $scale, '0');
            }
        }

        $this->value = $sign.$integer;
        if ($scale) {
            $this->value .= '.'.$fraction;
        }
        $this->scale = $scale;
    }

    public static function zero()
    {
        return new static(0, 0);
    }

    public function one()
    {
        return new static(1, 0);
    }

    public function value()
    {
        return $this->value;
    }

    public function scale()
    {
        return $this->scale;
    }

    public function precision()
    {
        $parts = explode('.', $this->value);

        return strlen(ltrim($parts[0], '-+'));
    }

    public function __toString()
    {
        return $this->value();
    }

    public function compareTo(BigDecimal $other)
    {
        $scale = $this->maxScale($other);

        return bccomp($this->value, $other->value(), $scale);
    }

    /**
     * add
     *
     * @param BigDecimal $addend
     *
     * @return self
     */
    public function add(BigDecimal $addend)
    {
        $scale = $this->maxScale($addend);

        return new static(bcadd($this->value, $addend->value(), $scale), $scale);
    }

    /**
     * sub
     *
     * @param BigDecimal $subtrahend
     *
     * @return self
     */
    public function sub(BigDecimal $subtrahend)
    {
        $scale = $this->maxScale($subtrahend);

        return new static(bcsub($this->value, $subtrahend->value(), $scale), $scale);
    }

    /**
     * mul
     *
     * @param BigDecimal $multiplier
     *
     * @return self
     */
    public function mul(BigDecimal $multiplier)
    {
        $scale = min($this->scale + $multiplier->scale(), self::MAX_SCALE);

        return new static(bcmul($this->value, $multiplier->value(), $scale), $scale);
    }

    /**
     * div
     *
     * @param BigDecimal $divisor
     *
     * @return self
     */
    public function div(BigDecimal $divisor)
    {
        $scale = min($this->scale + $divisor->scale(), self::MAX_SCALE);

        return new static(bcdiv($this->value, $divisor->value(), $scale), $scale);
    }

    /**
     * pow
     *
     * @param $n
     *
     * @return self
     */
    public function pow($n)
    {
        $n = (int) $n;
        if ($n < 0) {
            throw new \InvalidArgumentException(sprintf('Power "%s" is negative', $n));
        }
        if ($n === 0) {
            return static::one();
        }

        return new static(bcpow($this->value, $n, self::MAX_SCALE));
    }

    public function signum()
    {
        switch ($this->value[0]) {
            case '-':
                return -1;
            case '0':
                return 0;
            default:
                return 1;
        }
    }

    public function negate()
    {
        $value = $this->value;
        switch ($this->signum()) {
            case -1:
                $value[0] = '+';
                break;
            case 1:
                $value = '-'.$value;
                break;
        }

        return new static($value, $this->scale);
    }

    public function abs()
    {
        return $this->signum() < 0 ? $this->negate() : new static($this->value, $this->scale);
    }

    public function setScale($scale = 0, $roundMode = self::ROUND_DOWN)
    {
        // TODO: implement
        throw new \BadMethodCallException('Not implemented');
    }
    
    /**
     * maxScale
     *
     * @param BigDecimal $other
     *
     * @return mixed
     */
    private function maxScale(BigDecimal $other)
    {
        return max($this->scale, $other->scale());
    }
}
