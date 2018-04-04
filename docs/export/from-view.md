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

It will convert an HTML table into an Excel spreadsheet. For example; `users.blade.php`:

```blade
<table>
    <thead>
    <tr>
        <th>Name</th>
        <th>Email</th>
    </tr>
    </thead>
    <tbody>
    @foreach($users as $user)
        <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
```
