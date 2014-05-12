# Sheets

### Creating a sheet

To create a new sheet inside our newly created file, use `->sheets('Sheetname')`:

    Excel::create('Filename', function($excel) {

        $excel->sheet('Sheetname', function($sheet) {

            // sheet manipulation

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

There are a couple of properties we can change inside the closure. Most of them are set to the config values by default. See `app/config/packages/maatwebsite/excel/config.php`

    Excel::create('Filename', function($excel) {

        $excel->sheet('Sheetname', function($sheet) {

            $sheet->setOrientation('landscape');

        });

    })->export('xls');

> Go to the reference guide to see a list of available properties.