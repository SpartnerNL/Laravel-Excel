#Installation

Require this package in your `composer.json` and update composer. This will download the package and PHPExcel of PHPOffice.

#### Laravel 4

    "maatwebsite/excel": "~1.3"
    
#### Laravel 5

    "maatwebsite/excel": "~2.0"

After updating composer, add the ServiceProvider to the providers array in `app/config/app.php`

    'Maatwebsite\Excel\ExcelServiceProvider',

You can use the facade for shorter code. Add this to your aliasses:

    'Excel' => 'Maatwebsite\Excel\Facades\Excel',

The class is binded to the ioC as `excel`

    $excel = App::make('excel');
