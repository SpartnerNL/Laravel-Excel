<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class WithChartsTest extends TestCase
{
    public function test_can_export_with_a_single_chart(): void
    {
        $export = new class implements Export, FromArray, WithCharts, WithTitle
        {
            use Exportable;

            public function array(): array
            {
                return [[1], [2], [3]];
            }

            public function title(): string
            {
                return 'Sheet1';
            }

            public function charts(): Chart
            {
                return $this->buildChart('chart1');
            }

            private function buildChart(string $name): Chart
            {
                $series = new DataSeries(
                    DataSeries::TYPE_BARCHART,
                    DataSeries::GROUPING_CLUSTERED,
                    [0],
                    plotValues: [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Sheet1!$A$1:$A$3', null, 3)]
                );

                $chart = new Chart($name, new Title('Chart title'), new Legend, new PlotArea(null, [$series]));
                $chart->setTopLeftPosition('C1');
                $chart->setBottomRightPosition('J15');

                return $chart;
            }
        };

        $export->store('with-charts.xlsx');

        $sheet = $this->readWithCharts(__DIR__ . '/../Data/Disks/Local/with-charts.xlsx');

        $this->assertCount(1, $sheet->getChartCollection());
        $this->assertSame('chart1', $sheet->getChartByIndex('0')->getName());
    }

    public function test_can_export_with_multiple_charts(): void
    {
        $export = new class implements Export, FromArray, WithCharts, WithTitle
        {
            use Exportable;

            public function array(): array
            {
                return [[1], [2], [3]];
            }

            public function title(): string
            {
                return 'Sheet1';
            }

            /**
             * @return Chart[]
             */
            public function charts(): array
            {
                return [
                    $this->buildChart('chart1'),
                    $this->buildChart('chart2'),
                ];
            }

            private function buildChart(string $name): Chart
            {
                $series = new DataSeries(
                    DataSeries::TYPE_BARCHART,
                    DataSeries::GROUPING_CLUSTERED,
                    [0],
                    plotValues: [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Sheet1!$A$1:$A$3', null, 3)]
                );

                $chart = new Chart($name, new Title('Chart title'), new Legend, new PlotArea(null, [$series]));
                $chart->setTopLeftPosition('C1');
                $chart->setBottomRightPosition('J15');

                return $chart;
            }
        };

        $export->store('with-charts-multiple.xlsx');

        $sheet = $this->readWithCharts(__DIR__ . '/../Data/Disks/Local/with-charts-multiple.xlsx');

        $this->assertCount(2, $sheet->getChartCollection());
        $this->assertSame('chart1', $sheet->getChartByIndex('0')->getName());
        $this->assertSame('chart2', $sheet->getChartByIndex('1')->getName());
    }

    private function readWithCharts(string $filePath): Worksheet
    {
        $reader = IOFactory::createReader('Xlsx');
        $reader->setIncludeCharts(true);

        $spreadsheet = $reader->load($filePath);

        return $spreadsheet->getActiveSheet();
    }
}
