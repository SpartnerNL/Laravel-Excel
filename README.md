## Laravel Excel v1.0.0

[<img src="http://www.maatwebsite.nl/img/excel_banner.jpg"/>](http://www.maatwebsite.nl/laravel-excel/docs)

#### Laravel Excel brings the power of PHPExcel to Laravel 4 with a touch of the Laravel Magic: 

- Import into Laravel **Collections**
- Export **Blade views** to Excel and CSV
- **Batch** imports
- A lot of optional **config settings**
- Easy **cell caching**
- **Advanced import** by config files
- and many more...


```php
Excel::create('Laravel Excel', function($excel) {

    $excel->sheet('Excel sheet', function($sheet) {
  
        $sheet->setOrientation('landscape');
    
    });
  
})->export('xls');
```

---

[![Build Status](https://travis-ci.org/Maatwebsite/laravel4-PHPExcel.svg?branch=develop)](https://travis-ci.org/Maatwebsite/laravel4-PHPExcel)
[![Latest Stable Version](https://poser.pugx.org/maatwebsite/excel/v/stable.png)](https://packagist.org/packages/maatwebsite/excel) [![Total Downloads](https://poser.pugx.org/maatwebsite/excel/downloads.png)](https://packagist.org/packages/maatwebsite/excel)  [![License](https://poser.pugx.org/maatwebsite/excel/license.png)](https://packagist.org/packages/maatwebsite/excel)
[![Monthly Downloads](https://poser.pugx.org/maatwebsite/excel/d/monthly.png)](https://packagist.org/packages/maatwebsite/excel)
[![Daily Downloads](https://poser.pugx.org/maatwebsite/excel/d/daily.png)](https://packagist.org/packages/maatwebsite/excel)

#Installation

Require this package in your `composer.json` and update composer. This will download the package and PHPExcel of PHPOffice.

```php
"maatwebsite/excel": "1.*"
```

After updating composer, add the ServiceProvider to the providers array in `app/config/app.php`

```php
'Maatwebsite\Excel\ExcelServiceProvider',
```

You can use the facade for shorter code. Add this to your aliasses:

```php
'Excel' => 'Maatwebsite\Excel\Facades\Excel',
```

The class is binded to the ioC as `excel`

```php
$excel = App::make('excel');
```

# Documentation

The complete documentation can be found at: [http://www.maatwebsite.nl/laravel-excel/docs](http://www.maatwebsite.nl/laravel-excel/docs)


# License

This package is licensed under LGPL. You are free to use it in personal and commercial projects. The code can be forked and modified, but the original copyright author should always be included!
