<?php

use Maatwebsite\Excel\Excel;

return [
    /*
    |--------------------------------------------------------------------------
    | Temporary path
    |--------------------------------------------------------------------------
    |
    | When exporting files, we use a temporary file, before storing
    | or downloading. Here you can customize that path.
    |
    */
    'temp_path'          => sys_get_temp_dir(),

    /*
    |--------------------------------------------------------------------------
    | Remote temporary disk
    |--------------------------------------------------------------------------
    |
    | If not null, then temporary files for queued imports and exports will be stored
    | at and retrieved from the specified filesystem disk, in addition to
    | the configured temp_path on the local server.
    |
    */
    'remote_temp_disk'   => null,

    'exports' => [

        /*
        |--------------------------------------------------------------------------
        | Chunk size
        |--------------------------------------------------------------------------
        |
        | When using FromQuery, the query is automatically chunked.
        | Here you can specify how big the chunk should be.
        |
        */
        'chunk_size'             => 1000,

        /*
       |--------------------------------------------------------------------------
       | Pre-calculate formulas during export
       |--------------------------------------------------------------------------
       */
        'pre_calculate_formulas' => false,

        /*
        |--------------------------------------------------------------------------
        | CSV Settings
        |--------------------------------------------------------------------------
        |
        | Configure e.g. delimiter, enclosure and line ending for CSV exports.
        |
        */
        'csv'                    => [
            'delimiter'              => ',',
            'enclosure'              => '"',
            'line_ending'            => PHP_EOL,
            'use_bom'                => false,
            'include_separator_line' => false,
            'excel_compatibility'    => false,
        ],
    ],

    'imports'            => [

        'read_only' => true,

        'heading_row' => [

            /*
            |--------------------------------------------------------------------------
            | Heading Row Formatter
            |--------------------------------------------------------------------------
            |
            | Configure the heading row formatter.
            | Available options: none|slug|custom
            |
            */
            'formatter' => 'slug',
        ],

        /*
        |--------------------------------------------------------------------------
        | CSV Settings
        |--------------------------------------------------------------------------
        |
        | Configure e.g. delimiter, enclosure and line ending for CSV imports.
        |
        */
        'csv'                    => [
            'delimiter'              => ',',
            'enclosure'              => '"',
            'line_ending'            => PHP_EOL,
            'use_bom'                => false,
            'include_separator_line' => false,
            'excel_compatibility'    => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Extension detector
    |--------------------------------------------------------------------------
    |
    | Configure here which writer type should be used when
    | the package needs to guess the correct type
    | based on the extension alone.
    |
    */
    'extension_detector' => [
        'xlsx'     => Excel::XLSX,
        'xlsm'     => Excel::XLSX,
        'xltx'     => Excel::XLSX,
        'xltm'     => Excel::XLSX,
        'xls'      => Excel::XLS,
        'xlt'      => Excel::XLS,
        'ods'      => Excel::ODS,
        'ots'      => Excel::ODS,
        'slk'      => Excel::SLK,
        'xml'      => Excel::XML,
        'gnumeric' => Excel::GNUMERIC,
        'htm'      => Excel::HTML,
        'html'     => Excel::HTML,
        'csv'      => Excel::CSV,
        'tsv'      => Excel::TSV,

        /*
        |--------------------------------------------------------------------------
        | PDF Extension
        |--------------------------------------------------------------------------
        |
        | Configure here which Pdf driver should be used by default.
        | Available options: Excel::MPDF | Excel::TCPDF | Excel::DOMPDF
        |
        */
        'pdf'      => Excel::DOMPDF,
    ],
];
