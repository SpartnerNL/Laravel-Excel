# Basics

The easiest way to start an export is to create a custom export class. We'll use an invoices export as example.

Create a new class called `InvoicesExport` in `App/Exports`:

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

In your controller we can now download this export:

```php
public function export() 
{
    return Excel::download(new InvoicesExport, 'invoices.xlsx');
}
```

Or store it on a (e.g. s3) disk:

```php
public function storeExcel() 
{
    return Excel::store(new InvoicesExport, 'invoices.xlsx', 's3');
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

### Collection macros

The package provides some macro to Laravel's collection class to easily download or store a collection.

#### Downloading a collection as Excel

```php
(new Collection([[1, 2, 3], [1, 2, 3]))->downloadExcel($filePath, $writerType = null)
```

#### Storing a collection on disk

```php
(new Collection([[1, 2, 3], [1, 2, 3]))->storeExcel($filePath, $disk = null, $writerType = null)
```
