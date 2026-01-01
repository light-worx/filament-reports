<?php

namespace Lightworx\FilamentReports\Reports;

abstract class BaseReport extends TFPDF
{
    protected string $reportTitle = '';
    protected array $config = [];

    public function __construct()
    {
        $this->config = config('filament-reports');
        
        $orientation = $this->config['page']['orientation'];
        $unit = $this->config['page']['unit'];
        $size = $this->config['page']['size'];

        parent::__construct($orientation, $unit, $size);
        
        $this->SetAutoPageBreak(true, $this->config['page']['margins']['bottom']);
        $this->SetMargins(
            $this->config['page']['margins']['left'],
            $this->config['page']['margins']['top'],
            $this->config['page']['margins']['right']
        );
        
        $this->SetFont(
            $this->config['default_font']['family'],
            '',
            $this->config['default_font']['size']
        );
    }

    public function setReportTitle(string $title): static
    {
        $this->reportTitle = $title;
        return $this;
    }

    public function Header(): void
    {
        if (!$this->config['header']['enabled']) {
            return;
        }

        $this->SetY($this->config['page']['margins']['top'] - 5);
        
        // Logo if configured
        if ($logoPath = $this->config['branding']['logo_path']) {
            if (file_exists($logoPath)) {
                $this->Image($logoPath, 10, 6, 30);
            }
        }

        // Company name
        $this->SetFont($this->config['default_font']['family'], 'B', 16);
        $this->Cell(0, 10, $this->config['branding']['company_name'], 0, 0, 'R');
        $this->Ln(5);

        // Report title
        if ($this->reportTitle) {
            $this->SetFont($this->config['default_font']['family'], 'B', 12);
            $this->Cell(0, 10, $this->reportTitle, 0, 0, 'R');
            $this->Ln(5);
        }

        // Horizontal line
        $this->setDrawColor(...$this->config['branding']['secondary_color']);
        $this->Line(
            $this->config['page']['margins']['left'],
            $this->GetY(),
            $this->GetPageWidth() - $this->config['page']['margins']['right'],
            $this->GetY()
        );
        $this->Ln(5);
    }

    public function Footer(): void
    {
        if (!$this->config['footer']['enabled']) {
            return;
        }

        $this->SetY(-$this->config['footer']['height']);
        
        // Horizontal line
        $this->setDrawColor(...$this->config['branding']['secondary_color']);
        $this->Line(
            $this->config['page']['margins']['left'],
            $this->GetY(),
            $this->GetPageWidth() - $this->config['page']['margins']['right'],
            $this->GetY()
        );
        $this->Ln(2);

        $this->SetFont($this->config['default_font']['family'], 'I', 8);
        
        // Date on left
        $this->Cell(0, 5, date('Y-m-d H:i:s'), 0, 0, 'L');
        
        // Page numbers on right
        if ($this->config['footer']['show_page_numbers']) {
            $this->Cell(0, 5, 'Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'R');
        }
    }

    abstract public function generate(): void;

    public function output(string $filename = '', string $dest = 'I'): string
    {
        $this->AliasNbPages();
        return parent::Output($dest, $filename);
    }
}