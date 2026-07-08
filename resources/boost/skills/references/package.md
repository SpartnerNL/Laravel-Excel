# Laravel Excel Feature Coverage Reference

Use this file as the completeness checklist for `maatwebsite/excel`. If a user asks for "all features", compare the task against every section here.

## Repository Shape

- Package name: `maatwebsite/excel`
- Primary namespace: `Maatwebsite\Excel`
- Service provider: `Maatwebsite\Excel\ExcelServiceProvider`
- Facade: `Maatwebsite\Excel\Facades\Excel`
- Config file: `config/excel.php`
- Export/import concerns: `src/Concerns/`
- Console commands and stubs: `src/Console/`
- Tests: `tests/`

## Public Facade And Contracts

- `Excel::download($export, $fileName, $writerType = null, $headers = [])`
- `Excel::store($export, $filePath, $disk = null, $writerType = null, $diskOptions = [])`
- `Excel::queue($export, $filePath, $disk = null, $writerType = null, $diskOptions = [])`
- `Excel::raw($export, $writerType)`
- `Excel::import($import, $filePath, $disk = null, $readerType = null)`
- `Excel::queueImport($import, $filePath, $disk = null, $readerType = null)`
- `Excel::toArray($import, $filePath, $disk = null, $readerType = null)`
- `Excel::toCollection($import, $filePath, $disk = null, $readerType = null)`
- `Maatwebsite\Excel\Exporter`
- `Maatwebsite\Excel\Importer`

## File Types And Detection

- `Excel::XLSX`
- `Excel::CSV`
- `Excel::TSV`
- `Excel::ODS`
- `Excel::XLS`
- `Excel::SLK`
- `Excel::XML`
- `Excel::GNUMERIC`
- `Excel::HTML`
- `Excel::MPDF`
- `Excel::DOMPDF`
- `Excel::TCPDF`
- Extension detection through `config('excel.extension_detector')`
- `NoTypeDetectedException` when detection cannot resolve a writer/reader type

## Export Sources

- `FromArray`: rows from `array()`
- `FromCollection`: rows from `collection()`
- `FromGenerator`: rows from `generator()`
- `FromIterator`: rows from `iterator()`
- `FromQuery`: chunked query export from Eloquent, query builder, or relation
- `FromScout`: export from Laravel Scout builder
- `FromView`: export a Blade view/table

## Export Behavior And Formatting

- `Exportable`: object-level `download()`, `store()`, `queue()`, `raw()`, and responsable support
- `WithHeadings`: prepend heading rows
- `WithMapping`: transform each source row before writing
- `WithColumnFormatting`: PhpSpreadsheet number/date formats
- `WithColumnWidths`: fixed column widths
- `ShouldAutoSize`: auto-size columns
- `WithStyles`: style a worksheet directly
- `WithDefaultStyles`: workbook default styles
- `WithBackgroundColor`: workbook background color
- `WithEvents`: register event listeners manually
- `RegistersEventListeners`: auto-register static event listener methods
- `WithProperties`: workbook document properties
- `WithTitle`: worksheet title
- `WithCustomStartCell`: start export output at a custom cell
- `WithStrictNullComparison`: preserve empty string/null distinctions
- `WithPreCalculateFormulas`: pre-calculate formulas before writing
- `WithCharts`: attach PhpSpreadsheet charts
- `WithDrawings`: attach PhpSpreadsheet drawings/images
- `WithMultipleSheets`: build multi-sheet workbooks
- `WithConditionalSheets`: expose optional sheet subsets through `onlySheets()`
- `HasReferencesToOtherSheets`: mark formulas/references that cross sheets
- `WithCustomCsvSettings`: per-export CSV settings
- `MapsCsvSettings`: maps package CSV settings onto PhpSpreadsheet CSV writers/readers
- `WithCustomChunkSize`: override export chunk size
- `WithCustomQuerySize`: override query size estimation for queued query exports
- `WithProgressBar`: write progress to console output
- `WithColumnLimit`: limit exported columns through an end column

