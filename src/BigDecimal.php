<?php
/*
 * Copyright (c)
 * Kirill chEbba Chebunin <iam@chebba.org>
 * Vladislav Veselinov <vladislav@v3labs.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 */

namespace V3labs\Math;

/**
 * Immutable Arbitrary Precision decimal number.
 * Wrapper for BC Math
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 * @author Vladislav Veselinov <vladislav@v3labs.com>
 */
class BigDecimal
{
    const ROUND_UP          = 1;
    const ROUND_DOWN        = 2;
    const ROUND_CEILING     = 3;
    const ROUND_FLOOR       = 4;
    const ROUND_HALF_UP     = 5;
    const ROUND_HALF_DOWN   = 6;
    const ROUND_HALF_EVEN   = 7;
    const ROUND_HALF_ODD    = 8;
    const ROUND_UNNECESSARY = 9;

    const STRING_FORMAT_REGEX  = '/^([-+])?([0-9]+)(\.([0-9]+))?(E([+-]?[0-9]+))?$/';

    private $value;
    private $scale;

    public function __construct($value, ?int $scale = null)
    {
        if (!is_scalar($value)) {
            throw new \InvalidArgumentException(sprintf('Value of type "%s" is not as scalar', gettype($value)));
        }

        $value = (string) $value;

        if (!preg_match(self::STRING_FORMAT_REGEX, $value, $matches)) {
            throw new \InvalidArgumentException(sprintf('Wrong value "%s" format: expected "%s"', $value, self::STRING_FORMAT_REGEX));
        }

        $sign = $matches[1] === '-' ? '-' : '';
        $integer = ltrim($matches[2], '0') ?: '0';
        $fraction = isset($matches[4]) ? $matches[4] : '';
        $exponent = - strlen($fraction) + (isset($matches[6]) ? (int)$matches[6] : 0);

        $significand = $sign . $integer . $fraction;

        $exponentScale = abs(min($exponent, 0));

        $newValue = bcmul($significand, bcpow(10, $exponent, $exponentScale), $exponentScale);

        list($integer, $fraction) = (array_pad(explode('.', $newValue, 2), 2, ''));

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

        $this->value = $integer . ($scale ? ('.' . $fraction) : '');
        $this->scale = $scale;
    }

    /**
     * @deprecated
     */
    public static function create($value, ?int $scale = null): self
    {
        return new static($value, $scale);
    }

    public static function of($value, ?int $scale = null): self
    {
        return new static($value, $scale);
    }

    public static function ofValues(array $values, $scale = null): array
    {
        return array_map(function($value) use ($scale) { return static::create($value, $scale); }, $values);
    }

    public static function sum(array $values): self
    {
        return array_reduce($values, function(self $left, self $right) { return $left->add($right); }, static::zero());
    }

    public static function avg(array $values, ?int $scale = null): self
    {
        if (!count($values)) {
            return null;
        }

        $avg = static::sum($values)->divide(BigDecimal::of(count($values), $scale));

        return $scale !== null ? $avg->round($scale) : $avg;
    }

    public static function min(array $values): ?self
    {
        return array_reduce($values, function(?self $left, self $right) {
            return $left === null
                ? $right
                : ($left->isLessThan($right)
                    ? $left
                    : $right);
        }, null);
    }

    public static function max(array $values): ?self
    {
        return array_reduce($values, function(?self $left, self $right) {
            return $left === null
                ? $right
                : ($left->isGreaterThan($right)
                    ? $left
                    : $right);
        }, null);
    }

    public static function zero(): self
    {
        return new static(0, 0);
    }

