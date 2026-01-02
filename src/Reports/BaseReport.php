<?php

namespace Lightworx\FilamentReports\Reports;

use tFPDF;

abstract class BaseReport extends tFPDF
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

        $this->SetY($this->config['page']['margins']['top']);
        
        // Logo if configured
        if ($logoPath = $this->config['branding']['logo_path']) {
            if (file_exists($logoPath)) {
                $this->Image($logoPath,12,6,55);
            }
        }

        // Report title
        if ($this->reportTitle) {
            $this->setY(10);
            $this->SetFont($this->config['default_font']['family'], 'B', 18);
            $this->Cell(0, 10, $this->reportTitle, 0, 0, 'R');
            $this->Ln(7);
            $this->SetFont($this->config['default_font']['family'], '', 12);
            $this->Cell(0, 10, date('j F Y'), 0, 0, 'R');
        }
        $this->setY(26);
        $this->Line(
            $this->config['page']['margins']['left'],
            $this->GetY(),
            $this->GetPageWidth() - $this->config['page']['margins']['right'],
            $this->GetY()
        );
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

    public function handle()
    {
        $this->generate();
        
        $filename = $this->getFilename();
        $mode = $this->getOutputMode();
        
        return response($this->Output($mode, $filename))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', $this->getContentDisposition($filename));
    }

    protected function getFilename(): string
    {
        return 'report.pdf';
    }

    protected function getOutputMode(): string
    {
        return request()->has('download') ? 'D' : 'I';
    }

    protected function getContentDisposition(string $filename): string
    {
        $mode = $this->getOutputMode();
        return $mode === 'D' ? "attachment; filename=\"{$filename}\"" : "inline; filename=\"{$filename}\"";
    }

    public function Output($dest='', $name='', $isUTF8=false)
    {
        $this->AliasNbPages();
        return parent::Output($dest, $name, $isUTF8);
    }

    public function convert_smart_quotes($string) {
        $search = array(chr(0xe2) . chr(0x80) . chr(0x98),
                        chr(0xe2) . chr(0x80) . chr(0x99),
                        chr(0xe2) . chr(0x80) . chr(0x9c),
                        chr(0xe2) . chr(0x80) . chr(0x9d),
                        chr(0xe2) . chr(0x80) . chr(0x93),
                        chr(0xe2) . chr(0x80) . chr(0x94),
                        chr(226) . chr(128) . chr(153),
                        'â€™','â€œ','â€<9d>','â€"','Â  ');
        $replace = array("'","'",'"','"',' - ',' - ',"'","'",'"','"',' - ',' ');
    
        return str_replace($search, $replace, $string);
    }
}