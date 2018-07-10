# Available sheet properties

Properties that can be set with `$sheet->set{$property}()`

| Property name  | Possible value|
| ------------- |-----------------|
|orientation| string
|paperSize| integer
|scale| integer
|fitToPage| boolean/int *
|fitToHeight| boolean/int *
|fitToWidth| boolean/int *
|columnsToRepeatAtLeft| array
|rowsToRepeatAtTop| array
|horizontalCentered| boolean
|verticalCentered| boolean
|printArea| range
|firstPageNumber| integer

* See [PHPExcel's Page Setup: Scaling options](https://github.com/PHPOffice/PHPExcel/blob/develop/Documentation/markdown/Overview/08-Recipes.md#page-setup-scaling-options) for more information
