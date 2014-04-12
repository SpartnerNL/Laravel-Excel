<?php
/**
 * Part of the Laravel-4 PHPExcel package
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the LPGL.
 *
 * @package    Laravel-4 PHPExcel
 * @version    0.1.0
 * @author     Maatwebsite
 * @license    LGPL
 * @copyright  (c) 2013, Maatwebsite
 * @link       http://maatwebsite.nl
 */

return array(

    /*
    |--------------------------------------------------------------------------
    | Default properties
    |--------------------------------------------------------------------------
    |
    | The default properties when creating a new Excel file
    |
    */
    'properties' => array(
        'creator'           => 'Maatwebsite',
        'lastModifiedBy'    => 'Maatwebsite',
        'title'             => 'Spreadsheet',
        'description'       => 'Default spreadsheet export',
        'subject'           => 'Spreadsheet export',
        'keywords'          => 'maatwebsite, excel, export',
        'category'          => 'Excel',
        'manager'           => 'Maatwebsite',
        'company'           => 'Maatwebsite',
    ),

    /*
    |--------------------------------------------------------------------------
    | Sheets settings
    |--------------------------------------------------------------------------
    */
    'sheets' => array(

        /*
        |--------------------------------------------------------------------------
        | Default page setup
        |--------------------------------------------------------------------------
        */
        'pageSetup' => array(
            'orientation' => 'portrait',
            'paperSize' => '9',
            'scale' => '100',
            'fitToPage' => false,
            'fitToHeight' => true,
            'fitToWidth' => true,
            'columnsToRepeatAtLeft' => array('', ''),
            'rowsToRepeatAtTop' => array(0, 0),
            'horizontalCentered' => false,
            'verticalCentered' => false,
            'printArea' => null,
            'firstPageNumber' => null,
        ),

        /*
        |--------------------------------------------------------------------------
        | Autosize columns
        |--------------------------------------------------------------------------
        */
        'autosize'  => true

    ),

    /*
    |--------------------------------------------------------------------------
    | Creator
    |--------------------------------------------------------------------------
    |
    | The default creator of a new Excel file
    |
    */

	'creator' => 'Maatwebsite',

    /*
    |--------------------------------------------------------------------------
    | Delimiter
    |--------------------------------------------------------------------------
    |
    | The default delimiter which will be used to read out a CSV file
    | If you would like to use an other delimiter only one time,
    | you can also use the `setDelimiter()` chain.
    |
    */

    'delimiter' => ',',

    /*
    |--------------------------------------------------------------------------
    | Slug seperator
    |--------------------------------------------------------------------------
    |
    | The default seperator for the Str::slug() method.
    | If you have problemen with _ being converted to -, you can
    | change the seperator to _ here.
    |
    */

    'seperator' => '-',

     /*
    |--------------------------------------------------------------------------
    | Calculate
    |--------------------------------------------------------------------------
    |
    | By default cells with formulas will not be calculated.
    |
    */

    'calculate' => false,

     /*
    |--------------------------------------------------------------------------
    | Ignore empty cells
    |--------------------------------------------------------------------------
    |
    | By default empty cells are not ignored
    |
    */

    'ignoreEmpty' => false,

    /*
    |--------------------------------------------------------------------------
    | Date format
    |--------------------------------------------------------------------------
    |
    | The format dates will be parsed to
    |
    */

    'date_format' => 'Y-m-d',

    /*
    |--------------------------------------------------------------------------
    | Path
    |--------------------------------------------------------------------------
    |
    | The path we want to save excel file to
    |
    */

    'path' => base_path(),

);
