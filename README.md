## PHPExcel Wrapper for Laravel 4

Warning: This package is still in development!

Require this package in your composer.json and update composer. This will download the package and PHPExcel of PHPOffice.

    "maatwebsite/excel": "dev-master"

After updating composer, add the ServiceProvider to the providers array in app/config/app.php

    'Maatwebsite\Excel\ExcelServiceProvider',

You can optionally use the facade for shorter code. Add this to your facades:

    'Excel' => 'Maatwebsite\Excel\Facades\Excel',

For creating an Excel file use:

    Excel::create('ExcelName')
            ->sheet('SheetName')
                ->with(array('data', 'data'))
            ->export('xls');

Multiple sheets are allowed

    Excel::create('ExcelName')
            ->sheet('SheetName')
                ->with(array('data', 'data'))
            ->sheet('SheetName')
                ->with(array('data', 'data'))
            ->export('xls');

Export as CSV by using:

    ->export('csv');