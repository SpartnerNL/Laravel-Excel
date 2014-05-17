# Cell size

### Set column width

To set the column width use `->setWidth($cell, $width)`.

    // Set width for a single column
    $sheet->setWidth('A', 5);

    // Set width for multiple cells
    $sheet->setWidth(array(
        'A'     =>  5,
        'B'     =>  10
    ));

### Set row height

To set the row height use `->setHeight($row, $height)`.

    // Set height for a single row
    $sheet->setHeight(1, 50);

    // Set height for multiple rows
    $sheet->setHeight(array(
        1     =>  50,
        2     =>  25
    ));

### Set cell size

To set the cell size use `->setSize($cell, $width, $height)`.

    // Set size for a single cell
    $sheet->setSize('A1', 500, 50);

    $sheet->setSize(array(
        'A1' => array(
            'width'     => 50
            'height'    => 500,
        )
    ));