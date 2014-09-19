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

    )
);