# Column formatting

To tell Excel how it should interpret certain columns, you can use `->setColumnFormat($array)`.

    // Format column as percentage
    $sheet->setColumnFormat(array(
        'C' => '0%'
    ));
    
    // Format a range with e.g. leading zeros
    $sheet->setColumnFormat(array(
        'A2:K2' => '0000'
    ));

    // Set multiple column formats
    $sheet->setColumnFormat(array(
        'B' => '0',
        'D' => '0.00',
        'F' => '@',
        'F' => 'yyyy-mm-dd',
    ));

> Go to the reference guide to see a list of available formats.
