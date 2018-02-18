# Laravel Excel 3.0

[![Build Status](https://travis-ci.org/Maatwebsite/Laravel-Excel.svg?branch=3.0)](https://travis-ci.org/Maatwebsite/Laravel-Excel)
[![StyleCI](https://styleci.io/repos/14259390/shield?branch=3.0)](https://styleci.io/repos/14259390)
[![Latest Stable Version](https://poser.pugx.org/maatwebsite/excel/v/stable.png)](https://packagist.org/packages/maatwebsite/excel)
[![Total Downloads](https://poser.pugx.org/maatwebsite/excel/downloads.png)](https://packagist.org/packages/maatwebsite/excel)
[![License](https://poser.pugx.org/maatwebsite/excel/license.png)](https://packagist.org/packages/maatwebsite/excel)

[![Help the project](http://www.pledgie.com/campaigns/30385.png?skin_name=chrome)](http://pledgie.com/campaigns/30385)

## Introduction

Laravel Excel 3.0 is intended at being a simple, but elegant wrapper around PhpSpreadsheet with the goal of simplifying
exports. 

Currently only exports are supported. Imports will be added in version 3.1.

#### Laravel Excel 2.1

2.1 had a completely different approach. This approach will no longer be supported. Feel free to keep using 2.1 if you don't want to 
upgrade your export code. 2.1 will keep receiving security fixes and community provided bug fixes, but will no longer be actively maintained.

## Supported Versions

| Version | Laravel Version | PhpSpreadsheet Version | Php Version | Support |
|---- |----|----|----|----|
| 2.1 | <=5.6 | - | <=7.0 | Security fixes and community fixes |
| 3.0 | ^5.6 | ^1.1 | ^7.1 | New features |

## Installation

Install via composer:

```
composer require "maatwebsite/excel:~3.0"
```

The ServiceProvider and Facade will be auto-discovered by Laravel.

## Exports

### Simple exports

```php
// e.g. app/Exports/InvoicesExport.php
class InvoicesExport implements FromQuery, WithMapping, WithHeadings
{
    protected $year;
    
    public function __construct(int $year)
    {
        $this->year = $year;
    }

    public function query()
    {
        return Invoice::query()->whereYear('created_at', $this->year);
    }
    
    public function headings(): array
    {
        return [
            '#',
            'Date',
        ];
    }
    
    public function map($row): array
    {
        return [
            $row->invoice_number,
            \PhpOffice\PhpSpreadsheet\Shared\Date::dateTimeToExcel($invoice->created_at),
        ];
    }
}

// Controller
public function download() 
{
    // Downloads the file, chooses the writer type based on the extension.
    return Excel::download(new InvoicesExport(2018), 'invoices.xlsx');
    
    // Force a writer type.
    return Excel::download(new InvoicesExport(2018), 'invoices.xlsx', Excel::XLSX);
}
```

### Exporting a view

```php
// e.g. app/Exports/InvoicesExport.php
class InvoicesExport implements FromView
{
    public function view()
    {
        return view('exports.invoices', [
            'invoices' => Invoice::all()
        ]);
    }
}
```

### Formatting columns

You can easily format an entire column, by using WithColumnFormatting.
In case you want something more complicated, it's suggested to use the InteractsWithSheet concern.

In case of working with dates, it's recommended to use `\PhpOffice\PhpSpreadsheet\Shared\Date::dateTimeToExcel()` in your mapping.

```php
// e.g. app/Exports/InvoicesExport.php
class InvoicesExport implements WithColumnFormatting, WithMapping
{
    public function map($invoice): array
    {
        return [
            $invoice->invoice_number,
            \PhpOffice\PhpSpreadsheet\Shared\Date::dateTimeToExcel($invoice->created_at),
            $invoice->total
        ];
    }
    
    /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'B' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DDMMYYYY,
            'C' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
        ];
    }
}
```

### Multiple sheets

```php
// e.g. app/Exports/InvoicesExport.php
class InvoicesExport implements WithMultipleSheets
{
    protected $year;
    
    public function __construct(int $year)
    {
        $this->year = $year;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        for ($month = 1; $month <= 12; $month++) {
            $sheets[] = new InvoicesPerMonthSheet($this->year, $month);
        }

        return $sheets;
    }
}

// e.g. app/Exports/InvoicesPerMonthSheet.php
class InvoicesPerMonthSheet implements FromQuery, WithTitle
{
    private $month;
    private $year;

    public function __construct(int $year, int $month)
    {
        $this->month = $month;
        $this->year  = $year;
    }

    /**
     * @return Builder
     */
    public function query()
    {
        return Invoice
            ::query()
            ->whereYear('created_at', $this->year)
            ->whereMonth('created_at', $this->month);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Month ' . $this->month;
    }
}

// Controller
public function download() 
{
    // This will now download an xlsx of all invoices in 2018, with 12 worksheets representing each month of the year.
    return Excel::download(new InvoicesExport(2018), 'invoices.xlsx');
}
```

### Interacting with PhpSpreadsheet

Instead of providing a wrapper method for each function PhpSpreadsheet offers, we make sure you can 
easily use them directly. 

An export can completely be executed by only using these "interactions". 
No need to use convenience methods like "query" or "view", if you need full control over the export.

```php
// e.g. app/Exports/InvoicesExport.php
class InvoicesExport implements InteractsWithExport, InteractsWithSheet
{
    public function interact(Spreadsheet $spreadsheet)
    {
        $spreadsheet->getProperties()->setCreator('Patrick');
    }
    
    public function interactWithSheet(Worksheet $sheet)
    {
        $sheet->getPageSetup()
              ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
    }
}
```

### Storing export on disk

Unlike version 2.1, 3.0 is able to store files on any filesystem that Laravel supports.

```php
// Controller
public function download() 
{
    // Store on default disk
    Excel::store(new InvoicesExport(2018), 'invoices.xlsx');
    
    // Store on a different disk (e.g. s3)
    Excel::store(new InvoicesExport(2018), 'invoices.xlsx', 's3');
    
    // Store on a different disk with a defined writer type. 
    Excel::store(new InvoicesExport(2018), 'invoices.xlsx', 's3', Excel::XLSX);
}
```

### Self exportable Exports

It's also possible to turn it into a one-liner. Add the Exportable trait to your export. This will allow you to call the
`download` and `store` method right on the export, instead of needing to use the facade / inject the Excel class.

```php
class InvoicesExport
{
    use Exportable;
}

// Controller
public function download() 
{
    return (new InvoicesExport(2018))->download('invoices.xlsx');
}
```

### More complex exports with dependencies

In case your export needs dependencies (repositories / query classes), you can easily inject those dependencies in your Export class.

```php
class InvoicesExport implements WithQuery
{
    use Exportable;

    protected $year;
    protected $repository;

    public function __construct(InvoiceRepository $repository)
    {
        $this->repository = $repository;
    }
    
    public function forYear(int $year): self
    {
        $this->year = $year;
    
        return $this;
    }
    
    public function query()
    {
        return $this->repository->queryForYeary($this->year);
    }
}

// Controller
public function download(InvoiceExport $export) 
{
    return $export->forYear(2018)->download('invoices.xlsx');
}
```

### Export concerns overview

| Interface | Explanation |
|---- |----|
|`Maatwebsite\Excel\Concerns\FromQuery` | Will use an Eloquent query to populate the export. | 
| `Maatwebsite\Excel\Concerns\FromView` | Will use a (blade) view to to populate the export. |
| `Maatwebsite\Excel\Concerns\WithTitle` | Will set the Workbook or Worksheet title |
| `Maatwebsite\Excel\Concerns\WithHeadings` | Will prepend given heading row. |
| `Maatwebsite\Excel\Concerns\WithMapping` | Gives you the possibility to format the row before it's written to the file. |
| `Maatwebsite\Excel\Concerns\WithColumnFormatting` | Gives you the ability to format certain columns. |
| `Maatwebsite\Excel\Concerns\WithMultipleSheets` | Enables multi-sheet support. Each sheet can have its own concerns (expect the this one) |
| `Maatwebsite\Excel\Concerns\ShouldAutoSize` | Auto-sizes the columns in the worksheet |
| `Maatwebsite\Excel\Concerns\InteractsWithExport` | Gives you a hook into the PhpSpreadsheet Spreadsheet class. |
| `Maatwebsite\Excel\Concerns\InteractsWithSheet` | Gives you a hook into the PhpSpreadsheet Worksheet class. |

| Trait | Explanation |
|---- |----|
|`Maatwebsite\Excel\Concerns\Exportable` | Will add download/store abilities right on the export class itself. | 

## Support

Support only through Github. Please don't mail us about issues, make a Github issue instead.

## Contributing

**ALL** bug fixes should be made to appropriate branch (e.g. `3.0` for 3.0.* bug fixes). Bug fixes should never be sent to the `master` branch.

More about contributing can be found at: [http://www.maatwebsite.nl/laravel-excel/docs/getting-started#contributing](http://www.maatwebsite.nl/laravel-excel/docs/getting-started#contributing)

## License

This package is licensed under MIT. You are free to use it in personal and commercial projects. The code can be forked and modified, but the original copyright author should always be included!
