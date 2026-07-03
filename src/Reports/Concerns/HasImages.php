<?php

namespace Lightworx\FilamentReports\Reports\Concerns;

trait HasImages
{
    protected function renderImage(string $path, ?int $maxWidth = null, ?string $caption = null): void
    {
        if (!file_exists($path)) {
            return;
        }

        $maxWidth = $maxWidth ?? (
            $this->GetPageWidth()
            - $this->config['page']['margins']['left']
            - $this->config['page']['margins']['right']
        );

        [$pxWidth, $pxHeight] = getimagesize($path);
        $ratio = $pxHeight / $pxWidth;
        $displayWidth = $maxWidth;
        $displayHeight = $displayWidth * $ratio;

        $maxHeight = $this->GetPageHeight()
            - $this->config['page']['margins']['top']
            - $this->config['page']['margins']['bottom']
            - 20;

        if ($displayHeight > $maxHeight) {
            $displayHeight = $maxHeight;
            $displayWidth = $displayHeight / $ratio;
        }

        // Image() doesn't auto-paginate like Cell/MultiCell — check manually
        if ($this->GetY() + $displayHeight > $this->GetPageHeight() - $this->config['page']['margins']['bottom']) {
            $this->AddPage();
        }

        $this->Image($path, $this->GetX(), $this->GetY(), $displayWidth, $displayHeight);
        $this->Ln($displayHeight + 3);

        if ($caption) {
            $this->SetFont($this->config['default_font']['family'], 'I', 9);
            $this->Cell(0, 5, $caption, 0, 1, 'L');
            $this->SetFont($this->config['default_font']['family'], '', $this->config['default_font']['size']);
            $this->Ln(2);
        }
    }
}