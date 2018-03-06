# From View

Exports can be created from a Blade view, by using the `FromView` concern.

```php
use Illuminate\Contracts\View\View;

class InvoicesExport implements FromView
{
    public function view(): View
    {
        return view('exports.invoices', [
            'invoices' => Invoice::all()
        ]);
    }
}
```