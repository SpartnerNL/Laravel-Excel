# Extending

### Events

The export process has a few events you can leverage to interact with the underlying 
classes to add custom behaviour to the export.

You are able to hook into the parent package by using events.
No need to use convenience methods like "query" or "view", if you need full control over the export.

The events will be activated by adding the `WithEvents` concern. Inside the `registerEvents` method, you 
will have to return an array of events. The key is the Fully Qualified Name (FQN) of the event and the value is a callable event listener.
This can either be a closure, array-callable or invokable class.

```php
class InvoicesExport implements WithEvents
{
    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            // Handle by a closure.
            BeforeExport::class => function(BeforeExport $event) {
                $event->writer->getProperties()->setCreator('Patrick');
            },
            
            // Array callable, refering to a static method.
            BeforeWriting::class => [self::class, 'beforeWriting'],
            
            // Using a class with an __invoke method.
            BeforeSheet::class => new BeforeSheetHandler()
        ];
    }
    
    public static function beforeWriting(BeforeWriting $event) 
    {
        //
    }
}
```

Do note that using a `Closure` will not be possible in combination with queued exports, as PHP cannot serialize the closure.
In those cases it might be better to use the `RegistersEventListeners` trait.

#### Auto register event listeners

By using the `RegistersEventListeners` trait you can auto-register the event listeners,
without the need of using the `registerEvents`. The listener will only be registered if the method is created. 

```php
class InvoicesExport implements WithEvents
{
    use Exportable, RegistersEventListeners;
    
    public static function beforeExport(BeforeExport $event)
    {
        //
    }

    public static function beforeWriting(BeforeWriting $event)
    {
        //
    }

    public static function beforeSheet(BeforeSheet $event)
    {
        //
    }

    public static function afterSheet(AfterSheet $event)
    {
        //
    }
}
```

#### Available events

| Event name | Payload | Explanation |
|---- |----| ----|
|`Maatwebsite\Excel\Events\BeforeExport` | `$event->writer : Writer` | Event gets raised at the start of the process. | 
| `Maatwebsite\Excel\Events\BeforeWriting` | `$event->writer : Writer` | Event gets raised before the download/store starts. |
| `Maatwebsite\Excel\Events\BeforeSheet` | `$event->sheet : Sheet` | Event gets raised just after the sheet is created. |
| `Maatwebsite\Excel\Events\AfterSheet` | `$event->sheet : Sheet` | Event gets raised at the end of the sheet process. |


### Macroable

Both `Writer` and `Sheet` are macroable which means they can easily be extended to fit your needs. 
Both Writer and Sheet have a `->getDelegate()` method which returns the underlying PhpSpreadsheet class. 
This will allow you to add shortcuts to PhpSpreadsheets method that are not available in this package. 



#### Writer

```php
Writer::macro('setCreator', function (Writer $writer, string $creator) {
    $writer->getDelegate()->getProperties()->setCreator($creator);
});
```

#### Sheet

```php
Sheet::macro('setOrientation', function (Sheet $sheet, $orientation) {
    $sheet->getDelegate()->getPageSetup()->setOrientation($orientation);
});
```

You could also add some shortcut to style cells. Feel free to use this macro, or be creative and invent your own syntax!

```php
Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array style) {
    $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
});
```

Above examples could be used as:

```php
class InvoicesExport implements WithEvents
{
    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            BeforeExport::class  => function(BeforeExport $event) {
                $event->writer->setCreator('Patrick');
            },
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);

                $event->sheet->styleCells(
                    'B2:G8',
                    [
                        'borders' => [
                            'outline' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                                'color' => ['argb' => 'FFFF0000'],
                            ],
                        ]
                    ]
                );
            },
        ];
    }
}
```

For PhpSpreadsheet methods, please refer to their documentation: https://phpspreadsheet.readthedocs.io/
