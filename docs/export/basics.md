# Basics

The easiest way to start an export is by creating a custom export class. We'll use an invoices export as example.

Create a new class called `InvoicesExport` in `app/Exports`:

```php
namespace App\Exports;

class InvoicesExport implements FromCollection
{
    public function collection()
    {
        return Invoice::all();
    }
}
```

In your controller we can now download this export.

```php
public function export() 
{
    return Excel::download(new InvoicesExport, 'invoices.xlsx');
}
```

### Dependency injection

In case your export needs dependencies, you can inject the export class:

```php
namespace App\Exports;

class InvoicesExport implements FromCollection
{
    public function __construct(InvoicesRepository $invoices)
    {
        $this->invoices = $invoices;
    }

    public function collection()
    {
        return $this->invoices->all();
    }
}
```

```php
public function export(Excel $excel, InvoicesExport $export) 
{
    return $excel->download($export, 'invoices.xlsx');
}
```