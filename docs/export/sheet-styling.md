# Styling sheets

### Fonts

To change the font for the current sheet use `->setFont($array)`:

    $sheet->setFont(array(
        'family'     => 'Calibri',
        'size'       => '15',
        'bold'       => true
    ));

#### Font Family

    $sheet->setFontFamily('Comic Sans MS');

#### Font Size

    $sheet->setFontSize(15);

#### Bold

    $sheet->setFontBold(true);