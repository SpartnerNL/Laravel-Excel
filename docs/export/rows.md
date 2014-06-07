# Row manipulation

### Manipulate certain row

#### Change cell values

    // Manipulate first row
    $sheet->row(1, array(
         'test1', 'test2'
    ));

    // Manipulate 2nd row
    $sheet->row(2, array(
        'test3', 'test4'
    ));

#### Manipulate row cells

    // Set black background
    $sheet->row(1, function($row) {

        // call cell manipulation methods
        $row->setBackground('#000000');

    });

### Append row

    // Append row after row 2
    $sheet->appendRow(2, array(
        'appended', 'appended'
    ));

    // Append row as very last
    $sheet->appendRow(array(
        'appended', 'appended'
    ));

### Prepend row

    // Add before first row
    $sheet->prependRow(1, array(
        'prepended', 'prepended'
    ));

    // Add as very first
    $sheet->prependRow(array(
        'prepended', 'prepended'
    ));

### Append multiple rows

    // Append multiple rows
    $sheet->rows(array(
        array('test1', 'test2'),
        array('test3', 'test4')
    ));

    // Append multiple rows
    $sheet->rows(array(
        array('test5', 'test6'),
        array('test7', 'test8')
    ));