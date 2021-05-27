<?php

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Cell\Cell;

class Hyperlink extends Column
{
    /**
     * @var callable|null
     */
    protected $urlCallback;

    /**
     * @var string|null
     */
    protected $url;

    /**
     * @var callable|null
     */
    protected $tooltipCallback;

    /**
     * @var string|null
     */
    protected $tooltip;

    /**
     * @var bool
     */
    protected $wantsUrl = false;

    /**
     * @var bool
     */
    protected $wantsTooltip = false;

    protected function __construct(string $title, $attribute)
    {
        parent::__construct($title, $attribute);

        $this->writing(function (Cell $cell) {
            $cell
                ->getHyperlink()
                ->setUrl($this->url ?: $cell->getValue())
                ->setTooltip($this->tooltip ?: $this->url ?: $cell->getValue());
        });
    }

    protected function value(Cell $cell)
    {
        if ($this->wantsUrl) {
            return $cell->getHyperlink()->getUrl();
        }

        if ($this->wantsTooltip) {
            return $cell->getHyperlink()->getTooltip();
        }

        return parent::value($cell);
    }

    /**
     * @param callable|string|null $url
     *
     * @return $this
     */
    public function url($url = null)
    {
        if (null === $url) {
            $this->wantsUrl = true;

            return $this;
        }

        if (is_callable($url)) {
            $this->urlCallback = $url;
        } else {
            $this->url = $url;
        }

        return $this;
    }

    /**
     * @param callable|string|null $tooltip
     *
     * @return $this
     */
    public function tooltip($tooltip = null)
    {
        if (null === $tooltip) {
            $this->wantsTooltip = true;

            return $this;
        }

        if (is_callable($tooltip)) {
            $this->tooltipCallback = $tooltip;
        } else {
            $this->tooltip = $tooltip;
        }

        return $this;
    }

    protected function resolveValue($data)
    {
        if (is_callable($this->urlCallback)) {
            $this->url = ($this->urlCallback)($data);
        }

        if (is_callable($this->tooltipCallback)) {
            $this->tooltip = ($this->tooltipCallback)($data);
        }

        return parent::resolveValue($data);
    }
}
