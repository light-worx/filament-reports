# Filament Reports Plugin

A Filament plugin for creating pixel-perfect PDF reports using FPDF.

## Installation

### 1. Set up the plugin in your project

If developing locally within your project:

```bash
mkdir -p packages/yourvendor/filament-reports
# Copy all plugin files to this directory
```

### 2. Add to composer.json

Add to the `repositories` section:

```json
"repositories": [
    {
        "type": "path",
        "url": "./packages/yourvendor/filament-reports"
    }
]
```

Add to the `require` section:

```json
"yourvendor/filament-reports": "*"
```

### 3. Install dependencies

```bash
composer require setasign/fpdf
composer update yourvendor/filament-reports
```

### 4. Publish configuration

```bash
php artisan vendor:publish --tag="filament-reports-config"
```

### 5. Register the plugin

In your `AdminPanelProvider` or panel configuration:

```php
use YourVendor\FilamentReports\FilamentReportsPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentReportsPlugin::make(),
        ]);
}
```

## Creating Your First Report

### 1. Create a Report Class

```php
<?php

namespace App\Reports;

use YourVendor\FilamentReports\Reports\BaseReport;
use YourVendor\FilamentReports\Reports\Concerns\HasSections;
use YourVendor\FilamentReports\Reports\Concerns\HasTables;

class InvoiceReport extends BaseReport
{
    use HasSections, HasTables;

    protected $invoice;

    public function setRecord($invoice): static
    {
        $this->invoice = $invoice;
        return $this;
    }

    public function generate(): void
    {
        $this->setReportTitle('Invoice #' . $this->invoice->number);
        $this->AddPage();

        // Customer Information
        $this->renderSectionTitle('Customer Information');
        $this->renderKeyValueBlock([
            'Customer' => $this->invoice->customer->name,
            'Email' => $this->invoice->customer->email,
            'Date' => $this->invoice->date->format('Y-m-d'),
        ]);

        // Line Items
        $this->renderSectionTitle('Items');
        $this->renderTable(
            headers: ['Description', 'Quantity', 'Price', 'Total'],
            rows: $this->invoice->items->map(fn($item) => [
                $item->description,
                $item->quantity,
                '$' . number_format($item->price, 2),
                '$' . number_format($item->total, 2),
            ])->toArray(),
            columnWidths: [80, 30, 30, 30]
        );
    }
}
```

### 2. Add Export Action to Your Resource

```php
use YourVendor\FilamentReports\Actions\ExportPdfAction;
use App\Reports\InvoiceReport;

public static function table(Table $table): Table
{
    return $table
        ->columns([
            // your columns
        ])
        ->actions([
            ExportPdfAction::make()
                ->reportClass(InvoiceReport::class),
        ]);
}
```

## Available Traits

### HasTables

Provides table rendering capabilities:

- `renderTable(array $headers, array $rows, ?array $columnWidths = null)`: Full-featured table with headers
- `renderSimpleTable(array $data, array $columnWidths)`: Simple table without headers

### HasSections

Provides section and content rendering:

- `renderSectionTitle(string $title, int $size = 14)`: Render a section header
- `renderKeyValue(string $key, string $value, int $keyWidth = 50)`: Single key-value pair
- `renderKeyValueBlock(array $data, int $keyWidth = 50)`: Multiple key-value pairs
- `renderText(string $text, bool $bold = false)`: Text paragraph
- `renderDivider()`: Horizontal line divider

## Configuration

Edit `config/filament-reports.php` to customize:

- Default fonts and sizes
- Page margins and orientation
- Branding (company name, logo, colors)
- Header and footer settings
- Table styling

## Testing

To test your report:

```php
$report = new InvoiceReport();
$report->setRecord($invoice);
$report->generate();
$report->output('test.pdf', 'I'); // 'I' = inline, 'D' = download, 'F' = save to file
```

## Next Steps

- Create custom traits for specialized content (charts, signatures, etc.)
- Add bulk export actions
- Implement queued report generation for large datasets
- Add localization support

## License

MIT