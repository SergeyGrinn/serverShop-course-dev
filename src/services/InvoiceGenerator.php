<?php

class InvoiceGenerator {
    public static function renderPdf(array $order, array $invoiceSeller): string {
        require_once base_path('vendor/autoload.php');

        $statusLabels = [
            'pending' => 'Awaiting Payment',
            'paid' => 'Paid',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
        $orderStatus = $statusLabels[$order['status']] ?? ucfirst($order['status']);

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
