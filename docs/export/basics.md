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