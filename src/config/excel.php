<?php

return array(

    'cache'      => array(

        /*
        |--------------------------------------------------------------------------
        | Enable/Disable cell caching
        |--------------------------------------------------------------------------
        */
        'enable'   => true,

        /*
        |--------------------------------------------------------------------------
        | Caching driver
        |--------------------------------------------------------------------------
        |
        | Set the caching driver
        |
        | Available methods:
        | memory|gzip|serialized|igbinary|discISAM|apc|memcache|temp|wincache|sqlite|sqlite3
        |
        */
        'driver'   => 'memory',

        /*
        |--------------------------------------------------------------------------
        | Cache settings
        |--------------------------------------------------------------------------
        */
        'settings' => array(

            'memoryCacheSize' => '32MB',
            'cacheTime'       => 600

        ),

        /*
        |--------------------------------------------------------------------------
        | Memcache settings
        |--------------------------------------------------------------------------
        */
        'memcache' => array(

            'host' => 'localhost',
            'port' => 11211,

        ),

        /*
        |--------------------------------------------------------------------------
        | Cache dir (for discISAM)
        |--------------------------------------------------------------------------
        */

        'dir'      => storage_path('cache')
    ),

    'properties' => array(
        'creator'        => 'Maatwebsite',
        'lastModifiedBy' => 'Maatwebsite',
        'title'          => 'Spreadsheet',
        'description'    => 'Default spreadsheet export',
        'subject'        => 'Spreadsheet export',
        'keywords'       => 'maatwebsite, excel, export',
        'category'       => 'Excel',
        'manager'        => 'Maatwebsite',
        'company'        => 'Maatwebsite',
    ),

    /*
    |--------------------------------------------------------------------------
    | Sheets settings
    |--------------------------------------------------------------------------
    */
    'sheets'     => array(

        /*
        |--------------------------------------------------------------------------
        | Default page setup
        |--------------------------------------------------------------------------
        */
        'pageSetup' => array(
            'orientation'           => 'portrait',
            'paperSize'             => '9',
            'scale'                 => '100',
            'fitToPage'             => false,
            'fitToHeight'           => true,
            'fitToWidth'            => true,
            'columnsToRepeatAtLeft' => array('', ''),
            'rowsToRepeatAtTop'     => array(0, 0),
            'horizontalCentered'    => false,
            'verticalCentered'      => false,
            'printArea'             => null,
            'firstPageNumber'       => null,
        ),
    ),

    /*
    |--------------------------------------------------------------------------
    | Creator
    |--------------------------------------------------------------------------
    |
    | The default creator of a new Excel file
    |
    */

    'creator'    => 'Maatwebsite',

    'csv'        => array(
        /*
       |--------------------------------------------------------------------------
       | Delimiter
       |--------------------------------------------------------------------------
       |
       | The default delimiter which will be used to read out a CSV file
       |
       */

        'delimiter'   => ',',

        /*
        |--------------------------------------------------------------------------
        | Enclosure
        |--------------------------------------------------------------------------
        */

        'enclosure'   => '"',

        /*
        |--------------------------------------------------------------------------
        | Line endings
        |--------------------------------------------------------------------------
        */

        'line_ending' => "\r\n"
    ),

    'export'     => array(

        /*
        |--------------------------------------------------------------------------
        | Autosize columns
        |--------------------------------------------------------------------------
        |
        | Disable/enable column autosize or set the autosizing for
        | an array of columns ( array('A', 'B') )
        |
        */
        'autosize'                    => true,

        /*
        |--------------------------------------------------------------------------
        | Autosize method
        |--------------------------------------------------------------------------
        |
        | --> PHPExcel_Shared_Font::AUTOSIZE_METHOD_APPROX
        | The default is based on an estimate, which does its calculation based
        | on the number of characters in the cell value (applying any calculation
        | and format mask, and allowing for wordwrap and rotation) and with an
        | "arbitrary" adjustment based on the font (Arial, Calibri or Verdana,
        | defaulting to Calibri if any other font is used) and a proportional
        | adjustment for the font size.
        |
        | --> PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT
        | The second method is more accurate, based on actual style formatting as
        | well (bold, italic, etc), and is calculated by generating a gd2 imagettf
        | bounding box and using its dimensions to determine the size; but this
        | method is significantly slower, and its accuracy is still dependent on
        | having the appropriate fonts installed.
        |
        */
        'autosize-method'             => PHPExcel_Shared_Font::AUTOSIZE_METHOD_APPROX,

        /*
        |--------------------------------------------------------------------------
        | Auto generate table heading
        |--------------------------------------------------------------------------
        |
        | If set to true, the array indices (or model attribute names)
        | will automatically be used as first row (table heading)
        |
        */
        'generate_heading_by_indices' => true,

        /*
        |--------------------------------------------------------------------------
        | Auto set alignment on merged cells
        |--------------------------------------------------------------------------
        */
        'merged_cell_alignment'       => 'left',

        /*
        |--------------------------------------------------------------------------
        | Pre-calculate formulas during export
        |--------------------------------------------------------------------------
        */
        'calculate'                   => false,

        /*
        |--------------------------------------------------------------------------
        | Include Charts during export
        |--------------------------------------------------------------------------
        */
        'includeCharts'               => false,

        /*
        |--------------------------------------------------------------------------
        | Default sheet settings
        |--------------------------------------------------------------------------
        */
        'sheets'                      => array(

            /*
            |--------------------------------------------------------------------------
            | Default page margin
            |--------------------------------------------------------------------------
            |
            | 1) When set to false, default margins will be used
            | 2) It's possible to enter a single margin which will
            |    be used for all margins.
            | 3) Alternatively you can pass an array with 4 margins
            |    Default order: array(top, right, bottom, left)
            |
            */
            'page_margin'          => false,

            /*
            |--------------------------------------------------------------------------
            | Value in source array that stands for blank cell
            |--------------------------------------------------------------------------
            */
            'nullValue'            => null,

            /*
            |--------------------------------------------------------------------------
            | Insert array starting from this cell address as the top left coordinate
            |--------------------------------------------------------------------------
            */
            'startCell'            => 'A1',

            /*
            |--------------------------------------------------------------------------
            | Apply strict comparison when testing for null values in the array
            |--------------------------------------------------------------------------
            */
            'strictNullComparison' => false
        ),

        /*
        |--------------------------------------------------------------------------
        | Store settings
        |--------------------------------------------------------------------------
        */

        'store'                       => array(

            /*
            |--------------------------------------------------------------------------
            | Path
            |--------------------------------------------------------------------------
            |
            | The path we want to save excel file to
            |
            */
            'path'       => storage_path('exports'),

            /*
            |--------------------------------------------------------------------------
            | Return info
            |--------------------------------------------------------------------------
            |
            | Whether we want to return information about the stored file or not
            |
            */
            'returnInfo' => false

        ),

        /*
        |--------------------------------------------------------------------------
        | PDF Settings
        |--------------------------------------------------------------------------
        */
        'pdf'                         => array(

            /*
            |--------------------------------------------------------------------------
            | PDF Drivers
            |--------------------------------------------------------------------------
            | Supported: DomPDF, tcPDF, mPDF
            */
            'driver'  => 'DomPDF',

            /*
            |--------------------------------------------------------------------------
            | PDF Driver settings
            |--------------------------------------------------------------------------
            */
            'drivers' => array(

                /*
                |--------------------------------------------------------------------------
                | DomPDF settings
                |--------------------------------------------------------------------------
                */
                'DomPDF' => array(
                    'path' => base_path('vendor/dompdf/dompdf/')
                ),

                /*
                |--------------------------------------------------------------------------
                | tcPDF settings
                |--------------------------------------------------------------------------
                */
                'tcPDF'  => array(
                    'path' => base_path('vendor/tecnick.com/tcpdf/')
                ),

                /*
                |--------------------------------------------------------------------------
                | mPDF settings
                |--------------------------------------------------------------------------
                */
                'mPDF'   => array(
                    'path' => base_path('vendor/mpdf/mpdf/')
                ),
            )
        )
    ),

    'filters'    => array(
        /*
        |--------------------------------------------------------------------------
        | Register read filters
        |--------------------------------------------------------------------------
        */

        'registered' => array(
            'chunk' => 'Maatwebsite\Excel\Filters\ChunkReadFilter'
        ),

        /*
        |--------------------------------------------------------------------------
        | Enable certain filters for every file read
        |--------------------------------------------------------------------------
        */

        'enabled'    => array()
    ),

    'import'     => array(

        /*
        |--------------------------------------------------------------------------
        | Has heading
        |--------------------------------------------------------------------------
        |
        | The sheet has a heading (first) row which we can use as attribute names
        |
        | Options: true|false|slugged|slugged_with_count|ascii|numeric|hashed|trans|original
        |
        */

        'heading'                 => 'slugged',

        /*
        |--------------------------------------------------------------------------
        | First Row with data or heading of data
        |--------------------------------------------------------------------------
        |
        | If the heading row is not the first row, or the data doesn't start
        | on the first row, here you can change the start row.
        |
        */

        'startRow'                => 1,

        /*
        |--------------------------------------------------------------------------
        | Cell name word separator
        |--------------------------------------------------------------------------
        |
        | The default separator which is used for the cell names
        | Note: only applies to 'heading' settings 'true' && 'slugged'
        |
        */

        'separator'               => '_',

        /*
        |--------------------------------------------------------------------------
        | Include Charts during import
        |--------------------------------------------------------------------------
        */

        'includeCharts'           => false,

        /*
        |--------------------------------------------------------------------------
        | Sheet heading conversion
        |--------------------------------------------------------------------------
        |
        | Convert headings to ASCII
        | Note: only applies to 'heading' settings 'true' && 'slugged'
        |
        */

        'to_ascii'                => true,

        /*
        |--------------------------------------------------------------------------
        | Import encoding
        |--------------------------------------------------------------------------
        */

        'encoding'                => array(

            'input'  => 'UTF-8',
            'output' => 'UTF-8'

        ),

        /*
        |--------------------------------------------------------------------------
        | Calculate
        |--------------------------------------------------------------------------
        |
        | By default cells with formulas will be calculated.
        |
        */

        'calculate'               => true,

        /*
        |--------------------------------------------------------------------------
        | Ignore empty cells
        |--------------------------------------------------------------------------
        |
        | By default empty cells are not ignored
        |
        */

        'ignoreEmpty'             => false,

        /*
        |--------------------------------------------------------------------------
        | Force sheet collection
        |--------------------------------------------------------------------------
        |
        | For a sheet collection even when there is only 1 sheets.
        | When set to false and only 1 sheet found, the parsed file will return
        | a row collection instead of a sheet collection.
        | When set to true, it will return a sheet collection instead.
        |
        */
        'force_sheets_collection' => false,

        /*
        |--------------------------------------------------------------------------
        | Date format
        |--------------------------------------------------------------------------
        |
        | The format dates will be parsed to
        |
        */

        'dates'                   => array(

            /*
            |--------------------------------------------------------------------------
            | Enable/disable date formatting
            |--------------------------------------------------------------------------
            */
            'enabled' => true,

            /*
            |--------------------------------------------------------------------------
            | Default date format
            |--------------------------------------------------------------------------
            |
            | If set to false, a carbon object will return
            |
            */
            'format'  => false,

            /*
            |--------------------------------------------------------------------------
            | Date columns
            |--------------------------------------------------------------------------
            */
            'columns' => array()
        ),

        /*
        |--------------------------------------------------------------------------
        | Import sheets by config
        |--------------------------------------------------------------------------
        */
        'sheets'                  => array(

            /*
            |--------------------------------------------------------------------------
            | Example sheet
            |--------------------------------------------------------------------------
            |
            | Example sheet "test" will grab the firstname at cell A2
            |
            */

            'test' => array(

                'firstname' => 'A2'

            )

        )
    ),

    'views'      => array(

        /*
        |--------------------------------------------------------------------------
        | Styles
        |--------------------------------------------------------------------------
        |
        | The default styles which will be used when parsing a view
        |
        */

        'styles' => array(

            /*
            |--------------------------------------------------------------------------
            | Table headings
            |--------------------------------------------------------------------------
            */
            'th'     => array(
                'font' => array(
                    'bold' => true,
                    'size' => 12,
                )
            ),

            /*
            |--------------------------------------------------------------------------
            | Strong tags
            |--------------------------------------------------------------------------
            */
            'strong' => array(
                'font' => array(
                    'bold' => true,
                    'size' => 12,
                )
            ),

            /*
            |--------------------------------------------------------------------------
            | Bold tags
            |--------------------------------------------------------------------------
            */
            'b'      => array(
                'font' => array(
                    'bold' => true,
                    'size' => 12,
                )
            ),

            /*
            |--------------------------------------------------------------------------
            | Italic tags
            |--------------------------------------------------------------------------
            */
            'i'      => array(
                'font' => array(
                    'italic' => true,
                    'size'   => 12,
                )
            ),

            /*
            |--------------------------------------------------------------------------
            | Heading 1
            |--------------------------------------------------------------------------
            */
            'h1'     => array(
                'font' => array(
                    'bold' => true,
                    'size' => 24,
                )
            ),

            /*
            |--------------------------------------------------------------------------
            | Heading 2
            |--------------------------------------------------------------------------
            */
            'h2'     => array(
                'font' => array(
                    'bold' => true,
                    'size' => 18,
                )
            ),

            /*
            |--------------------------------------------------------------------------
            | Heading 2
            |--------------------------------------------------------------------------
            */
            'h3'     => array(
                'font' => array(
                    'bold' => true,
                    'size' => 13.5,
                )
            ),

            /*
             |--------------------------------------------------------------------------
             | Heading 4
             |--------------------------------------------------------------------------
             */
            'h4'     => array(
                'font' => array(
                    'bold' => true,
                    'size' => 12,
                )
            ),

            /*
             |--------------------------------------------------------------------------
             | Heading 5
             |--------------------------------------------------------------------------
             */
            'h5'     => array(
                'font' => array(
                    'bold' => true,
                    'size' => 10,
                )
            ),

            /*
             |--------------------------------------------------------------------------
             | Heading 6
             |--------------------------------------------------------------------------
             */
            'h6'     => array(
                'font' => array(
                    'bold' => true,
                    'size' => 7.5,
                )
            ),

            /*
             |--------------------------------------------------------------------------
             | Hyperlinks
             |--------------------------------------------------------------------------
             */
            'a'      => array(
                'font' => array(
                    'underline' => true,
                    'color'     => array('argb' => 'FF0000FF'),
                )
            ),

            /*
             |--------------------------------------------------------------------------
             | Horizontal rules
             |--------------------------------------------------------------------------
             */
            'hr'     => array(
                'borders' => array(
                    'bottom' => array(
                        'style' => 'thin',
                        'color' => array('FF000000')
                    ),
                )
            )
        )

    )

);
