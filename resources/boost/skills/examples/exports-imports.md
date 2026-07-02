# Laravel Excel Examples

These examples are representative patterns. For complete feature coverage, use `../references/package.md`.

## Collection Export

```php
<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return User::query()->latest()->get();
    }

    public function headings(): array
    {
        return ['Name', 'Email', 'Created At'];
    }

    public function map($user): array
    {
        return [
            $user->name,
            $user->email,
            $user->created_at?->toDateTimeString(),
        ];
    }
}
```

```php
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;

return Excel::download(new UsersExport, 'users.xlsx');
```

## Queued Query Export

```php
<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromQuery, ShouldQueue, WithCustomChunkSize, WithHeadings, WithMapping
{
    public function query(): Builder
    {
        return Order::query()->with('customer')->whereNotNull('paid_at');
    }

    public function headings(): array
    {
        return ['Order', 'Customer', 'Total', 'Paid At'];
    }

    public function map($order): array
    {
        return [
            $order->number,
            $order->customer->name,
            $order->total,
            $order->paid_at?->toDateString(),
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
```

```php
Excel::queue(new OrdersExport, 'exports/orders.xlsx', 's3');
```

## Multi-Sheet Export

```php
<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReportsWorkbookExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'users' => new UsersExport,
            'orders' => new OrdersExport,
        ];
    }
}
```

## Styling, Events, And CSV Settings

```php
<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RevenueExport implements FromArray, ShouldAutoSize, WithColumnFormatting, WithCustomCsvSettings, WithEvents, WithStyles
{
    public function array(): array
    {
        return [
            ['Month', 'Revenue'],
            ['January', 12345.67],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE,
        ];
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',
            'use_bom' => true,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $event->sheet->freezePane('A2');
            },
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
```

## Validated Chunked Import

```php
<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class UsersImport implements SkipsOnFailure, ToModel, WithBatchInserts, WithChunkReading, WithHeadingRow, WithValidation
{
    use Importable;
    use SkipsFailures;

    public function model(array $row): User
    {
        return new User([
            'name' => $row['name'],
            'email' => $row['email'],
        ]);
    }

    public function rules(): array
    {
        return [
            '*.name' => ['required', 'string', 'max:255'],
            '*.email' => ['required', 'email', 'distinct'],
        ];
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
```

```php
$import = new UsersImport;

$import->import($request->file('users'));

if ($import->failures()->isNotEmpty()) {
    // Return validation feedback to the user.
}
```

## Mapped Cells Import

```php
<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithMappedCells;

class InvoiceImport implements ToArray, WithMappedCells
{
    public function mapping(): array
    {
        return [
            'invoice_number' => 'B2',
            'customer_name' => 'B3',
            'total' => 'E20',
        ];
    }

    public function array(array $array): void
    {
        // Handle mapped values.
    }
}
```

## Testing With The Fake

```php
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;

Excel::fake();

$this->get('/users/export')->assertOk();

Excel::assertDownloaded('users.xlsx', function (UsersExport $export): bool {
    return true;
});
```
