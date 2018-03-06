# Multiple Sheets

To allow the export to have multiple sheets, the `WithMultipleSheets` concern should be used. 
The `sheets()` method expect an array of sheet export objects to be returned.

```php
class InvoicesExport implements WithMultipleSheets
{
    use Exportable;

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
```

The `InvoicesPerMonthSheet` can implement concerns like `FromQuery`, `FromCollection`, ... 

```php
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
```

This will now download an xlsx of all invoices in 2018, with 12 worksheets representing each month of the year.

```php
public function download() 
{
    return (new InvoicesExport(2018))->download('xlsx');
}
```