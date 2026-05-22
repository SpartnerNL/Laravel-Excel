<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use PHPUnit\Framework\Assert;

class QueuedExportWithLocalePreferences implements FromCollection, HasLocalePreference
{
    use Exportable;

    /**
     * QueuedExportWithLocalePreferences constructor.
     */
    public function __construct(
        protected string $locale,
    ) {
    }

    public function collection(): Collection
    {
        return collect([
            new User([
                'firstname' => 'Patrick',
                'lastname'  => 'Brouwers',
            ]),
        ]);
    }

    public function preferredLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * @param  iterable  $rows
     */
    public function prepareRows($rows): iterable
    {
        Assert::assertEquals('ru', app()->getLocale());

        app()->bind('queue-has-correct-locale', fn () => true);

        return $rows;
    }
}
