<p align="center">
  <a href="https://laravel-excel.maatwebsite.nl">
    <img alt="Laravel Excel" src="https://user-images.githubusercontent.com/7728097/43685313-ff1e2110-98b0-11e8-8b50-900a2b262f0f.png" />
  </a>
</p>

<h1 align="center">
  Laravel Excel 3.0
</h1>

<h3 align="center">
  :muscle: :fire: :rocket:
</h3>

<p align="center">
  <strong>Supercharged Excel exports</strong><br>
  A simple, but elegant wrapper around <a href="https://phpspreadsheet.readthedocs.io/">PhpSpreadsheet</a> with the goal of simplifying
exports. 
</p>

<p align="center">
  <a href="https://travis-ci.org/Maatwebsite/Laravel-Excel">
    <img src="https://travis-ci.org/Maatwebsite/Laravel-Excel.svg?branch=3.0" alt="Build Status">
  </a> 
  
  <a href="https://styleci.io/repos/14259390">
    <img src="https://styleci.io/repos/14259390/shield?branch=3.0" alt="StyleCI">
  </a> 
  
   <a href="https://packagist.org/packages/maatwebsite/excel">
      <img src="https://poser.pugx.org/maatwebsite/excel/v/stable.png" alt="Latest Stable Version">
  </a> 
  
  <a href="https://packagist.org/packages/maatwebsite/excel">
      <img src="https://poser.pugx.org/maatwebsite/excel/downloads.png" alt="Total Downloads">
  </a> 
  
  <a href="https://packagist.org/packages/maatwebsite/excel">
    <img src="https://poser.pugx.org/maatwebsite/excel/license.png" alt="License">
  </a>
</p>

<h4 align="center">
  <a href="https://laravel-excel.maatwebsite.nl/3.0/exports/">Quickstart</a>
  <span> · </span>
  <a href="https://laravel-excel.maatwebsite.nl/3.0/getting-started/">Documentation</a>
  <span> · </span>
  <a href="https://medium.com/@maatwebsite/laravel-excel-lessons-learned-7fee2812551">Blog</a>
  <span> · </span>
  <a href="https://laravel-excel.maatwebsite.nl/3.0/getting-started/contributing.html">Contributing</a>
  <span> · </span>
  <a href="https://laravel-excel.maatwebsite.nl/3.0/getting-started/support.html">Support</a>
</h4>

- **Easily export collections to Excel.** Supercharge your Laravel collections and export them directly to an Excel or CSV document. Exporting has never been so easy.

- **Supercharged exports.** Export queries with automatic chunking for better peformance. You provide us the query, we handle the performance. Exporting even larger datasets? No worries, Laravel Excel has your back. You can queue your exports so all of this happens in the background.

- **Export blade views.** Want to have a custom layout in your spreadsheet? Use a HTML table in a blade view and export that to Excel.

## :rocket: 5 minutes quick start

:bulb: Require this package in the `composer.json` of your Laravel project. This will download the package and PhpSpreadsheet.

```
composer require maatwebsite/excel
```

:muscle: Create an export class in `app/Exports`

```
php artisan make:export UsersExport --model=User
```

This should have created:

```php
<?php

namespace App\Exports;

use App\User;
use Maatwebsite\Excel\Concerns\FromCollection;

class UsersExport implements FromCollection
{
    public function collection()
    {
        return User::all();
    }
}
```

:fire: In your controller you can call this export now:

```php

use App\UsersExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;

class UsersController extends Controller 
{
    public function export() 
    {
        return Excel::download(new UsersExport, 'users.xlsx');
    }
}
```

:page_facing_up: Find your `users.xlsx` in your downloads folder!