## Import Targets

- `ToModel`: convert each row to an Eloquent model, model array, or null
- `ToCollection`: handle rows as a Laravel collection
- `ToArray`: handle rows as arrays
- `OnEachRow`: process a `Row` object one row at a time
- `WithMappedCells`: map specific cells to named array keys

## Import Reading Controls

- `Importable`: object-level `import()`, `queue()`, `toArray()`, `toCollection()`, and console output support
- `WithHeadingRow`: use a heading row for keys
- `WithGroupedHeadingRow`: group duplicate heading names
- `WithStartRow`: choose first data row
- `WithLimit`: limit number of read rows
- `WithColumnLimit`: limit read columns through an end column
- `WithReadFilter`: use a PhpSpreadsheet read filter
- `WithCalculatedFormulas`: read calculated formula results
- `WithFormatData`: read formatted values
- `WithCustomCsvSettings`: per-import CSV settings
- `WithCustomValueBinder`: custom PhpSpreadsheet value binding
- `SkipsEmptyRows`: skip empty rows
- `SkipsUnknownSheets`: handle sheet names/indexes that do not exist
- `RemembersRowNumber`: inspect current row number during import
- `RemembersChunkOffset`: inspect current chunk offset during chunked import

## Import Persistence, Validation, And Failures

- `WithValidation`: row validation rules
- `SkipsOnFailure`: handle validation failures without aborting
- `SkipsFailures`: collect failures through `failures()`
- `SkipsOnError`: handle thrown row errors without aborting
- `SkipsErrors`: collect errors through `errors()`
- `WithBatchInserts`: batch model inserts
- `WithUpserts`: upsert by `uniqueBy()`
- `WithUpsertColumns`: select columns updated during upsert
- `WithSkipDuplicates`: skip duplicate rows during insert/upsert flows
- `PersistRelations`: persist model relations from import rows
- `ValidationException`
- `Failure`
- `RowSkippedException`

## Queueing And Batching

- Use `Illuminate\Contracts\Queue\ShouldQueue` for queued exports/imports.
- Use `Maatwebsite\Excel\Concerns\ShouldBatch` when queue operations should return a pending batch.
- Use `Maatwebsite\Excel\Concerns\ShouldQueueWithoutChain` for queued imports without job chaining.
- Queued exports use jobs such as `QueueExport`, `AppendDataToSheet`, `AppendQueryToSheet`, `AppendPaginatedToSheet`, `AppendViewToSheet`, `CloseSheet`, and `StoreQueuedExport`.
- Queued imports use `QueueImport`, `ReadChunk`, `AfterImportJob`, `AfterBatch`, `AfterChunk`, and `ProxyFailures`.
- `Jobs\Middleware\LocalizeJob` preserves locale preferences for queued work.
- Remote temporary files support multi-server queue processing.

## Events

- `BeforeExport`
- `BeforeWriting`
- `BeforeSheet`
- `AfterSheet`
- `BeforeImport`
- `AfterImport`
- `AfterChunk`
- `AfterBatch`
- `ImportFailed`
- Base event support through `Event` and `HasEventBus`

## Console Commands And Stubs

- `php artisan make:export ExportName`
- `php artisan make:export ExportName --model=User`
- `php artisan make:export ExportName --query`
- `php artisan make:export ExportName --model=User --query`
- `php artisan make:import ImportName`
- `php artisan make:import ImportName --model=User`
- Publish stubs with the package `stubs` publish tag.
- Publish config with the package `config` publish tag.

## Collection And Query Macros

