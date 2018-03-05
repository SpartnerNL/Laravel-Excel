# Installation

Require this package in the `composer.json` of your Laravel project. This will download the package and PhpSpreadsheet.

```
composer require maatwebsite/excel
```

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

The `Excel` facade is also auto-discovered. In case you want to add it manually:

Add the Facade in `app/config/app.php`

```php
'aliases' => [
    ...
    'Excel' => Maatwebsite\Excel\Facades\Excel::class,
]
```

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