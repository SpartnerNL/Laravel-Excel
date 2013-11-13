## Laravel 4 Wrapper for PHPExcel

    Warning: This package is still in development!

#Installation

Require this package in your `composer.json` and update composer. This will download the package and PHPExcel of PHPOffice.
```php
"maatwebsite/excel": "dev-master"
```

After updating composer, add the ServiceProvider to the providers array in `app/config/app.php`
```php
'Maatwebsite\Excel\ExcelServiceProvider',
```

You can use the facade for shorter code. Add this to your aliasses:
```php
'Excel' => 'Maatwebsite\Excel\Facades\Excel',
```

#Exporting

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

#Export from View file

It's possible to export a blade view file to xls or csv. The view file must be a table.
Use `loadView()` with a view file and data to be used inside the view.
```php
Excel::loadView('folder.file', array('data'))->export('xls');
```

If you want to give the file and worksheet a name chain `setTitle()` and `sheet()` after the `loadView()`
```php
Excel::loadView('folder.file', array('data'))
        ->setTitle('Title')
        ->sheet('SheetName')
        ->export('xls');
```

#Importing

To import CSV data:
```php
Excel::load('file.csv')->toArray();
```

Optionally you can select columns, by there column index.
An empty `select()`, or no select at all, means we will return all columns
```php
Excel::load('file.csv')->select(array(1, 2))->toArray();
```

If the first row is the table heading, you can give the `load()` method an extra parameter. This will make sure the first row is interpreted as heading. These seperate columns values will be used as array indexes. Now you can select columns by their name. Note that the string will be lowercase and spaces will be replaced by `-`.
```php
Excel::load('file.csv', true)->select(array('column1', 'column2'))->toArray();
```

The delimiter can be changed right after the file load with `setDelimiter()`. The default delimiter is `,`, which has been set in the config file
```php
Excel::load('file.csv')->setDelimiter(';')->toArray();
```

By default cells with formulas will not be calculated. If you want to calculate them, use the `calculate()` chain. You can change the default inside the config.
```php
Excel::load('file.xls')->calculate()->toArray();
```

If you want to limit the data which will be parsed, use `limit()`.
```php
Excel::load('file.csv')->limit(10)->toArray();
```

For developping purposes you can choose to dump the returned parsed file to a readable array:
```php
Excel::load('file.csv')->dump();
```

#Converting

To convert from one filetype to another, use `convert()`:
```php
return Excel::load('file.csv')->convert('xls');
```

#Config

Optional settings can be found in the config file. Use the artisan publish command to publish the config file to your project.
```php
php artisan config:publish Maatwebsite/excel
```
