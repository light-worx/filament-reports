<?php

namespace Lightworx\FilamentReports\Reports\Examples;

use Lightworx\FilamentReports\Reports\BaseReport;
use Lightworx\FilamentReports\Reports\Concerns\HasSections;
use Lightworx\FilamentReports\Reports\Concerns\HasTables;

class ExampleReport extends BaseReport
{
    use HasSections, HasTables;

    protected $record;

    public function setRecord($record): static
    {
        $this->record = $record;
        return $this;
    }

    public function generate(): void
    {
        $this->setReportTitle('Example Report');
        $this->AddPage();

        // Section 1: Basic Information
        $this->renderSectionTitle('Basic Information');
        $this->renderKeyValueBlock([
            'ID' => $this->record->id ?? 'N/A',
            'Created At' => $this->record->created_at?->format('Y-m-d H:i:s') ?? 'N/A',
            'Updated At' => $this->record->updated_at?->format('Y-m-d H:i:s') ?? 'N/A',
        ]);

        $this->renderDivider();

        // Section 2: Description
        $this->renderSectionTitle('Description');
        $this->renderText(
            'This is an example report demonstrating the basic capabilities of the filament-reports plugin. ' .
            'You can customize this template to create your own reports.'
        );

        $this->Ln(5);

        // Section 3: Sample Table
        $this->renderSectionTitle('Sample Data Table');
        $this->renderTable(
            headers: ['Column 1', 'Column 2', 'Column 3', 'Column 4'],
            rows: [
                ['Data 1A', 'Data 1B', 'Data 1C', 'Data 1D'],
                ['Data 2A', 'Data 2B', 'Data 2C', 'Data 2D'],
                ['Data 3A', 'Data 3B', 'Data 3C', 'Data 3D'],
                ['Data 4A', 'Data 4B', 'Data 4C', 'Data 4D'],
            ],
            columnWidths: [40, 50, 40, 50]
        );

        // Additional content can be added here
    }
}