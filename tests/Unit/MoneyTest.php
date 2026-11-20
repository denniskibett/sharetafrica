<?php

namespace Tests\Unit;

use App\Ledger\LedgerException;
use App\Ledger\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_amounts_parse_to_exact_cents(): void
    {
        $this->assertSame(100000, Money::cents('1,000'));
        $this->assertSame(100050, Money::cents('1,000.5'));
        $this->assertSame(100050, Money::cents('1000.50'));
        $this->assertSame(-2000, Money::cents('-20'));
        $this->assertSame(1, Money::cents('0.01'));
        $this->assertSame(500, Money::cents(5));
        $this->assertSame(123456789012, Money::cents('1,234,567,890.12'));
    }

    public function test_bad_amounts_are_refused(): void
    {
        foreach (['1.005', 'abc', '1,00', '', '1e3', '10.', '--1', '1,000,00.00'] as $bad) {
            try {
                Money::cents($bad);
                $this->fail("Accepted {$bad}");
            } catch (LedgerException $e) {
                $this->assertSame('invalid_amount', $e->reason);
            }
        }
    }

    public function test_formatting_always_shows_two_decimals(): void
    {
        $this->assertSame('1,000.00', Money::format(100000));
        $this->assertSame('0.05', Money::format(5));
        $this->assertSame('-1,234.50', Money::format(-123450));
        $this->assertSame('1234.50', Money::plain(123450));
    }

    public function test_percentages_round_half_away_from_zero_once(): void
    {
        $this->assertSame(1478, Money::percent(98500, 150));  // 1.5% of 985.00 = 14.775 → 14.78
        $this->assertSame(222, Money::percent(1478, 1500));   // 15% excise of 14.78 = 2.217 → 2.22
        $this->assertSame(-1478, Money::percent(-98500, 150));
        $this->assertSame(0, Money::percent(3, 150));
        $this->assertSame(2, Money::divide(5, 3));
        $this->assertSame(1, Money::divide(4, 3));
    }

    public function test_whole_shillings_split_off_the_cents(): void
    {
        $this->assertSame([100200, 0], Money::wholeShillings(100200));
        $this->assertSame([100100, 99], Money::wholeShillings(100199));
    }
}
