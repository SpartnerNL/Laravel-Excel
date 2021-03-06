<?php

namespace Maatwebsite\Excel\Helpers;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\RichText\Run;
use PhpOffice\PhpSpreadsheet\Style\Font;

class RichTextReader
{
    public static function toHtml(Cell $cell): string
    {
        $cellData = '';

        // Loop through rich text elements
        $elements = $cell->getValue()->getRichTextElements();
        foreach ($elements as $element) {
            // Rich text start?
            if ($element instanceof Run) {
                $cellData .= '<span style="' . static::toCss($element->getFont()) . '">';

                $cellEnd = '';
                if ($element->getFont()->getSuperscript()) {
                    $cellData .= '<sup>';
                    $cellEnd  = '</sup>';
                } elseif ($element->getFont()->getSubscript()) {
                    $cellData .= '<sub>';
                    $cellEnd  = '</sub>';
                }

                // Convert UTF8 data to PCDATA
                $cellText = $element->getText();
                $cellData .= htmlspecialchars($cellText);

                $cellData .= $cellEnd;

                $cellData .= '</span>';
            } else {
                // Convert UTF8 data to PCDATA
                $cellText = $element->getText();
                $cellData .= htmlspecialchars($cellText);
            }
        }

        return $cellData;
    }

    protected static function toCss(Font $pStyle): string
    {
        // Construct CSS
        $css = [];

        // Create CSS
        if ($pStyle->getBold()) {
            $css['font-weight'] = 'bold';
        }
        if ($pStyle->getUnderline() != Font::UNDERLINE_NONE && $pStyle->getStrikethrough()) {
            $css['text-decoration'] = 'underline line-through';
        } elseif ($pStyle->getUnderline() != Font::UNDERLINE_NONE) {
            $css['text-decoration'] = 'underline';
        } elseif ($pStyle->getStrikethrough()) {
            $css['text-decoration'] = 'line-through';
        }
        if ($pStyle->getItalic()) {
            $css['font-style'] = 'italic';
        }

        $css['color']       = '#' . $pStyle->getColor()->getRGB();
        $css['font-family'] = '\'' . $pStyle->getName() . '\'';
        $css['font-size']   = $pStyle->getSize() . 'pt';

        $pairs = [];
        foreach ($css as $property => $value) {
            $pairs[] = $property . ':' . $value;
        }

        return implode('; ', $pairs);
    }
}
