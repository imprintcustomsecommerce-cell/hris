<?php

namespace Tests\Unit;

use App\Support\PayrollCalculator;
use PHPUnit\Framework\TestCase;

class CalculatorsTest extends TestCase
{
    public function test_statutory_deductions_for_20k_salary(): void
    {
        $d = PayrollCalculator::deductions(20000);

        $this->assertEqualsWithDelta(900, $d['sss'], 0.01);        // 20000 * 4.5%
        $this->assertEqualsWithDelta(500, $d['philhealth'], 0.01); // 20000 * 2.5%
        $this->assertEqualsWithDelta(200, $d['pagibig'], 0.01);    // min(20000,10000) * 2%
        $this->assertGreaterThanOrEqual(0, $d['tax']);
    }

    public function test_sss_is_capped_at_msc_ceiling(): void
    {
        // Basic above the 30k MSC ceiling caps SSS at 30000 * 4.5% = 1350.
        $d = PayrollCalculator::deductions(80000);
        $this->assertEqualsWithDelta(1350, $d['sss'], 0.01);
    }
}
