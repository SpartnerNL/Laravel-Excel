## PHPExcel Wrapper for Laravel 4

Warning: This package is still in development!

Require this package in your `composer.json` and update composer. This will download the package and PHPExcel of PHPOffice.
```php
    "maatwebsite/excel": "dev-master"
```

After updating composer, add the ServiceProvider to the providers array in `app/config/app.php`
```php
    'Maatwebsite\Excel\ExcelServiceProvider',
```

You can use the facade for shorter code. Add this to your facades:
```php
    'Excel' => 'Maatwebsite\Excel\Facades\Excel',
```

For creating an Excel file use:
```php
    Excel::create('ExcelName')
            ->sheet('SheetName')
                ->with(array('data', 'data'))
            ->export('xls');
```

Multiple sheets are allowed
```php
    Excel::create('ExcelName')
            ->sheet('SheetName')
                ->with(array('data', 'data'))
            ->sheet('SheetName')
                ->with(array('data', 'data'))
            ->export('xls');
```

Export as CSV by using:
```php
    ->export('csv');
```
