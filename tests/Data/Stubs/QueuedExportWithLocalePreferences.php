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
     * @var string
     */
    protected $locale;

    /**
     * QueuedExportWithLocalePreferences constructor.
     *
     * @param string $locale
     */
    public function __construct(string $locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return collect([
            new User([
                'firstname' => 'Patrick',
                'lastname'  => 'Brouwers',
            ]),
        ]);
    }

    /**
     * @return string|null
     */
    public function preferredLocale()
    {
        return $this->locale;
    }

    /**
     * @param iterable $rows
     * @return iterable
     */
    public function prepareRows($rows)
    {
        Assert::assertEquals('ru', app()->getLocale());

        app()->bind('queue-has-correct-locale', function () {
            return true;
        });

        return $rows;
    }
}
