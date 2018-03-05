# From Query

In the previous example, we did the query inside the export class. 
While this is a good solution for small exports, 
for bigger exports this will quickly become badly performant.

By using the `FromQuery` concern, we can prepare a query for an export. Behind the scenes this query is executed in chunks.

In the `InvoicesExport` class, add the `FromQuery` concern, and return a query.

```php
namespace App\Exports;

class InvoicesExport implements FromQuery
{
    use Exportable;

    public function query()
    {
        return Invoice::query();
    }
}
```

We can still download the export in the same way:

```php
return (new InvoicesExport)->download('invoices.xlsx');
```

### Customizing the query

It's easy to pass on custom parameters to the query, 
by simply passing them as dependencies to the export class.

#### As constructor param

```php
namespace App\Exports;

class InvoicesExport implements FromQuery
{
    use Exportable;

    public function __construct(int $year)
    {
        $this->year = $year;
    }

    public function query()
    {
        return Invoice::query()->whereYear('created_at', $this->year);
    }
}
```

The year can now be passed as dependency to the export class:

```php
return (new InvoicesExport(2018))->download('invoices.xlsx');
```

#### As setter

```php
namespace App\Exports;

class InvoicesExport implements FromQuery
{
    use Exportable;

    public function forYear(int $year)
    {
        $this->year = $year;
        
        return $this;
    }

    public function query()
    {
        return Invoice::query()->whereYear('created_at', $this->year);
    }
}
```

We can adjust the year now by using the `forYear` method.

```php
return (new InvoicesExport)->forYear(2018)->download('invoices.xlsx');
```