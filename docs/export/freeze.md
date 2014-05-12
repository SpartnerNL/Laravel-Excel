# Freeze rows

If you want to freeze a cell, row or column, use:

    // Freeze first row
    $sheet->freezeFirstRow();

    // Freeze the first column
    $sheet->freezeFirstColumn();

    // Freeze the first row and column
    $sheet->freezeFirstRowAndColumn();

    // Set freeze
    $sheet->setFreeze('A2');