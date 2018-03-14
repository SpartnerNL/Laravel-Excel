# Storing exports on disk

Exports can easily be stored on any [filesystem](https://laravel.com/docs/5.6/filesystem) that Laravel supports.

```php
public function storeExcel() 
{
    // Store on default disk
    Excel::store(new InvoicesExport(2018), 'invoices.xlsx');
    
    // Store on a different disk (e.g. s3)
    Excel::store(new InvoicesExport(2018), 'invoices.xlsx', 's3');
    
    // Store on a different disk with a defined writer type. 
    Excel::store(new InvoicesExport(2018), 'invoices.xlsx', 's3', Excel::XLSX);
}
```