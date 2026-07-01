<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use PHPUnit\Framework\Assert;

/**
 * @implements FromCollection<int, User>
 */
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

    /**
     * @return Collection<int, User>
     */
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
     * @param  iterable<array-key, mixed>  $rows
     * @return iterable<array-key, mixed>
     */
    public function prepareRows($rows): iterable
    {
        Assert::assertSame('ru', app()->getLocale());

        app()->bind('queue-has-correct-locale', fn () => true);

        return $rows;
    }
}
