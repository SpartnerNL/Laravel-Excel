# Auto filter

To enable the auto filter use `->setAutoFilter($range = false)`.

    // Auto filter for entire sheet
    $sheet->setAutoFilter();

    // Set auto filter for a range
    $sheet->setAutoFilter('A1:E10');