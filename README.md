## Laravel 4 Wrapper for PHPExcel

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

To import CSV data:
```php
Excel::load('file.csv')->toArray();
```

Optionally you can select columns (these are momentarily based on the first row / heading).
An empty `select()`, or no select at all, means we will return all columns
```php
Excel::load('file.csv')->select(array('column1', 'column4'))->toArray();
```

The delimiter can be changed before the select chain. The default delimiter is `,`.

```php
Excel::load('file.csv')->setDelimiter(';')->toArray();
```

For developping purposes you can choose to dump the returned parsed file to a readable array;
```php
Excel::load('file.csv')->dump();
```

Optional settings can be found in the config file. Use the artisan publish command to publish the config file to your project.
```php
php artisan config:publish Maatwebsite/excel
```