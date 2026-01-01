<?php

namespace Lightworx\FilamentReports\Reports\Concerns;

trait HasSections
{
    protected function renderSectionTitle(string $title, int $size = 14): void
    {
        $this->SetFont($this->config['default_font']['family'], 'B', $size);
        $this->SetTextColor(...$this->config['branding']['primary_color']);
        $this->Cell(0, 10, $title, 0, 1, 'L');
        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->config['default_font']['family'], '', $this->config['default_font']['size']);
        $this->Ln(2);
    }

    protected function renderKeyValue(string $key, string $value, int $keyWidth = 50): void
    {
        $this->SetFont($this->config['default_font']['family'], 'B', $this->config['default_font']['size']);
        $this->Cell($keyWidth, 6, $key . ':', 0, 0, 'L');
        
        $this->SetFont($this->config['default_font']['family'], '', $this->config['default_font']['size']);
        $this->Cell(0, 6, $value, 0, 1, 'L');
    }

    protected function renderKeyValueBlock(array $data, int $keyWidth = 50): void
    {
        foreach ($data as $key => $value) {
            $this->renderKeyValue($key, $value, $keyWidth);
        }
        $this->Ln(3);
    }

    protected function renderText(string $text, bool $bold = false): void
    {
        $style = $bold ? 'B' : '';
        $this->SetFont($this->config['default_font']['family'], $style, $this->config['default_font']['size']);
        $this->MultiCell(0, 5, $text);
        $this->Ln(2);
    }

    protected function renderDivider(): void
    {
        $this->setDrawColor(...$this->config['branding']['secondary_color']);
        $this->Line(
            $this->config['page']['margins']['left'],
            $this->GetY(),
            $this->GetPageWidth() - $this->config['page']['margins']['right'],
            $this->GetY()
        );
        $this->Ln(5);
    }
}