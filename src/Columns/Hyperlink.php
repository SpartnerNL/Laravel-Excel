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

    /**
     * @param callable|string $url
     *
     * @return $this
     */
    public function url($url)
    {
        if (is_callable($url)) {
            $this->urlCallback = $url;
        } else {
            $this->url = $url;
        }

        return $this;
    }

    /**
     * @param callable|string $tooltip
     *
     * @return $this
     */
    public function tooltip($tooltip)
    {
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
