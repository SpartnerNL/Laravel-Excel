<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Columns;

use BackedEnum;
use UnitEnum;

/**
 * @template TEnum of UnitEnum
 */
class Enum extends Column
{
    /** @var class-string<TEnum>|null */
    protected ?string $enumClass = null;

    protected bool $byName = false;

    /**
     * @template T of UnitEnum
     *
     * @param  class-string<T>  $enumClass
     *
     * @phpstan-this-out self<T>
     */
    public function of(string $enumClass): static
    {
        $this->enumClass = $enumClass;

        return $this;
    }

    public function byName(): static
    {
        $this->byName = true;

        return $this;
    }

    protected function toExcelValue(mixed $value): int|string|null
    {
        if ($value instanceof BackedEnum) {
            return $this->byName ? $value->name : $value->value;
        }

        if ($value instanceof UnitEnum) {
            return $value->name;
        }

        return $value;
    }

    /**
     * @return TEnum|null
     */
    protected function cast(mixed $value): mixed
    {
        if ($this->enumClass === null) {
            return $value;
        }

        if (!$this->byName && is_subclass_of($this->enumClass, BackedEnum::class)) {
            return $this->enumClass::tryFrom($value);
        }

        foreach ($this->enumClass::cases() as $case) {
            if ($case->name === (string) $value) {
                return $case;
            }
        }

        return null;
    }
}
