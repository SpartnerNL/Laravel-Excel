# Calling PHPExcel's native methods

It's possible to call all native PHPExcel methods on the `$excel` and `$sheet` objects.

### Calling Workbook methods

Example:

    // Get default style for this workbook
    $excel->getDefaultStyle();

### Calling worksheet methods

Example:

    // Protect cells
    $sheet->protectCells('A1', $password);

> Head over to PHPOffice to learn more about the native methods.