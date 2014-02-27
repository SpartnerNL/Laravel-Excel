## Laravel 4 Wrapper for PHPExcel v0.2.5

#Installation

Require this package in your `composer.json` and update composer. This will download the package and PHPExcel of PHPOffice.
```php
"maatwebsite/excel": "dev-master"
```

Set the minimum stability to dev
```php
"minimum-stability": "dev"
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

Export as Excel2007 by using:
```php
->export('xlsx');
```

#Export from View file

It's possible to export a blade view file to xls or csv. The view file must be a table.
Use `loadView()` with a view file and data to be used inside the view.
```php
Excel::loadView('folder.file', array('key' => 'value'))->export('xls');
```

If you want to give the file and worksheet a name chain `setTitle()` and `sheet()` after the `loadView()`
```php
Excel::loadView('folder.file', array('key' => 'value'))
        ->setTitle('Title')
        ->sheet('SheetName')
        ->export('xls');
```

It possible to use some basic styling inside the table.
HTML tags `<strong>, <i> and <b>` are supported at this moment.

#Store to server

To store the file to the server use `store($extension, $path)` The path is optional, when this is empty, the default setting in the config will be used.
```php
Excel::loadView('folder.file', array('data'))
        ->setTitle('Title')
        ->sheet('SheetName')
        ->store('xls');
```


#Freeze / lock rows and columns

To freeze the first row of the sheet:
```php
->freezeFirstRow()
```
To freeze the first column of the sheet:
```php
->freezeFirstColumn()
```

To freeze the first row and first column of the sheet:
```php
->freezeFirstRowAndColumn()
```

Freeze based on coordinate
```php
->setFreeze('B1')
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

To change the input encoding (default is UTF8), use the third parameter of `load()`
```php
Excel::load('file.csv', false, 'ISO-8859-1')->toArray();
```

The delimiter can be changed right after the file load with `setDelimiter()`. The default delimiter is `,`, which has been set in the config file
```php
Excel::load('file.csv')->setDelimiter(';')->toArray();
```

By default cells with formulas will not be calculated. If you want to calculate them, use the `calculate()` chain. You can change the default inside the config.
```php
Excel::load('file.xls')->calculate()->toArray();
```

By default cells will date/timestamps will be parsed to a PHP date Object and converted to Y-m-d.
You can disable this feature by using `formatDates(false)`
```php
Excel::load('file.xls')->formatDates(false)->toArray();
```

The date format can be changed by using `setDateFormat('Y-m-d')`. You can use all PHP Datetime formats;
```php
Excel::load('file.xls')->setDateFormat('Y-m-d')->toArray();
```

Optionally you can use Carbon to format the date. Use `useCarbon($methodName)`
```php
Excel::load('file.xls')->useCarbon('diffForHumans')->toArray();
```

If you want to limit the data which will be parsed, use `limit()`.
```php
Excel::load('file.csv')->limit(10)->toArray();
```

If you want to output the loaded data to an object, use `toObject()`
```php
Excel::load('file.csv')->toObject();
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

#Cell and range formatting

If you want to format a certain column or range, you can use `setColumnFormat(array())`.
Use the column coordinate or range as array index and use the format code as array value.

Example to get two leading zeros before the number:
```php
->setColumnFormat(array(
    'A2:K2' => '0000'
 )
```

#Auto filter

Setting filters on the heading
```php
->setAutoFilter()
```

#Setting and styling borders

To style and set all borders use:
```php
->setAllBorder('thick')
```

To style the border of a range
```php
->setBorder('A1:F10,'thick')
```

You can use all the PHP Excel border styles.

#Config

Optional settings can be found in the config file. Use the artisan publish command to publish the config file to your project.
```php
php artisan config:publish maatwebsite/excel
```
