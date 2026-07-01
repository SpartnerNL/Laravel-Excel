UPGRADE FROM 3.1 to 4.0
=======================

## Minimum dependency versions

Laravel-Excel 4.0 requires PHP 8.3 or higher, and Laravel 12 or higher. 
If you are using an older version of PHP or Laravel, you will need to upgrade those first before upgrading to Laravel-Excel 4.0.

## Fully typed code base

Native PHP types were added across entire code base, including public methods and interfaces. 
If you are implementing any of the interfaces or overriding any methods, 
you will need to update your code to match the new method signatures to include native types:


```php
class MyExport implements FromArray
{
    public function array(): array
    {
    }
}
```

## FromScout

To keep laravel/scout an optional dependency, `FromQuery` no longer supports returning a Scout `Builder` instance. 
Use the new `FromScout` export interface instead.
