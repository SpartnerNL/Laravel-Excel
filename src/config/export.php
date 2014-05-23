<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Autosize columns
    |--------------------------------------------------------------------------
    |
    | Disable/enable column autosize or set the autosizing for
    | an array of columns ( array('A', 'B') )
    |
    */
    'autosize'  => true,

    /*
    |--------------------------------------------------------------------------
    | Auto generate table heading
    |--------------------------------------------------------------------------
    |
    | If set to true, the array indices (or model attribute names)
    | will automatically be used as first row (table heading)
    |
    */
    'generate_heading_by_indices'  => true,

    /*
    |--------------------------------------------------------------------------
    | Store settings
    |--------------------------------------------------------------------------
    */

    'store' => array(

        /*
        |--------------------------------------------------------------------------
        | Path
        |--------------------------------------------------------------------------
        |
        | The path we want to save excel file to
        |
        */

        'path' => storage_path('exports'),

        /*
        |--------------------------------------------------------------------------
        | Return info
        |--------------------------------------------------------------------------
        |
        | Whether we want to return information about the stored file or not
        |
        */

        'returnInfo'    =>  false

    )

);