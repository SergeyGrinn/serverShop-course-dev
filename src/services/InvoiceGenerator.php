<?php

/**
 * Renders order invoices to PDF with Dompdf.
 */
class InvoiceGenerator {
    /**
     * Renders the invoice template for an order into PDF bytes.
     *
     * @param array $order Order data with items (used by the template).
     * @param array $invoiceSeller Seller details shown on the invoice.
     * @return string Binary PDF content.
     */
    public static function renderPdf(array $order, array $invoiceSeller): string {
        require_once base_path('vendor/autoload.php');

        // Map internal status codes to readable labels, with a fallback for unknown ones
        $statusLabels = [
            'pending' => 'Awaiting Payment',
            'paid' => 'Paid',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
        $orderStatus = $statusLabels[$order['status']] ?? ucfirst($order['status']);

        // Capture the template output as HTML instead of sending it to the browser
        ob_start();
        require base_path('templates/invoice.php');
        $invoiceHtml = ob_get_clean();

        $dompdf = new Dompdf\Dompdf();
        $dompdf->loadHtml($invoiceHtml, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
