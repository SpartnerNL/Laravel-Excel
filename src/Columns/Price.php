<?php

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Price extends Column
{
    protected $type   = DataType::TYPE_NUMERIC;
    protected $format = NumberFormat::FORMAT_NUMBER;

    /**
     * @param string $currency
     *
     * @return $this
     */
    public function currency(string $currency)
    {
        return $this->format($currency);
    }

    /**
     * @return $this
     */
    public function inEuros()
    {
        return $this->currency(NumberFormat::FORMAT_ACCOUNTING_EUR);
    }

    /**
     * @return $this
     */
    public function inDollars()
    {
        return $this->currency(NumberFormat::FORMAT_ACCOUNTING_USD);
    }
}
