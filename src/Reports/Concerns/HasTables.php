<?php

namespace Lightworx\FilamentReports\Reports\Concerns;

trait HasTables
{
    protected function renderTable(array $headers, array $rows, ?array $columnWidths = null): void
    {
        $config = config('filament-reports.table');
        
        // Calculate column widths if not provided
        if (!$columnWidths) {
            $availableWidth = $this->GetPageWidth() - 
                $this->config['page']['margins']['left'] - 
                $this->config['page']['margins']['right'];
            $columnWidth = $availableWidth / count($headers);
            $columnWidths = array_fill(0, count($headers), $columnWidth);
        }

        // Render header
        $this->SetFillColor(...$config['header_background']);
        $this->SetTextColor(...$config['header_text_color']);
        $this->SetDrawColor(...$config['border_color']);
        $this->SetFont($this->config['default_font']['family'], 'B', $this->config['default_font']['size']);

        foreach ($headers as $i => $header) {
            $this->Cell($columnWidths[$i], $config['header_height'], $header, 1, 0, 'C', true);
        }
        $this->Ln();

        // Render rows
        $this->SetFont($this->config['default_font']['family'], '', $this->config['default_font']['size']);
        $this->SetTextColor(0, 0, 0);
        
        $fill = false;
        foreach ($rows as $row) {
            if ($config['alternating_rows']) {
                if ($fill) {
                    $this->SetFillColor(...$config['alternating_color']);
                } else {
                    $this->SetFillColor(255, 255, 255);
                }
            }

            foreach ($row as $i => $cell) {
                $this->Cell(
                    $columnWidths[$i], 
                    $config['row_height'], 
                    $cell, 
                    1, 
                    0, 
                    'L', 
                    $config['alternating_rows']
                );
            }
            $this->Ln();
            
            if ($config['alternating_rows']) {
                $fill = !$fill;
            }
        }
        
        $this->Ln(5);
    }

    protected function renderSimpleTable(array $data, array $columnWidths): void
    {
        $config = config('filament-reports.table');
        $this->SetDrawColor(...$config['border_color']);
        
        foreach ($data as $row) {
            foreach ($row as $i => $cell) {
                $this->Cell($columnWidths[$i], $config['row_height'], $cell, 1);
            }
            $this->Ln();
        }
        
        $this->Ln(5);
    }
}