<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Helpers;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\RichText\Run;
use PhpOffice\PhpSpreadsheet\Style\Font;

class RichTextReader
{
    public static function toHtml(Cell $cell): string
    {
        $value = $cell->getValue();

        if (!$value instanceof RichText) {
            return (string) $value;
        }

        $cellData = '';

        foreach ($value->getRichTextElements() as $element) {
            if (!$element instanceof Run) {
                $cellData .= htmlspecialchars($element->getText());

                continue;
            }

            $cellData .= '<span style="' . static::toCss($element->getFont()) . '">';

            $cellEnd = '';
            if ($element->getFont()?->getSuperscript()) {
                $cellData .= '<sup>';
                $cellEnd = '</sup>';
            } elseif ($element->getFont()?->getSubscript()) {
                $cellData .= '<sub>';
                $cellEnd = '</sub>';
            }

            $cellData .= htmlspecialchars($element->getText());
            $cellData .= $cellEnd;
            $cellData .= '</span>';
        }

        return $cellData;
    }

    protected static function toCss(?Font $font): string
    {
        if (!$font instanceof Font) {
            return '';
        }

        $css = [];

        if ($font->getBold()) {
            $css['font-weight'] = 'bold';
        }

        $underlined = $font->getUnderline() !== Font::UNDERLINE_NONE;

        if ($underlined && $font->getStrikethrough()) {
            $css['text-decoration'] = 'underline line-through';
        } elseif ($underlined) {
            $css['text-decoration'] = 'underline';
        } elseif ($font->getStrikethrough()) {
            $css['text-decoration'] = 'line-through';
        }

        if ($font->getItalic()) {
            $css['font-style'] = 'italic';
        }

        $css['color']       = '#' . $font->getColor()->getRGB();
        $css['font-family'] = '\'' . $font->getName() . '\'';
        $css['font-size']   = $font->getSize() . 'pt';

        $pairs = [];
        foreach ($css as $property => $value) {
            $pairs[] = $property . ':' . $value;
        }

        return implode('; ', $pairs);
    }
}
