# Installation

Require this package in the `composer.json` of your Laravel project. This will download the package and PhpSpreadsheet.

```
composer require maatwebsite/excel
```

The `Maatwebsite\Excel\ExcelServiceProvider` is auto-discovered and registered by default, but if you want to register it yourself:

Add the ServiceProvider in `app/config/app.php`

```php
'providers' => [
    Maatwebsite\Excel\ExcelServiceProvider::class,
]
```

The `Excel` facade is also auto-discovered. In case you want to add it manually:

Add the ServiceProvider in `app/config/app.php`

```php
/*
 * Package Service Providers...
 */
Maatwebsite\Excel\ExcelServiceProvider::class,
```

Add the Facde in `app/config/app.php`

```php
'aliases' => [
    ...
    Maatwebsite\Excel\ExcelServiceProvider::class,
]
```

The class is bound to the ioC as `excel` or `Maatwebsite\Excel\Excel`

```
$excel = app('excel');
$excel = app(Maatwebsite\Excel\Excel::class);
```