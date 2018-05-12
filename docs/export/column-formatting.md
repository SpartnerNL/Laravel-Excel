# Formatting columns

You can easily format an entire column, by using `WithColumnFormatting`.
In case you want something more custom, it's suggested to use the `AfterSheet` event to directly interact with the underlying `Worksheet` class.

```php
namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithMapping;

class InvoicesExport implements WithColumnFormatting, WithMapping
{
    public function map($invoice): array
    {
        return [
            $invoice->invoice_number,
            Date::dateTimeToExcel($invoice->created_at),
            $invoice->total
        ];
    }
    
    /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'C' => NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
        ];
    }
}
```

### Dates

When working with dates, it's recommended to use `\PhpOffice\PhpSpreadsheet\Shared\Date::dateTimeToExcel()` in your mapping
to ensure correct parsing of dates.

### Auto size

If you want Laravel Excel to perform an automatic width calculation, use the following code. 

```php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class InvoicesExport implements ShouldAutoSize
{
    ...
}
```
