# Export concerns

| Interface | Explanation |
|---- |----|
|`Maatwebsite\Excel\Concerns\FromCollection`| Will use a Laravel Collection to populate the export. |
|`Maatwebsite\Excel\Concerns\FromQuery`| Will use an Eloquent query to populate the export. | 
|`Maatwebsite\Excel\Concerns\FromView`| Will use a (blade) view to to populate the export. |
|`Maatwebsite\Excel\Concerns\WithTitle`| Will set the Workbook or Worksheet title |
|`Maatwebsite\Excel\Concerns\WithHeadings`| Will prepend given heading row. |
|`Maatwebsite\Excel\Concerns\WithMapping`| Gives you the possibility to format the row before it's written to the file. |
|`Maatwebsite\Excel\Concerns\WithColumnFormatting`| Gives you the ability to format certain columns. |
|`Maatwebsite\Excel\Concerns\WithMultipleSheets`| Enables multi-sheet support. Each sheet can have its own concerns (expect the this one) |
|`Maatwebsite\Excel\Concerns\ShouldAutoSize`| Auto-sizes the columns in the worksheet |
|`Maatwebsite\Excel\Concerns\WithEvents`| Allows you to register events to hook into the PhpSpreadsheet process. |

### Traits

| Trait | Explanation |
|---- |----|
|`Maatwebsite\Excel\Concerns\Exportable` | Will add download/store abilities right on the export class itself. | 
