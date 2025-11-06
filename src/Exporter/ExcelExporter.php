<?php

namespace App\Exporter;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExcelExporter
{
    public static function export(array $publications, string $projectName): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setTitle('Publications');

        // Header row
        $headers = [
            'A1' => 'No',
            'B1' => 'Citation Key',
            'C1' => 'Type',
            'D1' => 'Title',
            'E1' => 'Authors',
            'F1' => 'Year',
            'G1' => 'Journal/Source',
            'H1' => 'Volume',
            'I1' => 'Number',
            'J1' => 'Pages',
            'K1' => 'Publisher',
            'L1' => 'DOI',
            'M1' => 'URL',
            'N1' => 'Abstract',
            'O1' => 'Keywords',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style header row
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ];

        $sheet->getStyle('A1:O1')->applyFromArray($headerStyle);

        // Data rows
        $row = 2;
        foreach ($publications as $index => $pub) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $pub['citation_key'] ?? '');
            $sheet->setCellValue('C' . $row, $pub['type'] ?? '');
            $sheet->setCellValue('D' . $row, $pub['title'] ?? '');
            $sheet->setCellValue('E' . $row, $pub['authors'] ?? '');
            $sheet->setCellValue('F' . $row, $pub['year'] ?? '');
            $sheet->setCellValue('G' . $row, $pub['journal'] ?? '');
            $sheet->setCellValue('H' . $row, $pub['volume'] ?? '');
            $sheet->setCellValue('I' . $row, $pub['number'] ?? '');
            $sheet->setCellValue('J' . $row, $pub['pages'] ?? '');
            $sheet->setCellValue('K' . $row, $pub['publisher'] ?? '');
            $sheet->setCellValue('L' . $row, $pub['doi'] ?? '');
            $sheet->setCellValue('M' . $row, $pub['url'] ?? '');
            $sheet->setCellValue('N' . $row, $pub['abstract'] ?? '');
            $sheet->setCellValue('O' . $row, $pub['keywords'] ?? '');

            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set max width for some columns
        $sheet->getColumnDimension('D')->setWidth(50); // Title
        $sheet->getColumnDimension('E')->setWidth(40); // Authors
        $sheet->getColumnDimension('N')->setWidth(60); // Abstract

        // Wrap text for abstract
        $sheet->getStyle('N2:N' . ($row - 1))->getAlignment()->setWrapText(true);

        // Generate filename
        $filename = 'data/uploads/' . preg_replace('/[^a-z0-9]+/', '_', strtolower($projectName)) . '_' . time() . '.xlsx';

        // Save file
        $writer = new Xlsx($spreadsheet);
        $writer->save($filename);

        return $filename;
    }
}
