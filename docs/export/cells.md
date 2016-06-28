# Cell manipulation

    $sheet->cell('A1', function($cell) {

        // manipulate the cell

    });

    $sheet->cells('A1:A5', function($cells) {

        // manipulate the range of cells

    });

### Set background

To change the background of a range of cells we can use `->setBackground($color, $type, $colorType)`

    // Set black background
    $cells->setBackground('#000000');

### Change fonts

    // Set with font color
    $cells->setFontColor('#ffffff');

    // Set font family
    $cells->setFontFamily('Calibri');

    // Set font size
    $cells->setFontSize(16);

    // Set font weight to bold
    $cells->setFontWeight('bold');

    // Set font
    $cells->setFont(array(
        'family'     => 'Calibri',
        'size'       => '16',
        'bold'       =>  true
    ));

### Set borders

    // Set all borders (top, right, bottom, left)
    $cells->setBorder('solid', 'none', 'none', 'solid');

    // Set borders with array
    $cells->setBorder(array(
        'top'   => array(
            'style' => 'solid'
        ),
    ));

### Set horizontal alignment

    // Set alignment to center
    $cells->setAlignment('center');

### Set vertical alignment

    // Set vertical alignment to middle
     $cells->setValignment('center');