More installation instructions can be found at: [https://laravel-excel.maatwebsite.nl/3.0/getting-started/installation.html](https://laravel-excel.maatwebsite.nl/3.0/getting-started/installation.html)

## 🎓 Learning Laravel Excel

You can find the full documentation of Laravel Excel [on the website](https://laravel-excel.maatwebsite.nl/).

We welcome suggestions for improving our docs. The documentation repository can be found at [https://github.com/Maatwebsite/laravel-excel-docs](https://github.com/Maatwebsite/laravel-excel-docs).

## :wrench: Supported Versions

Versions will be supported for a limited amount of time.

| Version | Laravel Version | Php Version | Support |
|---- |----|----|----|
| 2.1 | <=5.6 | <=7.0 | EOL on 15-5-2018 |
| 3.0 | ^5.5 |  ^7.0 | New features |

## :mag_right: Roadmap

Imports are currently not supported by 3.0. This functionality will be re-added in 3.1. There's currently no ETA on this release. 

## :mailbox_with_mail: License & Postcardware

Our software is open source and licensed under the MIT license.

If you use the software in your production environment we would appreciate to receive a postcard of your hometown. Please send it to:

**Maatwebsite**  
Florijnruwe 111-2  
6218 CA Maastricht  
The Netherlands  

More about the license can be found at: [https://laravel-excel.maatwebsite.nl/3.0/getting-started/license.html](https://laravel-excel.maatwebsite.nl/3.0/getting-started/license.html)

## Imports (beta)

### ToArray

```php
Excel::import(new UsersImport, 'users.xlxs')->toArray();
Excel::import(new UsersImport, 'users.xlxs')->toCollection();

// using "use Importable"
(new UsersImport)->toArray('users.xlsx');
```

### ToCollection

```php
class UsersImport implements ToCollection {

    public function collection(Collection $sheets)
    {
        foreach ($sheets as $sheet)
        {
            foreach ($sheet as $row)
            {
                User::create([
                    'name' => $row[0],
                ]);
            }
        }
    }
}
```

### OnRow

Handle each row however wanted. (In case of no Eloquent e.g.)

```php
class UsersImport implements OnRow {

    public function onRow(array $row): Model
    {
        User::create([
            'name' => $row[0],
        ]);
    }
}
```

### ToModel

One save per row, keeps model events

```php
class UsersImport implements ToModel {

    public function model(array $row): Model
    {
        return new User([
            'name' => $row[0],
        ]);
    }
}
```

### ToModel

Will inserts in batch, no model events

```php
class UsersImport implements ToModel, WithBatchInserts {

    public function model(array $row): Model
    {
        return new User([
            'name' => $row[0],
        ]);
    }
    
    public function batchSize(): int 
    {
        return 1000;
    }
}
```

### WithChunkReading

Will Read data in chunks, but insert each row separate in the db (with model events)

```php
class UsersImport implements ToModel, WithChunkReading {

    public function model(array $row): Model
    {
        return new User([
            'name' => $row[0],
        ]);
    }
    
    public function chunkSize(): int
    {
        return 1000;
    }
    
    public function batchSize(): int 
        {
            return 1000;
        }
}
```

### WithChunkReading and WithBatchInserts

Will insert the chunks in batches (no model events)

```php
class UsersImport implements ToModel, WithChunkReading, WithBatchInserts {

    public function model(array $row): Model
    {
        return new User([
            'name' => $row[0],
        ]);
    }
    
    public function chunkSize(): int
    {
        return 1000;
    }
}
```

### ShouldQueue with ToModel

Will queue each model insert (keeps model events)

```php
class UsersImport implements ToModel, ShouldQueue {

    public function model(array $row): Model
    {
        return new User([
            'name' => $row[0],
        ]);
    }
}
```

### ShouldQueue with ToModel and WithChunkReading

Will queue each chunk and then queue each model insert (with model events)

```php
class UsersImport implements ToModel, WithChunkReading, ShouldQueue {

    public function model(array $row): Model
    {
        return new User([
            'name' => $row[0],
        ]);
    }
    
    ...
}
```

### ShouldQueue with ToModel and WithBatchInserts

Will queue each batch insert (no model events)

```php
class UsersImport implements ToModel, WithBatchInserts, ShouldQueue {

    public function model(array $row): Model
    {
        return new User([
            'name' => $row[0],
        ]);
    }
    
    ...
}
```

### WithMappedCells

```php
class UsersImport implements ToModel, WithMappedCells {

    public function mapping(): Model
    {
        return [
            'name' => 'A1',
        ];
    }
    
    public function handle(array $sheet)
    {
        User::create([
            'name' => $sheet['name'],
        ]);
        
        // or even
        User::create($sheet);
    }
}
```

### WithCustomValueBinder

// Implement a value binder (with fallback to the default if you want!)

```php
class UsersImport extends DefaultValueBinder implements WithCustomValueBinder {
    
    public function bindValue(Cell $cell, $value)
    {
        if (is_numeric($value))
        {
            $cell->setValueExplicit($value, PHPExcel_Cell_DataType::TYPE_NUMERIC);

            return true;
        }
        
        // Else return default behavior
        return parent::bindValue($cell, $value);
    }
}
```

Use the advanced value binder

```php
class UsersImport extends AdvancedValueBinder implements WithCustomValueBinder {
    
    
}
```

Share value binders along imports (with traits or class extensions if you want).

```php
class UsersImport implements WithCustomValueBinder {
    
    use YourValueBinder;
    
}
```

### WithCalculatedFormulas

Will calculate formulas. (Disabled by default)

```php
class UsersImport implements WithCalculatedFormulas 
{
    
}
```

### WithMultipleSheets

Handle multiple sheets. Note that the array is indexed, so first object will handle first sheet, etc.

```php
class UsersImport implements WithMultipleSheets 
{
    public function sheets(): array
    {
        return [
            new FirstSheetImport(),
            new SecondSheetImport()
        ];
    }
}
```

Only use certain sheets. Select them by index or name.

```php
class UsersImport implements WithMultipleSheets 
{
    public function sheets(): array
    {
        return [
            0                  => new FirstSheetImport(), // by index
            'Third Sheet Name' => new ThirdSheetImport() // by name
        ];
    }
}
```

### Chaining exports

```php
Excel::import(new Usersimport, 'users.xlsx)->import(new InvoicesImport, 'invoices.xlsx');
```