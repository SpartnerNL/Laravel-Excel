<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Columns;

use Maatwebsite\Excel\Columns\Price;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

final class PriceColumnTest extends BaseColumnTestCase
{
    public function test_writes_with_the_default_number_format(): void
    {
        $this->write(Price::make('Price'), [
            'price' => 10.5,
        ]);

        $this->assertCellDataType(DataType::TYPE_NUMERIC);
        $this->assertNumberFormat(NumberFormat::FORMAT_NUMBER);
    }

    public function test_currency_sets_a_custom_number_format(): void
    {
        $this->write(Price::make('Price')->currency('#,##0.00 "USD"'), [
            'price' => 10.5,
        ]);

        $this->assertNumberFormat('#,##0.00 "USD"');
    }

    public function test_in_euros_uses_the_accounting_eur_format(): void
    {
        $this->write(Price::make('Price')->inEuros(), [
            'price' => 10.5,
        ]);

        $this->assertNumberFormat(NumberFormat::FORMAT_ACCOUNTING_EUR);
    }

    public function test_in_dollars_uses_the_accounting_usd_format(): void
    {
        $this->write(Price::make('Price')->inDollars(), [
            'price' => 10.5,
        ]);

        $this->assertNumberFormat(NumberFormat::FORMAT_ACCOUNTING_USD);
    }
}
