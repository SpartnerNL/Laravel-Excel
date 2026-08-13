<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Columns;

trait Styleable
{
    /**
     * @var array<string, mixed>|null
     */
    protected ?array $style = null;

    /**
     * @param  array<string, mixed>  $style
     */
    public function style(array $style): static
    {
        // Replacing, not array_merge_recursive: merging turns a repeated scalar
        // into an array, so calling style() twice would yield ['bold' => [true, true]].
        $this->style = array_replace_recursive($this->style ?? [], $style);

        return $this;
    }

    public function font(string $name, ?float $size = null): static
    {
        $this->style['font']['name'] = $name;

        if ($size !== null) {
            $this->textSize($size);
        }

        return $this;
    }

    public function textSize(float $size): static
    {
        $this->style['font']['size'] = $size;

        return $this;
    }

    public function bold(bool $bold = true): static
    {
        $this->style['font']['bold'] = $bold;

        return $this;
    }

    public function italic(bool $italic = true): static
    {
        $this->style['font']['italic'] = $italic;

        return $this;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getStyle(): ?array
    {
        return $this->style;
    }
}
