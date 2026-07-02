# Laravel Excel Examples

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

## Query Export For Large Data

```php
<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromQuery, ShouldQueue, WithHeadings, WithMapping
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
}
```

```php
Excel::queue(new OrdersExport, 'exports/orders.xlsx', 's3');
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
