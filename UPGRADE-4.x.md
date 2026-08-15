UPGRADE FROM 3.1 to 4.0
=======================

## Minimum dependency versions

Laravel-Excel 4.0 requires PHP 8.3 or higher, and Laravel 12 or higher.
If you are using an older version of PHP or Laravel, you will need to upgrade those first before upgrading to Laravel-Excel 4.0.

## PhpSpreadsheet 5

The underlying `phpoffice/phpspreadsheet` dependency has been upgraded from `^1.30` to `^5.3`.
Code that only uses Laravel-Excel's own API (exports, imports, concerns) is largely unaffected, but anywhere you interact
with PhpSpreadsheet objects directly you should review your code against the PhpSpreadsheet breaking changes. Common places
where this happens:

- Event listeners registered via `WithEvents` (e.g. styling a sheet through `$event->sheet->getDelegate()` in `AfterSheet`).
- The `WithCharts` and `WithDrawings` concerns, which return PhpSpreadsheet chart and drawing objects.
- Custom value binders (`WithCustomValueBinder`) and anything extending `DefaultValueBinder`.
- Direct use of PhpSpreadsheet classes such as `NumberFormat`, `Style`, or `Coordinate` in your exports and imports.

See the PhpSpreadsheet [changelog](https://github.com/PHPOffice/PhpSpreadsheet/blob/master/CHANGELOG.md) and
[release notes](https://github.com/PHPOffice/PhpSpreadsheet/releases) for the 2.0, 3.0, 4.0 and 5.0 breaking changes.

## Fully typed code base

Native PHP types were added across the entire code base, including public methods and interfaces.
If you are implementing any of the interfaces or overriding any methods,
you will need to update your code to match the new method signatures to include native types:

```php
class MyExport implements FromArray
{
    public function array(): array
    {
    }
}
```

Because return types are now enforced natively, some signatures are stricter than the 3.1 docblocks suggested:

- `Exportable::store()` returns `bool|PendingDispatch|PendingBatch` and `Exportable::queue()` returns `PendingDispatch|PendingBatch`.
- `Importable::import()` returns `Importer|PendingDispatch|PendingBatch` and `Importable::queue()` returns `PendingDispatch|PendingBatch`
  (it no longer advertises returning the importable instance itself).

## FromScout

To keep laravel/scout an optional dependency, `FromQuery` no longer supports returning a Scout `Builder` instance.
Use the new `FromScout` export interface instead.

## Job batching (`ShouldBatch`)

Queued exports and chunked queued imports can now implement the `Maatwebsite\Excel\Concerns\ShouldBatch` marker interface
to be dispatched as a [job batch](https://laravel.com/docs/queues#job-batching) instead of a chain. When an export or
import implements `ShouldBatch`, methods such as `Excel::store()`, `Excel::queue()`, `Exportable::queue()` and
`Importable::queue()` return an `Illuminate\Bus\PendingBatch` instead of a `PendingDispatch` — update any code that
type-hints these return values.

## Queue attributes

Imports can specify their queue and connection with Laravel's native `#[Queue]` and `#[Connection]` attributes
(Laravel 13), in addition to the existing `queue` and `connection` properties.

## Configuration

The published configuration file (`config/excel.php`) has no key changes compared to 3.1 — there is no need to
republish or migrate your configuration.

## Export and Import marker interfaces

Two new marker interfaces have been introduced: `Maatwebsite\Excel\Concerns\Export` and `Maatwebsite\Excel\Concerns\Import`.
All export-related concerns (e.g. `FromArray`, `FromCollection`, `FromQuery`) now extend `Export`, and all import-related
concerns (e.g. `ToModel`, `ToArray`, `ToCollection`, `OnEachRow`) now extend `Import`. Because your export and import classes already
implement those concerns, they automatically satisfy the new interfaces — no changes are required in most cases.

You can now use `Export` and `Import` as type hints wherever you previously used `object` to represent an export or import:

```php
use Maatwebsite\Excel\Concerns\Export;

public function handle(Export $export): void { ... }
```

### WithMultipleSheets

The `sheets()` method docblock return type has been narrowed from `array<int|string, object>` to `array<int|string, Export|Import>`.
Sheets returned from this method should implement at least one export or import concern interface, which is almost certainly
already the case.
It is also necessary for your import or export class to implement `Export` or `Import` accordingly if you use this concern.

### Event::getConcernable()

`Event::getConcernable()` now returns `Export|Import|null` instead of `object`. If you call this method and rely on the
`object` return type in a type-strict context, update your code accordingly.

## Removed requirements

The `ext-json` requirement has been dropped (JSON support is bundled with PHP 8). No action is required.
