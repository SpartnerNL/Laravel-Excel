<p align="center">
  <a href="https://laravel-excel.maatwebsite.nl">
    <img alt="Laravel Excel" src="https://user-images.githubusercontent.com/7728097/43685313-ff1e2110-98b0-11e8-8b50-900a2b262f0f.png" />
  </a>
</p>

<h1 align="center">
  Laravel Excel 3.1
</h1>

<h3 align="center">
  :muscle: :fire: :rocket:
</h3>

<p align="center">
  <strong>Supercharged Excel exports and imports</strong><br>
  A simple, but elegant wrapper around <a href="https://phpspreadsheet.readthedocs.io/">PhpSpreadsheet</a> with the goal of simplifying
exports and imports. 
</p>

<p align="center">
  <a href="https://travis-ci.org/Maatwebsite/Laravel-Excel">
    <img src="https://travis-ci.org/Maatwebsite/Laravel-Excel.svg?branch=3.1" alt="Build Status">
  </a> 
  
  <a href="https://styleci.io/repos/14259390">
    <img src="https://styleci.io/repos/14259390/shield?branch=3.1" alt="StyleCI">
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
  <a href="https://laravel-excel.maatwebsite.nl/3.1/exports/">Quickstart</a>
  <span> · </span>
  <a href="https://laravel-excel.maatwebsite.nl/3.1/getting-started/">Documentation</a>
  <span> · </span>
  <a href="https://github.com/Maatwebsite/Laravel-Nova-Excel">Nova</a>
  <span> · </span>
  <a href="https://medium.com/maatwebsite/laravel-excel/home">Blog</a>
  <span> · </span>
  <a href="https://laravel-excel.maatwebsite.nl/3.1/getting-started/contributing.html">Contributing</a>
  <span> · </span>
  <a href="https://laravel-excel.maatwebsite.nl/3.1/getting-started/support.html">Support</a>
</h4>

- **Easily export collections to Excel.** Supercharge your Laravel collections and export them directly to an Excel or CSV document. Exporting has never been so easy.

- **Supercharged exports.** Export queries with automatic chunking for better performance. You provide us the query, we handle the performance. Exporting even larger datasets? No worries, Laravel Excel has your back. You can queue your exports so all of this happens in the background.

- **Supercharged imports.** Import workbooks and worksheets to Eloquent models with chunk reading and batch inserts! Have large files? You can queue every chunk of a file! Your entire import will happen in the background.

- **Export Blade views.** Want to have a custom layout in your spreadsheet? Use a HTML table in a Blade view and export that to Excel.

## :rocket: 5 minutes quick start for exports

:bulb: Require this package in the `composer.json` of your Laravel project. This will download the package and PhpSpreadsheet.

```
composer require maatwebsite/excel
```

:muscle: Create an export class in `App/Exports`

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

use App\Exports\UsersExport;
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

More installation instructions can be found at: [https://laravel-excel.maatwebsite.nl/3.1/getting-started/installation.html](https://laravel-excel.maatwebsite.nl/3.1/getting-started/installation.html)

## :rocket: 5 minutes quick start for imports

:muscle: Create an import class in `App\Imports`

You may do this by using the `make:import` command.

```
php artisan make:import UsersImport --model=User
```

If you prefer to create the import manually, you can create the following in `App\Imports`:

```php
<?php

namespace App\Imports;

use App\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;

class UsersImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return User|null
     */
    public function model(array $row)
    {
        return new User([
           'name'     => $row[0],
           'email'    => $row[1], 
           'password' => Hash::make($row[2]),
        ]);
    }
}
```

:fire: In your controller you can call this import now:

```php

use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;

class UsersController extends Controller 
{
    public function import() 
    {
        Excel::import(new UsersImport, 'users.xlsx');
        
        return redirect('/')->with('success', 'All good!');
    }
}
```

:page_facing_up: Find the imported users in your database!

## 🎓 Learning Laravel Excel

You can find the full documentation of Laravel Excel [on the website](https://laravel-excel.maatwebsite.nl/).

We welcome suggestions for improving our docs. The documentation repository can be found at [https://github.com/Maatwebsite/laravel-excel-docs](https://github.com/Maatwebsite/laravel-excel-docs).

Read some articles and tutorials can be found on our blog: https://laravel-excel.maatwebsite.nl/blog/

## :wrench: Supported Versions

Versions will be supported for a limited amount of time.

| Version | Laravel Version | Php Version | Support |
|---- |----|----|----|
| 2.1 | <=5.6 | <=7.0 | Unsupported since 15-5-2018 |
| 3.0 | ^5.5 |  ^7.0 | Security fixes till 31-12-2018 |
| 3.1 | ^5.5 |  ^7.0 | New features |

## :mailbox_with_mail: License & Postcardware

Our software is open source and licensed under the MIT license.

If you use the software in your production environment we would appreciate to receive a postcard of your hometown. Please send it to:

**Maatwebsite**  
Florijnruwe 111-2  
6218 CA Maastricht  
The Netherlands  

More about the license can be found at: [https://laravel-excel.maatwebsite.nl/3.1/getting-started/license.html](https://laravel-excel.maatwebsite.nl/3.1/getting-started/license.html)
