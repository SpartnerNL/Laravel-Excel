# Installation

### Requirements

* PHP: ^7.1
* Laravel: ^5.6
* PhpSpreadsheet: ^1.1
* PHP extension php_zip enabled
* PHP extension php_xml enabled
* PHP extension php_gd2 enabled

**PHP version support**

Support for PHP versions will only be maintained for a period of six months beyond the end-of-life of that PHP version

### Composer

Require this package in the `composer.json` of your Laravel project. This will download the package and PhpSpreadsheet.

```
composer require maatwebsite/excel
```

### Service Provider

The `Maatwebsite\Excel\ExcelServiceProvider` is auto-discovered and registered by default, but if you want to register it yourself:

Add the ServiceProvider in `app/config/app.php`

```php
'providers' => [
    /*
     * Package Service Providers...
     */
    Maatwebsite\Excel\ExcelServiceProvider::class,
]
```

### Facade

The `Excel` facade is also auto-discovered. In case you want to add it manually:

Add the Facade in `app/config/app.php`

```php
'aliases' => [
    ...
    'Excel' => Maatwebsite\Excel\Facades\Excel::class,
]
```

### Config

To publish the config, run the vendor publish command:

```
php artisan vendor:publish
```

The config file should now exists as `config/excel.php`.

### Usage

You can use Excel in the following ways:

Via dependency injection:

```php
public function __construct(\Maatwebsite\Excel\Excel $excel)
{
    $this->excel = $excel;
}

public function export()
{
    return $this->excel->export(new Export);
}

```

Via the Facade

```php
public function export()
{
    return Excel::export(new Export);
}
```

Via container binding:

```
$this->app->bind(Exporter::class, function() {
    return new Exporter($this->app['excel']);
});
```