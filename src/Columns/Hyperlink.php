<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Columns;

use Closure;
use PhpOffice\PhpSpreadsheet\Calculation\Exception as CalculationException;
use PhpOffice\PhpSpreadsheet\Cell\Cell;

class Hyperlink extends Column
{
    protected ?Closure $urlCallback = null;

    protected ?string $url = null;

    protected ?Closure $tooltipCallback = null;

    protected ?string $tooltip = null;

    protected bool $wantsUrl = false;

    protected bool $wantsTooltip = false;

    /**
     * Read or write the hyperlink url. Called without arguments while importing,
     * the column reads the url of the cell instead of its value.
     */
    public function url(string|Closure|null $url = null): static
    {
        if ($url === null) {
            $this->wantsUrl = true;

            return $this;
        }

        if ($url instanceof Closure) {
            $this->urlCallback = $url;
        } else {
            $this->url = $url;
        }

        return $this;
    }

    /**
     * Read or write the hyperlink tooltip. Called without arguments while importing,
     * the column reads the tooltip of the cell instead of its value.
     */
    public function tooltip(string|Closure|null $tooltip = null): static
    {
        if ($tooltip === null) {
            $this->wantsTooltip = true;

            return $this;
        }

        if ($tooltip instanceof Closure) {
            $this->tooltipCallback = $tooltip;
        } else {
            $this->tooltip = $tooltip;
        }

        return $this;
    }

    /**
     * Hyperlinks are only loaded when the reader is not in read-only mode. Reading
     * the cell's value needs none of that, so only the url/tooltip modes ask.
     */
    public function needsStyleInformation(): bool
    {
        return $this->wantsUrl || $this->wantsTooltip;
    }

    protected function configure(): void
    {
        $this->writing(function (Cell $cell): void {
            $value = (string) $cell->getValue();

            $cell
                ->getHyperlink()
                ->setUrl($this->url ?: $value)
                ->setTooltip($this->tooltip ?: $this->url ?: $value);
        });
    }

    /**
     * @throws CalculationException
     */
    protected function value(Cell $cell): mixed
    {
        if ($this->wantsUrl) {
            return $cell->getHyperlink()->getUrl();
        }

        if ($this->wantsTooltip) {
            return $cell->getHyperlink()->getTooltip();
        }

        return parent::value($cell);
    }

    protected function resolveValue(mixed $data): mixed
    {
        if ($this->urlCallback instanceof Closure) {
            $this->url = ($this->urlCallback)($data);
        }

        if ($this->tooltipCallback instanceof Closure) {
            $this->tooltip = ($this->tooltipCallback)($data);
        }

        return parent::resolveValue($data);
    }
}
