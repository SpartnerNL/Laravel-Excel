# Auto filter

To enable the auto filter use `->setAutoFilter($range = false)`

    // Autofilter for entire sheet
    $sheet->setAutoFilter();

    // Set auto filter for a range
    $sheet->setAutoFilter('A1:E10');