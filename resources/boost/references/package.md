# Laravel Excel Package Reference

## Repository Shape

- Package name: `maatwebsite/excel`
- Primary namespace: `Maatwebsite\Excel`
- Service provider: `Maatwebsite\Excel\ExcelServiceProvider`
- Facade: `Maatwebsite\Excel\Facades\Excel`
- Config file: `config/excel.php`
- Export/import concerns: `src/Concerns/`
- Tests: `tests/`

## Core Classes

- `src/Excel.php` implements the main facade target and coordinates export/import operations.
- `src/Exporter.php` defines `download()`, `store()`, `queue()`, and `raw()`.
- `src/Importer.php` defines `import()`, `toArray()`, `toCollection()`, and `queueImport()`.
- `src/Writer.php` and `src/Reader.php` handle PhpSpreadsheet writer/reader integration.
- `src/QueuedWriter.php` and chunk reader jobs handle queued processing.
- `src/Fakes/ExcelFake.php` powers `Excel::fake()` assertions.

## File Types

Common constants exposed by `Maatwebsite\Excel\Excel`:

- `Excel::XLSX`
- `Excel::CSV`
- `Excel::TSV`
- `Excel::ODS`
- `Excel::XLS`
- `Excel::HTML`

The package usually detects reader/writer type from the file extension. Pass an explicit type when extension detection is not reliable.

## Export Concerns

Source concerns:

- `FromArray`
- `FromCollection`
- `FromGenerator`
- `FromIterator`
- `FromQuery`
- `FromScout`
- `FromView`

Output and behavior concerns:

- `Exportable`
- `WithHeadings`
- `WithMapping`
- `WithColumnFormatting`
- `WithColumnWidths`
- `ShouldAutoSize`
- `WithStyles`
- `WithDefaultStyles`
- `WithEvents`
- `WithMultipleSheets`
- `WithTitle`
- `WithProperties`
- `WithCustomCsvSettings`
- `WithStrictNullComparison`
- `ShouldBatch`
- `ShouldQueueWithoutChain`

Queue interfaces:

- `Illuminate\Contracts\Queue\ShouldQueue`
- `Maatwebsite\Excel\Concerns\ShouldBatch`
- `Maatwebsite\Excel\Concerns\ShouldQueueWithoutChain`

## Import Concerns

Target concerns:

- `ToModel`
- `ToCollection`
- `ToArray`
- `OnEachRow`

Input and behavior concerns:

- `Importable`
- `WithHeadingRow`
- `WithStartRow`
- `WithMappedCells`
- `WithValidation`
- `WithChunkReading`
- `WithBatchInserts`
- `WithUpserts`
- `WithSkipDuplicates`
- `SkipsEmptyRows`
- `SkipsOnFailure`
- `SkipsFailures`
- `SkipsOnError`
- `SkipsErrors`
- `WithCustomCsvSettings`
- `WithReadFilter`
- `ShouldBatch`
- `ShouldQueueWithoutChain`

## Testing Reference

`Excel::fake()` supports intent assertions for:

- Downloads
- Stored exports
- Queued exports
- Raw exports
- Imports
- Queued imports

Use real file assertions when fake intent is not enough. Existing package helpers in `tests/TestCase.php` can read generated files through PhpSpreadsheet.
