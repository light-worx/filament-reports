<?php

namespace Lightworx\FilamentReports\Actions;

use Filament\Actions\Action;
use Lightworx\FilamentReports\Reports\BaseReport;

class ExportPdfAction extends Action
{
    protected string $reportClass;

    public static function getDefaultName(): ?string
    {
        return 'export_pdf';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Export PDF');
        
        $this->icon('heroicon-o-document-arrow-down');

        $this->action(function ($record) {
            if (!isset($this->reportClass)) {
                throw new \Exception('Report class not set. Use reportClass() method.');
            }

            $report = new $this->reportClass();
            
            if (!$report instanceof BaseReport) {
                throw new \Exception('Report class must extend BaseReport.');
            }

            $report->setRecord($record);
            $report->generate();
            
            $filename = $this->getFilename($record);
            
            return response()->streamDownload(function () use ($report, $filename) {
                echo $report->output($filename, 'S');
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
        });
    }

    public function reportClass(string $class): static
    {
        $this->reportClass = $class;
        return $this;
    }

    protected function getFilename($record): string
    {
        $timestamp = now()->format('Y-m-d_His');
        $modelName = class_basename($record);
        
        return "{$modelName}_{$record->id}_{$timestamp}.pdf";
    }

    public function filename(string | \Closure $filename): static
    {
        $this->evaluate($filename);
        return $this;
    }
}