- `DownloadCollectionMixin`
- `StoreCollectionMixin`
- `DownloadQueryMacro`
- `StoreQueryMacro`
- `ImportMacro`
- `ImportAsMacro`
- `Collection::downloadExcel($fileName, $writerType = null, $withHeadings = false, $responseHeaders = [])`
- `Collection::storeExcel($filePath, $disk = null, $writerType = null, $withHeadings = false)`
- `Builder::downloadExcel($fileName, $writerType = null, $withHeadings = false)`
- `Builder::storeExcel($filePath, $disk = null, $writerType = null, $withHeadings = false)`
- `Builder::import($filename, $disk = null, $readerType = null)`
- `Builder::importAs($filename, callable $mapping, $disk = null, $readerType = null)`

## Configuration Surface

- `exports.chunk_size`
- `exports.pre_calculate_formulas`
- `exports.strict_null_comparison`
- `exports.csv.delimiter`
- `exports.csv.enclosure`
- `exports.csv.line_ending`
- `exports.csv.use_bom`
- `exports.csv.include_separator_line`
- `exports.csv.excel_compatibility`
- `exports.csv.output_encoding`
- `exports.csv.test_auto_detect`
- `exports.properties.*`
- `imports.read_only`
- `imports.ignore_empty`
- `imports.heading_row.formatter`
- `imports.csv.delimiter`
- `imports.csv.enclosure`
- `imports.csv.escape_character`
- `imports.csv.contiguous`
- `imports.csv.input_encoding`
- `imports.properties.*`
- `imports.cells.middleware`
- `extension_detector`
- `value_binder.default`
- `cache.driver`
- `cache.batch.memory_limit`
- `cache.illuminate.store`
- `cache.default_ttl`
- `transactions.handler`
- `transactions.db.connection`
- `temporary_files.local_path`
- `temporary_files.local_permissions`
- `temporary_files.remote_disk`
- `temporary_files.remote_prefix`
- `temporary_files.force_resync_remote`

## Cache, Transactions, Files, And Cell Middleware

- Cache drivers: `memory`, `illuminate`, `batch`
- Cache classes: `CacheManager`, `MemoryCache`, `BatchCache`
- Transactions: `TransactionManager`, `DbTransactionHandler`, `NullTransactionHandler`
- Temporary files: `TemporaryFileFactory`, `LocalTemporaryFile`, `RemoteTemporaryFile`
- Filesystem disk wrapper: `Files\Disk`, `Files\Filesystem`
- Cell middleware: `TrimCellValue`, `ConvertEmptyCellValuesToNull`, and custom `CellMiddleware`
- Value binders: package default binder, PhpSpreadsheet string binder, PhpSpreadsheet advanced binder, custom `WithCustomValueBinder`

## Testing And Fakes

- `Excel::fake()`
- `Excel::assertDownloaded($fileName, $callback = null)`
- `Excel::assertStored($filePath, $disk = null, $callback = null)`
- `Excel::assertQueued($filePath, $disk = null, $callback = null)`
- `Excel::assertQueuedWithChain($chain)`
- `Excel::assertExportedInRaw($className, $callback = null)`
- `Excel::assertImported($filePath, $disk = null, $callback = null)`
- `Excel::matchByRegex()`
- `Excel::doNotMatchByRegex()`
- Use real file reads through PhpSpreadsheet when cell values, sheets, formatting, formulas, drawings, or charts must be verified.

## Maintenance Map

- Concern changes: update matching `tests/Concerns/*Test.php`.
- Facade/fake changes: update `tests/ExcelFakeTest.php`.
- Queue changes: update `tests/QueuedExportTest.php`, `tests/QueuedImportTest.php`, `tests/QueuedQueryExportTest.php`, or `tests/QueuedViewExportTest.php`.
- Macro changes: update `tests/Mixins/*Test.php`.
- Validation changes: update `tests/Validators/RowValidatorTest.php`.
- Cache changes: update `tests/Cache/BatchCacheTest.php`.
- Service provider changes: update `tests/ExcelServiceProviderTest.php`.
- PhpSpreadsheet compatibility changes: update `tests/PhpSpreadsheetV5CompatibilityTest.php`.
