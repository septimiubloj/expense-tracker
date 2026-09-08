<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Money;
use InvalidArgumentException;
use OverflowException;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    #[TestWith(['0.29', 2, 29])]
    #[TestWith(['-12.50', 2, -1250])]
    #[TestWith(['12', 0, 12])]
    #[TestWith(['1.234', 3, 1234])]
    #[TestWith(['90071992547409.91', 2, 9007199254740991])]
    public function test_decimal_conversion_is_exact(string $input, int $precision, int $expected): void
    {
        $this->assertSame($expected, Money::parse($input, $precision));
    }

    #[TestWith(['1.001'])]
    #[TestWith(['1e2'])]
    #[TestWith(['1,000.00'])]
    #[TestWith(['NaN'])]
    public function test_invalid_or_overprecise_input_is_rejected(string $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        Money::parse($input);
    }

    public function test_amount_overflow_is_rejected(): void
    {
        $this->expectException(OverflowException::class);
        Money::parse('90071992547409.92');
    }

    public function test_total_overflow_is_rejected(): void
    {
        $this->expectException(OverflowException::class);
        Money::add(Money::MaxMinor, 1);
    }
}
