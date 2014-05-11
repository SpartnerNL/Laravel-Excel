#Installation

Require this package in your `composer.json` and update composer. This will download the package and PHPExcel of PHPOffice.

    "maatwebsite/excel": "1.*"

After updating composer, add the ServiceProvider to the providers array in `app/config/app.php`

    'Maatwebsite\Excel\ExcelServiceProvider',

You can use the facade for shorter code. Add this to your aliasses:

    'Excel' => 'Maatwebsite\Excel\Facades\Excel',

The class is binded to the ioC as `excel`

    $excel = App::make('excel');