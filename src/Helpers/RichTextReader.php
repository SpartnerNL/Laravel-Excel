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
            return htmlspecialchars((string) $value);
        }

        $cellData = '';

        foreach ($value->getRichTextElements() as $element) {
            if (!$element instanceof Run) {
                $cellData .= htmlspecialchars($element->getText());

                continue;
            }

            $cellData .= '<span style="' . htmlspecialchars(static::toCss($element->getFont()), ENT_COMPAT | ENT_SUBSTITUTE) . '">';

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

        $css['color'] = '#' . $font->getColor()->getRGB();

        if (($fontName = static::sanitizeFontName($font->getName())) !== '') {
            $css['font-family'] = '\'' . $fontName . '\'';
        }

        $css['font-size'] = (float) $font->getSize() . 'pt';

        $pairs = [];
        foreach ($css as $property => $value) {
            $pairs[] = $property . ':' . $value;
        }

        return implode('; ', $pairs);
    }

    /**
     * Font names come straight from the spreadsheet, so only keep characters a
     * font name legitimately needs. Escaping alone is not enough: the browser
     * decodes the attribute before parsing the css, so a quote could still
     * close the font-family string and inject other declarations.
     */
    protected static function sanitizeFontName(?string $name): string
    {
        return trim(preg_replace('/[^\p{L}\p{N} _.,+\-]/u', '', (string) $name) ?? '');
    }
}
