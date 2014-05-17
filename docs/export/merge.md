# Column merging

### Merging cells

To merge a range of cells, use `->mergeCells($range)`.

    $sheet->mergeCells('A1:E1');

### Merging columns and rows

To merge columns and rows, use `->setMergeColumn($array)`.

    $sheet->setMergeColumn(array(
        'columns' => array('A','B','C','D'),
        'rows' => array(
            array(2,3),
            array(5,11),
        )
    ));