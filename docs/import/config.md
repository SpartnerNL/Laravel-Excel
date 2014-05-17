# Import by Config

When using advanced Excel files (e.g. without any heading columns), it can be complicated to import these.
`->byConfig()` will help you handle this problem.

Inside `excel::import.sheets` config you can find an example.

    Excel::load('file.xls')->byConfig('excel::import.sheets', function($sheet) {

        // The firstname getter will correspond with a cell coordinate set inside the config
        $firstname = $sheet->firstname;

    });

> **Note:** if you are using multiple sheets. `->byConfig` will loop through all sheets. If these getters are only exist on one sheet, you can always use `->selectSheets()`.