<?php

namespace App\Service;

// Include TCPDF configuration constants
require_once dirname(__DIR__, 2) . '/vendor/tecnickcom/tcpdf/config/tcpdf_config.php';

class GroceryListPdfService
{
    private TunisianPriceService $priceService;

    public function __construct()
    {
        $this->priceService = new TunisianPriceService();
    }

    /**
     * Format price in Tunisian Dinar format (e.g., 30dt500)
     */
    private function formatPrice(float $price): string
    {
        return $this->priceService->formatPrice($price);
    }

    /**
     * Generate a personalized PDF grocery list with quantities, prices and calories
     */
    public function generateGroceryListPdf(array $groceryItems, string $userName = 'Client'): string
    {
        // Create new PDF document
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('WellCare Connect');
        $pdf->SetAuthor('WellCare Connect');
        $pdf->SetTitle('Liste de courses');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(15, 15, 15);

        // Set auto page breaks
        $pdf->SetAutoPageBreak(true, 15);

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', '', 12);

        // Header
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->SetTextColor(0, 167, 144); // WellCare green
        $pdf->Cell(0, 10, 'Liste de courses', 0, true, 'C', 0, '', 0, false, 'T', 'C');
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(128, 128, 128);
        $pdf->Cell(0, 5, 'Générée le ' . date('d/m/Y à H:i'), 0, true, 'C', 0, '', 0, false, 'T', 'C');
        
        $pdf->Ln(10);

        // Reset text color
        $pdf->SetTextColor(0, 0, 0);

        // Calculate totals
        $totalGeneral = 0;
        $totalCalories = 0;

        // Group items by category
        $groupedItems = $this->groupByCategoryWithQuantities($groceryItems);

        // Define category colors
        $categoryColors = [
            'Fruits & Légumes' => [34, 197, 94],
            'Produits laitiers' => [251, 191, 36],
            'Viandes & Poissons' => [239, 68, 68],
            'Pain & Céréales' => [234, 179, 8],
            'Boissons' => [59, 130, 246],
            'Snacks' => [168, 85, 247],
            'Huiles & Graisses' => [156, 163, 175],
            'Épices & Assaisonnements' => [249, 115, 22],
            'Conserves' => [236, 72, 153],
            'Sucre & Confiserie' => [236, 185, 11],
            'Produits secs' => [139, 92, 246],
            'Autres' => [107, 114, 128],
        ];

        foreach ($groupedItems as $category => $items) {
            // Skip empty categories
            if (empty($items)) {
                continue;
            }

            // Check if we need a new page
            if ($pdf->GetY() > 230) {
                $pdf->AddPage();
            }

            // Category header
            $color = $categoryColors[$category] ?? [107, 114, 128];
            $pdf->SetFillColor($color[0], $color[1], $color[2]);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Cell(0, 7, $category, 1, true, 'L', 1, '', 0, false, 'T', 'C');

            // Items table header
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->SetFillColor(245, 247, 250);
            
            // Header: Article | Qté | Cal. | Prix U. | Total
            $pdf->Cell(65, 6, 'Article', 1, 0, 'L', 1);
            $pdf->Cell(25, 6, 'Quantite', 1, 0, 'C', 1);
            $pdf->Cell(25, 6, 'Calories', 1, 0, 'R', 1);
            $pdf->Cell(25, 6, 'Prix U.', 1, 0, 'R', 1);
            $pdf->Cell(30, 6, 'Total', 1, true, 'R', 1);

            // Items
            $pdf->SetFont('helvetica', '', 8);
            $categoryTotal = 0;
            $categoryCalories = 0;
            
            foreach ($items as $item) {
                $name = $item['name'] ?? 'Article';
                $quantity = floatval($item['quantity'] ?? 1);
                $unit = $item['unit'] ?? 'piece';
                
                // Get price
                $priceData = $this->priceService->getPrice($name);
                $unitPrice = $priceData['price'] ?? 0;
                $calories = $priceData['calories'] ?? 0;
                $totalItem = $unitPrice * $quantity;
                $itemCalories = $calories * $quantity;
                
                $categoryTotal += $totalItem;
                $categoryCalories += $itemCalories;
                
                // Alternating row colors
                $pdf->SetFillColor(255, 255, 255);
                
                // Item name
                $pdf->Cell(65, 5, $name, 1, 0, 'L', 1);
                // Quantity
                $quantityStr = $quantity . ' ' . $unit;
                $pdf->Cell(25, 5, $quantityStr, 1, 0, 'C', 1);
                // Calories
                $calStr = $itemCalories > 0 ? number_format($itemCalories, 0, '', ' ') . ' kcal' : '-';
                $pdf->Cell(25, 5, $calStr, 1, 0, 'R', 1);
                // Unit price
                $pdf->Cell(25, 5, $this->formatPrice($unitPrice), 1, 0, 'R', 1);
                // Total
                $pdf->Cell(30, 5, $this->formatPrice($totalItem), 1, true, 'R', 1);
            }

            // Category total
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(140, 6, 'Total ' . $category . ' (' . number_format($categoryCalories, 0, '', ' ') . ' kcal):', 1, 0, 'R', 0);
            $pdf->SetFillColor(254, 249, 195);
            $pdf->Cell(40, 6, $this->formatPrice($categoryTotal), 1, true, 'R', 1);

            $totalGeneral += $categoryTotal;
            $totalCalories += $categoryCalories;
            $pdf->Ln(3);
        }

        // Grand total
        $pdf->SetFont('helvetica', 'B', 13);
        $pdf->SetFillColor(0, 167, 144);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(130, 10, 'TOTAL GENERAL (' . number_format($totalCalories, 0, '', ' ') . ' kcal):', 1, 0, 'R', 1);
        $pdf->Cell(50, 10, $this->formatPrice($totalGeneral), 1, true, 'R', 1);

        // Footer note
        $pdf->Ln(12);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(128, 128, 128);
        $pdf->Cell(0, 5, 'Prix indicatifs bases sur les prix moyens du marche tunisien - WellCare Connect', 0, true, 'C');

        return $pdf->Output('liste-courses-wellcare.pdf', 'S');
    }

