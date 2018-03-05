# Exportables

In the previous example, we used the `Excel::download` facade to start an export. 

Laravel-Excel also provides a `Maatwebsite\Excel\Concerns\Exportable` trait, to make export classes exportable.

```php
namespace App\Exports;

class InvoicesExport implements FromCollection
{
    use Exportable;

    public function collection()
    {
        return Invoice::all();
    }
}
```

We can now download the export without the need for the facade:

```php
return (new InvoicesExport)->download('invoices.xlsx');
```

### Responsable

The previous example can be made even shorter. Add Laravel's `Responsable` interface to the export class.

```php
namespace App\Exports;

class InvoicesExport implements FromCollection, Responsable
{
    use Exportable;
    
    /**
    * It's required to define the fileName within
    * the export class when making use of Responsable.
    */
    private $fileName = 'invoices.xlsx';

    public function collection()
    {
        return Invoice::all();
    }
}
```

You can now easily return the export class, without the need of calling `->download()`

```php
return new InvoicesExport();
```