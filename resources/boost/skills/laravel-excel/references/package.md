# Laravel Excel Reference

Use this reference when the task needs package-specific detail that would make `SKILL.md` too large.

## Entry Points

- Facade: `Maatwebsite\Excel\Facades\Excel`
- Service provider: `Maatwebsite\Excel\ExcelServiceProvider`
- Core classes:
  - `src/Excel.php`
  - `src/Reader.php`
  - `src/Writer.php`
  - `src/QueuedWriter.php`
  - `src/ChunkReader.php`

The service provider registers:

- `make:export`
- `make:import`
- collection macros for spreadsheet download and storage
- query builder macros for spreadsheet download, storage, and import

## Generator Commands

Prefer generator commands for the initial class shape:

```bash
php artisan make:export ReportExport
php artisan make:export ReportExport --model=User
php artisan make:export ReportExport --model=User --query

php artisan make:import UsersImport
php artisan make:import UsersImport --model=User
```

Command implementation details:

- `make:export --model=Model --query` generates a query-based export stub
- `make:import --model=Model` generates a model import stub
- default namespaces are `App\Exports` and `App\Imports`

## Export Source Concerns

Choose one primary export source concern:

- `FromArray`
- `FromCollection`
- `FromGenerator`
- `FromIterator`
- `FromQuery`
- `FromView`

Typical selection:

- use `FromCollection` when the rows already exist in memory
- use `FromQuery` for large datasets and queue-heavy workflows
- use `FromView` when the spreadsheet shape follows a Blade table
- use `FromGenerator` or `FromIterator` when memory pressure matters

## Export Behavior Concerns

Common export concerns in this package:

- `Exportable`
- `ShouldAutoSize`
- `WithBackgroundColor`
- `WithCharts`
- `WithColumnFormatting`
- `WithColumnLimit`
- `WithColumnWidths`
- `WithConditionalSheets`
- `WithCustomCsvSettings`
- `WithCustomQuerySize`
- `WithCustomStartCell`
- `WithCustomValueBinder`
- `WithDefaultStyles`
- `WithDrawings`
- `WithEvents`
- `WithHeadings`
- `WithLimit`
- `WithMappedCells`
- `WithMapping`
- `WithMultipleSheets`
- `WithPreCalculateFormulas`
- `WithProperties`
- `WithStrictNullComparison`
- `WithStyles`
- `WithTitle`

Practical guidance:

- pair `FromQuery` with `WithMapping` when rows need transformation
- use `WithHeadings` instead of manually prepending a heading row
- use `WithMultipleSheets` when each sheet has a separate export object
- use `WithColumnFormatting` for dates, currency, percentages, and IDs that should remain numeric
- use `WithCustomCsvSettings` for delimiter, encoding, BOM, or Excel compatibility changes

## Import Target Concerns

Choose one primary import target concern:

- `ToArray`
- `ToCollection`
- `ToModel`
- `OnEachRow`

Typical selection:

- use `ToModel` when each row maps cleanly to one persisted model
- use `OnEachRow` when rows trigger imperative workflows or service calls
- use `ToCollection` or `ToArray` when the import is validated or transformed before persistence

## Import Behavior Concerns

Common import concerns in this package:

- `Importable`
- `RegistersEventListeners`
- `RemembersChunkOffset`
- `RemembersRowNumber`
- `ShouldQueueWithoutChain`
- `SkipsEmptyRows`
- `SkipsErrors`
- `SkipsFailures`
- `SkipsOnError`
- `SkipsOnFailure`
- `SkipsUnknownSheets`
- `WithBatchInserts`
- `WithCalculatedFormulas`
- `WithChunkReading`
- `WithCustomCsvSettings`
- `WithCustomValueBinder`
- `WithGroupedHeadingRow`
- `WithHeadingRow`
- `WithLimit`
- `WithReadFilter`
- `WithSkipDuplicates`
- `WithStartRow`
- `WithUpserts`
- `WithUpsertColumns`
- `WithValidation`

Practical guidance:

- pair `ToModel` with `WithBatchInserts` and `WithChunkReading` for large files
- use `WithHeadingRow` when business users control the spreadsheet columns
- use `WithValidation` and skip concerns instead of custom row guards where possible
- use `WithUpserts` for repeatable imports keyed by a natural or unique key
- use `RemembersRowNumber` when validation or audit errors need exact row references

## Queueing Notes

Queue-related behavior is part of the package surface:

- queued imports must implement `Illuminate\Contracts\Queue\ShouldQueue`
- exports may be queued explicitly with `Excel::queue()` or by using `Exportable`
- imports may be queued with `Excel::queueImport()` or `Importable::queue()`
- queued imports and exports use temporary files and chunk jobs internally

For distributed workers:

- configure `excel.temporary_files.remote_disk`
- use a shared disk accessible by all workers
- consider `excel.temporary_files.remote_prefix`
- review `force_resync_remote` behavior when troubleshooting temp-file sync issues

## Macros

The package adds convenience macros:

- collection download helper
- collection store helper
- query builder `downloadExcel`
- query builder `storeExcel`
- query builder `import`
- query builder `importAs`

Prefer dedicated import and export classes when the spreadsheet workflow contains mapping, validation, formatting, or queueing logic.

## Configuration Map

Important `config/excel.php` sections:

- `exports.chunk_size`
- `exports.pre_calculate_formulas`
- `exports.strict_null_comparison`
- `exports.csv.*`
- `exports.properties.*`
- `imports.read_only`
- `imports.ignore_empty`
- `imports.heading_row.formatter`
- `imports.csv.*`
- `imports.cells.middleware`
- `imports.properties.*`
- `extension_detector.*`
- `value_binder.default`
- `cache.driver`
- `cache.batch.memory_limit`
- `cache.illuminate.store`
- `temporary_files.local_path`
- `temporary_files.remote_disk`
- `temporary_files.remote_prefix`
- `temporary_files.force_resync_remote`
- `transactions.handler`

When behavior is surprising, inspect config before rewriting application logic.

## Testing Reference

The package fake supports assertions for:

- `assertDownloaded()`
- `assertStored()`
- `assertQueued()`
- `assertQueuedWithChain()`
- `assertImported()`
- `assertExportedInRaw()`
- regex matching after `matchByRegex()`

Use the fake when testing:

- controller actions returning spreadsheet downloads
- storage dispatch to the right disk and filename
- queued spreadsheet jobs
- import dispatch with the correct import class

Use deeper content assertions only when the workbook bytes or transformed sheet structure are the feature under test.

## Good Defaults

- default to `FromQuery` over `FromCollection` for large exports
- default to `WithHeadingRow` for user-provided spreadsheets with named columns
- default to queueing for large imports and exports
- default to package concerns before writing custom spreadsheet plumbing
- default to `Excel::fake()` for workflow tests

## Common Failure Modes

- queuing an import without `ShouldQueue`
- reading heading keys without `WithHeadingRow`
- loading too much data into memory with `FromCollection`
- missing shared temporary storage in a distributed queue setup
- hand-parsing CSV instead of using package CSV settings
- skipping validation and then debugging database exceptions later