    public function one(): self
    {
        return new static(1, 0);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function scale(): int
    {
        return $this->scale;
    }

    public function setScale(int $scale): self
    {
        return new static($this->value(), $scale);
    }

    public function precision()
    {
        $parts = explode('.', $this->value);

        return strlen(ltrim($parts[0], '-'));
    }

    public function __toString()
    {
        return $this->value();
    }

    public function add(BigDecimal $addend): self
    {
        $scale = max($this->scale(), $addend->scale());
        return new static(bcadd($this->value, $addend->value(), $scale), $scale);
    }

    public function subtract(BigDecimal $subtrahend): self
    {
        $scale = max($this->scale(), $subtrahend->scale());
        return new static(bcsub($this->value, $subtrahend->value(), $scale), $scale);
    }

    public function multiply(BigDecimal $multiplier): self
    {
        $scale = $this->scale + $multiplier->scale();

        return new static(bcmul($this->value, $multiplier->value(), $scale), $scale);
    }

    public function divide(BigDecimal $divisor): self
    {
        if ($divisor->signum() === 0) {
            throw new \InvalidArgumentException('Division by zero');
        }

        $scale = $this->scale + $divisor->scale();

        return new static(bcdiv($this->value, $divisor->value(), $scale), $scale);
    }

    /**
     * @param int $exponent
     *
     * @return static
     * @throws \InvalidArgumentException
     */
    public function pow(int $exponent): self
    {
        $exponent = (int) $exponent;
        if ($exponent < 0) {
            throw new \InvalidArgumentException(sprintf('Power "%s" is negative', $exponent));
        }
        if ($exponent === 0) {
            return static::one();
        }

        return new static(bcpow($this->value, $exponent, $this->scale * $exponent));
    }

    public function signum(): int
    {
        return $this->compareTo(self::zero());
    }

    public function negate(): self
    {
        $value = $this->value;
        switch ($this->signum()) {
            case -1:
                $value = substr($value, 1);
                break;
            case 1:
                $value = '-'. $value;
                break;
        }

        return new static($value, $this->scale);
    }

    public function abs(): self
    {
        return $this->signum() < 0 ? $this->negate() : new static($this->value, $this->scale);
    }

    /**
     * @param int $scale
     * @param int $roundMode
     *
     * @return static
     * @throws \RuntimeException If round mode is UNNECESSARY and digit truncation is required
     */
    public function round(int $scale = 0, int $roundMode = self::ROUND_HALF_UP): self
    {
        if ($scale >= $this->scale) {
            return new static($this->value, $scale);
        }

        // Break string to 2 parts. Ex '123.45678', 3: '123.456' and '78'
        list($newValue, $truncated) = str_split($this->value, strlen($this->value) - ($this->scale - $scale));
        // Remove trailing dot for integer round
        if ($scale === 0) {
            $newValue = substr($newValue, 0, -1);
        }
        // remove extra zeros
        $truncated = rtrim($truncated, '0');

        // Check if truncated digits are zeros, than no rounding required
        if ($truncated === '') {
            return new static($newValue, $scale);
        }

        // If we should not round but got some truncated digits
        if ($roundMode === self::ROUND_UNNECESSARY) {
            throw new \RuntimeException(sprintf('Digits "%s" of "%s" should not be truncated with scale "%d"', $truncated, $this->value, $scale));
        }

        $rounded = new static($newValue, $scale);

        $sign = $this->signum() !== -1;
        if (self::isRoundAdditionRequired($roundMode, $sign, $newValue, $truncated)) {
            // If addition required we add (+/-)1E-{scale}
            $addition = ($sign ? '': '-').'1e-'.$scale;
            $rounded = $rounded->add(new static(number_format($addition, $scale, '.', '')));
        }

        return $rounded;
    }

    private static function isRoundAdditionRequired($roundMode, $sign, $value, $truncated): bool
    {
        switch ($roundMode) {
            case self::ROUND_UP:
                return true;

            case self::ROUND_DOWN:
                return false;

            case self::ROUND_CEILING:
                return $sign;

            case self::ROUND_FLOOR:
                return !$sign;

            case self::ROUND_HALF_UP:
                return $truncated === '5' || $truncated[0] >= 5 ;

            case self::ROUND_HALF_DOWN:
                return !($truncated === '5' || $truncated[0] < 5 );

            case self::ROUND_HALF_EVEN:
                return !($truncated[0] < 5 || ($truncated === '5' && ($value[strlen($value)-1] % 2 === 0)));

            case self::ROUND_HALF_ODD:
                return !($truncated[0] < 5 || $truncated === '5' && ($value[strlen($value)-1] % 2 === 1));
        }

        return false;
    }

    public function compareTo(BigDecimal $number): int
    {
        $scale = max($this->scale(), $number->scale());
        return bccomp($this->value, $number->value(), $scale);
    }

    public function isEqualTo(BigDecimal $number): bool
    {
        return $this->compareTo($number) == 0;
    }

    public function isGreaterThan(BigDecimal $number): bool
    {
        return $this->compareTo($number) == 1;
    }

    public function isGreaterThanOrEqualTo(BigDecimal $number): bool
    {
        return $this->compareTo($number) >= 0;
    }

    public function isLessThan(BigDecimal $number): bool
    {
        return $this->compareTo($number) == -1;
    }

    public function isLessThanOrEqualTo(BigDecimal $number): bool
    {
        return $this->compareTo($number) <= 0;
    }

    public function isNegative(): bool
    {
        return $this->isLessThan(static::zero());
    }

    public function isPositive(): bool
    {
        return $this->isGreaterThan(static::zero());
    }
}