    /**
     * Group items by category with quantities
     */
    private function groupByCategoryWithQuantities(array $groceryItems): array
    {
        $grouped = [];
        
        foreach ($groceryItems as $item) {
            $name = $item['name'] ?? '';
            $quantity = floatval($item['quantity'] ?? 1);
            $selected = $item['selected'] ?? true;
            
            // Skip unselected items
            if (!$selected) {
                continue;
            }
            
            if (empty($name)) {
                continue;
            }
            
            // Get category from price service
            $priceData = $this->priceService->getPrice($name);
            $category = $priceData['category'] ?? 'Autres';
            $unit = $priceData['unit'] ?? 'piece';
            
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            
            $grouped[$category][] = [
                'name' => $name,
                'quantity' => $quantity,
                'unit' => $unit,
            ];
        }
        
        return $grouped;
    }

    /**
     * Get sample grocery list with quantities for demonstration
     */
    public function getSampleGroceryList(): array
    {
        return [
            ['name' => 'Pommes', 'quantity' => 2, 'unit' => 'kg', 'selected' => true],
            ['name' => 'Bananes', 'quantity' => 1, 'unit' => 'kg', 'selected' => true],
            ['name' => 'Oranges', 'quantity' => 3, 'unit' => 'kg', 'selected' => true],
            ['name' => 'Tomates', 'quantity' => 2, 'unit' => 'kg', 'selected' => true],
            ['name' => 'Pommes de terre', 'quantity' => 3, 'unit' => 'kg', 'selected' => true],
            ['name' => 'Oignons', 'quantity' => 1, 'unit' => 'kg', 'selected' => true],
            ['name' => 'Carottes', 'quantity' => 1, 'unit' => 'kg', 'selected' => true],
            ['name' => 'Salade', 'quantity' => 2, 'unit' => 'piece', 'selected' => true],
            ['name' => 'Poulet', 'quantity' => 1.5, 'unit' => 'kg', 'selected' => true],
            ['name' => 'Boeuf', 'quantity' => 1, 'unit' => 'kg', 'selected' => true],
            ['name' => 'Poisson rouge', 'quantity' => 0.5, 'unit' => 'kg', 'selected' => true],
            ['name' => 'Lait', 'quantity' => 4, 'unit' => 'L', 'selected' => true],
            ['name' => 'Yaourt nature', 'quantity' => 8, 'unit' => 'piece', 'selected' => true],
            ['name' => 'Fromage blanc', 'quantity' => 0.5, 'unit' => 'kg', 'selected' => true],
            ['name' => 'Oeufs', 'quantity' => 12, 'unit' => 'piece', 'selected' => true],
            ['name' => 'Pain francais', 'quantity' => 3, 'unit' => 'pain', 'selected' => true],
            ['name' => 'Riz', 'quantity' => 2, 'unit' => 'kg', 'selected' => true],
            ['name' => 'Pates', 'quantity' => 1, 'unit' => 'kg', 'selected' => true],
            ['name' => 'Farine', 'quantity' => 2, 'unit' => 'kg', 'selected' => true],
            ['name' => 'Eau minerale', 'quantity' => 6, 'unit' => '1.5L', 'selected' => true],
            ['name' => 'Jus d\'orange', 'quantity' => 2, 'unit' => '1L', 'selected' => true],
            ['name' => 'Huile d\'olive', 'quantity' => 2, 'unit' => 'L', 'selected' => true],
            ['name' => 'Sel', 'quantity' => 1, 'unit' => 'kg', 'selected' => true],
            ['name' => 'Sucre', 'quantity' => 2, 'unit' => 'kg', 'selected' => true],
        ];
    }

    /**
     * Get all available items for the grocery list
     */
    public function getAllAvailableItems(): array
    {
        return $this->priceService->getAllPrices();
    }
}
