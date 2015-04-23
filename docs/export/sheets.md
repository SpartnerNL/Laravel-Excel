# Sheets

### Creating a sheet

To create a new sheet inside our newly created file, use `->sheet('Sheetname')`.

    Excel::create('Filename', function($excel) {

        $excel->sheet('Sheetname', function($sheet) {

            // Sheet manipulation

        });

    })->export('xls');


### Creating multiple sheets

You can set as many sheets as you like inside the file:

    Excel::create('Filename', function($excel) {

        // Our first sheet
        $excel->sheet('First sheet', function($sheet) {

        });

        // Our second sheet
        $excel->sheet('Second sheet', function($sheet) {

        });

    })->export('xls');

### Changing properties

There are a couple of properties we can change inside the closure. Most of them are set to the config values by default. See `app/config/packages/maatwebsite/excel/config.php`.

    Excel::create('Filename', function($excel) {

        $excel->sheet('Sheetname', function($sheet) {

            $sheet->setOrientation('landscape');

        });

    })->export('xls');

> Go to the reference guide to see a list of available properties.

### Default page margin

It's possible to set the default page margin inside the config file `excel::export.sheets`.
It accepts boolean, single value or array.

To manually set the page margin you can use: `->setPageMargin()`

    // Set top, right, bottom, left
    $sheet->setPageMargin(array(
        0.25, 0.30, 0.25, 0.30
    ));

    // Set all margins
    $sheet->setPageMargin(0.25);
    
### Password protecting a sheet
    
A sheet can be password protected with `$sheet->protect()`:

    // Default protect
    $sheet->protect('password');
    
    // Advanced protect
    $sheet->protect('password', function(\PHPExcel_Worksheet_Protection $protection) {
        $protection->setSort(true);
    });
