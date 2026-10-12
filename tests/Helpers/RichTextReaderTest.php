<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Helpers;

use Iterator;
use Maatwebsite\Excel\Helpers\RichTextReader;
use Maatwebsite\Excel\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PHPUnit\Framework\Attributes\DataProvider;

final class RichTextReaderTest extends TestCase
{
    private Worksheet $sheet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sheet = (new Spreadsheet)->getActiveSheet();
    }

    public function test_returns_plain_string_value_as_string(): void
    {
        $this->sheet->getCell('A1')->setValue('hello world');

        $this->assertSame('hello world', RichTextReader::toHtml($this->sheet->getCell('A1')));
    }

    public function test_plain_text_element_is_returned_without_span(): void
    {
        $richText = new RichText;
        $richText->createText('plain text');
        $this->sheet->getCell('A1')->setValue($richText);

        $result = RichTextReader::toHtml($this->sheet->getCell('A1'));

        $this->assertSame('plain text', $result);
        $this->assertStringNotContainsString('<span', $result);
    }

    public function test_html_special_chars_are_escaped_in_plain_text_element(): void
    {
        $richText = new RichText;
        $richText->createText('<b>not bold</b>');
        $this->sheet->getCell('A1')->setValue($richText);

        $result = RichTextReader::toHtml($this->sheet->getCell('A1'));

        $this->assertStringContainsString('&lt;b&gt;not bold&lt;/b&gt;', $result);
    }

    public function test_run_element_is_wrapped_in_span(): void
    {
        $richText = new RichText;
        $richText->createTextRun('styled text');
        $this->sheet->getCell('A1')->setValue($richText);

        $result = RichTextReader::toHtml($this->sheet->getCell('A1'));

        $this->assertStringContainsString('<span', $result);
        $this->assertStringContainsString('styled text', $result);
        $this->assertStringContainsString('</span>', $result);
    }

    public function test_bold_font_adds_font_weight_css(): void
    {
        $richText = new RichText;
        $run      = $richText->createTextRun('bold text');
        $run->getFont()->setBold(true);
        $this->sheet->getCell('A1')->setValue($richText);

        $this->assertStringContainsString('font-weight:bold', RichTextReader::toHtml($this->sheet->getCell('A1')));
    }

    public function test_italic_font_adds_font_style_css(): void
    {
        $richText = new RichText;
        $run      = $richText->createTextRun('italic text');
        $run->getFont()->setItalic(true);
        $this->sheet->getCell('A1')->setValue($richText);

        $this->assertStringContainsString('font-style:italic', RichTextReader::toHtml($this->sheet->getCell('A1')));
    }

    public function test_underline_adds_underline_text_decoration(): void
    {
        $richText = new RichText;
        $run      = $richText->createTextRun('underlined text');
        $run->getFont()->setUnderline(Font::UNDERLINE_SINGLE);
        $this->sheet->getCell('A1')->setValue($richText);

        $this->assertStringContainsString('text-decoration:underline', RichTextReader::toHtml($this->sheet->getCell('A1')));
    }

    public function test_strikethrough_adds_line_through_text_decoration(): void
    {
        $richText = new RichText;
        $run      = $richText->createTextRun('struck text');
        $run->getFont()->setStrikethrough(true);
        $this->sheet->getCell('A1')->setValue($richText);

        $this->assertStringContainsString('text-decoration:line-through', RichTextReader::toHtml($this->sheet->getCell('A1')));
    }

    public function test_underline_and_strikethrough_are_combined_in_text_decoration(): void
    {
        $richText = new RichText;
        $run      = $richText->createTextRun('combined');
        $run->getFont()->setUnderline(Font::UNDERLINE_SINGLE);
        $run->getFont()->setStrikethrough(true);
        $this->sheet->getCell('A1')->setValue($richText);

        $this->assertStringContainsString('text-decoration:underline line-through', RichTextReader::toHtml($this->sheet->getCell('A1')));
    }

    public function test_superscript_wraps_text_in_sup_tag(): void
    {
        $richText = new RichText;
        $run      = $richText->createTextRun('sup');
        $run->getFont()->setSuperscript(true);
        $this->sheet->getCell('A1')->setValue($richText);

        $result = RichTextReader::toHtml($this->sheet->getCell('A1'));

        $this->assertStringContainsString('<sup>sup</sup>', $result);
    }

    public function test_subscript_wraps_text_in_sub_tag(): void
    {
        $richText = new RichText;
        $run      = $richText->createTextRun('sub');
        $run->getFont()->setSubscript(true);
        $this->sheet->getCell('A1')->setValue($richText);

        $result = RichTextReader::toHtml($this->sheet->getCell('A1'));

        $this->assertStringContainsString('<sub>sub</sub>', $result);
    }

    public function test_multiple_elements_are_concatenated(): void
    {
        $richText = new RichText;
        $richText->createText('plain ');
        $run = $richText->createTextRun('bold');
        $run->getFont()->setBold(true);
        $this->sheet->getCell('A1')->setValue($richText);

        $result = RichTextReader::toHtml($this->sheet->getCell('A1'));

        $this->assertStringContainsString('plain ', $result);
        $this->assertStringContainsString('font-weight:bold', $result);
    }

    public function test_font_name_cannot_break_out_of_the_style_attribute(): void
    {
        $html = RichTextReader::toHtml($this->richTextCell('Arial" onmouseover="alert(document.domain)" x="'));

        $openingTag = substr($html, 0, (int) strpos($html, '>') + 1);

        $this->assertSame(2, substr_count($openingTag, '"'), $openingTag);
        $this->assertStringNotContainsString('onmouseover=', $html);
    }

    public function test_font_name_cannot_break_out_of_the_font_family_value(): void
    {
        $html = RichTextReader::toHtml($this->richTextCell("Arial'; background-image:url(https://example.com/x)"));

        $this->assertStringNotContainsString('background-image:', $html);
        $this->assertStringNotContainsString('url(', $html);
    }

    public function test_font_name_cannot_close_the_tag(): void
    {
        $html = RichTextReader::toHtml($this->richTextCell('Arial"><script>alert(1)</script>'));

        $this->assertStringNotContainsString('<script>', $html);
    }

    public function test_regular_font_names_are_kept(): void
    {
        $this->assertStringContainsString("font-family:'Times New Roman'", RichTextReader::toHtml($this->richTextCell('Times New Roman')));
        $this->assertStringContainsString("font-family:'ＭＳ Ｐゴシック'", RichTextReader::toHtml($this->richTextCell('ＭＳ Ｐゴシック')));
    }

    #[DataProvider('plainValues')]
    public function test_plain_cell_values_are_escaped(mixed $value, string $expected): void
    {
        $this->sheet->getCell('A1')->setValue($value);

        $this->assertSame($expected, RichTextReader::toHtml($this->sheet->getCell('A1')));
    }

    /**
     * @return Iterator<int<0, max>, array{mixed, string}>
     */
    public static function plainValues(): Iterator
    {
        yield ['<img src=x onerror="alert(1)">', '&lt;img src=x onerror=&quot;alert(1)&quot;&gt;'];
        yield ['normal text', 'normal text'];
        yield [10, '10'];
        yield [null, ''];
    }

    private function richTextCell(string $fontName): Cell
    {
        $richText = new RichText;
        $richText->createTextRun('imported cell text')->getFont()?->setName($fontName);
        $this->sheet->getCell('A1')->setValue($richText);

        return $this->sheet->getCell('A1');
    }
}
