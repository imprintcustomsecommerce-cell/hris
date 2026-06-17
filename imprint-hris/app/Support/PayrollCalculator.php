<?php

namespace App\Support;

class PayrollCalculator
{
    /**
     * Compute Philippine statutory contributions and withholding tax
     * from a monthly basic salary. Rates are approximate current values
     * and can be tuned in one place here.
     *
     * @return array{sss:float, philhealth:float, pagibig:float, tax:float}
     */
    public static function deductions(float $basic): array
    {
        $sss = self::sss($basic);
        $philhealth = self::philhealth($basic);
        $pagibig = self::pagibig($basic);

        $taxable = max(0, $basic - $sss - $philhealth - $pagibig);
        $tax = self::withholdingTax($taxable);

        return [
            'sss' => round($sss, 2),
            'philhealth' => round($philhealth, 2),
            'pagibig' => round($pagibig, 2),
            'tax' => round($tax, 2),
        ];
    }

    /** SSS employee share: 4.5% of MSC (₱4,000–₱30,000). */
    protected static function sss(float $basic): float
    {
        $msc = min(max($basic, 4000), 30000);
        return $msc * 0.045;
    }

    /** PhilHealth: 5% premium split 50/50 → 2.5% employee (floor 10k, ceiling 100k). */
    protected static function philhealth(float $basic): float
    {
        $base = min(max($basic, 10000), 100000);
        return $base * 0.025;
    }

    /** Pag-IBIG: 1% if ≤₱1,500 else 2%, on max ₱10,000 (so ≤₱200). */
    protected static function pagibig(float $basic): float
    {
        $base = min($basic, 10000);
        $rate = $basic <= 1500 ? 0.01 : 0.02;
        return $base * $rate;
    }

    /** Monthly withholding tax (TRAIN law, 2023 onward). */
    protected static function withholdingTax(float $taxable): float
    {
        return match (true) {
            $taxable <= 20833 => 0,
            $taxable <= 33332 => ($taxable - 20833) * 0.15,
            $taxable <= 66666 => 1875 + ($taxable - 33333) * 0.20,
            $taxable <= 166666 => 8541.80 + ($taxable - 66667) * 0.25,
            $taxable <= 666666 => 33541.80 + ($taxable - 166667) * 0.30,
            default => 183541.80 + ($taxable - 666667) * 0.35,
        };
    }
}
